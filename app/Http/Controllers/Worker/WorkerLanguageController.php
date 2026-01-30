<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Language;
use App\Models\WorkerProfile;

class WorkerLanguageController extends Controller
{
    /**
     * Búsqueda AJAX para autocomplete de idiomas.
     */
    public function search(Request $request)
    {
        $term = $request->get('term');

        $languages = Language::where('name', 'LIKE', '%' . $term . '%')
            ->limit(10)
            ->pluck('name');

        return response()->json($languages);
    }

    /**
     * Muestra la lista de todos los idiomas del trabajador.
     */
    public function index()
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Debes completar tu perfil para gestionar idiomas.');
        }

        // Cargar idiomas con el campo 'level' de la tabla pivote
        $languages = $profile->languages()->withPivot('level')->get();

        return view('worker.languages.index', compact('profile', 'languages'));
    }

    /**
     * Muestra el formulario para crear un nuevo idioma.
     */
    public function create()
    {
        return view('worker.languages.create');
    }

    /**
     * Almacena un nuevo idioma en la base de datos y lo adjunta al perfil.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:50',
        ]);

        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return back()->with('error', 'Perfil no encontrado. No se pudo guardar el idioma.');
        }

        // Busca o crea el idioma en el catálogo
        $language = Language::firstOrCreate(['name' => strtolower(trim($validatedData['name']))]);

        // Verifica si el idioma ya está asociado al perfil
        if ($profile->languages->contains($language->id)) {
            return back()->with('error', 'Este idioma ya está en tu perfil.');
        }

        // Adjunta el idioma al perfil con el nivel en la tabla pivote
        $profile->languages()->attach($language->id, ['level' => $validatedData['level'] ?? null]);

        return redirect()->route('worker.languages.index')->with('success', 'Idioma añadido con éxito.');
    }

    /**
     * Muestra el formulario para editar un idioma existente (Catálogo).
     */
    public function edit($idioma)
    {
        $language = Language::findOrFail($idioma);

        if (!Auth::user()->workerProfile->languages->contains($language->id)) {
            abort(403, 'No tienes este idioma asociado a tu perfil.');
        }

        return view('worker.languages.edit', compact('language'));
    }

    /**
     * Actualiza un idioma existente y su nivel en la tabla pivote.
     * Si solo este perfil usa el idioma, se actualiza en el catálogo global.
     * Si otros perfiles también lo usan, se crea un nuevo idioma y se asocia al usuario.
     */
    public function update(Request $request, $idioma)
    {
        // Obtener el idioma por ID
        $language = Language::findOrFail($idioma);

        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            $errorMessage = 'Perfil no encontrado.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            abort(404, $errorMessage);
        }

        // Verificar que el idioma está asociado al perfil
        if (!$profile->languages->contains($language->id)) {
            $errorMessage = 'No tienes este idioma asociado a tu perfil.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 403);
            }
            abort(403, $errorMessage);
        }

        // Validar los datos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'nullable|string|max:50',
        ]);

        $newName = strtolower(trim($validatedData['name']));
        $newLevel = $validatedData['level'] ?? null;

        // Obtener el nivel actual de la tabla pivote
        $currentPivot = $profile->languages()->where('language_id', $language->id)->first();
        $currentLevel = $currentPivot ? $currentPivot->pivot->level : null;

        // Verificar si el nuevo nombre es diferente al actual
        $nameChanged = $language->name !== $newName;
        $levelChanged = $currentLevel !== $newLevel;

        if (!$nameChanged && !$levelChanged) {
            $message = 'No se realizaron cambios.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'language' => $language, 'level' => $currentLevel], 200);
            }
            return redirect()->route('worker.idiomas.index')->with('success', $message);
        }

        // Si solo cambió el nivel, actualizar la tabla pivote
        if (!$nameChanged && $levelChanged) {
            $profile->languages()->updateExistingPivot($language->id, [
                'level' => $newLevel
            ]);

            $message = 'Nivel de idioma actualizado con éxito.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'language' => $language, 'level' => $newLevel], 200);
            }
            return redirect()->route('worker.idiomas.index')->with('success', $message);
        }

        // Contar cuántos perfiles usan este idioma
        $usageCount = \DB::table('language_worker_profile')
            ->where('language_id', $language->id)
            ->count();

        if ($usageCount <= 1) {
            // Solo este perfil usa el idioma, verificar si el nuevo nombre ya existe
            $existingLanguage = Language::where('name', $newName)
                ->where('id', '!=', $language->id)
                ->first();

            if ($existingLanguage) {
                // El nuevo nombre ya existe, desasociar el idioma actual y asociar el existente
                $profile->languages()->detach($language->id);

                // Verificar si el usuario ya tiene el idioma existente
                if (!$profile->languages->contains($existingLanguage->id)) {
                    $profile->languages()->attach($existingLanguage->id, ['level' => $newLevel]);
                } else {
                    // Solo actualizar el nivel si ya lo tiene
                    $profile->languages()->updateExistingPivot($existingLanguage->id, ['level' => $newLevel]);
                }

                $message = 'Idioma actualizado con éxito. Se asoció al idioma existente.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => $message, 'language' => $existingLanguage, 'level' => $newLevel], 200);
                }
                return redirect()->route('worker.idiomas.index')->with('success', $message);
            }

            // El nuevo nombre no existe, actualizar en el catálogo global
            $language->name = $newName;
            $language->save();

            // Actualizar el nivel en la tabla pivote
            $profile->languages()->updateExistingPivot($language->id, [
                'level' => $newLevel
            ]);

            $message = 'Idioma actualizado con éxito.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'language' => $language, 'level' => $newLevel], 200);
            }
            return redirect()->route('worker.idiomas.index')->with('success', $message);
        } else {
            // Otros perfiles también usan este idioma
            // Crear un nuevo idioma o buscar si ya existe uno con ese nombre
            $newLanguage = Language::firstOrCreate(['name' => $newName]);

            // Desasociar el idioma antiguo
            $profile->languages()->detach($language->id);

            // Verificar si el usuario ya tiene el nuevo idioma
            if (!$profile->languages->contains($newLanguage->id)) {
                // Asociar el nuevo idioma con el nivel
                $profile->languages()->attach($newLanguage->id, ['level' => $newLevel]);
            } else {
                // Solo actualizar el nivel si ya lo tiene
                $profile->languages()->updateExistingPivot($newLanguage->id, ['level' => $newLevel]);
            }

            $message = 'Idioma actualizado con éxito. Se creó una nueva entrada para no afectar a otros usuarios.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'language' => $newLanguage, 'level' => $newLevel], 200);
            }
            return redirect()->route('worker.idiomas.index')->with('success', $message);
        }
    }

    /**
     * Elimina un idioma de la asociación con el perfil del trabajador (detach).
     */
    public function destroy($languageId)
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile->languages->contains($languageId)) {
            return back()->with('error', 'El idioma no está asociado a tu perfil.');
        }

        // Desasocia el idioma del perfil
        $profile->languages()->detach($languageId);

        return redirect()->route('worker.idiomas.index')->with('success', 'Idioma eliminado de tu perfil con éxito.');
    }
}
