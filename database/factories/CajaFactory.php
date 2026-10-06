<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    protected $model = Caja::class;

    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'user_id' => User::factory(),
            'monto_apertura' => fake()->randomFloat(2, 0, 200),
            'fecha_apertura' => now(),
            'estado' => 'Abierta',
            'observaciones_apertura' => fake()->optional()->sentence(),
        ];
    }
}
