<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin' => 'Superadministrador con acceso total al sistema',
            'Médico' => 'Atención clínica, prescripción de recetas y solicitudes',
            'Enfermería' => 'Triaje, control de piso y consumos extras',
            'Farmacia' => 'Control de lotes y despacho de medicamentos',
            'Caja' => 'Liquidación de proformas y cobros',
            'Recepción' => 'Admisión de pacientes y apertura de proformas',
        ];

        foreach ($roles as $nombre => $descripcion) {
            Rol::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }
    }
}
