<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanySectorController extends Controller
{
    /**
     * Buscar sectores activos para el autocompletado.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->get('term', ''));

        $sectors = Sector::query()
            ->with('parent')
            ->where('is_active', true)
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%')
                        ->orWhereHas('parent', function ($parentQuery) use ($term) {
                            $parentQuery->where('name', 'like', '%' . $term . '%');
                        });
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function (Sector $sector) {
                $label = $sector->parent
                    ? $sector->parent->name . ' · ' . $sector->name
                    : $sector->name;

                return [
                    'id' => $sector->id,
                    'label' => $label,
                    'value' => $label,
                ];
            });

        return response()->json($sectors);
    }

    /**
     * Asocia un sector existente al perfil de la empresa.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sector_id' => 'required|exists:sectors,id',
        ]);

        $profile = Auth::user()->companyProfile;

        if (!$profile) {
            return response()->json([
                'error' => 'Debes completar y guardar la ficha antes de añadir sectores.',
            ], 422);
        }

        $alreadyAttached = $profile->sectors()
            ->where('sector_id', $data['sector_id'])
            ->exists();

        if ($alreadyAttached) {
            return response()->json([
                'message' => 'Ese sector ya está asociado a tu ficha.',
                'already_attached' => true,
            ]);
        }

        $profile->sectors()->attach($data['sector_id']);

        $sector = Sector::with('parent')->find($data['sector_id']);

        return response()->json([
            'message' => 'Sector añadido correctamente.',
            'sector' => [
                'id' => $sector->id,
                'label' => $sector->parent
                    ? $sector->parent->name . ' · ' . $sector->name
                    : $sector->name,
            ],
        ], 201);
    }

    /**
     * Elimina la asociación de un sector del perfil.
     */
    public function destroy(Sector $sector)
    {
        $profile = Auth::user()->companyProfile;

        if (!$profile) {
            return response()->json([
                'error' => 'Debes completar y guardar la ficha antes de gestionar sectores.',
            ], 422);
        }

        $isAttached = $profile->sectors()
            ->where('sector_id', $sector->id)
            ->exists();

        if (!$isAttached) {
            return response()->json([
                'error' => 'Ese sector no está asociado a tu ficha.',
            ], 404);
        }

        $profile->sectors()->detach($sector->id);

        return response()->json([
            'message' => 'Sector eliminado correctamente.',
        ]);
    }
}
