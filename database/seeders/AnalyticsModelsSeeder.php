<?php

namespace Database\Seeders;

use App\Models\AnalyticsModel;
use App\Models\AnalyticsFunction;
use App\Models\BonoCatalog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnalyticsModelsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicModel = $this->createModelWithFunctions([
            'name' => 'Analytics Básica',
            'slug' => 'analytics-basica',
            'level' => 'basic',
            'description' => 'Métricas de visitante por oferta y cierre de procesos.',
        ], [
            ['name' => 'Visitantes por oferta (sin inscribirse)', 'description' => 'Número de visitantes únicos que visualizan cada oferta sin completar inscripción.', 'position' => 10],
            ['name' => 'Tiempo finalización proceso', 'description' => 'Promedio de días hasta que la oferta encuentra personal y se cierra.', 'position' => 20],
        ]);

        $mediumModel = $this->createModelWithFunctions([
            'name' => 'Analytics Media',
            'slug' => 'analytics-media',
            'level' => 'medium',
            'description' => 'Incluye toda la analítica básica más canales y municipios.',
        ], [
            ['name' => 'Origen de visualización (buscador o canal externo)', 'position' => 10, 'description' => 'Detecta si el acceso vino desde el buscador de ofertas o directamente.'],
            ['name' => 'Oferta y candidato interesados', 'position' => 20, 'description' => 'Relaciona la oferta con los posibles candidatos que la visitan.'],
            ['name' => 'Tiempo primer candidato vs tiempo finalización', 'position' => 30, 'description' => 'Comparativa entre el primer candidato recibido y el cierre del proceso.'],
            ['name' => 'Municipios de inscritos y visualizadores', 'position' => 40, 'description' => 'Resumen geográfico por municipio para inscritos y visitantes de cada oferta.'],
        ]);

        $advancedModel = $this->createModelWithFunctions([
            'name' => 'Analytics Avanzada',
            'slug' => 'analytics-avanzada',
            'level' => 'advanced',
            'description' => 'Todo lo de la media además de share y conversiones completas.',
        ], [
            ['name' => 'Share en redes sociales', 'position' => 10, 'description' => 'Registra si la oferta fue compartida con el buscador share en redes sociales.'],
            ['name' => 'Día y hora de la visita', 'position' => 20, 'description' => 'Distribución de visitas por día de la semana y franjas horarias.'],
            ['name' => 'Estadísticas visitas vs inscritas', 'position' => 30, 'description' => 'Comparativa entre visitas totales, visitantes únicos e inscripciones.'],
            ['name' => 'Visitas al perfil de empresa', 'position' => 40, 'description' => 'Conteo de accesos al perfil de la empresa posterior a la oferta.'],
        ]);

        $basicBono = $this->createBono('Bono Analytics Básica');
        $mediumBono = $this->createBono('Bono Analytics Media');
        $advancedBono = $this->createBono('Bono Analytics Avanzada');

        $basicBono->analyticsFunctions()->sync($basicModel->functions()->pluck('id'));
        $mediumBono->analyticsFunctions()->sync($mediumModel->functions()->pluck('id'));
        $advancedBono->analyticsFunctions()->sync($advancedModel->functions()->pluck('id'));
    }

    protected function createModelWithFunctions(array $payload, array $functionsData): AnalyticsModel
    {
        $model = AnalyticsModel::updateOrCreate(
            ['slug' => $payload['slug']],
            $payload
        );

        $model->functions()->delete();
        $model->functions()->createMany(array_map(function ($function) {
            return array_merge([
                'code' => null,
                'details' => null,
                'is_active' => true,
            ], $function);
        }, $functionsData));

        return $model;
    }

    protected function createBono(string $name): BonoCatalog
    {
        return BonoCatalog::firstOrCreate(
            ['name' => $name],
            [
                'description' => "Acceso al módulo {$name}",
                'price' => 0,
                'offer_credits' => 0,
                'cv_views' => 0,
                'user_seats' => 0,
                'visibility_days' => 30,
                'duration_days' => 30,
                'credit_cost' => 0,
                'is_extra' => false,
                'is_active' => true,
                'destacado' => false,
            ]
        );
    }
}
