<?php

namespace Database\Factories;

use App\Models\Proforma;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receta>
 */
class RecetaFactory extends Factory
{
    protected $model = Receta::class;

    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'user_id' => User::factory(),
            'activo' => true,
            'observaciones' => fake()->sentence(),
        ];
    }
}
