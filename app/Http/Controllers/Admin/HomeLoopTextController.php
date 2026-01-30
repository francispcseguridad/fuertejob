<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeLoopText;
use Illuminate\Http\Request;

class HomeLoopTextController extends Controller
{
    public function index()
    {
        $texts = HomeLoopText::all();
        return view('admin.home_loop_texts.index', compact('texts'));
    }
    public function create()
    {
        return view('admin.home_loop_texts.create');
    }
    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'content' => 'required|string',
            'is_active' => 'boolean',
            'url' => 'nullable|url|max:255',
        ]);

        if ($request->filled('cropped_image')) {
            $storedPath = $this->persistCroppedImage($request->input('cropped_image'));
            if ($storedPath) {
                $validated['image'] = $storedPath;
            }
        }

        HomeLoopText::create($validated);

        return redirect()->route('admin.home_loop_texts.index')->with('success', 'Guardado.');
    }
    public function edit(HomeLoopText $homeLoopText)
    {
        return view('admin.home_loop_texts.edit', compact('homeLoopText'));
    }
    public function update(Request $request, HomeLoopText $homeLoopText)
    {
        $request->merge(['is_active' => $request->has('is_active')]);

        $validated = $request->validate([
            'content' => 'required|string',
            'is_active' => 'boolean',
            'url' => 'nullable|url|max:255',
        ]);

        $previousImage = $homeLoopText->image;
        $newImage = null;
        if ($request->filled('cropped_image')) {
            $newImage = $this->persistCroppedImage($request->input('cropped_image'));
            if ($newImage) {
                $validated['image'] = $newImage;
            }
        }

        $homeLoopText->update($validated);

        if ($newImage && $previousImage) {
            $this->deleteStoredImage($previousImage);
        }

        return redirect()->route('admin.home_loop_texts.index')->with('success', 'Actualizado.');
    }

    public function destroy(HomeLoopText $homeLoopText)
    {
        $this->deleteStoredImage($homeLoopText->image);
        $homeLoopText->delete();
        return back()->with('success', 'Eliminado.');
    }

    private function persistCroppedImage(string $croppedImage): ?string
    {
        $parts = explode(';base64,', $croppedImage);
        if (count($parts) !== 2) {
            return null;
        }

        preg_match('/data:image\\/([a-zA-Z0-9]+)/', $parts[0], $matches);
        $extension = $matches[1] ?? 'jpg';
        $decodedImage = base64_decode($parts[1]);
        if ($decodedImage === false) {
            return null;
        }

        $directory = public_path('img/home-loop');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'loop_' . uniqid() . '.' . $extension;
        file_put_contents($directory . '/' . $filename, $decodedImage);

        return '/img/home-loop/' . $filename;
    }

    private function deleteStoredImage(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $absolutePath = public_path(ltrim($relativePath, '/'));
        if (file_exists($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
