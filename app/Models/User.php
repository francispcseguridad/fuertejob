<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\WorkerProfile;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\CustomVerificationMail;
use App\Http\Controllers\MailsController;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_WORKER = 'trabajador';
    public const ROLE_COMPANY = 'empresa';
    public const ROLE_COMPANY_COLLABORATOR = 'empresa_colaborador';

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function companyProfile(): HasOne
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    /**
     * Lógica centralizada para determinar si el usuario es "empresa".
     */
    public function hasCompanyRole(): bool
    {
        // Aceptamos el rol exacto o cualquier variación que empiece por empresa_
        return $this->rol === self::ROLE_COMPANY
            || $this->rol === self::ROLE_COMPANY_COLLABORATOR
            || (is_string($this->rol) && str_starts_with($this->rol, 'empresa_'));
    }

    // ... (resto de métodos del modelo se mantienen igual)

    public function sendEmailVerificationNotification()
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->getKey(), 'hash' => sha1($this->getEmailForVerification())]
        );
        Mail::to($this->email)->send(new CustomVerificationMail($this, $verificationUrl));
    }

    public function sendPasswordResetNotification($token)
    {
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $this->email], false));
        $supportEmail = config('mail.from.address', 'no-reply@fuertejob.com');
        $asunto = 'Restablece tu contraseña en FuerteJob';
        $mensaje = "Hola {$this->name},<br><br>Haz clic para restablecer:<br><a href=\"{$resetUrl}\">Restablecer</a>";
        MailsController::enviaremail($this->email, $this->name, $supportEmail, $asunto, $mensaje);
    }

    /**
     * Cuenta los mensajes no leídos para este usuario.
     * Para colaboradores de empresa, delega al usuario principal de la empresa.
     */
    public function unreadMessagesCount(): int
    {
        // Si es un colaborador de empresa, buscar el usuario principal de la empresa
        if ($this->rol === self::ROLE_COMPANY_COLLABORATOR) {
            // Buscar la membresía del colaborador para obtener el company_profile_id
            $membership = \App\Models\CompanyUserMembership::where('user_id', $this->id)->first();

            if ($membership && $membership->companyProfile) {
                // Obtener el usuario principal de la empresa (dueño del CompanyProfile)
                $mainUser = $membership->companyProfile->user;

                if ($mainUser) {
                    // Contar los mensajes no leídos del usuario principal
                    return \App\Models\Message::whereHas('thread', function ($query) use ($mainUser) {
                        $query->where('receiver_id', $mainUser->id);
                    })
                        ->whereNull('read_at')
                        ->count();
                }
            }

            // Si no se encuentra la empresa o el usuario principal, retornar 0
            return 0;
        }

        // Para usuarios normales (admin, trabajador, empresa principal)
        return \App\Models\Message::whereHas('thread', function ($query) {
            $query->where('receiver_id', $this->id);
        })
            ->whereNull('read_at')
            ->count();
    }
}
