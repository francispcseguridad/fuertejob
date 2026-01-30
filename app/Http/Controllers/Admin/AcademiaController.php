<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Academia;
use App\Models\Island;
use Illuminate\Http\Request;

class AcademiaController extends Controller
{
    public function index()
    {
        $academias = Academia::with('island')->orderByDesc('created_at')->get();
        $islands = Island::orderBy('name')->get();

        return view('admin.academias.index', compact('academias', 'islands'));
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

        Academia::create($validated);

        return redirect()->route('admin.academias.index')->with('success', 'Academia creada.');
    }

    public function update(Request $request, Academia $academia)
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

        $previousLogo = $academia->logo;
        $newLogo = null;

        if ($request->filled('cropped_image')) {
            $newLogo = $this->persistCroppedImage($request->input('cropped_image'));
            if ($newLogo) {
                $validated['logo'] = $newLogo;
            }
        }

        $academia->update($validated);

        if ($newLogo && $previousLogo) {
            $this->deleteStoredImage($previousLogo);
        }

        return redirect()->route('admin.academias.index')->with('success', 'Academia actualizada.');
    }

    public function destroy(Academia $academia)
    {
        $this->deleteStoredImage($academia->logo);
        $academia->delete();

        return back()->with('success', 'Academia eliminada.');
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

        $directory = public_path('img/academias');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'academia_' . uniqid() . '.' . $extension;
        file_put_contents($directory . '/' . $filename, $decoded);

        return '/img/academias/' . $filename;
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
