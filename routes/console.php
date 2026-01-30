<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\CanaryLocation;
use App\Models\WorkerProfile;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fuertejob:backfill-worker-locations {--dry-run : No guarda cambios, solo muestra conteos}', function () {
    $dryRun = (bool) $this->option('dry-run');

    $query = WorkerProfile::query()
        ->where(function ($q) {
            $q->whereNull('province')
                ->orWhereNull('island')
                ->orWhereNull('country');
        })
        ->whereNotNull('city');

    $total = (int) $query->count();
    $this->info("Perfiles candidatos: {$total}");

    $updated = 0;
    $notFound = 0;

    $query->orderBy('id')->chunkById(200, function ($profiles) use ($dryRun, &$updated, &$notFound) {
        foreach ($profiles as $profile) {
            $city = trim((string) $profile->city);
            if ($city === '') {
                continue;
            }

            $location = CanaryLocation::query()
                ->whereRaw('LOWER(city) = ?', [Str::lower($city)])
                ->first();

            if (!$location) {
                $notFound++;
                continue;
            }

            $profile->province = $profile->province ?: $location->province;
            $profile->island = $profile->island ?: $location->island;
            $profile->country = $profile->country ?: $location->country;

            if (!$dryRun) {
                $profile->save();
            }

            $updated++;
        }
    });

    $this->info("Actualizados: {$updated}" . ($dryRun ? ' (dry-run)' : ''));
    $this->warn("Sin coincidencia en canary_locations: {$notFound}");
})->purpose('Rellena province/island/country en worker_profiles usando canary_locations');
