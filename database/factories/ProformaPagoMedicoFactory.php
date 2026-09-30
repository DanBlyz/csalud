<?php

namespace Database\Factories;

use App\Models\Proforma;
use App\Models\ProformaPagoMedico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaPagoMedico>
 */
class ProformaPagoMedicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'medico_id' => User::factory(),
            'monto' => fake()->randomFloat(2, 50, 1500),
            'observaciones' => fake()->sentence(),
            'fecha_pago' => now()->toDateString(),
            'user_id' => User::factory(),
        ];
    }
}
