<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecetaDetalle>
 */
class RecetaDetalleFactory extends Factory
{
    protected $model = RecetaDetalle::class;

    public function definition(): array
    {
        return [
            'receta_id' => Receta::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 5),
            'indicaciones' => '1 cada 8 horas por 5 días',
            'despachado' => false,
        ];
    }
}
