<?php

namespace Database\Factories;

use App\Models\CierreMensual;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CierreMensual>
 */
class CierreMensualFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'anio' => (int) date('Y'),
            'mes' => (int) date('n'),
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->endOfMonth()->toDateString(),
            'total_ingresos' => 10000.00,
            'total_egresos' => 4000.00,
            'utilidad_neta' => 6000.00,
            'estado' => 'Borrador',
            'observaciones' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
