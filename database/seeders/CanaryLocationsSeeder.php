<?php

namespace Database\Seeders;

use App\Models\CanaryLocation;
use Illuminate\Database\Seeder;

class CanaryLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['city' => 'Las Palmas de Gran Canaria', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Telde', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Arucas', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Gáldar', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Agaete', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Santa Brígida', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Santa María de Guía', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Valsequillo de Gran Canaria', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Valleseco', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Ingenio', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Agüimes', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Mogán', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'San Bartolomé de Tirajana', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Teror', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Tejeda', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Artenara', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Firgas', 'province' => 'Las Palmas', 'island' => 'Gran Canaria', 'country' => 'España'],
            ['city' => 'Santa Cruz de Tenerife', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'San Cristóbal de La Laguna', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'La Orotava', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Puerto de la Cruz', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Los Realejos', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Icod de los Vinos', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Tacoronte', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Candelaria', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Güímar', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Adeje', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Arona', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Granadilla de Abona', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Guía de Isora', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Santiago del Teide', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'La Matanza de Acentejo', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'La Victoria de Acentejo', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Arafo', 'province' => 'Santa Cruz de Tenerife', 'island' => 'Tenerife', 'country' => 'España'],
            ['city' => 'Arrecife', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'San Bartolomé', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'Teguise', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'Tías', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'Tinajo', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'Yaiza', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'Haría', 'province' => 'Las Palmas', 'island' => 'Lanzarote', 'country' => 'España'],
            ['city' => 'Puerto del Rosario', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'La Oliva', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Corralejo', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Antigua', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Betancuria', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Tuineje', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Pájara', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Costa Calma', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Morro Jable', 'province' => 'Las Palmas', 'island' => 'Fuerteventura', 'country' => 'España'],
            ['city' => 'Santa Cruz de La Palma', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Palma', 'country' => 'España'],
            ['city' => 'Los Llanos de Aridane', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Palma', 'country' => 'España'],
            ['city' => 'El Paso', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Palma', 'country' => 'España'],
            ['city' => 'Breña Alta', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Palma', 'country' => 'España'],
            ['city' => 'Breña Baja', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Palma', 'country' => 'España'],
            ['city' => 'Tazacorte', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Palma', 'country' => 'España'],
            ['city' => 'San Sebastián de La Gomera', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Gomera', 'country' => 'España'],
            ['city' => 'Valle Gran Rey', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Gomera', 'country' => 'España'],
            ['city' => 'Vallehermoso', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Gomera', 'country' => 'España'],
            ['city' => 'Hermigua', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Gomera', 'country' => 'España'],
            ['city' => 'Agulo', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Gomera', 'country' => 'España'],
            ['city' => 'Alajeró', 'province' => 'Santa Cruz de Tenerife', 'island' => 'La Gomera', 'country' => 'España'],
            ['city' => 'Valverde', 'province' => 'Santa Cruz de Tenerife', 'island' => 'El Hierro', 'country' => 'España'],
            ['city' => 'Frontera', 'province' => 'Santa Cruz de Tenerife', 'island' => 'El Hierro', 'country' => 'España'],
            ['city' => 'El Pinar de El Hierro', 'province' => 'Santa Cruz de Tenerife', 'island' => 'El Hierro', 'country' => 'España'],
            ['city' => 'Caleta de Sebo', 'province' => 'Las Palmas', 'island' => 'La Graciosa', 'country' => 'España'],
        ];

        foreach ($locations as $location) {
            CanaryLocation::updateOrCreate(
                [
                    'city' => $location['city'],
                    'province' => $location['province'],
                    'island' => $location['island'],
                ],
                ['country' => $location['country']]
            );
        }
    }
}
