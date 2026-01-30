<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SectorController extends Controller
{
    /**
     * Display the management screen for sectors.
     */
    public function index()
    {
        $sectors = Sector::with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $parentOptions = Sector::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.sectors.index', compact('sectors', 'parentOptions'));
    }

    /**
     * Store a newly created sector.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $this->validateData($request);

        $validated['slug'] = $this->generateUniqueSlug($validated['slug'] ?? null, $validated['name']);

        Sector::create($validated);

        return redirect()
            ->route('admin.sectores.index')
            ->with('success', 'Sector creado correctamente.');
    }

    /**
     * Update the specified sector in storage.
     */
    public function update(Request $request, Sector $sector): RedirectResponse
    {
        $request->merge([
            'is_active' => $request->boolean('is_active'),
        ]);

        $validated = $this->validateData($request, $sector);

        $validated['slug'] = $this->generateUniqueSlug($validated['slug'] ?? null, $validated['name'], $sector->id);

        $sector->update($validated);

        return redirect()
            ->route('admin.sectores.index')
            ->with('success', 'Sector actualizado correctamente.');
    }

    /**
     * Remove the specified sector from storage.
     */
    public function destroy(Sector $sector): RedirectResponse
    {
        $sector->delete();

        return redirect()
            ->route('admin.sectores.index')
            ->with('success', 'Sector eliminado correctamente.');
    }

    protected function validateData(Request $request, ?Sector $sector = null): array
    {
        $slugRule = Rule::unique('sectors', 'slug');
        if ($sector) {
            $slugRule->ignore($sector->id);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:sectors,id',
                function ($attribute, $value, $fail) use ($sector) {
                    if ($sector && $value && (int) $value === $sector->id) {
                        $fail('Un sector no puede ser su propio padre.');
                    }
                },
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los :max caracteres.',
            'slug.unique' => 'El slug ya está en uso.',
            'slug.max' => 'El slug no puede superar los :max caracteres.',
            'parent_id.integer' => 'El sector padre seleccionado no es válido.',
            'parent_id.exists' => 'El sector padre seleccionado no existe.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
            'sort_order.min' => 'El orden debe ser mayor o igual que :min.',
            'is_active.boolean' => 'El campo estado debe ser verdadero o falso.',
        ];

        $attributes = [
            'name' => 'nombre',
            'slug' => 'slug',
            'parent_id' => 'sector padre',
            'sort_order' => 'orden',
            'is_active' => 'estado',
        ];

        return $request->validate($rules, $messages, $attributes);
    }

    protected function generateUniqueSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        if (empty($baseSlug)) {
            $baseSlug = Str::slug($name . '-' . uniqid());
        }

        $uniqueSlug = $baseSlug;
        $counter = 1;

        while (
            Sector::where('slug', $uniqueSlug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $uniqueSlug;
    }
}
