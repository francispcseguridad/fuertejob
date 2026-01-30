<?php

namespace Tests\Feature\Company;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobOfferAiControllerTest extends TestCase
{
    public function test_company_user_can_generate_job_offer_description(): void
    {
        config([
            'gemini.api_key' => 'test-key',
            'gemini.model' => 'fake-model',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Oferta generada por IA'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->make([
            'rol' => 'empresa',
            'email' => 'empresa@example.com',
        ]);
        $user->id = 10;

        $profile = new CompanyProfile();
        $profile->id = 55;
        $profile->company_name = 'Fuertejob Labs';
        $user->setRelation('companyProfile', $profile);

        $payload = [
            'title' => 'Ingeniero/a de Software',
            'level' => 'senior',
            'orientation' => 'tecnico',
            'specialization' => 'Backend PHP',
            'experience' => '5+ años liderando proyectos en Laravel',
            'requirements' => 'Experiencia en APIs, liderazgo técnico.',
            'benefits' => 'Teletrabajo parcial, seguro médico.',
            'modality' => 'hibrido',
            'contract_type' => 'Indefinido',
            'location' => 'Madrid',
            'additional_context' => 'Equipo en crecimiento buscando impacto.',
        ];

        $response = $this->actingAs($user)->postJson(route('empresa.ofertas.generate-description'), $payload);

        $response->assertOk()
            ->assertJson([
                'description' => 'Oferta generada por IA',
            ]);

        Http::assertSent(function ($request) use ($payload) {
            return str_contains($request->body(), $payload['title']);
        });
    }

    public function test_cannot_generate_if_company_profile_missing(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake();

        $user = User::factory()->make([
            'rol' => 'empresa',
            'email' => 'sinperfil@example.com',
        ]);
        $user->id = 11;

        $payload = [
            'title' => 'Product Manager',
            'level' => 'senior',
            'orientation' => 'estrategico',
            'specialization' => 'Producto digital',
            'experience' => '8 años en scale-ups',
            'requirements' => 'Experiencia en discovery.',
            'benefits' => 'Flexibilidad.',
            'modality' => 'remoto',
            'contract_type' => 'Indefinido',
            'location' => 'Barcelona',
            'additional_context' => '',
        ];

        $response = $this->actingAs($user)->postJson(route('empresa.ofertas.generate-description'), $payload);

        $response->assertStatus(403);
        Http::assertNothingSent();
    }
}
