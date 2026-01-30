<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str; // Importar la clase Str para generar slugs
use Illuminate\Support\Arr;

/**
 * Controlador para la administración del contenido CMS (Páginas y Blog).
 */
class CmsContentController extends Controller
{
    /**
     * Muestra una lista paginada de todo el contenido CMS con filtros y búsqueda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $status = $request->get('status');

        $contents = CmsContent::query()
            ->when($search, function ($query, $search) {
                // Búsqueda por título o slug
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->when($type, function ($query, $type) {
                // Filtrar por tipo (page o blog)
                $query->where('type', $type);
            })
            ->when($status !== null, function ($query) use ($status) {
                // Filtrar por estado de publicación
                if ($status === 'published') {
                    $query->published();
                } elseif ($status === 'draft') {
                    $query->where('is_published', false);
                }
            })
            ->orderBy('type')
            ->orderByDesc('created_at')
            ->paginate(15) // Paginación de 15 elementos por página
            ->withQueryString(); // Mantener los parámetros de filtro en los enlaces de paginación

        $types = [
            CmsContent::TYPE_PAGE => 'Páginas',
            CmsContent::TYPE_BLOG => 'Blog',
        ];

        return view('admin.cms_contents.index', compact('contents', 'search', 'type', 'status', 'types'));
    }

    /**
     * Muestra el formulario para crear nuevo contenido.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $types = [
            CmsContent::TYPE_PAGE => 'Página Estática (Legal, Info)',
            CmsContent::TYPE_BLOG => 'Entrada de Blog',
        ];

        // Se asume que el usuario autenticado es el autor por defecto
        $currentUserId = auth()->id() ?? 1; // Usamos un fallback si no hay autenticación.

        $menuParents = Menu::orderBy('title')->get();
        $menuLocations = $this->getMenuLocations();
        $linkedMenu = null;
        $infoBaseUrl = $this->getInfoBaseUrl();

        return view('admin.cms_contents.create', compact(
            'types',
            'currentUserId',
            'menuParents',
            'menuLocations',
            'linkedMenu',
            'infoBaseUrl'
        ));
    }

    /**
     * Almacena nuevo contenido CMS en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Reglas de validación
        $validationRules = [
            'type' => ['required', Rule::in([CmsContent::TYPE_PAGE, CmsContent::TYPE_BLOG])],
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'slug' => 'nullable|string|max:255|unique:cms_contents,slug',
            'image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cropped_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ] + $this->menuValidationRules($request);

        $validatedData = $request->validate($validationRules);
        $menuData = $this->extractMenuRequestData($validatedData);
        $validatedData = Arr::except($validatedData, array_keys($menuData));

        // 2. Procesamiento y Asignación de Valores
        try {
            // Generar o validar el SLUG
            $slug = $validatedData['slug'] ?? Str::slug($validatedData['title']);
            $validatedData['slug'] = $this->makeUniqueSlug($slug);

            // Si es blog y está publicado, asegurar la fecha de publicación
            if ($validatedData['type'] === CmsContent::TYPE_BLOG && $request->has('is_published') && empty($validatedData['published_at'])) {
                $validatedData['published_at'] = now();
            }

            // Asegurarse de que is_published se establezca correctamente
            $validatedData['is_published'] = $request->has('is_published');

            // Manejar la imagen destacada (upload o crop)
            $this->handleFeaturedImage($request, $validatedData);
            unset($validatedData['cropped_image']);

            // 3. Creación del registro
            $cmsContent = CmsContent::create($validatedData);

            $this->syncMenuFromRequest($request, $cmsContent, $menuData);

            // 4. Redirección
            return redirect()->route('admin.cms_contents.index')
                ->with('success', 'El contenido CMS/Blog se ha creado exitosamente.');
        } catch (\Exception $e) {
            dd($e);
            Log::error('Error al crear CmsContent: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Hubo un error al crear el contenido. Inténtalo de nuevo.');
        }
    }

    /**
     * Muestra el formulario para editar contenido existente.
     *
     * @param  \App\Models\CmsContent  $cmsContent
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(CmsContent $contenido)
    {
        $types = [
            CmsContent::TYPE_PAGE => 'Página Estática (Legal, Info)',
            CmsContent::TYPE_BLOG => 'Entrada de Blog',
        ];

        $currentUserId = auth()->id() ?? 1;
        $linkedMenu = $this->findMenuForSlug($contenido->slug);
        $menuParents = Menu::when($linkedMenu, function ($query) use ($linkedMenu) {
            return $query->where('id', '!=', $linkedMenu->id);
        })->orderBy('title')->get();
        $menuLocations = $this->getMenuLocations();
        $infoBaseUrl = $this->getInfoBaseUrl();

        return view('admin.cms_contents.edit', [
            'cmsContent' => $contenido,
            'types' => $types,
            'currentUserId' => $currentUserId,
            'linkedMenu' => $linkedMenu,
            'menuParents' => $menuParents,
            'menuLocations' => $menuLocations,
            'infoBaseUrl' => $infoBaseUrl,
        ]);
    }

    /**
     * Actualiza contenido CMS existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CmsContent  $cmsContent
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CmsContent $contenido)
    {
        // 1. Reglas de validación (el slug debe ser único, excluyendo el actual)
        $validationRules = [
            'type' => ['required', Rule::in([CmsContent::TYPE_PAGE, CmsContent::TYPE_BLOG])],
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('cms_contents')->ignore($contenido->id)],
            'image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cropped_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ] + $this->menuValidationRules($request);

        $validatedData = $request->validate($validationRules);
        $menuData = $this->extractMenuRequestData($validatedData);
        $validatedData = Arr::except($validatedData, array_keys($menuData));

        // 2. Procesamiento y Asignación de Valores
        try {
            // Generar o validar el SLUG si no se proporcionó o es nulo
            $slug = $validatedData['slug'] ?? Str::slug($validatedData['title']);
            $validatedData['slug'] = $this->makeUniqueSlug($slug, $contenido->id);

            // Si se está publicando por primera vez, establecer published_at (solo si es blog)
            if ($validatedData['type'] === CmsContent::TYPE_BLOG && $request->has('is_published') && is_null($contenido->published_at)) {
                $validatedData['published_at'] = now();
            }

            // Asegurarse de que is_published se establezca correctamente
            $validatedData['is_published'] = $request->has('is_published');

            // Manejar la imagen destacada (upload o crop)
            $this->handleFeaturedImage($request, $validatedData, $contenido);
            unset($validatedData['cropped_image']);

            // 3. Actualización del registro
            $contenido->update($validatedData);
            $this->syncMenuFromRequest($request, $contenido, $menuData);

            // 4. Redirección
            return redirect()->route('admin.cms_contents.index')
                ->with('success', 'El contenido CMS/Blog se ha actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar CmsContent ID: ' . $contenido->id . ' - ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Hubo un error al actualizar el contenido. Inténtalo de nuevo.');
        }
    }

    /**
     * Elimina contenido CMS de la base de datos.
     *
     * @param  \App\Models\CmsContent  $cmsContent
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CmsContent $contenido)
    {
        try {
            $this->deleteImageIfExists($contenido->imagen_url);
            $contenido->delete();

            return redirect()->route('admin.cms_contents.index')
                ->with('success', 'El contenido CMS/Blog se ha eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar CmsContent ID: ' . $contenido->id . ' - ' . $e->getMessage());
            return back()
                ->with('error', 'Hubo un error al eliminar el contenido. Inténtalo de nuevo.');
        }
    }

    /**
     * Asegura que el slug sea único, añadiendo un sufijo si es necesario.
     *
     * @param string $slug
     * @param int|null $ignoreId ID del registro a ignorar para el chequeo de unicidad (en caso de actualización).
     * @return string
     */
    private function makeUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $originalSlug = $slug;
        $count = 1;

