<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cv;
use App\Services\PdfTextExtractorService;
use App\Services\GeminiCvParserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CvTestController extends Controller
{
    /**
     * Prueba el flujo de extracción y parsing para un CV específico.
     * La ID del CV a probar está HARDCODEADA (Cv::find(3))
     */
    public function testParsing()
    {
        $cvId = 3;
        $cv = Cv::find($cvId);

        if (!$cv) {
            return response()->json(['error' => "CV con ID {$cvId} no encontrado."], 404);
        }

        // 1. Verificar existencia del archivo físico
        if (!Storage::disk('private_cvs')->exists($cv->file_path)) {
            return response()->json(['error' => "Archivo físico no encontrado para el CV con ID {$cvId} en la ruta: {$cv->file_path}"], 500);
        }

        try {
            // 2. Extracción de Texto Plano
            $extractor = new PdfTextExtractorService();
            $cvTextContent = $extractor->extract($cv->file_path);

            // LOGGING: Verificar texto extraído
            Log::info('TEST PARSING: Texto Extraído', [
                'cv_id' => $cvId,
                'file_path' => $cv->file_path,
                'length' => strlen($cvTextContent),
                'snippet' => substr($cvTextContent, 0, 500) . '...' // Primeros 500 caracteres
            ]);

            if (empty($cvTextContent)) {
                return response()->json([
                    'status' => 'FALLO EN EXTRACCIÓN',
                    'message' => 'El texto extraído del CV está vacío. Falla en PdfTextExtractorService.',
                    'cv_id' => $cvId
                ], 500);
            }

            // 3. Análisis con Gemini
            $parser = new GeminiCvParserService();
            $cvData = $parser->parseCv($cvTextContent);

            // 4. Devolver Resultados
            if ($cvData) {
                return response()->json([
                    'status' => 'ÉXITO TOTAL',
                    'cv_id' => $cvId,
                    'message' => 'Gemini devolvió datos estructurados.',
                    'extracted_data' => $cvData,
                    'experience_count' => count($cvData['experiences'] ?? []),
                    'education_count' => count($cvData['education'] ?? []),
                ]);
            } else {
                return response()->json([
                    'status' => 'FALLO DE PARSING',
                    'message' => 'GeminiCvParserService devolvió NULL. Revise la clave API y el log de errores de Gemini.',
                    'cv_id' => $cvId
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("TEST PARSING EXCEPTION: " . $e->getMessage(), ['cv_id' => $cvId]);
            return response()->json([
                'status' => 'ERROR EXCEPCIÓN',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
