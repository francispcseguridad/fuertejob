<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeParallaxImage;
use Illuminate\Http\Request;

class HomeParallaxImageController extends Controller
{
    public function index()
    {
        $images = HomeParallaxImage::all();
        return view('admin.home_parallax_images.index', compact('images'));
    }

    public function create()
    {
        return view('admin.home_parallax_images.create');
    }

    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'parallax_' . uniqid() . '.' . $image_type;
            $path = public_path('img/parallax');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['image'] = '/img/parallax/' . $filename;
        }

        HomeParallaxImage::create($validated);

        return redirect()->route('admin.home_parallax_images.index')->with('success', 'Imagen creada correctamente.');
    }

    public function edit(HomeParallaxImage $homeParallaxImage)
    {
        return view('admin.home_parallax_images.edit', compact('homeParallaxImage'));
    }

    public function update(Request $request, HomeParallaxImage $homeParallaxImage)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = 'parallax_' . uniqid() . '.' . $image_type;
            $path = public_path('img/parallax');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . '/' . $filename, $image_base64);
            $validated['image'] = '/img/parallax/' . $filename;
        }

        $homeParallaxImage->update($validated);

        return redirect()->route('admin.home_parallax_images.index')->with('success', 'Imagen actualizada correctamente.');
    }

    public function destroy(HomeParallaxImage $homeParallaxImage)
    {
        $homeParallaxImage->delete();
        return redirect()->route('admin.home_parallax_images.index')->with('success', 'Imagen eliminada correctamente.');
    }
}
