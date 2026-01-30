<?php

namespace App\Services;

use App\Http\Controllers\MailsController;
use App\Models\JobOffer;
use Illuminate\Support\Facades\Log;

class JobOfferPublicationNotifier
{
    public function __construct(private JobOfferMatcher $matcher) {}

    public function notify(JobOffer $offer): void
    {
        if ($offer->status !== 'Publicado' || !$offer->is_published) {
            return;
        }

        $recipients = $this->matcher->getPublicationRecipients($offer);
        if ($recipients->isEmpty()) {
            Log::info("No se encontraron trabajadores coincidentes para la oferta {$offer->id} ({$offer->title}).");
            return;
        }

        $companyName = $offer->companyProfile?->company_name ?? 'FuerteJob';
        $sectorName = $offer->jobSector?->name ?? 'tu sector preferido';
        $jobLink = route('jobs.show', ['id' => $offer->id]);
        $subject = "Nueva oferta publicada: {$offer->title}";

        foreach ($recipients as $worker) {
            $user = $worker->user;
            if (!$user || empty($user->email)) {
                continue;
            }

            $recipientName = $worker->first_name ?? $user->name ?? 'Candidato';
            $message = "
                <p>Hola {$recipientName},</p>
                <p>Te avisamos porque tu perfil tiene afinidad con el sector <strong>{$sectorName}</strong> o alcanza un match del 40% con la oferta <strong>{$offer->title}</strong> que acaba de publicar {$companyName}.</p>
                <p>Revisa los detalles y postúlate si encaja con tu experiencia:</p>
                <p><a href=\"{$jobLink}\" style=\"color:#1d4ed8;\">Ver oferta y postular</a></p>
                <p>Gracias por confiar en FuerteJob.</p>
            ";

            MailsController::enviaremail(
                $user->email,
                $recipientName,
                'info@fuertejob.com',
                $subject,
                $message
            );
        }

        Log::info("Notificaciones enviadas a {$recipients->count()} trabajadores para la oferta {$offer->id}.");
    }
}
