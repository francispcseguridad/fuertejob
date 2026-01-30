<?php

namespace App\Services;

use App\Models\JobOffer;
use App\Models\WorkerProfile;
use App\Models\Thread;
use App\Http\Controllers\MailsController;
use Illuminate\Support\Facades\Auth;

class CandidateInterestNotifier
{
    /**
     * Envia notificaciones por email y mensaje dentro de la app cuando una empresa muestra interés.
     */
    public static function notify(WorkerProfile $workerProfile, JobOffer $jobOffer, ?int $senderIdOverride = null): void
    {
        $workerUser = $workerProfile->user;
        if (!$workerUser || !$workerUser->email) {
            return;
        }

        $companyProfile = $jobOffer->companyProfile;
        $companyName = $companyProfile->company_name ?? 'Una empresa';
        $companyEmail = $companyProfile->contact_email ?? $companyProfile->email ?? 'info@fuertejob.com';
        $recipientName = $workerUser->name ?? trim("{$workerProfile->first_name} {$workerProfile->last_name}") ?? 'Candidato';

        $subject = "La empresa {$companyName} ha mostrado interés en tu perfil";
        $message = "Hola {$recipientName},<br><br>"
            . "La empresa <strong>{$companyName}</strong> ha mostrado interés en tu perfil para la oferta <strong>{$jobOffer->title}</strong>.<br>"
            . "Puedes continuar la conversación desde el panel de mensajes de FuerteJob.<br><br>"
            . "Un saludo,<br>Equipo FuerteJob";

        MailsController::enviaremail(
            $workerUser->email,
            $recipientName,
            $companyEmail,
            $subject,
            $message
        );

        $senderId = $senderIdOverride ?? Auth::id();
        $receiverId = $workerUser->id;

        if (!$senderId || $senderId === $receiverId) {
            return;
        }

        $starterId = min($senderId, $receiverId);
        $receiverThreadId = max($senderId, $receiverId);

        $thread = Thread::firstOrCreate([
            'starter_id' => $starterId,
            'receiver_id' => $receiverThreadId,
            'resource_type' => JobOffer::class,
            'resource_id' => $jobOffer->id,
        ], [
            'last_message_at' => now(),
        ]);

        $thread->messages()->create([
            'sender_id' => $senderId,
            'content' => "La empresa {$companyName} ha mostrado interés en tu perfil para la oferta \"{$jobOffer->title}\" en FuerteJob.",
        ]);

        $thread->update(['last_message_at' => now()]);
    }
}
