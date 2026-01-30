<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ChatbotController extends Controller
{
    /**
     * Proxy endpoint to call Gemini without exposing the API key to the client.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'history' => 'required|array|min:1',
            'systemPrompt' => 'nullable|string',
        ]);

        $apiKey = config('gemini.api_key');

        if (empty($apiKey)) {
            Log::error('Gemini API key is missing. Configure GEMINI_API_KEY in .env.');

            return response()->json([
                'message' => 'El asistente no está disponible por una configuración faltante.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $payload = [
            'contents' => $validated['history'],
            'tools' => [
                ['googleSearch' => (object)[]],
            ],
            'systemInstruction' => [
                'parts' => [[
                    'text' => $validated['systemPrompt'] ?: 'Eres el asistente oficial de Fuertejob.',
                ]],
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                    $payload
                );
        } catch (\Throwable $exception) {
            Log::error('Fallo al contactar a Gemini desde el chatbot.', ['exception' => $exception]);

            return response()->json([
                'message' => 'No se pudo contactar con el asistente. Inténtalo más tarde.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        if ($response->failed()) {
            Log::warning('Gemini devolvió un error para el chatbot.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'message' => 'El asistente tuvo un inconveniente al responder.',
                'status' => $response->status(),
            ], $response->status());
        }

        $json = $response->json();
        $candidate = data_get($json, 'candidates.0');
        $text = data_get($candidate, 'content.parts.0.text');

        if (!$text) {
            Log::warning('Gemini chatbot respondió sin contenido utilizable.', ['response' => $json]);

            return response()->json([
                'message' => 'No hubo respuesta disponible en este momento.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $sources = collect(data_get($candidate, 'groundingMetadata.groundingAttributions', []))
            ->map(function ($attribution) {
                return [
                    'uri' => data_get($attribution, 'web.uri'),
                    'title' => data_get($attribution, 'web.title'),
                ];
            })
            ->filter(fn ($source) => !empty($source['uri']) && !empty($source['title']))
            ->values()
            ->all();

        return response()->json([
            'text' => $text,
            'sources' => $sources,
        ]);
    }
}
