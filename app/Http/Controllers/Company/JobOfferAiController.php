<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\JobOfferDescriptionGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class JobOfferAiController extends Controller
{
    /**
     * Genera la descripción de una oferta de trabajo usando el servicio de IA.
     */
    public function generateDescription(
        Request $request,
        JobOfferDescriptionGenerator $descriptionGenerator
    ): JsonResponse {
        $user = Auth::user();
        $companyProfile = $user?->companyProfile;

        if (!$companyProfile) {
            return response()->json([
                'message' => 'Debes completar el perfil de empresa antes de usar el generador.',
            ], Response::HTTP_FORBIDDEN);
        }

        $levels = ['junior', 'mid', 'senior', 'lead', 'manager'];
        $orientations = ['tecnico', 'funcional', 'estrategico', 'mixto'];
        $contractTypes = ['Indefinido', 'Temporal', 'Freelance', 'Prácticas', 'Otro'];
        $modalities = ['presencial', 'remoto', 'hibrido'];

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'level' => ['required', 'string', Rule::in($levels)],
            'orientation' => ['required', 'string', Rule::in($orientations)],
            'specialization' => 'required|string|max:255',
            'experience' => 'required|string|max:255',
            'requirements' => 'nullable|string|max:4000',
            'benefits' => 'nullable|string|max:4000',
            'modality' => ['nullable', 'string', Rule::in($modalities)],
            'contract_type' => ['nullable', 'string', Rule::in($contractTypes)],
            'location' => 'nullable|string|max:255',
            'additional_context' => 'nullable|string|max:4000',
        ], [
            'level.in' => 'Selecciona un nivel válido.',
            'orientation.in' => 'Selecciona una orientación válida.',
        ]);

        try {
            $description = $descriptionGenerator->generate(array_merge($validated, [
                'company_name' => $companyProfile->company_name,
            ]));
        } catch (\RuntimeException $exception) {
            Log::warning('Fallo generando descripción con Gemini.', [
                'company_id' => $companyProfile->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }

        return response()->json([
            'description' => $description,
        ]);
    }
}
