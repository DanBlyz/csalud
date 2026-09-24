<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        $precioCompra = fake()->randomFloat(2, 5, 100);
        $precioVenta = round($precioCompra * 1.35, 2);
        $cantidad = fake()->numberBetween(10, 100);

        return [
            'sucursal_id' => Sucursal::factory(),
            'producto_id' => Producto::factory(),
            'proveedor_id' => Proveedor::factory(),
            'codigo_lote' => 'LOT-'.strtoupper(fake()->bothify('??-####')),
            'cantidad_ingresada' => $cantidad,
            'cantidad_actual' => $cantidad,
            'fecha_vencimiento' => fake()->dateTimeBetween('+6 months', '+3 years')->format('Y-m-d'),
            'precio_compra' => $precioCompra,
            'precio_venta' => $precioVenta,
        ];
    }
}
