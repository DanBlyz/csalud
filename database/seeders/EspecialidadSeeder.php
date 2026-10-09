<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidades = ['Medicina General', 'Pediatría', 'Ginecología', 'Traumatología', 'Cardiología'];

        foreach ($especialidades as $esp) {
            Especialidad::firstOrCreate(
                ['nombre' => $esp],
                ['descripcion' => 'Especialidad médica de '.$esp]
            );
        }
    }
}
