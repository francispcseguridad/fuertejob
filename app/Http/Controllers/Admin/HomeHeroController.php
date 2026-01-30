<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeHero;
use Illuminate\Http\Request;

class HomeHeroController extends Controller
{
    public function index()
    {
        $heroes = HomeHero::all();
        return view('admin.home_heroes.index', compact('heroes'));
    }

    public function create()
    {
        return view('admin.home_heroes.create');
    }

    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button1_text' => 'nullable|string|max:255',
            'button1_url' => 'nullable|string|max:255',
            'button2_text' => 'nullable|string|max:255',
            'button2_url' => 'nullable|string|max:255',
            'background_image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'hero_' . uniqid() . '.' . $image_type;
            $path = public_path('img/banner');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['background_image'] = '/img/banner/' . $filename;
        }

        HomeHero::create($validated);

        return redirect()->route('admin.home_heroes.index')->with('success', 'Banner creado correctamente.');
    }

    public function edit(HomeHero $homeHero)
    {
        return view('admin.home_heroes.edit', compact('homeHero'));
    }

    public function update(Request $request, HomeHero $homeHero)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button1_text' => 'nullable|string|max:255',
            'button1_url' => 'nullable|string|max:255',
            'button2_text' => 'nullable|string|max:255',
            'button2_url' => 'nullable|string|max:255',
            'background_image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'hero_' . uniqid() . '.' . $image_type;
            $path = public_path('img/banner');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['background_image'] = '/img/banner/' . $filename;
        }

        $homeHero->update($validated);

        return redirect()->route('admin.home_heroes.index')->with('success', 'Banner actualizado correctamente.');
    }

    public function destroy(HomeHero $homeHero)
    {
        $homeHero->delete();
        return redirect()->route('admin.home_heroes.index')->with('success', 'Banner eliminado correctamente.');
    }
}
