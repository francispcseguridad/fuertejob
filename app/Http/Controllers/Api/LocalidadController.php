<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CanaryLocation;
use Illuminate\Http\Request;

class LocalidadController extends Controller
{
    /**
     * Devuelve coincidencias de la tabla canary_locations para autocompletado.
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = CanaryLocation::query()
            ->where(function ($query) use ($q) {
                $query->where('city', 'like', '%' . $q . '%')
                    ->orWhere('island', 'like', '%' . $q . '%')
                    ->orWhere('province', 'like', '%' . $q . '%');
            })
            ->orderBy('city')
            ->limit(10)
            ->get(['city', 'island', 'province', 'country']);

        return response()->json($results);
    }
}
