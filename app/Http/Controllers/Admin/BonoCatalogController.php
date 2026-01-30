<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonoCatalog;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\AnalyticsModel;

class BonoCatalogController extends Controller
{
    /**
     * Muestra una lista de todos los bonos del catálogo.
     * Diseñado para ser usado vía AJAX o para cargar la vista.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $bonos = BonoCatalog::with('analyticsModel')->latest()->get();
        $analyticsModels = AnalyticsModel::where('is_active', true)->orderBy('level')->get(['id', 'name', 'level']);

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'data' => $bonos
            ]);
        }

        return view('admin.bonos.index', compact('bonos', 'analyticsModels'));
    }

    /**
     * Almacena un nuevo bono en el catálogo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos
        $request->validate([
            'name'             => ['required', 'string', 'max:255', 'unique:bono_catalogs,name'],
            'description'      => ['nullable', 'string'],
            'price'            => ['required', 'numeric', 'min:0'],
            'offer_credits'    => ['nullable', 'integer', 'min:0'],
            'cv_views'         => ['nullable', 'integer', 'min:0'],
            'user_seats'       => ['nullable', 'integer', 'min:0'],
            'visibility_days'  => ['nullable', 'integer', 'min:0'],
            'duration_days'    => ['nullable', 'integer', 'min:1'],
            'credit_cost'      => ['required', 'integer', 'min:0'],
            'is_extra'         => ['required', 'boolean'],
            'is_active'        => ['required', 'boolean'],
            'destacado'       => ['required', 'boolean'],
        ]);

        $bono = DB::transaction(function () use ($request) {
            $payload = $request->only([
                'name',
                'description',
                'price',
                'offer_credits',
                'cv_views',
                'user_seats',
                'visibility_days',
                'duration_days',
                'credit_cost',
                'is_extra',
                'is_active',
                'destacado',
            ]);

            $payload['is_extra'] = $request->boolean('is_extra');

            $bono = BonoCatalog::create($payload);
            $bono->update([
                'credits_included' => $bono->offer_credits,
            ]);

            return $bono;
        });

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'message' => 'Bono creado exitosamente.',
                'data' => $bono
            ], 201);
        }

        return redirect()
            ->route('admin.bonos.index')
            ->with('success', 'Bono creado exitosamente.');
    }

    /**
     * Muestra el bono especificado.
     *
     * @param  \App\Models\BonoCatalog  $bono
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(BonoCatalog $bono)
    {
        return response()->json([
            'success' => true,
            'data' => $bono
        ]);
    }

    /**
     * Actualiza el bono especificado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BonoCatalog  $bono
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, BonoCatalog $bono)
    {
        // 1. Validación de los datos
        $request->validate([
            // La regla 'unique' debe ignorar al bono actual para permitir actualizar sin cambiar el nombre
            'name'             => ['required', 'string', 'max:255', Rule::unique('bono_catalogs')->ignore($bono->id)],
            'description'      => ['nullable', 'string'],
            'price'            => ['required', 'numeric', 'min:0'],
            'offer_credits'    => ['nullable', 'integer', 'min:0'],
            'cv_views'         => ['nullable', 'integer', 'min:0'],
            'user_seats'       => ['nullable', 'integer', 'min:0'],
            'visibility_days'  => ['nullable', 'integer', 'min:0'],
            'duration_days'    => ['nullable', 'integer', 'min:1'],
            'credit_cost'      => ['required', 'integer', 'min:0'],
            'is_extra'         => ['required', 'boolean'],
            'is_active'        => ['required', 'boolean'],
            'destacado'       => ['required', 'boolean'],
        ]);

        $bono = DB::transaction(function () use ($request, $bono) {
            $payload = $request->only([
                'name',
                'description',
                'price',
                'offer_credits',
                'cv_views',
                'user_seats',
                'visibility_days',
                'duration_days',
                'credit_cost',
                'is_extra',
                'is_active',
                'destacado',
            ]);

            $payload['is_extra'] = $request->boolean('is_extra');

            $bono->update($payload);
            $bono->update([
                'credits_included' => $bono->offer_credits,
            ]);

            return $bono;
        });

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'message' => 'Bono actualizado exitosamente.',
                'data' => $bono
            ]);
        }

        return redirect()
            ->route('admin.bonos.index')
            ->with('success', 'Bono actualizado exitosamente.');
    }

    /**
     * Elimina el bono especificado.
     *
     * @param  \App\Models\BonoCatalog  $bono
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, BonoCatalog $bono)
    {
        $bono->delete();

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'message' => 'Bono eliminado exitosamente.'
            ]);
        }

        return redirect()
            ->route('admin.bonos.index')
            ->with('success', 'Bono eliminado exitosamente.');
    }
}
