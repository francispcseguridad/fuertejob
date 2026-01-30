<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSector;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HomeSectorController extends Controller
{
    public function index()
    {
        $sectors = HomeSector::orderBy('order')->get();
        $sectors->each(function (HomeSector $sector) {
            $sector->sector_reference_id = $this->resolveSectorId($sector);
        });

        return view('admin.home_sectors.index', compact('sectors'));
    }
    public function create()
    {
        return view('admin.home_sectors.create');
    }
    public function store(Request $request)
    {
        $request->merge(['is_active' => $request->boolean('is_active')]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector_id' => 'required|integer|exists:sectors,id',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['url'] = $this->buildSectorUrl((int) $request->input('sector_id'));

        // Handle image upload (Slim JSON or Standard File)
        if ($imagePath = $this->handleImageUpload($request, 'sector_image')) {
            $validated['image'] = $imagePath;
        }

        HomeSector::create($validated);

        return redirect()->route('admin.home_sectors.index')->with('success', 'Guardado.');
    }

    public function edit(HomeSector $homeSector)
    {
        return view('admin.home_sectors.edit', compact('homeSector'));
    }

    public function update(Request $request, HomeSector $homeSector)
    {
        $request->merge(['is_active' => $request->boolean('is_active')]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector_id' => 'required|integer|exists:sectors,id',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['url'] = $this->buildSectorUrl((int) $request->input('sector_id'));

        if ($imagePath = $this->handleImageUpload($request, 'sector_image')) {
            $this->deleteImage($homeSector->image);
            $validated['image'] = $imagePath;
        }

        $homeSector->update($validated);

        return redirect()->route('admin.home_sectors.index')->with('success', 'Actualizado.');
    }

    public function destroy(HomeSector $homeSector)
    {
        $this->deleteImage($homeSector->image);
        $homeSector->delete();
        return back()->with('success', 'Eliminado.');
    }

    private function buildSectorUrl(int $sectorId): string
    {
        return 'https://www.fuertejob.com/empleos?search=&province=&island=&sectors%5B%5D=' . $sectorId . '&modality=&contract_type=';
    }

    private function handleImageUpload(Request $request, string $key): ?string
    {
        // 1. Try Cropper.js Base64
        if ($request->filled('cropped_image')) {
            $image_parts = explode(";base64,", $request->input('cropped_image'));
            if (count($image_parts) >= 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1] ?? 'jpg';
                $image_base64 = base64_decode($image_parts[1]);
                $filename = 'sector_' . uniqid() . '.' . $image_type;
                $directory = public_path('img/sectors');

                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }

                file_put_contents($directory . '/' . $filename, $image_base64);
                return '/img/sectors/' . $filename;
            }
        }

        // 2. Try Standard File (Fallback)
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            if ($file->isValid()) {
                $filename = 'sector_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $directory = public_path('img/sectors');
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }
                $file->move($directory, $filename);
                return '/img/sectors/' . $filename;
            }
        }

        return null;
    }

    private function saveBase64Image(string $imageData): ?string
    {
        [$meta, $content] = array_pad(explode(',', $imageData, 2), 2, null);
        $content ??= $meta;
        $meta = $content === $meta ? '' : $meta;

        if (!$content) {
            return null;
        }

        $binary = base64_decode($content);

        if ($binary === false) {
            return null;
        }

        $extension = 'jpg';
        if ($meta && preg_match('/image\/(\w+)/', $meta, $matches)) {
            $extension = $matches[1] === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        }

        $filename = 'sector_' . uniqid() . '.' . $extension;
        $directory = public_path('img/sectors');

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($directory . '/' . $filename, $binary);

        return '/img/sectors/' . $filename;
    }

    private function normalizeSlimPayload($payload): ?array
    {
        if (!$payload) {
            return null;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeSlimPayload($decoded);
            }
            return null;
        }

        if (is_array($payload)) {
            if (isset($payload['output'])) {
                return $payload;
            }
            if (isset($payload[0]) && is_array($payload[0])) {
                return $this->normalizeSlimPayload($payload[0]);
            }
        }

        return null;
    }

    private function resolveSectorId(HomeSector $sector): ?int
    {
        $fromUrl = $this->extractSectorIdFromUrl($sector->url);
        if ($fromUrl !== null) {
            return $fromUrl;
        }

        $match = Sector::where('name', $sector->name)->value('id');

        return $match !== null ? (int) $match : null;
    }

    private function extractSectorIdFromUrl(?string $url): ?int
    {
        if (!$url) {
            return null;
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if (!$query) {
            return null;
        }

        parse_str($query, $params);

        if (!isset($params['sectors'])) {
            return null;
        }

        $sectorParam = $params['sectors'];
        if (is_array($sectorParam)) {
            $sectorParam = $sectorParam[0] ?? null;
        }

        return $sectorParam !== null ? (int) $sectorParam : null;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
