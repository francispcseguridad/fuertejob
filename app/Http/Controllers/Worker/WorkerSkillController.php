<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Skill;
use App\Models\SkillWorkerProfile;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Validation\Validator;

class WorkerSkillController extends Controller
{
    /**
     * Muestra la lista de todas las habilidades del trabajador.
     */
    public function index()
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Debes completar tu perfil para gestionar habilidades.');
        }

        $skills = $profile->skills()->get();

        return view('worker.skills.index', compact('profile', 'skills'));
    }

    /**
     * Busca habilidades para el autocompletado.
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');

        $skills = Skill::where('name', 'LIKE', '%' . $term . '%')
            ->limit(10)
            ->pluck('name')
            ->toArray();

        return response()->json($skills);
    }

    /**
     * Almacena una nueva habilidad o múltiples habilidades separadas por punto y coma.
     */
    public function store(Request $request)
    {
        // 1. Validar que el campo name existe
        $request->validate([
            'name' => 'required|string',
        ], [
            'name.required' => 'El nombre de la habilidad es obligatorio.',
        ]);

        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            $errorMessage = 'Perfil no encontrado. No se pudo guardar la habilidad.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return redirect()->route('worker.habilidades.index')->with('error', $errorMessage);
        }

        // 2. Detectar si hay múltiples habilidades separadas por punto y coma
        $skillNames = array_map('trim', explode(';', $request->name));
        $skillNames = array_filter($skillNames); // Eliminar elementos vacíos

        $added = [];
        $skipped = [];
        $errors = [];
        $skillsData = [];

        foreach ($skillNames as $skillName) {
            if (empty($skillName)) {
                continue;
            }

            $cleanName = strtolower(trim($skillName));

            // Validar longitud
            if (strlen($cleanName) > 255) {
                $errors[] = "\"$skillName\" es demasiado largo (máximo 255 caracteres)";
                continue;
            }

            // Busca o crea la habilidad
            $skill = Skill::firstOrCreate(['name' => $cleanName]);

            // Verifica si ya la tiene
            if ($profile->skills->contains($skill->id)) {
                $skipped[] = ucfirst($cleanName);
            } else {
                // Adjunta la habilidad
                $profile->skills()->attach($skill->id);
                $added[] = ucfirst($cleanName);
                $skillsData[] = $skill; // Para respuesta AJAX
            }
        }

        // 3. Construir mensaje de respuesta
        $messages = [];

        if (count($added) > 0) {
            $messages[] = count($added) . ' habilidad(es) añadida(s): ' . implode(', ', $added);
        }

        if (count($skipped) > 0) {
            $messages[] = count($skipped) . ' ya existente(s): ' . implode(', ', $skipped);
        }

        if (count($errors) > 0) {
            $messages[] = 'Errores: ' . implode(', ', $errors);
        }

        $finalMessage = implode(' | ', $messages);

        if ($request->ajax() || $request->wantsJson()) {
            if (count($added) > 0) {
                return response()->json([
                    'success' => $finalMessage,
                    'skills' => $skillsData,
                    'count' => count($added)
                ], 201);
            } else {
                return response()->json(['error' => $finalMessage], 422);
            }
        }

        if (count($added) > 0) {
            return redirect()->route('worker.habilidades.index')->with('success', $finalMessage);
        } else {
            return redirect()->route('worker.habilidades.index')->with('error', $finalMessage);
        }
    }

    /**
     * Actualiza una habilidad existente.
     */
    public function update(Request $request, Skill $habilidade)
    {
        // Comprobación de que la habilidad está asociada
        if (!Auth::user()->workerProfile->skills->contains($habilidade->id)) {
            return redirect()->route('worker.habilidades.index')->with('error', 'No puedes editar una habilidad que no tienes asociada.');
        }

        // Validar, excluyendo la habilidad actual de la unicidad
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:skills,name,' . $habilidade->id,
        ]);

        $cleanName = strtolower(trim($validatedData['name']));
        $habilidade->update(['name' => $cleanName]);

        return redirect()->route('worker.habilidades.index')->with('success', 'Habilidad actualizada con éxito.');
    }

    /**
     * Elimina una habilidad de la asociación.
     */
    public function destroy(Request $request, $skill_id)
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            $errorMessage = 'Perfil no encontrado.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return redirect()->route('worker.habilidades.index')->with('error', $errorMessage);
        }

        // Verificar si la habilidad está asociada al perfil
        if (!$profile->skills->contains($skill_id)) {
            $errorMessage = 'La habilidad no está asociada a tu perfil.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return redirect()->route('worker.habilidades.index')->with('error', $errorMessage);
        }

        // Desvincular la habilidad del perfil (no elimina la habilidad de la tabla skills)
        $profile->skills()->detach($skill_id);

        $message = 'Habilidad eliminada de tu perfil con éxito.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $message], 200);
        }

        return redirect()->route('worker.habilidades.index')->with('success', $message);
    }

    /**
     * Personaliza la redirección al fallar la validación.
     * Siempre redirige a la ruta de índice para evitar el 404 después de un POST.
     */
    protected function failedValidation(Validator $validator): RedirectResponse
    {
        // 1. Manejo especial para el modal de EDICIÓN (update)
        if (request()->route()->getName() === 'worker.habilidades.update') {
            $skill = request()->route('habilidade');
            if ($skill) {
                // Flashea el ID para que la vista sepa qué modal reabrir
                session()->flash('skill_edit_error_id', $skill->id);
            }
        }

        // 2. Construye la respuesta de redirección explícita a 'index'
        return redirect()->route('worker.habilidades.index')
            ->withInput(request()->all()) // Mantiene los datos que se intentaron guardar (old('name'))
            ->withErrors($validator);      // Adjunta los errores

        // La excepción será manejada por Laravel usando esta respuesta de redirección.
    }
}
