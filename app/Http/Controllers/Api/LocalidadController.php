<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CanaryLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
    /**
     * Autocompletado de localidades.
     *
     * - `provider=local`: solo base de datos local (canary_locations)
     * - `provider=locationiq`: solo LocationIQ (si hay API key configurada)
     * - `provider=auto` (default): devuelve resultados locales y, si hay API key, también LocationIQ
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $provider = (string) $request->query('provider', 'auto'); // auto|local|locationiq
        $limit = (int) $request->query('limit', 10);
        $limit = max(1, min($limit, 20));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $localResults = [];
        if ($provider !== 'locationiq') {
            $localResults = CanaryLocation::query()
                ->where(function ($query) use ($q) {
                    $query->where('city', 'like', '%' . $q . '%')
                        ->orWhere('island', 'like', '%' . $q . '%')
                        ->orWhere('province', 'like', '%' . $q . '%');
                })
                ->orderBy('city')
                ->limit($limit)
                ->get(['city', 'island', 'province', 'country'])
                ->map(function (CanaryLocation $row) {
                    return [
                        'city' => $row->city,
                        'province' => $row->province,
                        'island' => $row->island,
                        'country' => $row->country,
                    ];
                })
                ->all();
        }

        if ($provider === 'local') {
            return response()->json($this->uniqueResults($localResults, $limit));
        }

        $locationIqKey = (string) config('services.locationiq.key', '');
        $locationIqBaseUrl = rtrim((string) config('services.locationiq.base_url', 'https://api.locationiq.com/v1'), '/');

        if ($locationIqKey === '') {
            return response()->json($this->uniqueResults($localResults, $limit));
        }

        $locationIqResults = $this->searchLocationIq($locationIqBaseUrl, $locationIqKey, $q, $limit);

        $merged = $provider === 'locationiq'
            ? $locationIqResults
            : array_merge($localResults, $locationIqResults);

        return response()->json($this->uniqueResults($merged, $limit));
    }

    private function searchLocationIq(string $baseUrl, string $apiKey, string $q, int $limit): array
    {
        $cacheKey = 'locationiq.autocomplete:' . md5(mb_strtolower($q) . '|' . $limit);

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($baseUrl, $apiKey, $q, $limit) {
            try {
                $response = Http::acceptJson()
                    ->timeout(3)
                    ->retry(1, 200)
                    ->get($baseUrl . '/autocomplete', [
                        'key' => $apiKey,
                        'q' => $q,
                        'limit' => min($limit, 10),
                        'tag' => 'place:city,place:town,place:village,place:hamlet',
                    ]);

                if (!$response->ok()) {
                    return [];
                }

                $data = $response->json();
                if (!is_array($data)) {
                    return [];
                }

                $results = [];
                foreach ($data as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $address = is_array($item['address'] ?? null) ? $item['address'] : [];

                    $city = $address['city']
                        ?? $address['town']
                        ?? $address['village']
                        ?? $address['hamlet']
                        ?? $address['name']
                        ?? null;

                    $country = $address['country'] ?? '';
                    $province = $address['province']
                        ?? $address['state']
                        ?? $address['county']
                        ?? '';
                    $island = $address['island'] ?? '';

                    if (!is_string($city) || trim($city) === '') {
                        continue;
                    }

                    $results[] = [
                        'city' => trim($city),
                        'province' => is_string($province) ? trim($province) : '',
                        'island' => is_string($island) ? trim($island) : '',
                        'country' => is_string($country) ? trim($country) : '',
                    ];
                }

                return $results;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    private function uniqueResults(array $items, int $limit): array
    {
        $unique = [];
        $seen = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $city = trim((string) ($item['city'] ?? ''));
            if ($city === '') {
                continue;
            }

            $province = trim((string) ($item['province'] ?? ''));
            $island = trim((string) ($item['island'] ?? ''));
            $country = trim((string) ($item['country'] ?? ''));

            $key = mb_strtolower($city . '|' . $province . '|' . $island . '|' . $country);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = [
                'city' => $city,
                'province' => $province,
                'island' => $island,
                'country' => $country,
            ];

            if (count($unique) >= $limit) {
                break;
            }
        }

        return $unique;
    }
}
