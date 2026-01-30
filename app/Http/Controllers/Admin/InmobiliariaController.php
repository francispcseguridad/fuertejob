<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inmobiliaria;
use App\Models\Island;
use Illuminate\Http\Request;

class InmobiliariaController extends Controller
{
    public function index()
    {
        $inmobiliarias = Inmobiliaria::with('island')->orderByDesc('created_at')->get();
        $islands = Island::orderBy('name')->get();

        return view('admin.inmobiliarias.index', compact('inmobiliarias', 'islands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'island_id' => 'required|exists:islands,id',
            'cropped_image' => 'nullable|string',
        ]);

        if ($request->filled('cropped_image')) {
            $storedLogo = $this->persistCroppedImage($request->input('cropped_image'));
            if ($storedLogo) {
                $validated['logo'] = $storedLogo;
            }
        }

        Inmobiliaria::create($validated);

        return redirect()->route('admin.inmobiliarias.index')->with('success', 'Inmobiliaria creada.');
    }

    public function update(Request $request, Inmobiliaria $inmobiliaria)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'island_id' => 'required|exists:islands,id',
            'cropped_image' => 'nullable|string',
        ]);

        $previousLogo = $inmobiliaria->logo;
        $newLogo = null;

        if ($request->filled('cropped_image')) {
            $newLogo = $this->persistCroppedImage($request->input('cropped_image'));
            if ($newLogo) {
                $validated['logo'] = $newLogo;
            }
        }

        $inmobiliaria->update($validated);

        if ($newLogo && $previousLogo) {
            $this->deleteStoredImage($previousLogo);
        }

        return redirect()->route('admin.inmobiliarias.index')->with('success', 'Inmobiliaria actualizada.');
    }

    public function destroy(Inmobiliaria $inmobiliaria)
    {
        $this->deleteStoredImage($inmobiliaria->logo);
        $inmobiliaria->delete();

        return back()->with('success', 'Inmobiliaria eliminada.');
    }

    private function persistCroppedImage(string $croppedImage): ?string
    {
        $parts = explode(';base64,', $croppedImage);
        if (count($parts) !== 2) {
            return null;
        }

        preg_match('/data:image\/([a-zA-Z0-9]+)/', $parts[0], $matches);
        $extension = $matches[1] ?? 'jpg';
        $decoded = base64_decode($parts[1]);
        if ($decoded === false) {
            return null;
        }

        $directory = public_path('img/inmobiliarias');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'inmobiliaria_' . uniqid() . '.' . $extension;
        file_put_contents($directory . '/' . $filename, $decoded);

        return '/img/inmobiliarias/' . $filename;
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
