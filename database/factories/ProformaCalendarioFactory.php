<?php

namespace Database\Factories;

use App\Models\Proforma;
use App\Models\ProformaCalendario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaCalendario>
 */
class ProformaCalendarioFactory extends Factory
{
    protected $model = ProformaCalendario::class;

    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'fecha' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'hora' => fake()->time('H:i'),
            'descripcion' => fake()->sentence(),
            'estado' => 'Programado',
        ];
    }
}
