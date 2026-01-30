<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Usaremos Gate para la autorización
use App\Models\Experience;
use App\Models\WorkerProfile; // Asegúrate de importar WorkerProfile

class WorkerExperienceController extends Controller
{
    /**
     * Muestra la lista de todas las experiencias laborales del trabajador.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $profile = Auth::user()->workerProfile;

        // CRÍTICO: Comprobación de existencia del perfil antes de acceder a la relación
        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Debes completar tu perfil para gestionar experiencias.');
        }

        $experiences = $profile->experiences()->orderByDesc('start_date')->get();

        return view('worker.experiences.index', compact('profile', 'experiences'));
    }

    /**
     * Muestra el formulario para crear una nueva experiencia.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('worker.experiences.create');
    }

    /**
     * Almacena una nueva experiencia en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Validar datos
        $validatedData = $request->validate([
            // Usaremos los nombres de columna job_title y company_name para coincidir con la base de datos
            'job_title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_current' => 'nullable|boolean', // El campo en la validación debe ser como está en la DB
        ]);

        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            return back()->with('error', 'Perfil no encontrado. No se pudo guardar la experiencia.');
        }

        // 2. Manejar checkbox is_current: si está marcado, end_date es nulo
        if ($request->has('is_current')) {
            $validatedData['end_date'] = null;
        } else {
            // Eliminar 'is_current' para que no interfiera con Mass Assignment si no existe en la tabla Experience
            unset($validatedData['is_current']);
        }

        // 3. Crear experiencia asociada al perfil
        $profile->experiences()->create($validatedData);

        return redirect()->route('worker.experiences.index')->with('success', 'Experiencia añadida con éxito.');
    }

    /**
     * Muestra el formulario para editar una experiencia existente.
     *
     * @param  \App\Models\Experience  $experience // CAMBIO: Usar $experience por convención
     * @return \Illuminate\View\View
     */
    public function edit(Experience $experience)
    {
        // MEJORA DE SEGURIDAD: Uso de la Política de Autorización (requiere ExperiencePolicy)
        $this->authorize('update', $experience);

        return view('worker.experiences.edit', compact('experience'));
    }

    /**
     * Actualiza una experiencia existente en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Experience  $experience // CAMBIO: Usar $experience por convención
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Experience $experience)
    {
        // MEJORA DE SEGURIDAD: Uso de la Política de Autorización (requiere ExperiencePolicy)
        $this->authorize('update', $experience);

        $validatedData = $request->validate([
            'job_title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        // 1. Manejar checkbox is_current: si está marcado, end_date es nulo
        if ($request->has('is_current')) {
            $validatedData['end_date'] = null;
        } else {
            unset($validatedData['is_current']);
        }

        $experience->update($validatedData);

        // Respuesta AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => 'Experiencia actualizada con éxito.',
                'experience' => $experience->fresh()
            ]);
        }

        // CORREGIDO: Redirección estandarizada a 'worker.experiences.index'
        return redirect()->route('worker.experiences.index')->with('success', 'Experiencia actualizada con éxito.');
    }

    /**
     * Elimina una experiencia de la base de datos.
     *
     * @param  \App\Models\Experience  $experience // CAMBIO: Usar $experience por convención
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Experience $experience)
    {
        // MEJORA DE SEGURIDAD: Uso de la Política de Autorización (requiere ExperiencePolicy)
        $this->authorize('delete', $experience);

        $experience->delete();

        // Respuesta AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => 'Experiencia eliminada con éxito.'
            ]);
        }

        // CORREGIDO: Redirección estandarizada a 'worker.experiences.index'
        return redirect()->route('worker.experiences.index')->with('success', 'Experiencia eliminada con éxito.');
    }
}
