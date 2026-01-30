<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\PortalSetting; // Usamos el modelo PortalSetting para la configuración del remitente
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use App\Models\User;

/**
 * Controlador estático para el envío de correos.
 * IMPORTANTE: La configuración de correo SMTP debe estar en el archivo .env.
 */
class MailsController extends Controller
{
    /**
     * Define la configuración del remitente y envía un correo genérico.
     *
     * @param string $para El email del destinatario.
     * @param string $nombre El nombre del remitente (si aplica).
     * @param string $de El email de contacto (si aplica para la respuesta).
     * @param string $asunto El asunto del correo.
     * @param string $mensaje El contenido principal del mensaje.
     * @param string|null $adjunto Ruta completa al archivo adjunto.
     * @return bool
     */
    public static function enviaremail($para, $nombre, $de, $asunto, $mensaje, $adjunto = null)
    {
        // 1. Obtener la información de la configuración del portal
        // Utilizamos el método getSettings() que asegura que siempre tengamos una instancia.
        $settings = PortalSetting::getSettings();

        // 2. Definir el remitente usando la configuración
        $senderAddress = 'no-reply@fuertejob.com';
        $senderName = 'FuerteJob';

        // 3. Envío del Correo
        Mail::send('emails.fuertejob', [
            'nombre' => $nombre,
            'asunto' => $asunto,
            'email' => $de,
            'mensaje' => $mensaje,
            'settings' => $settings, // Pasamos la configuración del portal a la vista (antes 'empresa')
        ], function ($message) use ($senderAddress, $senderName, $asunto, $para, $adjunto) {

            $message->from($senderAddress, $senderName)
                ->to($para, $para)
                ->subject($asunto);

            if ($adjunto) {
                $message->attach($adjunto);
            }
        });

        return true;
    }
    public static function enviarEmailConPlantilla(User $user, string $templateType, array $data = []): bool
    {
        try {
            $template = EmailTemplate::where('type', $templateType)->first();

            if (!$template) {
                Log::error("MailController: Plantilla no encontrada para el tipo: " . $templateType);
                return false;
            }

            // 1. Reemplazar los placeholders en el Asunto y Cuerpo (para que el cuerpo HTML esté limpio para la vista)
            $subject = $template->subject;
            $body = $template->body;
            $replacements = [];
            foreach ($data as $key => $value) {
                $placeholder = '{{ $' . $key . ' }}';
                $subject = str_replace($placeholder, $value, $subject);
                // Pre-procesar el cuerpo para que la vista lo reciba con los datos inyectados
                $body = str_replace($placeholder, $value, $body);
            }

            // 2. Enviar el correo utilizando la vista Blade 'emails.fuertejob'
            Mail::send('emails.emailsgenericos', ['bodyContent' => $body], function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)
                    ->subject($subject);
            });

            Log::info("MailController: Correo enviado exitosamente a {$user->email} con plantilla {$templateType}");
            return true;
        } catch (\Exception $e) {
            Log::error("MailController: Error al enviar correo de tipo {$templateType} a {$user->email}. Error: " . $e->getMessage());
            return false;
        }
    }
}
