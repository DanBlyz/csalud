<?php

namespace Database\Factories;

use App\Models\ConsumoExtra;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsumoExtra>
 */
class ConsumoExtraFactory extends Factory
{
    protected $model = ConsumoExtra::class;

    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 3),
            'precio_unitario' => fake()->randomFloat(2, 5, 50),
            'user_id' => User::factory(),
            'observaciones' => fake()->sentence(),
        ];
    }
}
