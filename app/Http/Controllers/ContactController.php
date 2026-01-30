<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesPortalLayoutData;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MailsController;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    use ProvidesPortalLayoutData;

    public const ROLE_TYPES = [
        'visitor' => 'Visitante',
        'company' => 'Empresa',
        'worker' => 'Trabajador',
    ];

    public const INQUIRY_TYPES = [
        'platform' => 'Manejo de la plataforma',
        'support' => 'Soporte',
        'administration' => 'Administración',
    ];

    public function create()
    {
        $this->refreshCaptcha();

        $shared = $this->getSharedLayoutData();
        $authedRoleType = Auth::user() ? $this->resolveUserRoleType(Auth::user()) : null;

        return view('contact.index', array_merge($shared, [
            'roleTypes' => self::ROLE_TYPES,
            'inquiryTypes' => self::INQUIRY_TYPES,
            'captchaQuestion' => session('contact_math_captcha_question'),
            'authedRoleType' => $authedRoleType,
        ]));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'first_name' => $user ? ['nullable'] : ['required', 'string', 'max:255'],
            'last_name' => $user ? ['nullable'] : ['required', 'string', 'max:255'],
            'email' => $user ? ['nullable'] : ['required', 'email', 'max:255'],
            'role_type' => $user ? ['nullable'] : ['required', 'in:' . implode(',', array_keys(self::ROLE_TYPES))],
            'inquiry_type' => ['required', 'in:' . implode(',', array_keys(self::INQUIRY_TYPES))],
            'message' => ['required', 'string', 'max:2000'],
            'math_captcha' => ['required', 'integer'],
            'attachment' => ['nullable', 'image', 'max:4096'],
        ];

        $validator = Validator::make($request->all(), $rules, [
            'math_captcha.required' => 'Debes resolver la operación de seguridad.',
            'math_captcha.integer' => 'La respuesta debe ser un número válido.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $expected = session('contact_math_captcha_result');
            if ($expected === null || (int) $request->input('math_captcha') !== (int) $expected) {
                $validator->errors()->add('math_captcha', 'La respuesta de seguridad no coincide.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $firstName = $user ? (
            optional($user->workerProfile)->first_name
            ?? optional($user->companyProfile)->company_name
            ?? $user->name
            ?? 'Sin nombre'
        ) : $request->input('first_name');
        $lastName = $user ? (
            optional($user->workerProfile)->last_name
            ?? optional($user->companyProfile)->company_name
            ?? ''
        ) : $request->input('last_name');
        $email = $user ? $user->email : $request->input('email');
        $roleType = $user ? $this->resolveUserRoleType($user) : $request->input('role_type');
        $inquiryType = $request->input('inquiry_type');

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('contact_attachments', 'public');
        }

        $contact = ContactMessage::create([
            'user_id' => $user ? $user->id : null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'role_type' => $roleType,
            'inquiry_type' => $inquiryType,
            'message' => $request->input('message'),
            'attachment_path' => $attachmentPath,
            'ip_address' => $request->ip(),
        ]);

        $nameForEmail = trim("{$firstName} {$lastName}") ?: 'Contacto';
        $messageLines = [
            "Nuevo mensaje de contacto recibido.",
            "Rol: " . (self::ROLE_TYPES[$roleType] ?? $roleType),
            "Tipo de consulta: " . (self::INQUIRY_TYPES[$inquiryType] ?? $inquiryType),
            "Nombre: {$nameForEmail}",
            "Email: {$email}",
            "",
            "Mensaje:",
            $request->input('message'),
            "",
            "ID de usuario asociado: " . ($contact->user_id ?? 'N/A'),
            "IP: {$contact->ip_address}",
        ];

        try {
            $attachmentFullPath = $attachmentPath ? Storage::disk('public')->path($attachmentPath) : null;
            MailsController::enviaremail(
                'info@fuertejob.com',
                $nameForEmail,
                $email,
                "Nuevo contacto: " . (self::INQUIRY_TYPES[$inquiryType] ?? 'Consulta'),
                implode("\n", $messageLines),
                $attachmentFullPath
            );
        } catch (\Exception $e) {
            Log::error("Error enviando contacto a info@fuertejob.com: " . $e->getMessage(), ['contact_id' => $contact->id]);
        }

        session()->flash('contact_success', true);
        session()->forget('contact_math_captcha_result');
        session()->forget('contact_math_captcha_question');

        return redirect()->route('contact.create');
    }

    private function refreshCaptcha()
    {
        $num1 = random_int(1, 9);
        $num2 = random_int(1, 9);

        session([
            'contact_math_captcha_result' => $num1 + $num2,
            'contact_math_captcha_question' => "{$num1} + {$num2}",
        ]);
    }

    private function resolveUserRoleType($user): string
    {
        if ($user->hasCompanyRole()) {
            return 'company';
        }

        if ($user->workerProfile) {
            return 'worker';
        }

        return 'visitor';
    }
}