        while (CmsContent::where('slug', $slug)
            ->where('id', '!=', $ignoreId)
            ->exists()
        ) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }

        return $slug;
    }

    /**
     * Procesa la imagen enviada (archivo o base64) y guarda la ruta en el array validado.
     */
    private function handleFeaturedImage(Request $request, array &$validatedData, ?CmsContent $contenido = null): void
    {
        // 1. Check if we have any image input
        if (!$request->hasFile('image_upload') && !$request->filled('cropped_image')) {
            return;
        }

        $slug = $validatedData['slug'];
        $directory = public_path('img/cms');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // 2. Logic to delete old image if a new one is being set
        if ($contenido && $contenido->imagen_url) {
            $this->deleteImageIfExists($contenido->imagen_url);
        }

        // 3. PRIORITY 1: Cropped Base64 Image
        if ($request->filled('cropped_image')) {
            $imageParts = explode(';base64,', $request->input('cropped_image'));
            if (count($imageParts) === 2) {
                $extension = $this->getExtensionFromBase64($imageParts[0]);
                $filename = $slug . '.' . $extension;
                $imageBase64 = base64_decode($imageParts[1]);
                File::put($directory . '/' . $filename, $imageBase64);
                $validatedData['imagen_url'] = 'img/cms/' . $filename;
                return;
            }
        }

        // 4. PRIORITY 2: Raw File Upload
        if ($request->hasFile('image_upload')) {
            $file = $request->file('image_upload');
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
            if (!in_array($extension, ['jpg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }
            $filename = $slug . '.' . $extension;
            $file->move($directory, $filename);
            $validatedData['imagen_url'] = 'img/cms/' . $filename;
            return;
        }
    }

    /**
     * Elimina la imagen previa si existe en el sistema de archivos.
     */
    private function deleteImageIfExists(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $absolutePath = public_path($relativePath);
        if (File::exists($absolutePath)) {
            @File::delete($absolutePath);
        }
    }

    /**
     * Obtiene la extensión a partir del segmento mime enviado por Cropper.
     */
    private function getExtensionFromBase64(string $mimeSegment): string
    {
        $extension = 'jpg';

        if (str_contains($mimeSegment, 'image/')) {
            $parts = explode('image/', $mimeSegment);
            $extension = $parts[1] ?? 'jpg';
        }

        $extension = strtolower($extension);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $allowed = ['jpg', 'png', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            return 'jpg';
        }

        return $extension;
    }

    /**
     * Build validation rules for menu creation/update embedded in the CMS form.
     */
    private function menuValidationRules(Request $request): array
    {
        $locations = array_keys($this->getMenuLocations());

        return [
            'menu_enabled' => 'nullable|boolean',
            'menu_id' => 'nullable|exists:menus,id',
            'menu_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'menu_location' => [
                'nullable',
                'string',
                Rule::in($locations),
                Rule::requiredIf(fn() => $request->boolean('menu_enabled') && $request->input('type') !== CmsContent::TYPE_BLOG),
            ],
            'menu_parent_id' => [
                'nullable',
                'exists:menus,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->filled('menu_id') && (int) $request->input('menu_id') === (int) $value) {
                        $fail('El menú no puede ser padre de sí mismo.');
                    }
                },
            ],
            'menu_order' => 'nullable|integer',
            'menu_is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Extract only the menu fields from the validated data array.
     */
    private function extractMenuRequestData(array $validated): array
    {
        return Arr::only($validated, [
            'menu_enabled',
            'menu_id',
            'menu_title',
            'menu_location',
            'menu_parent_id',
            'menu_order',
            'menu_is_active',
        ]);
    }

    /**
     * Create or update the related menu entry for a CMS page.
     */
    private function syncMenuFromRequest(Request $request, CmsContent $cmsContent, array $menuData): void
    {
        $shouldSync = !empty($menuData['menu_enabled']) && $cmsContent->type === CmsContent::TYPE_PAGE;
        if (!$shouldSync) {
            return;
        }

        $url = $this->buildInfoUrlFromSlug($cmsContent->slug);
        $fallbackLocation = array_key_first($this->getMenuLocations()) ?: 'primary';

        $payload = [
            'title' => !empty($menuData['menu_title']) ? $menuData['menu_title'] : $cmsContent->title,
            'url' => $url,
            'parent_id' => !empty($menuData['menu_parent_id']) ? $menuData['menu_parent_id'] : null,
            'order' => isset($menuData['menu_order']) ? (int) $menuData['menu_order'] : 0,
            'location' => !empty($menuData['menu_location']) ? $menuData['menu_location'] : $fallbackLocation,
            'is_active' => !empty($menuData['menu_is_active']),
        ];

        $menu = null;
        if (!empty($menuData['menu_id'])) {
            $menu = Menu::find($menuData['menu_id']);
        }

        if (!$menu) {
            $menu = Menu::where('url', $url)->first();
        }

        if ($menu && $payload['parent_id'] && (int) $payload['parent_id'] === $menu->id) {
            $payload['parent_id'] = null;
        }

        if ($menu) {
            $menu->update($payload);
        } else {
            Menu::create($payload);
        }
    }

    /**
     * Available menu locations exposed to the CMS forms.
     */
    private function getMenuLocations(): array
    {
        return [
            'primary' => 'Barra Principal (Header)',
            'footer_1' => 'Footer Columna 1 (FuerteJob)',
            'footer_2' => 'Footer Columna 2 (Empresas)',
            'footer_3' => 'Footer Columna 3 (Solicitantes)',
        ];
    }

    /**
     * Returns the info page base URL (can be overridden via env).
     */
    private function getInfoBaseUrl(): string
    {
        $prefix = env('PORTAL_INFO_URL_PREFIX');

        if (!$prefix) {
            $prefix = 'https://www.fuertejob.com/info';
        }

        return rtrim($prefix, '/') . '/';
    }

    private function buildInfoUrlFromSlug(string $slug): string
    {
        return $this->getInfoBaseUrl() . ltrim($slug, '/');
    }

    private function findMenuForSlug(?string $slug): ?Menu
    {
        if (!$slug) {
            return null;
        }

        return Menu::where('url', $this->buildInfoUrlFromSlug($slug))->first();
    }
}
