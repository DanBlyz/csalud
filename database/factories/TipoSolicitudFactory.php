<?php

namespace Database\Factories;

use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoSolicitud>
 */
class TipoSolicitudFactory extends Factory
{
    protected $model = TipoSolicitud::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
        ];
    }
}
