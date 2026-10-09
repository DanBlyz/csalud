<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sucursal::firstOrCreate(
            ['nombre' => 'Sede Central'],
            [
                'direccion' => 'Av. de la Salud #123, Zona Central',
                'telefono' => '4-4123456',
                'ciudad' => 'Central',
                'estado' => true,
            ]
        );
    }
}
