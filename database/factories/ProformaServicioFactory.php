<?php

namespace Database\Factories;

use App\Models\Proforma;
use App\Models\ProformaServicio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaServicio>
 */
class ProformaServicioFactory extends Factory
{
    protected $model = ProformaServicio::class;

    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'servicio_id' => Servicio::factory(),
            'costo_final' => fake()->randomFloat(2, 30, 200),
            'observaciones' => fake()->sentence(),
        ];
    }
}
