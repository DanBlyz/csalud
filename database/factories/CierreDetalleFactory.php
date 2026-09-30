<?php

namespace Database\Factories;

use App\Models\CierreDetalle;
use App\Models\CierreMensual;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CierreDetalle>
 */
class CierreDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cierre_mensual_id' => CierreMensual::factory(),
            'tipo' => fake()->randomElement(['Ingreso', 'Egreso']),
            'categoria' => fake()->randomElement(['Cobro Proforma', 'Honorario Médico', 'Compra Farmacia', 'Servicio Básico', 'Sueldo', 'Gasto Operativo']),
            'concepto' => fake()->sentence(),
            'monto' => fake()->randomFloat(2, 50, 2000),
            'fecha' => now()->toDateString(),
            'comprobante_referencia' => fake()->bothify('REF-####'),
            'observaciones' => fake()->sentence(),
        ];
    }
}
