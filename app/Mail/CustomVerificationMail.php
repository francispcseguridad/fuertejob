<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CustomVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    /**
     * Crea una nueva instancia del mensaje.
     */
    public function __construct($user, $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Obtiene la envoltura del mensaje.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verificación de Correo Electrónico | ¡Activa tu Cuenta!',
        );
    }

    /**
     * Obtiene la definición del contenido del mensaje (Apunta a la vista HTML).
     */
    public function content(): Content
    {
        // El campo 'html' se utiliza para renderizar la plantilla HTML pura.
        return new Content(
            // Utilizamos el nombre de la vista que contiene la plantilla HTML.
            html: 'emails.verification_email_html',
            // Pasamos las variables requeridas por la plantilla HTML.
            with: [
                'name' => $this->user->name ?? 'Usuario',
                'url' => $this->verificationUrl,
            ],
        );
    }

    /**
     * Obtiene los archivos adjuntos para el mensaje.
     */
    public function attachments(): array
    {
        return [];
    }
}
