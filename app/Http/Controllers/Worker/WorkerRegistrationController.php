<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\MailsController; // Importación del controlador de correo
use Carbon\Carbon;

use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\Cv;
use App\Models\Experience; // Usado para la inserción de datos
use App\Models\Education; // Usado para la inserción de datos
use App\Models\Skill;
use App\Models\Tool;
use App\Models\Language;
use App\Services\GeminiCvParserService;
use App\Utils\YearExtractor;

class WorkerRegistrationController extends Controller
{
    /**
     * Disco usado para guardar CVs.
     */
    protected const CV_DISK = 'private_cvs';

    /**
     * Muestra la vista de confirmación de alta de usuario.
     */
    public function finalizaraltausuario()
    {
        return view('verification.altausuario');
    }

    /**
     * Muestra el formulario de registro del trabajador.
     */
    public function showRegistrationForm()
    {
        $num1 = random_int(1, 9);
        $num2 = random_int(1, 9);

        session(['worker_math_captcha_result' => $num1 + $num2]);

        return view('auth.worker_register', compact('num1', 'num2'));
    }

    /**
     * Maneja la solicitud de registro, crea los modelos, sube el CV y lo analiza con Gemini.
     * Utiliza análisis directo de PDF con Gemini para mayor robustez con CVs complejos.
     *
     * @param Request $request
     * @param GeminiCvParserService $geminiCvParser Inyección para el servicio de análisis de Gemini.
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function register(
        Request $request,
        GeminiCvParserService $geminiCvParser
    ) {


        // 1. Definición de Reglas de Validación (MANTENIDO)
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'preferred_modality' => ['nullable', 'string', 'max:40'],
            'min_expected_salary' => ['nullable', 'string', 'max:255'],
            'cv_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // Max 5MB
            'data_veracity' => ['required', 'accepted'],
            'accept_privacy_policy' => ['required', 'accepted'],
            'accept_terms' => ['required', 'accepted'],
            'math_captcha' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $expected = session('worker_math_captcha_result');
                    if ($expected === null || (int) $value !== (int) $expected) {
                        $fail('La respuesta de verificación no es correcta.');
                    }
                },
            ],
        ];

        // 2. Definición de Mensajes de Error Personalizados (MANTENIDO)
        $messages = [
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'email.unique' => 'Este correo electrónico ya está registrado.',

            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',

            'first_name.required' => 'El campo nombre es obligatorio.',
            'last_name.required' => 'El campo apellido es obligatorio.',

            'cv_file.required' => 'Debe adjuntar su CV.',
            'cv_file.file' => 'El archivo del CV es inválido.',
            'cv_file.mimes' => 'El CV debe ser un archivo PDF, DOC o DOCX.',
            'cv_file.max' => 'El CV no debe exceder los 5MB de tamaño.',
            'preferred_modality.max' => 'La modalidad preferida es demasiado larga.',
            'min_expected_salary.max' => 'El salario esperado es demasiado largo.',


            'data_veracity.required' => 'Debe declarar que los datos son veraces.',
            'data_veracity.accepted' => 'Debe declarar que los datos son veraces.',
            'accept_privacy_policy.required' => 'Debe aceptar la política de privacidad.',
            'accept_privacy_policy.accepted' => 'Debe aceptar la política de privacidad.',
            'accept_terms.required' => 'Debe aceptar las condiciones de uso.',
            'accept_terms.accepted' => 'Debe aceptar las condiciones de uso.',
            'math_captcha.required' => 'Debes resolver la operación de seguridad.',
            'math_captcha.integer' => 'La respuesta debe ser un número válido.',

        ];

        // 3. Ejecución de la Validación (MANTENIDO)
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();
        $cvFilePath = null;

        // 4. Inicio de la Transacción de Base de Datos (MANTENIDO)
        DB::beginTransaction();

        try {
            // 5. Creación del Usuario (users) (MANTENIDO)
            $user = User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'rol' => 'trabajador',
                'email_verified_at' => null,
            ]);

            // 6. Creación del Perfil del Trabajador (worker_profiles) (MANTENIDO)
            $profile = WorkerProfile::create([
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'phone_number' => $validatedData['phone_number'] ?? null,
                'city' => $validatedData['city'],
                'country' => $validatedData['country'],
                'preferred_modality' => $validatedData['preferred_modality'] ?? null,
                'min_expected_salary' => $validatedData['min_expected_salary'] ?? null,
                'rgpd_acceptance' => $request->has('rgpd_acceptance'),
                'data_veracity' => $request->has('data_veracity'),
            ]);

            // 7. Subida y Registro del CV (MANTENIDO)
            // 7. Subida y Registro del CV (MANTENIDO)
            $file = $request->file('cv_file');
            $cvFilePath = Storage::disk(self::CV_DISK)->putFile('user_' . $user->id, $file);

            // Validar que el archivo realmente quedó guardado y es legible
            if (!$cvFilePath) {
                throw new \RuntimeException('No se pudo guardar el archivo de CV en el almacenamiento.');
            }

            $absoluteCvPath = Storage::disk(self::CV_DISK)->path($cvFilePath);
            if (!Storage::disk(self::CV_DISK)->exists($cvFilePath) || !is_readable($absoluteCvPath)) {
                throw new \RuntimeException('El archivo de CV no existe o no es legible después de subirlo.');
            }

            $cv = Cv::create([
                'worker_profile_id' => $profile->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $cvFilePath,
                'is_primary' => true,
            ]);

            if (!$cv) {
                throw new \RuntimeException('No se pudo registrar el CV en la base de datos.');
            }

            // --- PUNTO CRÍTICO: HACEMOS COMMIT AQUÍ PARA SALVAGUARDAR AL USUARIO Y SU CV ---
            // Si el análisis de IA falla (timeout, error de API, crash de Imagick), el usuario YA EXISTE y tiene CV.
            DB::commit();
        } catch (\Exception $e) {
            // 14. Reversión de la Transacción y Limpieza (Solo si falla la creación básica)
            DB::rollback();

            Log::error("Error Fatal en WorkerRegistration (Fase Crítica): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            // Limpieza del archivo CV subido
            if (isset($cvFilePath) && Storage::disk(self::CV_DISK)->exists($cvFilePath)) {
                Storage::disk(self::CV_DISK)->delete($cvFilePath);
            }

            // Relanzar la excepción como error de validación
            throw ValidationException::withMessages([
                'registro_fallido' => ['Ocurrió un error al procesar tu CV o al guardar los datos. Por favor, inténtalo de nuevo o contacta a soporte.'],
            ]);
        }

        // ====================================================================================
        // FASE 2: PROCESAMIENTO NO CRÍTICO (IA Y EXTRACCIÓN DE DATOS)
        // Si esto falla, el usuario ya está registrado, pero sin datos autocompletados.
        // ====================================================================================

        try {
            // 8. PROCESAMIENTO CON GEMINI
            $cvData = [];
            try {
                Log::info("Iniciando análisis de CV con Gemini (PDF directo) para usuario {$user->id}");
                $cvData = $geminiCvParser->extractStructuredDataFromPdf($absoluteCvPath);

                if (empty($cvData) || !isset($cvData['experiences'])) {
                    Log::warning("El análisis de Gemini devolvió datos vacíos para el usuario {$user->id}");
                } else {
                    Log::info('CV Parsing: Resultado exitoso de Gemini', ['user_id' => $user->id]);
                }
            } catch (\Exception $e) {
                Log::error("Fallo en el análisis del CV con Gemini para el usuario {$user->id}: " . $e->getMessage());
                // No lanzamos excepción, continuamos
            }

            // 9. INSERCIÓN DE DATOS ESTRUCTURADOS (NUEVA TRANSACCIÓN INDEPENDIENTE)
            if ($cvData) {
                DB::transaction(function () use ($profile, $cvData) {
                    $cleanDate = function ($data, $key) {
                        $value = $data[$key] ?? null;
                        if ($value === 'null' || empty($value)) return null;
                        if (is_string($value) && preg_match('/^\d{4}$/', trim($value))) return trim($value) . '-01-01';
                        return $value;
                    };

                    $profile->update(['professional_summary' => $cvData['professional_summary'] ?? null]);

                    $profile->experiences()->delete();
                    if (isset($cvData['experiences']) && is_array($cvData['experiences'])) {
                        foreach ($cvData['experiences'] as $experience) {
                            $startRaw = $cleanDate($experience, 'start_date');
                            $endRaw = $cleanDate($experience, 'end_date');
                            [$rangeStart, $rangeEnd] = YearExtractor::extractYearsFromRange($startRaw);
                            $startYear = $rangeStart ?: YearExtractor::extractYear($startRaw);
                            $endYear = $rangeEnd ?: YearExtractor::extractYear($endRaw);

                            Experience::create([
                                'worker_profile_id' => $profile->id,
                                'job_title' => $experience['title'] ?? 'Sin título',
                                'company_name' => $experience['company'] ?? 'Sin empresa',
                                'start_year' => $startYear,
                                'end_year' => $endYear,
                                'description' => $experience['description'] ?? null,
                            ]);
                        }
                    }

                    $profile->educations()->delete();
                    if (isset($cvData['education']) && is_array($cvData['education'])) {
                        foreach ($cvData['education'] as $education) {
                            Education::create([
                                'worker_profile_id' => $profile->id,
                                'institution' => $education['institution'] ?? 'Sin institución',
                                'degree' => $education['degree'] ?? 'Sin titulación',
                                'field_of_study' => $education['field_of_study'] ?? null,
                                'start_date' => $cleanDate($education, 'start_date'),
                                'end_date' => $cleanDate($education, 'end_date'),
                            ]);
                        }
                    }

                    $this->syncKnowledge($profile, $cvData);
                });
            }
        } catch (\Exception $e) {
            // Si falla la Fase 2, solo lo logueamos. El usuario ya está registrado correctamente.
            Log::error("Error en Fase 2 (Enriquecimiento de perfil) para usuario {$user->id}: " . $e->getMessage());
        }

        session()->forget('worker_math_captcha_result');

        // ====================================================================================
        // FASE 3: VERIFICACIÓN Y REDIRECCIÓN
        // ====================================================================================

        try {
            // 11. GENERACIÓN DEL ENLACE DE VERIFICACIÓN
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                ['id' => $user->id, 'hash' => sha1($user->email)]
            );

            // 12. ENVÍO DEL EMAIL DE VERIFICACIÓN
            MailsController::enviarEmailConPlantilla(
                $user,
                'worker_verification',
                ['user_name' => $user->name, 'verification_link' => $verificationUrl]
            );

            $message = '¡Registro completado! Te hemos enviado un correo electrónico a ' . $user->email . ' para que verifiques tu cuenta. Por favor, revisa tu bandeja de entrada.';

            return redirect()->route('verification.altausuario')
                ->with('status', $message);
        } catch (\Exception $e) {
            Log::error("Error al enviar email de verificación usuario {$user->id}: " . $e->getMessage());
            // Redirigimos igual, el usuario puede pedir reenvío luego
            return redirect()->route('verification.altausuario')
                ->with('status', 'Registro completado, pero hubo un problema enviando el email. Intenta iniciar sesión para reenviarlo.');
        }
    }

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
            // Intentamos buscar el nivel (ej: 'Intermedio', 'Básico', 'Avanzado')
            $parts = explode(' ', $fullLang);
            $level = null;
            $name = strtolower($fullLang);

            // Heurística simple: si hay más de una palabra y la última no es un nombre de idioma común, asumimos que es el nivel.
            if (count($parts) > 1 && !in_array(strtolower($parts[count($parts) - 1]), ['español', 'english', 'francés', 'alemán', 'chino', 'japonés'])) {
                $level = array_pop($parts);
                $name = strtolower(implode(' ', $parts)); // El resto es el nombre del idioma
            }

            // Buscamos o creamos el idioma (el nivel se guarda en la entidad Language)
            $language = Language::firstOrCreate(
                ['name' => $name],
                ['level' => strtolower($level) ?? null]
            );

            // Devolvemos el ID para la sincronización M:N
            return $language->id;
        })->filter()->all();
        $profile->languages()->sync($languageIds);
    }
}
