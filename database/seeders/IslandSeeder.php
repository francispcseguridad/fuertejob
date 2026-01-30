<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Island;

class IslandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $islands = [
            'Tenerife',
            'Gran Canaria',
            'Lanzarote',
            'Fuerteventura',
            'La Palma',
            'La Gomera',
            'El Hierro',
            'La Graciosa',
        ];

        foreach ($islands as $islandName) {
            Island::firstOrCreate(['name' => $islandName]);
        }
    }
}
