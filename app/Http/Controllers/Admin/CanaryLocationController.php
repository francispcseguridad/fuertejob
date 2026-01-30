<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CanaryLocation;
use App\Models\Island;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CanaryLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('q');
        $province = $request->get('province');
        $island = $request->get('island');

        $locationsQuery = CanaryLocation::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('city', 'like', '%' . $search . '%')
                        ->orWhere('province', 'like', '%' . $search . '%')
                        ->orWhere('island', 'like', '%' . $search . '%')
                        ->orWhere('country', 'like', '%' . $search . '%');
                });
            })
            ->when($province, function ($query) use ($province) {
                $query->where('province', 'like', '%' . $province . '%');
            })
            ->when($island, function ($query) use ($island) {
                $query->where('island', 'like', '%' . $island . '%');
            })
            ->orderBy('city')
            ->orderBy('island')
            ->orderBy('province')
            ->orderBy('country');

        $locations = $locationsQuery->paginate(20)->withQueryString();

        $islandOptions = Island::query()
            ->orderBy('name')
            ->pluck('name');

        $islandProvinceMap = Island::query()
            ->whereNotNull('province')
            ->pluck('province', 'name');

        $provinceOptions = CanaryLocation::query()
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        return view('admin.canary_locations.index', compact('locations', 'search', 'province', 'island', 'islandOptions', 'provinceOptions', 'islandProvinceMap'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $location = new CanaryLocation();

        return view('admin.canary_locations.create', compact('location'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $data['province'] = $this->resolveProvinceFromIsland($data['island'] ?? null) ?? $data['province'] ?? null;

        CanaryLocation::updateOrCreate(
            ['city' => $data['city'], 'island' => $data['island']],
            [
                'province' => $data['province'],
                'country' => $data['country'],
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Municipio/Localidad creado correctamente.',
            ]);
        }

        return redirect()->route('admin.localidades.index')
            ->with('success', 'Municipio/Localidad creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CanaryLocation $canary_location)
    {
        return view('admin.canary_locations.edit', ['location' => $canary_location]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CanaryLocation $canary_location)
    {
        $data = $this->validateData($request, $canary_location);

        $data['province'] = $this->resolveProvinceFromIsland($data['island'] ?? null) ?? $data['province'] ?? null;

        CanaryLocation::updateOrCreate(
            ['id' => $canary_location->id],
            [
                'city' => $data['city'],
                'island' => $data['island'],
                'province' => $data['province'],
                'country' => $data['country'],
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Municipio/Localidad actualizado correctamente.',
            ]);
        }

        return redirect()->route('admin.localidades.index')
            ->with('success', 'Municipio/Localidad actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CanaryLocation $canary_location)
    {
        try {
            $canary_location->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Municipio/Localidad eliminado.',
                ]);
            }

            return redirect()->route('admin.localidades.index')
                ->with('success', 'Municipio/Localidad eliminado.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo eliminar: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->route('admin.localidades.index')
                ->with('error', 'No se pudo eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Centraliza las reglas de validación para crear/editar.
     */
    protected function validateData(Request $request, ?CanaryLocation $location = null): array
    {
        $id = $location?->id;

        return $request->validate([
            'city' => [
                'required',
                'string',
                'max:255',
                Rule::unique('canary_locations')->where(function ($query) use ($request) {
                    return $query
                        ->where('city', $request->input('city'))
                        ->where('province', $request->input('province'))
                        ->where('island', $request->input('island'));
                })->ignore($id),
            ],
            'province' => ['nullable', 'string', 'max:255'],
            'island' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Intenta resolver la provincia a partir de la isla seleccionada.
     */
    protected function resolveProvinceFromIsland(?string $island): ?string
    {
        if (empty($island)) {
            return null;
        }

        return Island::where('name', $island)->value('province');
    }
}
