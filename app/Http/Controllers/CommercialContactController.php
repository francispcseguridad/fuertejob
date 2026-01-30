<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailsController;
use App\Models\CommercialContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CommercialContactController extends Controller
{
    public const CAPTCHA_RESULT_KEY = 'commercial_contact_math_captcha_result';
    public const CAPTCHA_QUESTION_KEY = 'commercial_contact_math_captcha_question';

    public static function ensureCaptchaQuestion(): ?string
    {
        if (!session()->has(self::CAPTCHA_RESULT_KEY) || !session()->has(self::CAPTCHA_QUESTION_KEY)) {
            self::refreshCaptcha();
        }

        return session(self::CAPTCHA_QUESTION_KEY);
    }

    public static function generateCaptchaQuestion(): string
    {
        self::refreshCaptcha();

        return session(self::CAPTCHA_QUESTION_KEY);
    }

    public static function refreshCaptcha(): void
    {
        $num1 = random_int(2, 9);
        $num2 = random_int(2, 9);

        session([
            self::CAPTCHA_RESULT_KEY => $num1 + $num2,
            self::CAPTCHA_QUESTION_KEY => "{$num1} + {$num2}",
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:255'],
            'origin' => ['required', 'string', 'max:64'],
            'detail' => ['required', 'string', 'max:2000'],
            'math_captcha' => ['required', 'integer'],
        ], [
            'math_captcha.required' => 'Responde a la pregunta de seguridad.',
            'math_captcha.integer' => 'El resultado debe ser un número válido.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $expected = session(self::CAPTCHA_RESULT_KEY);

            if ($expected === null || (int) $request->input('math_captcha') !== (int) $expected) {
                $validator->errors()->add('math_captcha', 'La respuesta no coincide con la operación.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $contact = CommercialContact::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'origin' => $request->input('origin'),
            'detail' => $request->input('detail'),
            'ip_address' => $request->ip(),
        ]);

        $messageBody = collect([
            'Nuevo contacto comercial recibido.',
            "Origen: {$contact->origin}",
            "Nombre: {$contact->name}",
            "Teléfono: {$contact->phone}",
            "Email: {$contact->email}",
            '',
            'Detalle:',
            $contact->detail,
            '',
            "ID de registro: {$contact->id}",
            "IP: {$contact->ip_address}",
        ])->implode("\n");

        try {
            MailsController::enviaremail(
                'info@fuertejob.com',
                $contact->name,
                $contact->email,
                "Nuevo Contacto {$contact->origin}",
                $messageBody
            );
        } catch (\Exception $e) {
            Log::error('Error enviando contacto comercial: ' . $e->getMessage(), ['contact_id' => $contact->id]);
        }

        self::refreshCaptcha();

        return redirect()->back()->with('commercial_contact_success', true);
    }
}
