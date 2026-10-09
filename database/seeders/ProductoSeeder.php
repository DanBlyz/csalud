<?php

namespace Database\Seeders;

use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marcaBayer = Marca::where('nombre', 'Bayer')->first();
        $marcaBago = Marca::where('nombre', 'Bagó')->first();
        $marcaGenfar = Marca::where('nombre', 'Genfar')->first();
        $marcaNipro = Marca::where('nombre', 'Nipro Medical')->first();

        $productos = [
            [
                'marca_id' => $marcaBayer?->id ?? 1,
                'nombre' => 'Paracetamol 500mg (Comprimidos)',
                'unidad_medida' => 'Caja x 20',
                'ultimo_precio_venta' => 15.00,
                'descripcion' => 'Analgésico y antipirético',
                'stock_minimo' => 10,
            ],
            [
                'marca_id' => $marcaBago?->id ?? 2,
                'nombre' => 'Ibuprofeno 400mg (Tabletas)',
                'unidad_medida' => 'Caja x 10',
                'ultimo_precio_venta' => 18.50,
                'descripcion' => 'Antiinflamatorio no esteroideo',
                'stock_minimo' => 10,
            ],
            [
                'marca_id' => $marcaGenfar?->id ?? 3,
                'nombre' => 'Amoxicilina 500mg (Cápsulas)',
                'unidad_medida' => 'Caja x 24',
                'ultimo_precio_venta' => 32.00,
                'descripcion' => 'Antibiótico betalactámico',
                'stock_minimo' => 8,
            ],
            [
                'marca_id' => $marcaNipro?->id ?? 4,
                'nombre' => 'Jeringa Desechable 5ml c/ Aguja',
                'unidad_medida' => 'Unidad',
                'ultimo_precio_venta' => 3.50,
                'descripcion' => 'Insumo descartable estéril',
                'stock_minimo' => 50,
            ],
            [
                'marca_id' => $marcaNipro?->id ?? 4,
                'nombre' => 'Gasas Estériles 10x10 (Sobre)',
                'unidad_medida' => 'Sobre x 5',
                'ultimo_precio_venta' => 5.00,
                'descripcion' => 'Material de curación estéril',
                'stock_minimo' => 40,
            ],
            [
                'marca_id' => $marcaBayer?->id ?? 1,
                'nombre' => 'Solución Fisiológica 0.9% 500ml',
                'unidad_medida' => 'Frasco',
                'ultimo_precio_venta' => 16.00,
                'descripcion' => 'Solución isotónica para perfusión',
                'stock_minimo' => 25,
            ],
        ];

        foreach ($productos as $prod) {
            Producto::firstOrCreate(
                ['nombre' => $prod['nombre']],
                $prod
            );
        }
    }
}
