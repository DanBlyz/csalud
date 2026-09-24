<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proforma>
 */
class ProformaFactory extends Factory
{
    protected $model = Proforma::class;

    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'paciente_id' => Paciente::factory(),
            'medico_id' => User::factory(),
            'tipo_atencion' => fake()->randomElement(['Ambulatoria', 'Internacion']),
            'fecha_ingreso' => fake()->dateTimeBetween('-1 month', 'now'),
            'fecha_salida' => null,
            'motivo_consulta' => fake()->sentence(),
            'diagnostico' => fake()->sentence(),
            'pieza' => fake()->randomElement(['Sala 1', 'Habitación 101', 'Box 3', null]),
            'estado' => 'En Curso',
            'costo_total' => 0.00,
        ];
    }
}
