<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catConsultas = Categoria::where('nombre', 'Consultas Médicas')->first();
        $catProcedimientos = Categoria::where('nombre', 'Procedimientos Menores')->first();
        $catEnfermeria = Categoria::where('nombre', 'Servicios de Enfermería')->first();

        $servicios = [
            [
                'categoria_id' => $catConsultas?->id ?? 1,
                'nombre' => 'Consulta Médica General',
                'precio_tentativo' => 70.00,
                'descripcion' => 'Evaluación clínica inicial por médico general',
                'estado' => true,
            ],
            [
                'categoria_id' => $catConsultas?->id ?? 1,
                'nombre' => 'Consulta Especializada',
                'precio_tentativo' => 120.00,
                'descripcion' => 'Atención por médico especialista',
                'estado' => true,
            ],
            [
                'categoria_id' => $catProcedimientos?->id ?? 2,
                'nombre' => 'Sutura Simple (1-3 puntos)',
                'precio_tentativo' => 85.00,
                'descripcion' => 'Afrontamiento de herida superficial con anestesia local',
                'estado' => true,
            ],
            [
                'categoria_id' => $catProcedimientos?->id ?? 2,
                'nombre' => 'Curación y Vendaje',
                'precio_tentativo' => 45.00,
                'descripcion' => 'Limpieza antiséptica y colocación de gasas',
                'estado' => true,
            ],
            [
                'categoria_id' => $catEnfermeria?->id ?? 3,
                'nombre' => 'Colocación de Vía / Suero EV',
                'precio_tentativo' => 35.00,
                'descripcion' => 'Canalización endovenosa y perfusión',
                'estado' => true,
            ],
            [
                'categoria_id' => $catEnfermeria?->id ?? 3,
                'nombre' => 'Inyección Intramuscular',
                'precio_tentativo' => 20.00,
                'descripcion' => 'Administración de medicamento por enfermería',
                'estado' => true,
            ],
        ];

        foreach ($servicios as $serv) {
            Servicio::firstOrCreate(
                ['nombre' => $serv['nombre']],
                $serv
            );
        }
    }
}
