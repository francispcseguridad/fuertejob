<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSearchSection;
use Illuminate\Http\Request;

class HomeSearchSectionController extends Controller
{
    public function index()
    {
        $sections = HomeSearchSection::all();
        return view('admin.home_search_sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.home_search_sections.create');
    }

    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'background_image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'search_section_' . uniqid() . '.' . $image_type;
            $path = public_path('img/search_sections');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['background_image'] = '/img/search_sections/' . $filename;
        }

        HomeSearchSection::create($validated);

        return redirect()->route('admin.home_search_sections.index')->with('success', 'Sección creada correctamente.');
    }

    public function edit(HomeSearchSection $homeSearchSection)
    {
        return view('admin.home_search_sections.edit', compact('homeSearchSection'));
    }

    public function update(Request $request, HomeSearchSection $homeSearchSection)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'background_image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'search_section_' . uniqid() . '.' . $image_type;
            $path = public_path('img/search_sections');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['background_image'] = '/img/search_sections/' . $filename;
        }

        $homeSearchSection->update($validated);

        return redirect()->route('admin.home_search_sections.index')->with('success', 'Sección actualizada correctamente.');
    }

    public function destroy(HomeSearchSection $homeSearchSection)
    {
        $homeSearchSection->delete();
        return redirect()->route('admin.home_search_sections.index')->with('success', 'Sección eliminada correctamente.');
    }
}
