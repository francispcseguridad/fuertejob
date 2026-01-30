<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Comprime y redimensiona una imagen proporcionalmente.
     * 
     * @param \Illuminate\Http\UploadedFile $file El archivo de imagen subido.
     * @param int $maxHeight Altura máxima deseada (default 1000px).
     * @param int $quality Calidad de compresión (0-100, default 75).
     * @return string Contenido binario de la imagen procesada.
     */
    public static function compressAndResizeImage($file, $maxHeight = 1000, $quality = 75)
    {
        // Obtener ruta física del archivo
        $filePath = $file->getRealPath();

        // Obtener información de la imagen
        list($width, $height, $type) = getimagesize($filePath);

        // Calcular nuevas dimensiones manteniendo la proporción
        if ($height > $maxHeight) {
            $ratio = $width / $height;
            $newHeight = $maxHeight;
            $newWidth = $maxHeight * $ratio;
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Crear lienzo de imagen según el tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            default:
                return file_get_contents($filePath); // Si no es soportado, devolver original
        }

        // Crear nueva imagen vacía
        $destination = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG y GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($destination, imagecolorallocatealpha($destination, 0, 0, 0, 127));
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }

        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Capturar salida en buffer
        ob_start();

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, null, $quality); // Calidad 0-100
                break;
            case IMAGETYPE_PNG:
                $pngQuality = (int)($quality / 10); // PNG usa escala 0-9
                $pngQuality = 9 - $pngQuality; // Invertido: 0 es sin compresión
                if ($pngQuality < 0) $pngQuality = 0;
                imagepng($destination, null, $pngQuality);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination);
                break;
        }

        $content = ob_get_clean();

        // Liberar memoria
        imagedestroy($source);
        imagedestroy($destination);

        return $content;
    }
}
