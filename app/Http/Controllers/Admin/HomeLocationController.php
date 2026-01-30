<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeLocation;
use Illuminate\Http\Request;

class HomeLocationController extends Controller
{
    public function index()
    {
        $locations = HomeLocation::orderBy('order')->get();
        return view('admin.home_locations.index', compact('locations'));
    }
    public function create()
    {
        return view('admin.home_locations.create');
    }
    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'name' => 'required',
            'image' => 'nullable',
            'url' => 'nullable',
            'order' => 'integer',
            'is_active' => 'boolean'
        ]);

        if ($request->filled('cropped_image')) {
            // 1. Separar la data base64
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);

            // 2. Generar nombre único
            $filename = 'location_' . uniqid() . '.' . $image_type;

            // 3. Definir ruta: public/img/locations
            $path = public_path('img/locations');

            // 4. Crear carpeta si no existe
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // 5. Guardar el archivo físico
            file_put_contents($path . '/' . $filename, $image_base64);

            // 6. Guardar la URL pública en la BD
            $validated['image'] = '/img/locations/' . $filename;
        }

        HomeLocation::create($validated);
        return redirect()->route('admin.home_locations.index')->with('success', 'Guardado.');
    }

    public function edit(HomeLocation $homeLocation)
    {
        return view('admin.home_locations.edit', compact('homeLocation'));
    }

    public function update(Request $request, HomeLocation $homeLocation)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'name' => 'required',
            'image' => 'nullable',
            'url' => 'nullable',
            'order' => 'integer',
            'is_active' => 'boolean'
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'location_' . uniqid() . '.' . $image_type;
            $path = public_path('img/locations');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['image'] = '/img/locations/' . $filename;
        }

        $homeLocation->update($validated);
        return redirect()->route('admin.home_locations.index')->with('success', 'Actualizado.');
    }
    public function destroy(HomeLocation $homeLocation)
    {
        $homeLocation->delete();
        return back()->with('success', 'Eliminado.');
    }
}
