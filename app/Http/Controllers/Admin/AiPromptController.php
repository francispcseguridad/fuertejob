<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para la administración de los Prompts de IA (fragmentos de conocimiento/instrucciones del sistema).
 * Este controlador maneja las operaciones CRUD y soporta respuestas JSON para interacción vía AJAX.
 */
class AiPromptController extends Controller
{
    /**
     * Muestra una lista de todos los prompts de IA, agrupados por categoría.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Obtiene todos los prompts para poder compilar el System Instruction completo
        $allPrompts = AiPrompt::orderBy('category')->orderBy('title')->get();

        // Agrupa los prompts por categoría para la vista
        // Estructura deseada JS:Array<{category: string, items: Array<AiPrompt>}>
        $groupedPrompts = $allPrompts->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'items' => $items
            ];
        })->values();

        return view('admin.ai_prompts.index', compact('groupedPrompts'));
    }

    /**
     * Muestra el formulario para crear un nuevo prompt (View fallback).
     */
    public function create()
    {
        $statuses = [AiPrompt::STATUS_ACTIVE, AiPrompt::STATUS_INACTIVE];
        return view('admin.ai_prompts.create', compact('statuses'));
    }

    /**
     * Almacena un nuevo prompt de IA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'detail' => 'required|string',
            'status' => ['nullable', Rule::in([AiPrompt::STATUS_ACTIVE, AiPrompt::STATUS_INACTIVE])],
        ]);

        // Default status if not provided
        if (!isset($validatedData['status'])) {
            $validatedData['status'] = AiPrompt::STATUS_ACTIVE;
        }

        try {
            $aiPrompt = AiPrompt::create($validatedData);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prompt creado exitosamente',
                    'data' => $aiPrompt
                ]);
            }

            return redirect()->route('admin.ai_prompts.index')
                ->with('success', 'El Prompt de IA se ha creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear AiPrompt: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el prompt: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Hubo un error al crear el prompt.');
        }
    }

    /**
     * Muestra el formulario para editar (View fallback).
     */
    public function edit(AiPrompt $aiPrompt)
    {
        $statuses = [AiPrompt::STATUS_ACTIVE, AiPrompt::STATUS_INACTIVE];
        return view('admin.ai_prompts.edit', compact('aiPrompt', 'statuses'));
    }

    /**
     * Actualiza un prompt existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiPrompt  $aiPrompt
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AiPrompt $aiPrompt)
    {
        $validatedData = $request->validate([
            'category' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'detail' => 'required|string',
            'status' => ['nullable', Rule::in([AiPrompt::STATUS_ACTIVE, AiPrompt::STATUS_INACTIVE])],
        ]);

        try {
            $aiPrompt->update($validatedData);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prompt actualizado exitosamente',
                    'data' => $aiPrompt
                ]);
            }

            return redirect()->route('admin.ai_prompts.index')
                ->with('success', 'El Prompt de IA se ha actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar AiPrompt: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Hubo un error al actualizar.');
        }
    }

    /**
     * Elimina un prompt.
     *
     * @param  \App\Models\AiPrompt  $aiPrompt
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, AiPrompt $aiPrompt)
    {
        try {
            $aiPrompt->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Prompt eliminado correctamente'
                ]);
            }

            return redirect()->route('admin.ai_prompts.index')
                ->with('success', 'El Prompt de IA se ha eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar AiPrompt: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Hubo un error al eliminar el prompt.');
        }
    }
}
