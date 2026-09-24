<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition(): array
    {
        return [
            'nombres' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'cedula' => fake()->unique()->numerify('########'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-80 years', '-1 years')->format('Y-m-d'),
            'genero' => fake()->randomElement(['Masculino', 'Femenino']),
            'direccion' => fake()->address(),
            'celular' => fake()->numerify('7#######'),
            'contacto_emergencia_nombre' => fake()->name(),
            'contacto_emergencia_telefono' => fake()->numerify('7#######'),
            'antecedentes_alergias' => fake()->optional()->sentence(),
        ];
    }
}
