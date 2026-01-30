<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class JobOfferDescriptionGenerator
{
    protected string $model;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->model = config('gemini.model', 'gemini-2.5-flash');
        $this->apiKey = config('gemini.api_key');
    }

    /**
     * Genera un texto descriptivo para una oferta de empleo usando Gemini.
     *
     * @param array $data Datos del perfil objetivo y del puesto.
     * @return string
     */
    public function generate(array $data): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Falta la configuración de GEMINI_API_KEY.');
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $this->systemPrompt() . "\n\nDATOS DE LA OFERTA:\n" . $this->buildPrompt($data)]
                    ],
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4096,
                'topP' => 0.95,
            ],
        ];

        try {
            $response = Http::timeout(90)
                ->acceptJson()
                ->post($endpoint, $payload);
        } catch (\Throwable $exception) {
            Log::error('No se pudo contactar con Gemini para generar la oferta.', [
                'exception' => $exception,
            ]);

            throw new RuntimeException('No se pudo contactar con el servicio de IA.');
        }

        if ($response->failed()) {
            Log::warning('Gemini devolvió un error al generar la oferta.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gemini no pudo generar la descripción solicitada.');
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (!$text) {
            Log::error('Respuesta incompleta de Gemini', ['response' => $response->json()]);
            throw new RuntimeException('La respuesta de Gemini no contenía texto utilizable.');
        }

        return trim($text);
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
Eres un redactor senior experto en Employer Branding. Tu objetivo es crear descripciones de puestos de trabajo extensas, detalladas y atractivas.

REGLAS DE FORMATO CRÍTICAS:
1. ESCRITURA NORMAL: Escribe utilizando mayúsculas y minúsculas de forma correcta (gramática estándar). ESTÁ PROHIBIDO escribir párrafos enteros en mayúsculas. Solo los títulos de sección deben ir en MAYÚSCULAS.
2. EXTENSIÓN: El texto debe ser largo (mínimo 600-800 palabras) para evitar que parezca una descripción genérica.
3. ESTILO: Profesional, motivador y fluido. Sin usar Markdown (ni asteriscos, ni almohadillas). Usa doble salto de línea para separar párrafos y secciones.

ESTRUCTURA OBLIGATORIA:
- TITULO DEL PUESTO
- INTRODUCCIÓN (Párrafo narrativo sobre el valor del rol)
- SOBRE NUESTRA EMPRESA (Cultura, valores y visión)
- MISIÓN DEL PUESTO (Qué se espera lograr a largo plazo)
- RESPONSABILIDADES DETALLADAS (Lista de al menos 10 puntos, explicando el "cómo" y el "por qué")
- PERFIL DEL CANDIDATO (Experiencia, formación y habilidades técnicas)
- COMPETENCIAS BLANDAS (Actitud y valores)
- LO QUE OFRECEMOS (Beneficios, horario, flexibilidad, ambiente)
- PROCESO DE SELECCIÓN
- LLAMADA A LA ACCIÓN

CREATIVIDAD: Si tienes poca información inicial, usa tu conocimiento experto para completar una oferta que parezca real y atractiva para el sector. No te detengas, genera un texto completo y rico en detalles.
PROMPT;
    }

    /**
     * Construye el prompt dinámico con los datos proporcionados por la empresa.
     */
    protected function buildPrompt(array $data): string
    {
        $lines = [];

        $lines[] = "Puesto: " . ($data['title'] ?? 'Sin título');
        $lines[] = "Empresa: " . ($data['company_name'] ?? 'Nuestra Empresa');
        $lines[] = "Categoría/Nivel: " . ($data['level'] ?? 'Senior');
        $lines[] = "Especialización: " . ($data['specialization'] ?? '');
        $lines[] = "Experiencia: " . ($data['experience'] ?? '');
        $lines[] = "Modalidad: " . ($data['modality'] ?? 'Híbrido');
        $lines[] = "Ubicación: " . ($data['location'] ?? 'No especificada');

        if (!empty($data['requirements'])) {
            $lines[] = "\nRequisitos base:\n{$data['requirements']}";
        }

        if (!empty($data['benefits'])) {
            $lines[] = "\nBeneficios base:\n{$data['benefits']}";
        }

        if (!empty($data['additional_context'])) {
            $lines[] = "\nContexto extra:\n{$data['additional_context']}";
        }

        $lines[] = "\nRECUERDA: Genera un texto LARGO y COMPLETO. No te detengas hasta haber cubierto todas las secciones con detalle.";

        return implode("\n", array_filter($lines));
    }
}
