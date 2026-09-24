<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoInventario>
 */
class MovimientoInventarioFactory extends Factory
{
    protected $model = MovimientoInventario::class;

    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'producto_id' => Producto::factory(),
            'lote_id' => Lote::factory(),
            'cantidad' => fake()->numberBetween(1, 10),
            'tipo_movimiento' => 'Ajuste',
            'receta_id' => null,
            'proforma_id' => null,
            'user_id' => User::factory(),
        ];
    }
}
