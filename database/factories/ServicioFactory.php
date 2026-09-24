<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'nombre' => fake()->words(3, true),
            'descripcion' => fake()->sentence(),
            'precio_tentativo' => fake()->randomFloat(2, 20, 500),
            'estado' => true,
        ];
    }
}
