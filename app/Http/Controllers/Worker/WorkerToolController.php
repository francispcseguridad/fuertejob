<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Tool;
use App\Models\WorkerProfile;

class WorkerToolController extends Controller
{
    /**
     * Muestra la lista de todas las herramientas del trabajador.
     */
    public function index()
    {
        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Debes completar tu perfil para gestionar herramientas.');
        }

        // Se usa get() para obtener la colección de herramientas asociadas al perfil.
        $tools = $profile->tools()->get();

        return view('worker.tools.index', compact('profile', 'tools'));
    }

    /**
     * Busca herramientas para el autocompletado.
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');

        $tools = Tool::where('name', 'LIKE', '%' . $term . '%')
            ->limit(10)
            ->pluck('name')
            ->toArray();

        return response()->json($tools);
    }

    /**
     * Muestra el formulario para crear una nueva herramienta (redundante).
     */
    public function create()
    {
        return view('worker.tools.create');
    }

    /**
     * Almacena una o múltiples herramientas separadas por punto y coma.
     */
    public function store(Request $request)
    {
        // 1. Validar que el campo name existe
        $request->validate([
            'name' => 'required|string',
        ], [
            'name.required' => 'El nombre de la herramienta es obligatorio.',
        ]);

        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            $errorMessage = 'Perfil no encontrado. No se pudo guardar la herramienta.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return back()->with('error', $errorMessage);
        }

        // 2. Detectar si hay múltiples herramientas separadas por punto y coma
        $toolNames = array_map('trim', explode(';', $request->name));
        $toolNames = array_filter($toolNames); // Eliminar elementos vacíos

        $added = [];
        $skipped = [];
        $errors = [];
        $toolsData = [];

        foreach ($toolNames as $toolName) {
            if (empty($toolName)) {
                continue;
            }

            $cleanName = strtolower(trim($toolName));

            // Validar longitud
            if (strlen($cleanName) > 255) {
                $errors[] = "\"$toolName\" es demasiado largo (máximo 255 caracteres)";
                continue;
            }

            // Busca o crea la herramienta
            $tool = Tool::firstOrCreate(['name' => $cleanName]);

            // Verifica si ya la tiene
            if ($profile->tools->contains($tool->id)) {
                $skipped[] = ucfirst($cleanName);
            } else {
                // Adjunta la herramienta al perfil
                $profile->tools()->syncWithoutDetaching([$tool->id]);
                $added[] = ucfirst($cleanName);
                $toolsData[] = $tool; // Para respuesta AJAX
            }
        }

        // 3. Construir mensaje de respuesta
        $messages = [];

        if (count($added) > 0) {
            $messages[] = count($added) . ' herramienta(s) añadida(s): ' . implode(', ', $added);
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
                    'tools' => $toolsData,
                    'count' => count($added)
                ], 201);
            } else {
                return response()->json(['error' => $finalMessage], 422);
            }
        }

        if (count($added) > 0) {
            return redirect()->route('worker.herramientas.index')->with('success', $finalMessage);
        } else {
            return redirect()->route('worker.herramientas.index')->with('error', $finalMessage);
        }
    }

    /**
     * Muestra el formulario para editar una herramienta existente.
     * La edición del nombre del catálogo global NO está permitida.
     */
    public function edit($toolId) // Acepta el ID de la herramienta
    {
        $profile = Auth::user()->workerProfile;
        $tool = Tool::find($toolId);

        if (!$tool) {
            abort(404, 'Herramienta no encontrada.');
        }

        // Verificación robusta: la herramienta debe estar asociada al perfil
        if (!$profile || !$profile->tools()->where('tool_id', $toolId)->exists()) {
            abort(403, 'La herramienta no está asociada a tu perfil.');
        }

        return view('worker.tools.edit', compact('tool'));
    }

    /**
     * Actualiza una herramienta.
     * Si solo este perfil usa la herramienta, se actualiza en el catálogo global.
     * Si otros perfiles también la usan, se crea una nueva herramienta y se asocia al usuario.
     */
    public function update(Request $request, $toolId) // Acepta el ID de la herramienta
    {
        $profile = Auth::user()->workerProfile;
        $tool = Tool::find($toolId);

        if (!$tool) {
            $errorMessage = 'Herramienta no encontrada en el catálogo.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            abort(404, $errorMessage);
        }

        // Verificación robusta: la herramienta debe estar asociada al perfil
        if (!$profile || !$profile->tools()->where('tool_id', $toolId)->exists()) {
            $errorMessage = 'No tienes esta herramienta asociada a tu perfil.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 403);
            }
            abort(403, $errorMessage);
        }

        // Validar el nuevo nombre
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $newName = strtolower(trim($request->name));

        // Verificar si el nuevo nombre es diferente al actual
        if ($tool->name === $newName) {
            $message = 'No se realizaron cambios.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'tool' => $tool], 200);
            }
            return redirect()->route('worker.herramientas.index')->with('success', $message);
        }

        // Contar cuántos perfiles usan esta herramienta
        $usageCount = \DB::table('tool_worker_profile')
            ->where('tool_id', $toolId)
            ->count();

        if ($usageCount <= 1) {
            // Solo este perfil usa la herramienta, actualizar en el catálogo global
            $tool->name = $newName;
            $tool->save();

            $message = 'Herramienta actualizada con éxito.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'tool' => $tool], 200);
            }
            return redirect()->route('worker.herramientas.index')->with('success', $message);
        } else {
            // Otros perfiles también usan esta herramienta
            // Crear una nueva herramienta o buscar si ya existe una con ese nombre
            $newTool = Tool::firstOrCreate(['name' => $newName]);

            // Desasociar la herramienta antigua
            $profile->tools()->detach($toolId);

            // Asociar la nueva herramienta
            $profile->tools()->syncWithoutDetaching([$newTool->id]);

            $message = 'Herramienta actualizada con éxito. Se creó una nueva entrada para no afectar a otros usuarios.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => $message, 'tool' => $newTool], 200);
            }
            return redirect()->route('worker.herramientas.index')->with('success', $message);
        }
    }

    /**
     * Elimina una herramienta desasociándola del perfil del trabajador (detach).
     */
    public function destroy(Request $request, $toolId) // Acepta el ID de la herramienta
    {
        $profile = Auth::user()->workerProfile;
        $tool = Tool::find($toolId); // Buscamos la herramienta solo para el manejo de errores

        if (!$tool) {
            $errorMessage = 'Herramienta no encontrada en el catálogo.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return back()->with('error', $errorMessage);
        }

        // Verificación robusta: la herramienta debe estar asociada al perfil
        if (!$profile || !$profile->tools()->where('tool_id', $toolId)->exists()) {
            $errorMessage = 'La herramienta no está asociada a tu perfil.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return back()->with('error', $errorMessage);
        }

        // Desasocia la herramienta del perfil
        $profile->tools()->detach($toolId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Herramienta eliminada de tu perfil con éxito.'], 200);
        }

        return redirect()->route('worker.tools.index')->with('success', 'Herramienta eliminada de tu perfil con éxito.');
    }
}
