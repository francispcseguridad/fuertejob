<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Cv;
use App\Models\Skill;
use App\Models\Tool;
use App\Models\Language;
use Carbon\Carbon;

/**
 * Servicio para analizar Currículums Vitae (CV) usando la API de Gemini.
 * Soporta tanto la entrada de texto plano como la lectura directa de archivos PDF
 * para análisis multimodal como fallback.
 */
class GeminiCvParserService
{
    private $apiUrl;
    private $apiKey;
    private $model;
    private $diskName = 'private_cvs';

    public function __construct()
    {
        // Se mantiene la lógica original para la API Key
        $this->apiKey = env('GEMINI_API_KEY', '');
        $this->model = 'gemini-2.5-flash-preview-09-2025';
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
    }

    /**
     * Define el esquema JSON para la respuesta estructurada del CV.
     * Esto asegura que la salida sea consistente y fácil de manejar.
     *
     * @return array
     */
    protected function getCvJsonSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'professional_summary' => ['type' => 'STRING', 'description' => 'Un resumen conciso del perfil profesional del candidato.'],
                'city' => ['type' => 'STRING', 'description' => 'La ciudad de residencia actual del candidato.'],
                'experiences' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'title' => ['type' => 'STRING'],
                            'company' => ['type' => 'STRING'],
                            'start_date' => ['type' => 'STRING', 'description' => 'Fecha de inicio en formato AAAA-MM-DD o solo AAAA.'],
                            'end_date' => ['type' => 'STRING', 'description' => 'Fecha de finalización en formato AAAA-MM-DD o solo AAAA. Usar "Presente" si aún trabaja allí.'],
                            'description' => ['type' => 'STRING', 'description' => 'Tareas y logros principales en el puesto.'],
                        ],
                        'required' => ['title', 'company'],
                    ],
                ],
                'education' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'institution' => ['type' => 'STRING'],
                            'degree' => ['type' => 'STRING', 'description' => 'Título o certificación obtenida.'],
                            'field_of_study' => ['type' => 'STRING'],
                            'start_date' => ['type' => 'STRING', 'description' => 'Fecha de inicio en formato AAAA-MM-DD o solo AAAA.'],
                            'end_date' => ['type' => 'STRING', 'description' => 'Fecha de finalización en formato AAAA-MM-DD o solo AAAA. Usar la fecha si aplica.'],
                        ],
                        'required' => ['institution', 'degree'],
                    ],
                ],
                'skills' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING'], 'description' => 'Lista de habilidades blandas y de gestión.'],
                'tools' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING'], 'description' => 'Lista de herramientas de software, plataformas y tecnologías.'],
                'languages' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING'], 'description' => 'Lista de idiomas y su nivel (Ej: Español nativo, Inglés intermedio).'],
            ],
            'required' => ['experiences', 'education', 'skills'],
        ];
    }

    /**
     * Analiza un archivo CV y actualiza el perfil del trabajador asociado.
     * Este es el método principal llamado desde el controlador.
     *
     * @param Cv $cv Modelo Cv con la ruta del archivo.
     * @return void
     */
    public function analyzeCv(Cv $cv)
    {
        try {
            Log::info("GeminiCvParser: Iniciando análisis para CV ID: {$cv->id}");

            // 1. Extraer datos estructurados del PDF
            $structuredData = $this->extractStructuredDataFromPdf($cv->file_path);

            if (!$structuredData) {
                Log::error("GeminiCvParser: Falló el análisis estructurado para CV ID: {$cv->id}");
                return;
            }

            // 2. Obtener el perfil del trabajador
            $workerProfile = $cv->workerProfile;
            if (!$workerProfile) {
                Log::error("GeminiCvParser: CV sin perfil de trabajador asociado. ID: {$cv->id}");
                return;
            }

            // 3. Actualizar datos básicos del perfil
            $workerProfile->update([
                'professional_summary' => $structuredData['professional_summary'] ?? $workerProfile->professional_summary,
                'city' => $structuredData['city'] ?? $workerProfile->city,
            ]);

            // 4. Actualizar Experiencias
            if (isset($structuredData['experiences']) && is_array($structuredData['experiences'])) {
                $workerProfile->experiences()->delete();
                foreach ($structuredData['experiences'] as $exp) {
                    $workerProfile->experiences()->create([
                        'job_title' => $exp['title'] ?? 'Sin título',
                        'company_name' => $exp['company'] ?? 'Sin empresa',
                        'start_date' => $this->parseDate($exp['start_date'] ?? null),
                        'end_date' => $this->parseDate($exp['end_date'] ?? null),
                        'description' => $exp['description'] ?? null,
                    ]);
                }
            }

            // 5. Actualizar Educación
            if (isset($structuredData['education']) && is_array($structuredData['education'])) {
                $workerProfile->educations()->delete();
                foreach ($structuredData['education'] as $edu) {
                    $workerProfile->educations()->create([
                        'institution' => $edu['institution'] ?? 'Sin institución',
                        'degree' => $edu['degree'] ?? 'Sin título',
                        'field_of_study' => $edu['field_of_study'] ?? null,
                        'start_date' => $this->parseDate($edu['start_date'] ?? null),
                        'end_date' => $this->parseDate($edu['end_date'] ?? null),
                    ]);
                }
            }

            // 6. Actualizar Habilidades
            if (isset($structuredData['skills']) && is_array($structuredData['skills'])) {
                $skillIds = [];
                foreach ($structuredData['skills'] as $skillName) {
                    $skill = Skill::firstOrCreate(['name' => trim($skillName)]);
                    $skillIds[] = $skill->id;
                }
                $workerProfile->skills()->sync($skillIds);
            }

            // 7. Actualizar Herramientas
            if (isset($structuredData['tools']) && is_array($structuredData['tools'])) {
                $toolIds = [];
                foreach ($structuredData['tools'] as $toolName) {
                    $tool = Tool::firstOrCreate(['name' => trim($toolName)]);
                    $toolIds[] = $tool->id;
                }
                $workerProfile->tools()->sync($toolIds);
            }

            // 8. Actualizar Idiomas
            if (isset($structuredData['languages']) && is_array($structuredData['languages'])) {
                $languageIds = [];
                foreach ($structuredData['languages'] as $langName) {
                    $language = Language::firstOrCreate(['name' => trim($langName)]);
                    $languageIds[] = $language->id;
                }
                $workerProfile->languages()->sync($languageIds);
            }

            Log::info("GeminiCvParser: Análisis completado y perfil actualizado para CV ID: {$cv->id}");
        } catch (\Exception $e) {
            Log::error("GeminiCvParser: Excepción durante analyzeCv: " . $e->getMessage());
        }
    }

    /**
     * Helper para parsear fechas flexibles.
     */
    private function parseDate($dateString)
    {
        if (empty($dateString) || strtolower($dateString) === 'presente' || strtolower($dateString) === 'actualidad') {
            return null;
        }

        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Analiza el contenido de un CV proporcionado como texto plano (Cascada A).
     *
     * @param string $textContent El contenido de texto del CV.
     * @return array|null Datos estructurados del CV o null si falla.
     */
    public function parseCv(string $textContent): ?array
    {
        $prompt = "Analiza el siguiente texto de CV y extrae la información en el formato JSON proporcionado. Asegúrate de que las fechas estén en formato AAAA o AAAA-MM-DD. Si no hay una ciudad específica, omítela. Texto del CV:\n\n" . $textContent;

        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $this->getCvJsonSchema(),
            ],
        ];

        return $this->callGeminiApi($payload);
    }

    /**
     * Analiza un archivo PDF/DOCX directamente usando las capacidades multimodales de Gemini (Cascada B - Fallback).
     *
     * @param string $filePath Ruta interna del archivo en el disco 'private_cvs'.
     * @return array|null Datos estructurados del CV o null si falla.
     */
    public function extractStructuredDataFromPdf(string $filePath): ?array
    {
        try {
            // --- INICIO: CORRECCIÓN DE RUTA ABSOLUTA ---
            $disk = Storage::disk($this->diskName);
            $diskRoot = $disk->path('/');

            $relativePath = $filePath;

            // Detectamos si la ruta pasada es absoluta y si contiene la raíz del disco
            if (str_starts_with($filePath, $diskRoot)) {
                // Si la ruta es absoluta y comienza con la raíz del disco,
                // la cortamos para obtener solo la parte relativa.
                $relativePath = substr($filePath, strlen($diskRoot));

                // Limpieza de barras adicionales (si la ruta absoluta no tenía una barra al inicio)
                $relativePath = ltrim($relativePath, '/');
            }
            // Si la ruta no comienza con la raíz del disco, asumimos que ya es relativa o está incorrecta.

            Log::info("GeminiParser: Intentando leer archivo con ruta relativa: {$relativePath}");

            // 1. Leer el archivo y obtener su contenido binario usando la ruta relativa corregida
            $fileContent = $disk->get($relativePath);

            if (!$fileContent) {
                // Si get() devuelve false o null
                $fullPathAttempt = $disk->path($relativePath);

                Log::error("GeminiParser: Falló CRÍTICAMENTE la lectura del archivo después de corrección de ruta.", [
                    'disk' => $this->diskName,
                    'input_file_path' => $filePath, // La ruta original que causó el error
                    'relative_path_used' => $relativePath, // La ruta que realmente se usó para Storage::get()
                    'full_path_attempt' => $fullPathAttempt,
                    'exists' => $disk->exists($relativePath) ? 'Sí' : 'No',
                    'details' => 'El archivo no existe en la ruta relativa calculada o hay un problema de permisos. La clave "exists" es la que confirma si la ruta es correcta o no.'
                ]);

                throw new \Exception("GeminiParser: Error en la lectura del archivo CV. Consulte el log para más detalles sobre la ruta final y permisos.");
            }
            // --- FIN: CORRECCIÓN DE RUTA ABSOLUTA ---


            // 2. Convertir a Base64
            $base64Data = base64_encode($fileContent);

            // 3. Determinar el MIME type
            $mimeType = 'application/pdf';
            if (str_ends_with($relativePath, '.docx') || str_ends_with($relativePath, '.doc')) {
                $mimeType = 'application/pdf';
            }


            // 4. Definir el prompt y la estructura de la solicitud
            $prompt = "Extrae y estructura toda la información del currículum vitae contenido en este archivo PDF/documento. El resultado debe ser un objeto JSON que siga estrictamente el esquema proporcionado. Asegúrate de completar todos los campos posibles (resumen, experiencias, educación, etc.) con la información del CV.";

            $payload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64Data,
                            ]
                        ]
                    ]
                ]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->getCvJsonSchema(),
                ],
            ];

            return $this->callGeminiApi($payload);
        } catch (\Exception $e) {
            Log::error("GeminiParser: Error final al procesar PDF como fallback: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Realiza la llamada a la API de Gemini con reintentos.
     *
     * @param array $payload
     * @param int $maxRetries
     * @return array|null
     */
    protected function callGeminiApi(array $payload, int $maxRetries = 3): ?array
    {
        $attempt = 0;
        $delay = 1;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $response = $this->makeHttpRequest($payload);
                $result = json_decode($response, true);

                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = $result['candidates'][0]['content']['parts'][0]['text'];
                    return json_decode($jsonText, true);
                }

                // Si no hay texto de respuesta, puede ser un error de la API o formato inesperado
                Log::warning("Gemini API: Respuesta inesperada en el intento {$attempt}.", ['response' => $result]);
            } catch (\Exception $e) {
                Log::error("Gemini API: Fallo en el intento {$attempt}. Error: " . $e->getMessage());
            }

            if ($attempt < $maxRetries) {
                // Espera exponencial antes del próximo reintento
                sleep($delay);
                $delay *= 2;
            }
        }

        Log::error("Gemini API: Fallo después de {$maxRetries} intentos.");
        return null;
    }

    /**
     * Simula la función para realizar la solicitud HTTP.
     * En un entorno real de Laravel, usarías Guzzle/HttpClient.
     *
     * @param array $payload
     * @return string Respuesta JSON de la API.
     * @throws \Exception si la solicitud falla.
     */
    protected function makeHttpRequest(array $payload): string
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("Error en la solicitud cURL: {$error}");
        }

        if ($httpCode !== 200) {
            Log::error("Error HTTP al llamar a Gemini.", ['code' => $httpCode, 'response' => $response]);
            // Intentamos obtener un mensaje de error legible del cuerpo
            $errorDetails = json_decode($response, true)['error']['message'] ?? 'Error desconocido';
            throw new \Exception("La API de Gemini devolvió un código HTTP {$httpCode}. Detalles: {$errorDetails}");
        }

        return $response;
    }
}
