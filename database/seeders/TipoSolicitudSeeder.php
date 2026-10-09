<?php

namespace Database\Seeders;

use App\Models\TipoSolicitud;
use Illuminate\Database\Seeder;

class TipoSolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Hemograma Completo', 'descripcion' => 'Laboratorio sanguíneo integral'],
            ['nombre' => 'Radiografía de Tórax', 'descripcion' => 'Estudio radiológico de tórax AP'],
            ['nombre' => 'Ecografía Abdominal', 'descripcion' => 'Rastreo ecográfico abdominal completo'],
            ['nombre' => 'Examen General de Orina', 'descripcion' => 'Laboratorio de orina completo'],
        ];

        foreach ($tipos as $tipo) {
            TipoSolicitud::firstOrCreate(
                ['nombre' => $tipo['nombre']],
                $tipo
            );
        }
    }
}
