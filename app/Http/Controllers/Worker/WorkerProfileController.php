<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Utils\YearExtractor;
use App\Models\Cv; // Importación del modelo CV
use App\Models\Experience;
use App\Models\Education;
use App\Models\Skill;
use App\Models\Tool;
use App\Models\Language;
use App\Models\JobSector;
use Illuminate\Database\Eloquent\Collection;
use App\Services\GeminiCvParserService; // Importación del nuevo servicio

class WorkerProfileController extends Controller
{
    /**
     * Helper para obtener el documento de CV primario/actual.
     * CORREGIDO: Accede directamente al modelo Cv usando el ID del perfil, 
     * en caso de que la relación cvs() no esté definida en WorkerProfile.
     * * @param WorkerProfile $profile
     * @return \App\Models\Cv|null
     */
    protected function getPrimaryCvDocument($profile)
    {
        return Cv::where('worker_profile_id', $profile->id)
            ->where('is_primary', true)
            ->latest()
            ->first();
    }

    /**
     * Autocomplete de puestos deseados.
     */
    public function searchJobPositions(Request $request)
    {
        $term = trim($request->get('term', ''));

        // Ya no usamos JobPosition, buscamos directamente en JobSector para ambas búsquedas
        $positions = JobSector::query()
            ->whereRaw('name COLLATE utf8mb4_unicode_ci LIKE ?', ['%' . $term . '%'])
            ->distinct()
            ->limit(30)
            ->pluck('name');

        return response()->json($positions);
    }

    /**
     * Autocomplete de sectores para el trabajador.
     */
    public function searchJobSectors(Request $request)
    {
        $term = trim($request->get('term', ''));

        // Búsqueda insensible a mayúsculas/minúsculas y acentos mediante la colación.
        $sectors = JobSector::query()
            ->whereRaw('name COLLATE utf8mb4_unicode_ci LIKE ?', ['%' . $term . '%'])
            ->distinct()
            ->limit(30)
            ->pluck('name');

        return response()->json($sectors);
    }


    public function dashboard()
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        // CRÍTICO: Asegurarse de que el perfil exista antes de intentar cargar relaciones.
        if ($profile) {
            $experiences = $profile->experiences()->latest('start_year')->take(3)->get();
            $education = $profile->educations()->latest('start_date')->take(3)->get();
        } else {
            $experiences = new Collection();
            $education = new Collection();
        }

        // 5. Cargar las candidaturas del usuario (ofertas a las que se ha inscrito)
        // Incluimos la oferta de empleo y el perfil de la empresa para mostrar información completa
        $candidaturas = $profile->candidateSelections()
            ->with(['jobOffer.companyProfile'])
            ->orderBy('selection_date', 'desc')
            ->get();

