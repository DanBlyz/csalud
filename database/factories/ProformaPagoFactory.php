<?php

namespace Database\Factories;

use App\Models\Proforma;
use App\Models\ProformaPago;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaPago>
 */
class ProformaPagoFactory extends Factory
{
    protected $model = ProformaPago::class;

    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'tipo_pago' => fake()->randomElement(['Efectivo', 'QR', 'Transferencia']),
            'monto' => fake()->randomFloat(2, 50, 500),
            'numero_referencia' => fake()->optional()->bothify('REF-#####'),
            'user_id' => User::factory(),
        ];
    }
}
