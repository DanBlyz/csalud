<?php

namespace Database\Seeders;

use App\Models\Marca;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marcas = [
            ['nombre' => 'Bayer', 'descripcion' => 'Laboratorio farmacéutico internacional'],
            ['nombre' => 'Bagó', 'descripcion' => 'Laboratorios Bagó'],
            ['nombre' => 'Genfar', 'descripcion' => 'Medicamentos genéricos de calidad'],
            ['nombre' => 'Nipro Medical', 'descripcion' => 'Insumos y material descartable médico'],
        ];

        foreach ($marcas as $marca) {
            Marca::firstOrCreate(
                ['nombre' => $marca['nombre']],
                $marca
            );
        }
    }
}
