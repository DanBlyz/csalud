<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\Pago;
use App\Models\Proforma;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'caja_id' => Caja::factory(),
            'proforma_id' => Proforma::factory(),
            'tipo_movimiento' => 'Ingreso Proforma',
            'categoria' => 'Proforma',
            'tipo_pago' => fake()->randomElement(['Efectivo', 'QR', 'Transferencia']),
            'concepto' => fake()->sentence(3),
            'monto' => fake()->randomFloat(2, 50, 500),
            'numero_referencia' => fake()->optional()->bothify('REF-#####'),
            'user_id' => User::factory(),
        ];
    }
}
