<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'razon_social' => 'Droguería Inti S.A.',
                'nit_ruc' => '1020304050',
                'contacto_nombre' => 'Lic. Fernando Rojas',
                'celular' => '71122334',
            ],
            [
                'razon_social' => 'Distribuidora Médica Boliviana',
                'nit_ruc' => '2030405060',
                'contacto_nombre' => 'Dra. Patricia Lima',
                'celular' => '72233445',
            ],
        ];

        foreach ($proveedores as $prov) {
            Proveedor::firstOrCreate(
                ['razon_social' => $prov['razon_social']],
                $prov
            );
        }
    }
}