        return view('worker.dashboard.index', compact('user', 'candidaturas', 'profile', 'experiences', 'education', 'candidaturas'));
    }

    /**
     * Muestra la vista para editar el perfil del trabajador.
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Tu perfil de trabajador no pudo ser cargado. Por favor, crea uno.');
        }

        $experiences = $profile->experiences ?? collect();
        $education = $profile->educations ?? collect();
        $profile->loadMissing('desiredSectors');

        return view('worker.profile.edit', compact('user', 'profile', 'experiences', 'education'));
    }

    /**
     * Procesa la solicitud para actualizar el perfil del trabajador y los datos del usuario.
     */
    public function update(Request $request, GeminiCvParserService $geminiCvParser)
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        if (!$profile) {
            return back()->with('error', 'El perfil de trabajador no existe para este usuario.');
        }

        // 1. Validación de Datos (sin cambios, solo se actualizó la lógica del CV)
        $validatedData = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'professional_summary' => ['nullable', 'string'],
            // Nuevas preferencias para matching
            'contract_preference' => ['nullable', 'string', 'max:50'],
            'island' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'is_available' => ['sometimes', 'boolean'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'desired_positions' => ['sometimes', 'array'],
            'desired_positions.*' => ['nullable', 'string', 'max:255'],
            'desired_sectors' => ['sometimes', 'array'],
            'desired_sectors.*' => ['nullable', 'string', 'max:255'],
        ], [
            // Mensajes personalizados
            //   'profile_picture.image' => 'El archivo debe ser una imagen válida (jpg, jpeg, png, bmp, gif, svg, o webp).',
            //      'profile_picture.max' => 'El tamaño de la foto es muy grande, no puede pesar más de 2 MB.',
        ]);

        DB::beginTransaction();
        try {
            // Actualización del Usuario y Perfil (sin cambios)
            $user->update([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
            ]);

            $profile->update([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'phone_number' => $validatedData['phone_number'],
                'city' => $validatedData['city'],
                'country' => $validatedData['country'],
                'professional_summary' => $validatedData['professional_summary'],
                // Nuevas preferencias para matching
                'contract_preference' => $validatedData['contract_preference'] ?? null,
                'island' => $validatedData['island'] ?? null,
                'province' => $validatedData['province'] ?? null,
                'is_available' => $request->has('is_available'),
            ]);

            // Manejo de la subida del CV
            if ($request->hasFile('cv_file')) {
                $uploadedFile = $request->file('cv_file');
                $fileName = $uploadedFile->getClientOriginalName();

                // 1. Eliminar (y desmarcar) CV anterior si existe
                $primaryCv = $this->getPrimaryCvDocument($profile);

                if ($primaryCv) {
                    // Eliminar archivo anterior físicamente
                    Storage::disk('private_cvs')->delete($primaryCv->file_path);

                    // Eliminar el registro anterior (ya que es sustituido por el nuevo)
                    $primaryCv->delete();
                }

                // 2. Subir nuevo CV y guardar la ruta en el disco 'private_cvs'
                $cvPath = $uploadedFile->store('cvs/' . $user->id, 'private_cvs');

                // 3. Crear nuevo registro en la tabla 'cvs'
                $newCv = Cv::create([
                    'worker_profile_id' => $profile->id,
                    'file_name' => $fileName,
                    'file_path' => $cvPath,
                    'is_primary' => true,
                ]);

                // 4. Analizar CV con Gemini
                $geminiCvParser->analyzeCv($newCv);
            }

            // Manejo de la Imagen de Perfil (guardando en public_path/img/workers)
            if ($request->hasFile('profile_picture')) {
                // Eliminar imagen anterior si existe
                if ($profile->profile_image_url) {
                    $oldImagePath = public_path($profile->profile_image_url);
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }

                // USO DE LA FUNCIÓN DE COMPRESIÓN DE HomeController
                $file = $request->file('profile_picture');
                // Comprimimos y redimensionamos
                $optimizedContent = \App\Http\Controllers\HomeController::compressAndResizeImage($file, 1000, 75);

                // Generamos nombre y ruta
                // Se guarda en public/img/workers/foto-{id}.jpg
                $imageRelativePath = 'img/workers/' . 'foto-' . $user->workerProfile->id . '.jpg';
                $fullPath = public_path($imageRelativePath);

                // Asegurar que el directorio existe
                $directory = dirname($fullPath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Guardamos el contenido optimizado
                file_put_contents($fullPath, $optimizedContent);

                $profile->profile_image_url = $imageRelativePath;
                $profile->save();
            }

            // Sectores y Puestos deseados (Ahora todo se guarda en Sectores)
            $sectorIds = [];

            // Procesar Puestos Deseados
            if ($request->has('desired_positions')) {
                foreach ($request->input('desired_positions') as $posName) {
                    $cleanName = trim($posName);
                    if ($cleanName === '') continue;
                    $sector = JobSector::firstOrCreate(['name' => strtolower($cleanName)]);
                    $sectorIds[] = $sector->id;
                }
            }

            // Procesar Sectores Deseados
            if ($request->has('desired_sectors')) {
                foreach ($request->input('desired_sectors') as $sectorName) {
                    $cleanName = trim($sectorName);
                    if ($cleanName === '') continue;
                    $sector = JobSector::firstOrCreate(['name' => strtolower($cleanName)]);
                    $sectorIds[] = $sector->id;
                }
            }
            $profile->desiredSectors()->sync(array_unique($sectorIds));

            DB::commit();

            return redirect()->route('worker.profile.edit')
                ->with('success', 'Tu perfil ha sido actualizado con éxito. 🎉');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['general' => 'Ocurrió un error al guardar los datos: ' . $e->getMessage()]);
        }
    }

    /**
     * Maneja la subida del archivo CV.
     * Usa el disco 'private_cvs' y crea un registro en la tabla 'cvs'.
     * NOTA: Se llama al servicio de análisis directamente después de la subida.
     */
    public function uploadCv(Request $request, GeminiCvParserService $geminiCvParser)
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        if (!$profile) {
            return back()->with('error', 'El perfil de trabajador no existe.');
        }

        $request->validate([
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:5120', // Máx 5MB
        ]);

        DB::beginTransaction();
        try {
            $uploadedFile = $request->file('cv_file');
            $fileName = $uploadedFile->getClientOriginalName();

            // 1. Eliminar (y desmarcar) CV anterior si existe
            $primaryCv = $this->getPrimaryCvDocument($profile);

            if ($primaryCv) {
                // Eliminar archivo anterior físicamente
                Storage::disk('private_cvs')->delete($primaryCv->file_path);

                // Eliminar el registro anterior (ya que es sustituido por el nuevo)
                $primaryCv->delete();
            }

            // 2. Subir nuevo CV y guardar la ruta en el disco 'private_cvs'
            $cvPath = $uploadedFile->store('cvs/' . $user->id, 'private_cvs');

            // 3. Crear nuevo registro en la tabla 'cvs'
            $newCv = Cv::create([
                'worker_profile_id' => $profile->id,
                'file_name' => $fileName,
                'file_path' => $cvPath, // Esta es la ruta almacenada
                'is_primary' => true, // Marcar el nuevo CV como primario
            ]);

            // 4. LLAMADA DIRECTA AL SERVICIO DE ANÁLISIS (SIN JOBS)
            // Esto hará que la solicitud HTTP espere la respuesta de la IA.
            $geminiCvParser->analyzeCv($newCv);

            DB::commit();

            return back()->with('success', 'CV cargado y analizado con éxito. Los datos de tu perfil han sido actualizados.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error subiendo y analizando CV: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al subir/analizar el CV. Inténtalo de nuevo. Error: ' . $e->getMessage());
        }
    }

    /**
     * Elimina el archivo CV.
     * Usa el disco 'private_cvs' y la tabla 'cvs'.
     */
    public function deleteCv()
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return back()->with('error', 'El perfil de trabajador no existe.');
        }

        $primaryCv = $this->getPrimaryCvDocument($profile);

        if ($primaryCv) {
            try {
                // 1. Eliminar archivo físicamente (usando file_path)
                Storage::disk('private_cvs')->delete($primaryCv->file_path);

                // 2. Eliminar registro de la base de datos
                $primaryCv->delete();

                return back()->with('success', 'CV eliminado con éxito.');
            } catch (\Exception $e) {
                \Log::error('Error eliminando CV: ' . $e->getMessage());
                return back()->with('error', 'Ocurrió un error al eliminar el CV físico.');
            }
        }

        return back()->with('error', 'No se encontró CV para eliminar.');
    }

    /**
     * Solicita re-analizar el CV existente utilizando la IA.
     * Inyección del servicio GeminiCvParserService para el análisis directo.
     *
     * @param Request $request
     * @param GeminiCvParserService $geminiCvParser
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reanalyzeCv(Request $request, GeminiCvParserService $geminiCvParser)
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return back()->with('error', 'El perfil de trabajador no existe.');
        }

        $primaryCv = $this->getPrimaryCvDocument($profile);

        // 1. Verificar si hay un CV para re-analizar
        if (!$primaryCv || !Storage::disk('private_cvs')->exists($primaryCv->file_path)) {
            return back()->with('error', 'No se encontró ningún CV válido para re-analizar.');
        }

        try {
            // --- LÓGICA CLAVE: LLAMADA DIRECTA AL SERVICIO DE IA ---
            // Invocamos el método del servicio que contendrá la lógica de la API de Gemini.
            $geminiCvParser->analyzeCv($primaryCv);

            // ------------------------------------------------

            return back()->with('success', 'El CV ha sido re-analizado con éxito. Los datos de tu perfil han sido actualizados.');
        } catch (\Exception $e) {
            \Log::error("Error al re-analizar CV para User ID: " . Auth::id() . " | Error: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al solicitar el re-análisis. Inténtalo de nuevo más tarde. Error: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la vista de edición simplificada (única pantalla).
     */
    public function simplifiedEdit()
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Tu perfil de trabajador no pudo ser cargado.');
        }

        // Cargar relaciones necesarias
        $profile->load(['experiences', 'educations', 'skills', 'tools', 'languages', 'desiredSectors']);

        return view('worker.profile.simplified', compact('user', 'profile'));
    }

    /**
     * Procesa la actualización masiva desde la pantalla única.
     */
    public function simplifiedUpdate(Request $request)
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        if (!$profile) {
            return back()->with('error', 'El perfil de trabajador no existe.');
        }

        DB::beginTransaction();
        try {
            // 1. Actualizar Experiencias
            $profile->experiences()->delete();
            if ($request->has('experiences')) {
                foreach ($request->input('experiences') as $exp) {
                    if (!empty($exp['job_title']) && !empty($exp['company_name'])) {
                        $profile->experiences()->create([
                            'job_title' => $exp['job_title'],
                            'company_name' => $exp['company_name'],
                            'start_year' => YearExtractor::extractYear($exp['start_year'] ?? null),
                            'end_year' => isset($exp['is_current']) ? null : YearExtractor::extractYear($exp['end_year'] ?? null),
                            'description' => $exp['description'] ?? null,
                        ]);
                    }
                }
            }

            // 2. Actualizar Educación
            $profile->educations()->delete();
            if ($request->has('education')) {
                foreach ($request->input('education') as $edu) {
                    if (!empty($edu['degree']) && !empty($edu['institution'])) {
                        $profile->educations()->create([
                            'degree' => $edu['degree'],
                            'institution' => $edu['institution'],
                            'field_of_study' => $edu['field_of_study'] ?? null,
                            'start_date' => $edu['start_date'],
                            'end_date' => isset($edu['is_current']) ? null : ($edu['end_date'] ?? null),
                            'description' => $edu['description'] ?? null,
                        ]);
                    }
                }
            }

            // 3. Sincronizar Habilidades (Many-to-Many)
            if ($request->has('skills')) {
                $skillIds = [];
                foreach ($request->input('skills') as $skillName) {
                    if (empty($skillName)) continue;
                    $skill = Skill::firstOrCreate(['name' => strtolower(trim($skillName))]);
                    $skillIds[] = $skill->id;
                }
                $profile->skills()->sync($skillIds);
            }

            // 4. Sincronizar Herramientas (Many-to-Many)
            if ($request->has('tools')) {
                $toolIds = [];
                foreach ($request->input('tools') as $toolName) {
                    if (empty($toolName)) continue;
                    $tool = Tool::firstOrCreate(['name' => strtolower(trim($toolName))]);
                    $toolIds[] = $tool->id;
                }
                $profile->tools()->sync($toolIds);
            }

            // 5. Sincronizar Idiomas (Many-to-Many)
            if ($request->has('languages')) {
                $languageIds = [];
                foreach ($request->input('languages') as $langData) {
                    if (empty($langData['name'])) continue;
                    $language = Language::firstOrCreate(['name' => strtolower(trim($langData['name']))]);
                    $languageIds[] = $language->id;
                }
                $profile->languages()->sync($languageIds);
            }

            // 6. Sincronizar Sectores y Puestos (Todo combinado en JobSector)
            $sectorIds = [];

            // Puestos
            if ($request->has('desired_positions')) {
                foreach ($request->input('desired_positions') as $pName) {
                    $cleanName = trim($pName);
                    if ($cleanName === '') continue;
                    $sector = JobSector::firstOrCreate(['name' => strtolower($cleanName)]);
                    $sectorIds[] = $sector->id;
                }
            }

            // Sectores
            if ($request->has('desired_sectors')) {
                foreach ($request->input('desired_sectors') as $sName) {
                    $cleanName = trim($sName);
                    if ($cleanName === '') continue;
                    $sector = JobSector::firstOrCreate(['name' => strtolower($cleanName)]);
                    $sectorIds[] = $sector->id;
                }
            }
            $profile->desiredSectors()->sync(array_unique($sectorIds));

            DB::commit();
            return redirect()->route('worker.dashboard')->with('success', '¡Perfil actualizado correctamente en una sola pantalla! 🚀');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }
}
