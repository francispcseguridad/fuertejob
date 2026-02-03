<?php

namespace App\Services;

use App\Services\PdfTextExtractorService;
use App\Utils\YearExtractor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    private function getExperiencePromptExample(): string
    {
        return "\n\nEjemplo de sección EXPERIENCIA:\nEmprendimiento online/Marketing (actual)\n  • Organización de pedidos y clientes\nFred. Olsen Express - Aux. Administrativa\n(2024) periodo de verano\n  • Apoyo en tareas administrativas\nPFAE Turismo-Cabildo (2023-2024)\n  • Formación en turismo\n  • Prácticas en atención al visitante\n";
    }

    /**
     * Analiza un archivo CV y actualiza el perfil del trabajador asociado.
     * Este es el método principal llamado desde el controlador.
     *
     * @param Cv $cv Modelo Cv con la ruta del archivo.
     * @return bool
     */
    public function analyzeCv(Cv $cv): bool
    {
        try {
            Log::info("GeminiCvParser: Iniciando análisis para CV ID: {$cv->id}");

            // 1. Extraer datos estructurados del PDF
            $structuredData = $this->extractStructuredDataFromPdf($cv->file_path);

            if (!$structuredData) {
                Log::error("GeminiCvParser: Falló el análisis estructurado para CV ID: {$cv->id}");
                return false;
            }
            if (empty($structuredData['experiences'])) {
                $fallback = $this->extractExperiencesFromPdfText($cv->file_path);
                if (!empty($fallback)) {
                    Log::info("GeminiCvParser: Usando fallback manual para experiencias CV ID: {$cv->id}");
                    $structuredData['experiences'] = $fallback;
                }
            }

            // 2. Obtener el perfil del trabajador
            $workerProfile = $cv->workerProfile;
            if (!$workerProfile) {
                Log::error("GeminiCvParser: CV sin perfil de trabajador asociado. ID: {$cv->id}");
                return false;
            }

            // 3. Actualizar datos básicos del perfil
            $workerProfile->update([
                'professional_summary' => $structuredData['professional_summary'] ?? $workerProfile->professional_summary,
                'city' => $structuredData['city'] ?? $workerProfile->city,
            ]);

            // 4. Actualizar Experiencias
            if (isset($structuredData['experiences']) && is_array($structuredData['experiences'])) {
                Log::debug("GeminiCvParser: Experiencias raw recibidas: " . json_encode($structuredData['experiences']));
                $workerProfile->experiences()->delete();
                foreach ($structuredData['experiences'] as $exp) {
                    [$startYear, $endYear] = $this->resolveExperienceYears($exp);
                    Log::debug("GeminiCvParser: Procesando exp '{$exp['title']}' - Start: $startYear, End: $endYear");

                    $workerProfile->experiences()->create([
                        'job_title' => $exp['title'] ?? 'Sin título',
                        'company_name' => $exp['company'] ?? 'Sin empresa',
                        'start_year' => $startYear,
                        'end_year' => $endYear,
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
            return true;
        } catch (\Exception $e) {
            Log::error("GeminiCvParser: Excepción durante analyzeCv: " . $e->getMessage());
            return false;
        }
        return false;
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

    private function resolveExperienceYears(array &$exp): array
    {
        // 1. Prioridad: Buscar en Título, Empresa o DESCRIPCIÓN
        // A veces Gemini pone la fecha como primera línea de la descripción
        foreach (['title', 'company', 'description'] as $field) {
            if (!isset($exp[$field]) || !is_string($exp[$field])) {
                continue;
            }
            $text = $exp[$field];

            // Patrón A: Rango o Fecha al inicio (especialmente para descripción)
            // Para description, solo nos interesa si ESTÁ AL PRINCIPIO
            if ($field === 'description') {
                // Busca fecha al inicio del string
                if (preg_match('/^\s*\(?(\d{4})\s*[-–—\/]?\s*(\d{4}|presente|actualidad|en curso)?\)?/iu', $text, $matches)) {
                    $start = (int) $matches[1];
                    $end = isset($matches[2]) && is_numeric($matches[2]) ? (int) $matches[2] : null;

                    // Si es año único entre paréntesis al inicio: (2024) ...
                    if (!$end && preg_match('/^\s*\((\d{4})\)/', $text)) {
                        $end = $start;
                    }

                    // Limpiamos la fecha de la descripción
                    $exp[$field] = trim(str_replace($matches[0], '', $text));
                    return [$start, $end ?: $start];
                }
                continue;
            }

            // Patrón A: Rango de años explícito ej: 2023-2024, 2023/2024, (2023-2024)
            if (preg_match('/\(?(\d{4})\s*[-–—\/]\s*(\d{4}|presente|actualidad|en curso)?\)?/iu', $text, $matches)) {
                $start = (int) $matches[1];
                $end = null;

                if (isset($matches[2]) && is_numeric($matches[2])) {
                    $end = (int) $matches[2];
                }

                // Limpiar el texto (quitamos los años)
                $exp[$field] = trim(str_replace($matches[0], '', $text));

                return [$start, $end];
            }

            // Patrón B: Año único entre paréntesis ej: (2024) -> Señal muy fuerte
            // Si está entre paréntesis, casi seguro es la fecha de esa experiencia
            if (preg_match('/\((\d{4})\)/', $text, $matches)) {
                $start = (int) $matches[1];
                // Asumimos start=end para año único en paréntesis
                $exp[$field] = trim(str_replace($matches[0], '', $text));
                return [$start, $start];
            }
        }

        // 2. Si no se encontró en el texto, usar campos estructurados
        // Si viene del fallback manual, ya trae los años parseados en keys específicas
        if (isset($exp['start_year'])) {
            return [$exp['start_year'], $exp['end_year'] ?? null];
        }

        $rawStart = $exp['start_date'] ?? $exp['dates'] ?? '';
        $rawEnd = $exp['end_date'] ?? '';

        [$rangeStart, $rangeEnd] = YearExtractor::extractYearsFromRange($rawStart);
        $startYear = $rangeStart ?: YearExtractor::extractYear($rawStart);
        $endYear = $rangeEnd ?: YearExtractor::extractYear($rawEnd);

        // Lógica de año único (si start existe pero end no)
        if ($startYear && !$endYear && !$rangeStart) {
            // Verificamos si la cadena indica rango (guión, 'actual', etc)
            if (!preg_match('/[-–—\/]|actual|presente/i', $rawStart)) {
                $endYear = $startYear;
            }
        }

        return [$startYear, $endYear];
    }

    /**
     * Analiza el contenido de un CV proporcionado como texto plano (Cascada A).
     *
     * @param string $textContent El contenido de texto del CV.
     * @return array|null Datos estructurados del CV o null si falla.
     */
    public function parseCv(string $textContent): ?array
    {
        $prompt = "Analiza el siguiente texto de CV y extrae la información en el formato JSON proporcionado. Asegúrate de que las fechas estén en formato AAAA o AAAA-MM-DD. Si no hay una ciudad específica, omítela. Texto del CV:\n\n" . $textContent . $this->getExperiencePromptExample();

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
            $extraExample = $this->getExperiencePromptExample();
            $prompt = "Extrae y estructura toda la información del currículum vitae contenido en este archivo PDF/documento. El resultado debe ser un objeto JSON que siga estrictamente el esquema proporcionado. 

            INSTRUCCIONES CLAVE PARA EXPERIENCIA:
            1. TÍTULOS Y EMPRESAS: Usa EXACTAMENTE el texto de la línea del encabezado (la línea que suele tener la fecha). NO inventes títulos ni uses el texto de las viñetas/descripción como título.
            2. FECHAS: Busca fechas en la línea del encabezado o en la inmediata siguiente (ej: '(2024) ...'). Si encuentras '(2023-2024)', start_date=2023, end_date=2024.
            3. Si una línea dice 'Fred. Olsen Express-Aux. Administrativa', la Empresa es 'Fred. Olsen Express' y el Puesto es 'Aux. Administrativa'.
            4. Si una línea dice 'PFAE Turismo-Cabildo (2023-2024)', el Título/Empresa debe venir de 'PFAE Turismo-Cabildo' y los años son 2023 y 2024. ¡NO uses 'Formación y Prácticas' como título!
            
            Asegúrate de completar todos los campos posibles." . $extraExample;

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

    private function extractPlainTextFromPdf(string $filePath): ?string
    {
        try {
            /** @var PdfTextExtractorService $textExtractor */
            $textExtractor = app(PdfTextExtractorService::class);
            return $textExtractor->extract($filePath);
        } catch (\Exception $e) {
            Log::warning("GeminiParser: No se pudo extraer texto con el extractor interno: {$e->getMessage()}");
            return null;
        }
    }

    private function extractExperiencesFromPdfText(string $filePath): array
    {
        $plainText = $this->extractPlainTextFromPdf($filePath);
        if (!$plainText) {
            return [];
        }

        $section = $this->extractSection($plainText, 'EXPERIENCIA', ['FORMACIÓN', 'IDIOMAS', 'HERRAMIENTAS', 'CONTACTO', 'SOBRE MÍ']);
        if (empty($section)) {
            return [];
        }

        $lines = preg_split('/\R/', $section);
        $count = count($lines);
        $entries = [];
        $current = null;

        for ($i = 0; $i < $count; $i++) {
            $trim = trim($lines[$i]);
            if ($trim === '') {
                continue;
            }

            $isHeader = $this->isHeaderLine($trim);
            $combinedLine = $trim;

            // Lógica para detectar encabezados divididos en dos líneas:
            // Línea 1: Empresa - Puesto (sin fecha)
            // Línea 2: (Fecha) ...
            if (!$isHeader && $i + 1 < $count) {
                $nextTrim = trim($lines[$i + 1]);
                if ($this->isHeaderLine($nextTrim) && preg_match('/^.+[-–—].+$/u', $trim)) {
                    // Fusionamos las líneas para tratarlas como un solo encabezado
                    $combinedLine = $trim . ' ' . $nextTrim;
                    $isHeader = true;
                    $i++; // Saltamos la siguiente línea ya que la hemos consumido
                }
            }

            if ($isHeader) {
                if ($current) {
                    $this->flushExperienceEntry($entries, $current);
                }

                [$company, $title, $dates] = $this->parseHeaderLine($combinedLine);
                $current = [
                    'header' => $title ?: $company,
                    'company' => $company,
                    'dates' => $dates,
                    'description' => [],
                ];
                continue;
            }

            if ($current) {
                $current['description'][] = preg_replace('/^[•\\-*]\\s*/u', '', $trim);
            }
        }

        if ($current) {
            $this->flushExperienceEntry($entries, $current);
        }

        return $entries;
    }

    private function flushExperienceEntry(array &$entries, array $current): void
    {
        $header = $current['header'] ?? '';
        $company = $current['company'] ?? '';
        $title = $header;

        if (str_contains($header, '/')) {
            [$titlePart, $companyPart] = array_map('trim', explode('/', $header, 2));
            $title = $titlePart;
            $company = $companyPart;
        } elseif (str_contains($header, '-') && !str_contains($header, ' – ')) {
            [$companyPart, $titlePart] = array_map('trim', explode('-', $header, 2));
            $title = $titlePart;
            $company = $companyPart;
        }

        [$start, $end] = $this->splitDatesString($current['dates'] ?? '');

        $entries[] = [
            'title' => $title ?: 'Experiencia',
            'company' => $company ?: 'Empresa no especificada',
            'start_year' => $start,
            'end_year' => $end,
            'description' => trim(implode(' ', $current['description'] ?? [])),
        ];
    }

    private function splitDatesString(string $dateSegment): array
    {
        $startYear = null;
        $endYear = null;

        if (trim($dateSegment) !== '') {
            [$rangeStart, $rangeEnd] = YearExtractor::extractYearsFromRange($dateSegment);
            $startYear = $rangeStart;
            $endYear = $rangeEnd;
        }

        $parts = preg_split('/[-–\\/]/u', $dateSegment);
        if (!$startYear) {
            $startCandidate = trim($parts[0] ?? '');
            $startYear = $startYear ?: YearExtractor::extractYear($startCandidate);
        }

        if (!$endYear) {
            $endCandidate = trim($parts[1] ?? '');
            if ($endCandidate === '') {
                $endYear = null;
            } else {
                $endYear = YearExtractor::extractYear($endCandidate);
            }
        }

        if ($startYear && !$endYear && trim($dateSegment) !== '' && !preg_match('/[-–\\/]/', $dateSegment)) {
            $endYear = $startYear;
        }

        return [$startYear, $endYear];
    }

    private function isHeaderLine(string $line): bool
    {
        $clean = ltrim($line, '•*- ');
        return (bool) preg_match('/\d{4}/', $clean);
    }

    private function parseHeaderLine(string $line): array
    {
        $cleanLine = preg_replace('/^[•\\-*]\\s*/u', '', trim($line));
        $dates = null;

        if (preg_match('/\(([^)]+)\)/', $cleanLine, $match)) {
            $dates = $match[1];
            $cleanLine = trim(str_replace($match[0], '', $cleanLine));
        }

        $company = '';
        $title = $cleanLine;

        if (preg_match('/^(.+?)\\s*[-–—]\\s*(.+)$/u', $cleanLine, $parts)) {
            $company = trim($parts[1]);
            $title = trim($parts[2]);
        } else {
            $company = $cleanLine;
        }

        return [$company, $title, $dates];
    }

    private function extractSection(string $text, string $start, array $endMarkers): string
    {
        $lowerText = mb_strtolower($text);
        $startLower = mb_strtolower($start);
        $pos = mb_strpos($lowerText, $startLower);
        if ($pos === false) {
            return '';
        }

        $endPos = mb_strlen($text);
        foreach ($endMarkers as $marker) {
            $markerPos = mb_strpos($lowerText, mb_strtolower($marker), $pos + mb_strlen($startLower));
            if ($markerPos !== false && $markerPos < $endPos) {
                $endPos = $markerPos;
            }
        }

        $startPos = $pos + mb_strlen($startLower);
        return trim(mb_substr($text, $startPos, $endPos - $startPos));
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
