<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SelectionProcessLog;
use App\Models\CandidateSelection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\JobOffer;

class SelectionProcessLogController extends Controller
{
    /**
     * Muestra todos los registros de log para una selección específica (usado por AJAX para recargar la tabla).
     * @param CandidateSelection $candidateSelection
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(CandidateSelection $candidateSelection)
    {
        // 1. Verificación de Autorización
        $companyProfileId = Auth::user()->companyProfile->id ?? null;
        if (!$companyProfileId || $candidateSelection->company_profile_id !== $companyProfileId) {
            return response()->json(['error' => 'No autorizado para ver este historial.'], 403);
        }

        // 2. Obtener y ordenar los logs
        $logs = $candidateSelection->processLog()
            ->orderBy('stage_order', 'asc') // Ordenar por orden de la etapa
            ->orderBy('stage_date', 'asc') // Y luego por fecha
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs->map(function ($log) {
                // Formatear la fecha para mostrar en la vista
                $log->formatted_stage_date = Carbon::parse($log->stage_date)->format('d/m/Y');
                return $log;
            })
        ]);
    }

    /**
     * Crea un nuevo registro de log.
     * @param Request $request
     * @param CandidateSelection $candidateSelection
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, CandidateSelection $candidateSelection)
    {
        // 1. Verificación de Autorización
        $companyProfileId = Auth::user()->companyProfile->id ?? null;
        if (!$companyProfileId || $candidateSelection->company_profile_id !== $companyProfileId) {
            return response()->json(['error' => 'No autorizado para crear registros en esta selección.'], 403);
        }

        // 2. Validación
        $validatedData = $request->validate([
            'stage_order' => 'required|integer|min:1',
            'stage_name' => 'required|string|max:255',
            'stage_date' => 'required|date',
            'contact_type' => ['required', Rule::in(['Entrevista Presencial', 'Llamada', 'Videoconferencia', 'Email', 'Prueba Técnica'])],
            'result' => ['nullable', Rule::in(['Pendiente', 'Positivo', 'Negativo', 'Completado'])],
            'interviewer_name' => 'nullable|string|max:255',
            'interviewer_notes' => 'nullable|string|max:1000',
            'next_step' => 'nullable|string|max:500',
        ]);

        // 3. Creación
        $log = $candidateSelection->processLog()->create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Etapa de proceso creada correctamente.',
            'log' => $log
        ]);
    }

    /**
     * Actualiza un registro de log existente.
     * @param Request $request
     * @param SelectionProcessLog $log
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $candidato_id, $log_id)
    {
        // 1. Verificación de Autorización (A través del CandidateSelection)
        $companyProfileId = Auth::user()->companyProfile->id ?? null;

        $log = SelectionProcessLog::findOrFail($log_id);
        if (!$companyProfileId || $log->candidateSelection->company_profile_id !== $companyProfileId) {
            return response()->json(['error' => 'No autorizado para actualizar este registro.'], 403);
        }

        // 2. Validación
        $validatedData = $request->validate([
            'stage_order' => 'required|integer|min:1',
            'stage_name' => 'required|string|max:255',
            'stage_date' => 'required|date',
            'contact_type' => ['required', Rule::in(['Entrevista Presencial', 'Llamada', 'Videoconferencia', 'Email', 'Prueba Técnica'])],
            'result' => ['nullable', Rule::in(['Pendiente', 'Positivo', 'Negativo', 'Completado'])],
            'interviewer_name' => 'nullable|string|max:255',
            'interviewer_notes' => 'nullable|string|max:1000',
            'next_step' => 'nullable|string|max:500',
        ]);

        // 3. Actualización
        $log->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Etapa de proceso actualizada correctamente.',
            'log' => $log
        ]);
    }

    /**
     * Elimina un registro de log.
     * @param SelectionProcessLog $log
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SelectionProcessLog $log)
    {
        // 1. Verificación de Autorización
        $companyProfileId = Auth::user()->companyProfile->id ?? null;
        if (!$companyProfileId || $log->candidateSelection->company_profile_id !== $companyProfileId) {
            return response()->json(['error' => 'No autorizado para eliminar este registro.'], 403);
        }

        // 2. Eliminación
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Etapa de proceso eliminada correctamente.',
        ]);
    }
}
