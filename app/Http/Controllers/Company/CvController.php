<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Models\Cv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CvController extends Controller
{
    /**
     * Sirve el CV de forma segura (inline) a la empresa.
     *
     * @param \App\Models\Cv $cv
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serve(Cv $cv)
    {

        // 1. Verificar autenticación y autorización:
        // - Empresas (companyProfile) y admins pueden ver cualquier CV.
        // - Un trabajador solo puede ver su propio CV.
        if (!Auth::check()) {
            abort(403, 'Acceso denegado. Debes iniciar sesión.');
        }

        $user = Auth::user();
        $isCompanyOrAdmin = ($user->companyProfile || $user->rol === 'admin');
        $isOwnerWorker = $user->workerProfile && $user->workerProfile->id === $cv->worker_profile_id;

        if (!$isCompanyOrAdmin && !$isOwnerWorker) {
            abort(403, 'Acceso denegado. Solo empresas, admins o el propietario pueden visualizar este CV.');
        }


        // 3. Obtener la ruta completa del archivo
        // Usamos el disco 'private_cvs' que está configurado para apuntar a storage/app/private/cvs
        $fileRelativePath = $cv->file_path;

        if (!Storage::disk('private_cvs')->exists($fileRelativePath)) {
            // El archivo no se encuentra en el storage
            abort(404, 'El archivo CV no existe en el almacenamiento.');
        }

        // 4. Servir el archivo de forma segura y forzar la visualización INLINE
        // Storage::disk('private_cvs')->path() devuelve la ruta absoluta del sistema de archivos.
        return response()->file(
            Storage::disk('private_cvs')->path($fileRelativePath),
            [
                'Content-Type' => 'application/pdf',
                // Clave: 'inline' fuerza la visualización en el navegador
                'Content-Disposition' => 'inline; filename="' . $cv->file_name . '"',
                // Headers para evitar el cacheo y potencialmente la descarga
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }
}
