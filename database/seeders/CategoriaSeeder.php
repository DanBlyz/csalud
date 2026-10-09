<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Consultas Médicas', 'descripcion' => 'Atenciones médicas ambulatorias', 'estado' => true],
            ['nombre' => 'Procedimientos Menores', 'descripcion' => 'Suturas, curaciones y vendajes', 'estado' => true],
            ['nombre' => 'Servicios de Enfermería', 'descripcion' => 'Inyectables, sueros y tomas de signos', 'estado' => true],
            ['nombre' => 'Laboratorio y Diagnóstico', 'descripcion' => 'Exámenes y análisis clínicos', 'estado' => true],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['nombre' => $cat['nombre']],
                $cat
            );
        }
    }
}
