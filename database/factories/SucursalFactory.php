<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sucursal>
 */
class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Sucursal '.fake()->city(),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->numerify('4######'),
            'ciudad' => fake()->city(),
            'estado' => true,
        ];
    }
}
