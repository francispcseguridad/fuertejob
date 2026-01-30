<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL; // Se necesita para generar la URL firmada
use App\Models\User;
use App\Models\CompanyProfile;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\MailsController; // Asumiendo que tu MailsController está aquí
use Illuminate\Support\Str;
use App\Models\Sector;

class CompanyRegistrationController extends Controller
{
    /**
     * Muestra el formulario de registro de la empresa.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Genera un captcha aritmético simple y guarda el resultado esperado en sesión
        $num1 = random_int(1, 9);
        $num2 = random_int(1, 9);
        session(['math_captcha_result' => $num1 + $num2]);

        $oldSectorIds = collect(session()->getOldInput('sectors', []))
            ->filter()
            ->unique()
            ->values();

        $preselectedSectors = $oldSectorIds->isEmpty()
            ? collect()
            : Sector::with('parent')
            ->whereIn('id', $oldSectorIds)
            ->get()
            ->map(function ($sector) {
                $label = $sector->parent
                    ? $sector->parent->name . ' · ' . $sector->name
                    : $sector->name;

                return [
                    'id' => $sector->id,
                    'label' => $label,
                ];
            })
            ->values();

        // La ruta de esta vista debe ser 'company.register.create'
        return view('company.auth.register', compact('preselectedSectors', 'num1', 'num2'));
    }

    /**
     * Procesa la solicitud de registro y crea el usuario y el perfil de empresa.
     * También inicia el proceso de verificación de email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {


        // 1. Validación de los datos
        $validated = $request->validate([
            // Datos del Usuario
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Datos del Perfil de Empresa (iniciales)
            'company_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'vat_id' => ['required', 'string', 'max:50', 'unique:company_profiles,vat_id'],
            'fiscal_address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'string', 'email', 'max:255'], // Email de la empresa (público), distinto del usuario
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'sectors' => ['nullable', 'array'],
            'sectors.*' => ['integer', 'distinct', 'exists:sectors,id'],
            'accept_privacy_policy' => ['required', 'accepted'],
            'accept_terms' => ['required', 'accepted'],
            // Captcha aritmético simple
            'math_captcha' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $expected = session('math_captcha_result');
                    if ($expected === null || (int) $value !== (int) $expected) {
                        $fail('La respuesta de verificación no es correcta.');
                    }
                },
            ],
        ]);

        // Usamos una transacción para asegurar que ambos, User y CompanyProfile, se creen o ninguno lo haga.
        try {
            DB::beginTransaction();

            // 2. Crear el Usuario (Rol por defecto 'company')
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'], // Email de Login
                'password' => Hash::make($validated['password']),
                'rol' => 'empresa',
            ]);

            // 3. Crear el Perfil de Empresa asociado
            $logoPath = null;
            if ($request->hasFile('logo_url')) {
                $file = $request->file('logo_url');
                $slug = Str::slug($validated['company_name']);
                // Forzamos extensión jpg o png según lo que devuelva el compresor (el compresor devuelve binario, no cambia extensión, pero mejor mantener la original si es compatible)
                // Ojo: si el compresor convierte a JPG, la extensión debería ser jpg. 
                // Mi función preserva el tipo (JPG, PNG, GIF).
                $filename = $slug . '.' . $file->getClientOriginalExtension();

                // Comprimir imagen
                $optimizedContent = \App\Http\Controllers\HomeController::compressAndResizeImage($file, 1000, 75);

                // Guardar en public_path
                $destinationPath = public_path('img/companies/' . $filename);

                // Asegurar que el directorio existe
                if (!file_exists(dirname($destinationPath))) {
                    mkdir(dirname($destinationPath), 0755, true);
                }

                file_put_contents($destinationPath, $optimizedContent);

                $logoPath = 'img/companies/' . $filename;
            }

            $companyProfile = CompanyProfile::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'legal_name' => $validated['legal_name'],
                'vat_id' => $validated['vat_id'],
                'fiscal_address' => $validated['fiscal_address'],
                'description' => $validated['description'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['company_email'] ?? null, // Mapeamos company_email al campo email de la tabla company_profiles
                'logo_url' => $logoPath,
                'video_url' => $validated['video_url'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'contact' =>  $validated['name'] ?? null,
                'contact_email' => $validated['email'] ?? null,
                'city' => $validated['city'],
                'country' => $validated['country'],
            ]);

            if (isset($validated['sectors']) && is_array($validated['sectors'])) {
                $companyProfile->sectors()->sync($validated['sectors']);
            }

            // 4. Generar el enlace de verificación (Se asume que existe la ruta 'verification.verify')
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify', // Ruta que verifica el email (debes definirla)
                now()->addMinutes(60), // Enlace válido por 60 minutos
                [
                    'id' => $user->id,
                    // Usamos sha1(email) para validar que el hash de la URL sea correcto
                    'hash' => sha1($user->email),
                ]
            );

            // 5. ENVÍO DEL EMAIL DE VERIFICACIÓN
            MailsController::enviarEmailConPlantilla(
                $user,
                'company_verification', // Usamos una plantilla específica para empresas
                [
                    'user_name' => $user->name,
                    'verification_link' => $verificationUrl
                ]
            );

            DB::commit();

            // Limpiar el resultado del captcha tras un registro exitoso
            session()->forget('math_captcha_result');

            // 6. Redirigir al usuario a la página de confirmación de registro
            return redirect()->route('company.register.success')
                ->with('status', 'Registro exitoso. Por favor, revisa tu email para verificar tu cuenta.');
        } catch (\Exception $e) {
            // dd($e); // Eliminamos para producción
            DB::rollBack();
            // Manejo de errores
            \Log::error("Error de registro de empresa: " . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Ocurrió un error durante el registro. Inténtalo de nuevo.']);
        }
    }
}
