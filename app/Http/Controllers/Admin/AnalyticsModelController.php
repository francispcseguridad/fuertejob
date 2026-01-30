<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsModel;
use App\Models\AnalyticsFunction;
use App\Models\BonoCatalog;
use Illuminate\Http\Request;

class AnalyticsModelController extends Controller
{
    public function index()
    {
        $models = AnalyticsModel::with('functions.bonoCatalogs')
            ->orderBy('level')
            ->get();
        $bonos = BonoCatalog::orderBy('name')->get(['id', 'name']);
        return view('admin.analytics_models.index', compact('models', 'bonos'));
    }

    public function updateFunctionBonos(Request $request, AnalyticsFunction $analyticsFunction)
    {
        $request->validate([
            'bono_catalog_ids' => ['nullable', 'array'],
            'bono_catalog_ids.*' => ['integer', 'exists:bono_catalogs,id'],
        ]);

        $analyticsFunction->bonoCatalogs()->sync($request->input('bono_catalog_ids', []));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Vínculos actualizados correctamente.')
            ]);
        }

        return back()->with('success', __('Vínculos actualizados correctamente.'));
    }
}
