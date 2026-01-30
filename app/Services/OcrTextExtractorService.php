<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Servicio para extraer texto de archivos PDF/Imagen usando un motor de OCR externo (ej: OCR.space)
 * y un fallback con la API de Gemini para extracción de datos.
 */
class OcrTextExtractorService
{
    // OBTENER ESTA CLAVE DE LAS CONFIGURACIONES DE ENTORNO (.env) Y REEMPLAZAR EL VALOR
    protected const OCR_API_KEY = 'K86466972688957';
    protected const OCR_ENDPOINT = 'https://api.ocr.space/parse/image';

    // Configuración para el Fallback con Gemini
    protected const GEMINI_API_KEY = ''; // Dejar vacío; el entorno lo proporcionará
    protected const GEMINI_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent';

    /**
     * Esquema JSON para la extracción de datos del CV (lo que esperamos de Gemini).
     */
    protected const CV_JSON_SCHEMA = [
        'type' => 'OBJECT',
        'properties' => [
            'nombre' => ['type' => 'STRING', 'description' => 'Nombre completo del candidato.'],
            'contacto' => ['type' => 'STRING', 'description' => 'Teléfono, correo electrónico y dirección.'],
            'resumen' => ['type' => 'STRING', 'description' => 'Un breve resumen de la sección Sobre Mí o Perfil.'],
            'experiencia' => [
                'type' => 'ARRAY',
                'description' => 'Lista de experiencias laborales. Cada elemento debe ser un objeto.',
                'items' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'puesto' => ['type' => 'STRING'],
                        'empresa' => ['type' => 'STRING'],
                        'descripcion' => ['type' => 'STRING'],
                    ]
                ]
            ],
            'formacion' => [
                'type' => 'ARRAY',
                'description' => 'Lista de títulos y formación académica.',
                'items' => [
                    'type' => 'STRING'
                ]
            ],
            'habilidades' => [
                'type' => 'ARRAY',
                'description' => 'Lista de habilidades técnicas, herramientas e idiomas.',
                'items' => [
                    'type' => 'STRING'
                ]
            ],
        ],
        'propertyOrdering' => ['nombre', 'contacto', 'resumen', 'experiencia', 'formacion', 'habilidades']
    ];

    /**
     * Extrae texto de un PDF usando el servicio de OCR.
     *
     * @param string $filePath Ruta del archivo en el disco 'private_cvs'.
     * @return string El texto extraído o una cadena vacía en caso de fallo.
     * @throws Exception Si hay un error en la comunicación o la API responde con un error.
     */
    public function extractFromPdf(string $filePath): string
    {
        // ... (El código de OCR.space sigue siendo el mismo aquí)
        $absolutePath = Storage::disk('private_cvs')->path($filePath);

        if (!file_exists($absolutePath)) {
            throw new Exception("El archivo para OCR no existe: " . $filePath);
        }

        try {
            $fileContent = file_get_contents($absolutePath);

            $response = Http::timeout(60)
                ->asMultipart()
                ->post(self::OCR_ENDPOINT, [
                    ['name' => 'apikey', 'contents' => self::OCR_API_KEY],
                    ['name' => 'language', 'contents' => 'spa'],
                    ['name' => 'filetype', 'contents' => 'PDF'], // Corregido: Usamos 'filetype'
                    ['name' => 'isOverlayRequired', 'contents' => 'false'],
                    ['name' => 'file', 'contents' => $fileContent, 'filename' => basename($filePath)],
                ]);

            if (!$response->successful()) {
                throw new Exception("La llamada al servicio OCR falló con código: " . $response->status());
            }

            $result = $response->json();

            if ($result['IsErroredOnProcessing']) {
                $errorMessage = $this->handleOcrError($result['ErrorMessage'] ?? null);
                throw new Exception("Error devuelto por el motor OCR: " . $errorMessage);
            }

            $extractedText = '';
            foreach ($result['ParsedResults'] as $parsedResult) {
                $extractedText .= $parsedResult['ParsedText'] . "\n\n";
            }

            return $extractedText;
        } catch (Exception $e) {
            throw new Exception("Fallo en la extracción por OCR.space: " . $e->getMessage());
        }
    }

    /**
     * Fallback: Extrae datos estructurados de un PDF usando la API de Gemini.
     *
     * @param string $filePath Ruta del archivo en el disco 'private_cvs'.
     * @return array Los datos estructurados del CV o un array vacío en caso de fallo.
     * @throws Exception Si hay un error de comunicación o la API responde con un error.
     */
    public function extractWithGemini(string $filePath): array
    {
        $absolutePath = Storage::disk('private_cvs')->path($filePath);

        if (!file_exists($absolutePath)) {
            throw new Exception("El archivo para Gemini no existe: " . $filePath);
        }

        try {
            // 1. Cargar y codificar el PDF en Base64
            $pdfContent = file_get_contents($absolutePath);
            $base64Pdf = base64_encode($pdfContent);

            $systemPrompt = "Eres un analista de recursos humanos experto. Tu tarea es extraer la información clave de un currículum vitae (CV) en formato PDF. Debes estructurar la información exactamente según el esquema JSON proporcionado. Asegúrate de que todos los nombres, contactos, resúmenes, experiencias, formación y habilidades estén presentes en la respuesta JSON. Prioriza la precisión de los datos. Si un campo está vacío en el CV, usa una cadena vacía ('').";

            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => 'Extrae toda la información relevante de este CV para rellenar un perfil de candidato. Devuelve solo el JSON.'],
                            [
                                'inlineData' => [
                                    'mimeType' => 'application/pdf',
                                    'data' => $base64Pdf
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => self::CV_JSON_SCHEMA
                ],
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ]
            ];

            // 2. Realizar la solicitud a la API de Gemini
            $response = Http::timeout(120) // Un poco más de tiempo para archivos grandes
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(self::GEMINI_ENDPOINT . '?key=' . self::GEMINI_API_KEY, $payload);

            // 3. Verificar si la llamada HTTP fue exitosa
            if (!$response->successful()) {
                throw new Exception("La llamada a Gemini falló con código: " . $response->status() . " y mensaje: " . $response->body());
            }

            $result = $response->json();
            $candidate = $result['candidates'][0] ?? null;

            if ($candidate && $candidate['content']['parts'][0]['text']) {
                $jsonString = $candidate['content']['parts'][0]['text'];
                // La respuesta debe ser un JSON, lo parseamos
                return json_decode($jsonString, true) ?? [];
            } else {
                throw new Exception("Respuesta de Gemini vacía o mal formada.");
            }
        } catch (Exception $e) {
            // Capturar errores y relanzarlos para que el controlador lo gestione
            throw new Exception("Fallo en la extracción por Gemini: " . $e->getMessage());
        }
    }


    /**
     * Asegura que el mensaje de error de la API de OCR.space sea una cadena.
     *
     * @param mixed $errorData
     * @return string
     */
    protected function handleOcrError($errorData): string
    {
        if (is_array($errorData)) {
            if (isset($errorData[0]) && is_string($errorData[0])) {
                return implode('; ', $errorData);
            }
            return json_encode($errorData);
        }

        return (string) $errorData;
    }
}
