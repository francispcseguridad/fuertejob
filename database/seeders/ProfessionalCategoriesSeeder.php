<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfessionalCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Sectores principales en España (enfocados a Canarias/FuerteJob)
        $sectors = [
            ['name' => 'Hostelería y Turismo', 'icon' => 'bi-sun'],
            ['name' => 'Construcción e Inmobiliaria', 'icon' => 'bi-house'],
            ['name' => 'Comercio y Retail', 'icon' => 'bi-cart'],
            ['name' => 'Transporte y Logística', 'icon' => 'bi-truck'],
            ['name' => 'Tecnología e Informática', 'icon' => 'bi-laptop'],
            ['name' => 'Administración y Oficinas', 'icon' => 'bi-briefcase'],
            ['name' => 'Sanidad y Cuidado de Personas', 'icon' => 'bi-heart-pulse'],
            ['name' => 'Educación y Formación', 'icon' => 'bi-book'],
            ['name' => 'Agricultura y Pesca', 'icon' => 'bi-water'],
        ];

        foreach ($sectors as $sector) {
            DB::table('job_sectors')->insert(array_merge($sector, ['created_at' => now()]));
        }

        // Grupos de Cotización Oficiales (Seguridad Social España)
        $categories = [
            ['group_number' => 1, 'name' => 'Ingenieros y Licenciados'],
            ['group_number' => 2, 'name' => 'Ingenieros Técnicos, Peritos y Ayudantes Titulados'],
            ['group_number' => 3, 'name' => 'Jefes Administrativos y de Taller'],
            ['group_number' => 4, 'name' => 'Ayudantes no Titulados'],
            ['group_number' => 5, 'name' => 'Oficiales Administrativos'],
            ['group_number' => 6, 'name' => 'Subalternos'],
            ['group_number' => 7, 'name' => 'Auxiliares Administrativos'],
            ['group_number' => 8, 'name' => 'Oficiales de primera y segunda'],
            ['group_number' => 9, 'name' => 'Oficiales de tercera y Especialistas'],
            ['group_number' => 10, 'name' => 'Peones'],
            ['group_number' => 11, 'name' => 'Trabajadores menores de 18 años'],
        ];

        foreach ($categories as $cat) {
            DB::table('professional_categories')->insert(array_merge($cat, ['created_at' => now()]));
        }
    }
}
