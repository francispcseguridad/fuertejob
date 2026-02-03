<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// --- IMPORTACIONES ---
use App\Models\WorkerProfile;
use App\Models\Cv;
use App\Services\GeminiCvParserService;
// Se eliminan PdfTextExtractorService y OcrTextExtractorService
use App\Models\Skill;
use App\Models\Tool;
use App\Models\Language;
// --------------------------------


class WorkerCvController extends Controller
{
    // Constante que define el disco de almacenamiento usado para los CVs
    protected const CV_DISK = 'private_cvs';

    /**
     * Sincroniza SKILLS, TOOLS y LANGUAGES usando las tablas pivote (M:N).
     *
     * @param WorkerProfile $profile
     * @param array $cvData
     * @return void
     */
    protected function syncKnowledge(WorkerProfile $profile, array $cvData)
    {
        // 1. SKILLS (Habilidades blandas/gestión)
        $skillIds = collect($cvData['skills'] ?? [])->map(function ($name) {
            $normalizedName = strtolower(trim($name));
            return Skill::firstOrCreate(['name' => $normalizedName])->id;
        })->filter()->all();
        $profile->skills()->sync($skillIds);

        // 2. TOOLS (Herramientas y Software)
        $toolIds = collect($cvData['tools'] ?? [])->map(function ($name) {
            $normalizedName = strtolower(trim($name));
            return Tool::firstOrCreate(['name' => $normalizedName])->id;
        })->filter()->all();
        $profile->tools()->sync($toolIds);

        // 3. LANGUAGES (Idiomas)
        $languageIds = collect($cvData['languages'] ?? [])->map(function ($fullLang) {
            $fullLang = trim($fullLang);
            $parts = explode(' ', $fullLang);
            $level = null;
            $name = strtolower($fullLang);

            if (count($parts) > 1 && !in_array(strtolower($parts[count($parts) - 1]), ['español', 'english', 'francés', 'alemán', 'chino', 'japonés'])) {
                $level = array_pop($parts);
                $name = strtolower(implode(' ', $parts));
            }

            $language = Language::firstOrCreate(
                ['name' => $name],
                ['level' => strtolower($level) ?? null]
            );

            return $language->id;
        })->filter()->all();

        $profile->languages()->sync($languageIds);
    }

    /**
     * Analiza el CV primario del trabajador usando el modo multimodal de Gemini.
     *
     * @param Request $request
     * @param GeminiCvParserService $geminiCvParser
     * @return \Illuminate\Http\JsonResponse
     */
    public function reAnalyzeCv(
        Request $request,
        // Se eliminan las dependencias de los extractores locales
        GeminiCvParserService $geminiCvParser
    ) {
        $user = Auth::user();
        $profile = WorkerProfile::where('user_id', $user->id)->first();
        if (!$profile) {
            return response()->json(['message' => 'Perfil de trabajador no encontrado.'], 404);
        }

        $cv = $profile->cv()->where('is_primary', true)->first();

        if (!$cv) {
            return response()->json(['message' => 'No se encontró un CV primario para reanalizar.'], 404);
        }

        $relativePathInDb = $cv->file_path;
        $cvFilePath = null;

        try {
            // Obtener la ruta absoluta usando el disco correcto
            $cvFilePath = Storage::disk(self::CV_DISK)->path($relativePathInDb);
        } catch (\Exception $e) {
            Log::error("Reanálisis CV: Error al resolver la ruta absoluta.", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error de configuración de almacenamiento. No se puede resolver la ruta del CV.',
                'error' => true
            ], 500);
        }

        // --- DIAGNÓSTICO DE EXISTENCIA Y PERMISOS DE LECTURA DE PHP ---
        // 1. Verificar existencia usando el disco de Laravel
        if (!Storage::disk(self::CV_DISK)->exists($relativePathInDb)) {
            Log::error("Reanálisis CV: Archivo no encontrado en el disco.", [
                'path' => $cvFilePath,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'message' => 'ERROR: El archivo de CV no fue encontrado en el disco de almacenamiento de Laravel. Path: ' . $cvFilePath,
                'error' => true
            ], 500);
        }

        // 2. Verificar legibilidad por el proceso PHP (PUNTO CRÍTICO)
        if (!is_readable($cvFilePath)) {
            Log::error("Reanálisis CV: Permisos insuficientes para leer el archivo.", ['path' => $cvFilePath, 'user_id' => $user->id]);

            $permission_error_message =
                "ERROR CRÍTICO DE PERMISOS: El proceso de PHP no puede leer el archivo. " .
                "Asegúrate de que el usuario que ejecuta PHP (ej. 'www-data' o el usuario de tu vhost) sea el propietario o tenga permisos de lectura. " .
                "Intenta los comandos SSH: `chown -R www-data:www-data " . dirname($cvFilePath) . "` y `chmod -R 755 " . dirname($cvFilePath) . "`";

            return response()->json([
                'message' => $permission_error_message,
                'error' => true
            ], 500);
        }
        // --- FIN DIAGNÓSTICO ---


        try {
            Log::info("Reanálisis CV: Iniciando reprocesamiento con Gemini Multimodal para usuario {$user->id}.");
            $success = $geminiCvParser->analyzeCv($cv);

            if ($success) {
                return response()->json([
                    'message' => 'El CV ha sido reanalizado con éxito usando Gemini Multimodal.',
                    'error' => false,
                ], 200);
            }

            return response()->json([
                'message' => 'Gemini no pudo extraer datos completos del CV. Verifica el documento e inténtalo de nuevo.',
                'error' => true,
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error Fatal en Reanálisis CV para {$user->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Ocurrió un error crítico al procesar el CV. Detalle: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }
}
