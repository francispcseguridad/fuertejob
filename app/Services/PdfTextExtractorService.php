<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Exceptions\PdfToTextException;
use Exception;

/**
 * Servicio encargado de extraer texto plano de archivos PDF.
 * Implementa múltiples estrategias de reintento y un fallback a OCR si todas fallan.
 */
class PdfTextExtractorService
{
    // Ruta estándar del ejecutable pdftotext
    protected const PDF_TO_TEXT_PATH = '/usr/bin/pdftotext';
    // Tiempo máximo de ejecución para el comando de extracción (en segundos)
    protected const EXTRACTION_TIMEOUT = 60; 

    // **NOTA:** El constructor ha sido removido para evitar el error "Too few arguments".

    /**
     * Extrae el texto plano de un archivo CV.
     *
     * @param string $filePath La ruta del archivo en el disco 'private_cvs'.
     * @return string El contenido del CV en texto plano.
     * @throws \Exception Si la extracción falla definitivamente.
     */
    public function extract(string $filePath): string
    {
        $absolutePath = Storage::disk('private_cvs')->path($filePath);

        if (!Storage::disk('private_cvs')->exists($filePath)) {
            throw new Exception("El archivo CV no se encontró en la ruta de almacenamiento: " . $filePath);
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        if ($extension === 'pdf') {
            try {
                // --- ESTRATEGIAS DE EXTRACCIÓN NATIVA (pdftotext) ---
                $strategies = [
                    '-layout',
                    '-raw',
                    '-nopgbrk',
                    ''
                ];

                $pdf = (new Pdf(self::PDF_TO_TEXT_PATH))
                    ->setPdf($absolutePath)
                    ->setTimeout(self::EXTRACTION_TIMEOUT);

                // Intento 1: Extraer con pdftotext
                foreach ($strategies as $option) {
                    try {
                        $pdf->setOptions(empty($option) ? [] : [$option]);
                        $text = $pdf->text();
                        $cleanText = $this->cleanText($text);

                        // Si se obtiene texto limpio, lo devolvemos inmediatamente
                        if (!empty($cleanText)) {
                            return $cleanText;
                        }
                    } catch (PdfToTextException $e) {
                        // Ignoramos errores de comando individuales y pasamos a la siguiente estrategia.
                        continue;
                    }
                }

                // --- FALLBACK A OCR ---
                // Si la extracción nativa falló completamente, resolvemos el servicio OCR e intentamos.
                
                // RESOLVIENDO LA DEPENDENCIA EN EL MÉTODO PARA EVITAR EL ERROR DEL CONSTRUCTOR
                /** @var \App\Services\OcrTextExtractorService $ocrService */
                $ocrService = app(OcrTextExtractorService::class);

                $ocrText = $ocrService->extractFromPdf($filePath);
                $cleanOcrText = $this->cleanText($ocrText);

                if (!empty($cleanOcrText)) {
                    // Si el OCR funcionó, devolvemos el texto
                    return $cleanOcrText;
                }

                // --- FALLA DEFINITIVA ---
                $failedStrategies = implode(', ', array_filter($strategies));
                $errorMessage = "Error Definitivo: El CV subido es ilegible tanto para el extractor nativo (pdftotext, estrategias: {$failedStrategies}) como para el OCR. ";
                $errorMessage .= "Por favor, pida al usuario que guarde el CV como un PDF 'solo texto' o que use una plantilla de diseño simple.";

                throw new Exception($errorMessage);
            } catch (PdfNotFound $e) {
                throw new Exception("Error de Configuración: El ejecutable 'pdftotext' no se encontró. Ruta: " . self::PDF_TO_TEXT_PATH);
            } catch (PdfToTextException $e) {
                throw new Exception("Error al ejecutar pdftotext. Revise permisos o si el PDF está corrupto. Detalle: " . $e->getMessage());
            } catch (Exception $e) {
                // Capturar errores generales (incluidos los fallos del servicio OCR)
                throw new Exception("Error en el proceso de extracción: " . $e->getMessage());
            }
        }

        // Manejo de formatos no soportados
        throw new Exception("Formato de archivo no soportado para extracción de texto ({$extension}).");
    }

    /**
     * Limpia el texto extraído para una mejor entrada a la IA.
     */
    protected function cleanText(string $text): string
    {
        // 1. Convertir todo a UTF-8 y manejar problemas de codificación
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'UTF-8, ISO-8859-1', true));
        }

        // 2. Reemplazar múltiples espacios en blanco (incluidos tabs) por uno solo.
        $text = preg_replace('/\s{2,}/u', ' ', $text);

        // 3. Reemplazar múltiples saltos de línea por uno solo para simplificar la estructura.
        $text = preg_replace('/[\r\n]{2,}/', "\n", $text);

        // 4. Eliminar caracteres no imprimibles o de control (excepto salto de línea y tabulación)
        $text = preg_replace('/[^\P{C}\n\t]/u', '', $text);

        // 5. Eliminar el espacio inicial y final
        $text = trim($text);

        return $text;
    }
}
