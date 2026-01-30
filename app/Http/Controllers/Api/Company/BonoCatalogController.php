<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\BonoCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador para mostrar el catálogo de bonos disponibles a las empresas.
 */
class BonoCatalogController extends Controller
{
    /**
     * Muestra una lista de todos los bonos disponibles para la compra.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Se asume que solo queremos mostrar bonos que estén marcados como activos
        // y ordenados por precio o algún campo relevante.
        $bonos = BonoCatalog::where('is_active', true)
            ->orderBy('sort_order', 'asc') // Campo de ordenamiento opcional
            ->orderBy('price', 'asc')
            ->get([
                'id',
                'name',
                'description',
                'price',
                'bono_value', // El valor real o los créditos que otorga el bono
                'duration_days', // Por si el bono tiene una caducidad o duración
            ]);

        if ($bonos->isEmpty()) {
            return response()->json([
                'message' => 'Actualmente no hay bonos disponibles para la compra. Por favor, inténtelo más tarde.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Lista de bonos disponibles.',
            'data' => $bonos,
        ]);
    }

    /**
     * Muestra los detalles de un bono específico.
     *
     * @param int $id El ID del bono a mostrar.
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $bono = BonoCatalog::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail([
                'id',
                'name',
                'description',
                'price',
                'bono_value',
                'duration_days',
            ]);

        return response()->json([
            'message' => 'Detalles del bono',
            'data' => $bono,
        ]);
    }
}
