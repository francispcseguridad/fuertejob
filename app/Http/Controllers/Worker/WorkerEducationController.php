<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Importamos Gate para la política de autorización
use App\Models\Education;
use App\Models\WorkerProfile; // Asegúrate de importar WorkerProfile

class WorkerEducationController extends Controller
{
    /**
     * Muestra la lista de todos los registros de educación del trabajador.
     * Corresponde a la ruta GET /trabajador/educacion (worker.education.index)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        $profile = $user->workerProfile;

        // CRÍTICO: Comprobación de existencia del perfil antes de acceder a la relación
        if (!$profile) {
            return redirect()->route('worker.dashboard')->with('error', 'Debes completar tu perfil para gestionar tu historial educativo.');
        }

        // Obtener los registros de educación ordenados por fecha de finalización descendente
        $educationRecords = $profile->educations()->orderByDesc('end_date')->get();

        return view('worker.education.index', compact('profile', 'educationRecords'));
    }

    /**
     * Muestra el formulario para crear un nuevo registro de educación.
     * Corresponde a la ruta GET /trabajador/educacion/create (worker.education.create)
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('worker.education.create');
    }

    /**
     * Almacena un nuevo registro de educación en la base de datos.
     * Corresponde a la ruta POST /trabajador/educacion (worker.education.store)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        $profile = Auth::user()->workerProfile;

        if (!$profile) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Perfil no encontrado. No se pudo guardar el registro educativo.'], 404);
            }
            return back()->with('error', 'Perfil no encontrado. No se pudo guardar el registro educativo.');
        }

        // Si está marcado como actual, forzamos end_date a null
        if ($request->has('is_current') && $request->is_current) {
            $validatedData['end_date'] = null;
            $validatedData['is_current'] = true;
        } else {
            // Si end_date está vacío, convertirlo a null
            if (empty($validatedData['end_date'])) {
                $validatedData['end_date'] = null;
            }
            unset($validatedData['is_current']);
        }

        $profile->educations()->create($validatedData);

        // CORREGIDO: Redirección estandarizada a 'worker.educacion.index'
        return redirect()->route('worker.educacion.index')->with('success', 'Registro de educación añadido con éxito.');
    }

    /**
     * Muestra el formulario para editar un registro de educación existente.
     * Corresponde a la ruta GET /trabajador/educacion/{education}/edit (worker.education.edit)
     *
     * @param  \App\Models\Education  $education // CORREGIDO: Usamos $education
     * @return \Illuminate\View\View
     */
    public function edit(Education $education)
    {
        return view('worker.education.edit', compact('education'));
    }

    /**
     * Actualiza un registro de educación existente en la base de datos.
     * Corresponde a la ruta PUT/PATCH /trabajador/educacion/{education} (worker.education.update)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Education  $education // CORREGIDO: Usamos $education
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Education $education)
    {

        $validatedData = $request->validate([
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_current' => 'nullable|boolean',
        ]);

        // Si está marcado como actual, forzamos end_date a null
        if ($request->has('is_current') && $request->is_current) {
            $validatedData['end_date'] = null;
            $validatedData['is_current'] = true;
        } else {
            // Si end_date está vacío, convertirlo a null
            if (empty($validatedData['end_date'])) {
                $validatedData['end_date'] = null;
            }
            unset($validatedData['is_current']);
        }

        $education->update($validatedData);

        // CORREGIDO: Redirección estandarizada a 'worker.educacion.index'
        return redirect()->route('worker.educacion.index')->with('success', 'Registro de educación actualizado con éxito.');
    }

    /**
     * Elimina un registro de educación de la base de datos.
     * Corresponde a la ruta DELETE /trabajador/educacion/{education} (worker.education.destroy)
     *
     * @param  \App\Models\Education  $education // CORREGIDO: Usamos $education
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Education $education)
    {

        $education->delete();

        // CORREGIDO: Redirección estandarizada a 'worker.educacion.index'
        return redirect()->route('worker.educacion.index')->with('success', 'Registro de educación eliminado con éxito.');
    }
}
