<?php

namespace Database\Seeders;

use App\Models\Seccion;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SeccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sucursal = Sucursal::where('nombre', 'Sede Central')->first();

        if ($sucursal) {
            Seccion::firstOrCreate(
                [
                    'sucursal_id' => $sucursal->id,
                    'nombre' => 'Farmacia',
                ],
                [
                    'descripcion' => 'Depósito central de medicamentos e insumos de la sede central',
                    'es_almacen_principal' => true,
                    'activo' => true,
                ]
            );
            Seccion::firstOrCreate(
                [
                    'sucursal_id' => $sucursal->id,
                    'nombre' => 'Emergencia',
                ],
                [
                    'descripcion' => 'Depósito de medicamentos e insumos de emergencias',
                    'es_almacen_principal' => false,
                    'activo' => true,
                ]
            );
            Seccion::firstOrCreate(
                [
                    'sucursal_id' => $sucursal->id,
                    'nombre' => 'Quirofano',
                ],
                [
                    'descripcion' => 'Depósito de medicamentos e insumos del quirofano',
                    'es_almacen_principal' => false,
                    'activo' => true,
                ]
            );
            Seccion::firstOrCreate(
                [
                    'sucursal_id' => $sucursal->id,
                    'nombre' => 'Enfermeria',
                ],
                [
                    'descripcion' => 'Depósito de medicamentos e insumos de enfermeria',
                    'es_almacen_principal' => false,
                    'activo' => true,
                ]
            );
        }
    }
}
