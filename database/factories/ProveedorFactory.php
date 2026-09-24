<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'razon_social' => fake()->unique()->company().' S.A.',
            'nit_ruc' => fake()->unique()->numerify('##########'),
            'contacto_nombre' => fake()->name(),
            'telefono' => fake()->numerify('4######'),
            'celular' => fake()->numerify('7#######'),
            'direccion' => fake()->address(),
            'correo' => fake()->companyEmail(),
        ];
    }
}
