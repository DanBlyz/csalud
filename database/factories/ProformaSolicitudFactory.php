<?php

namespace Database\Factories;

use App\Models\Proforma;
use App\Models\ProformaSolicitud;
use App\Models\TipoSolicitud;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProformaSolicitud>
 */
class ProformaSolicitudFactory extends Factory
{
    protected $model = ProformaSolicitud::class;

    public function definition(): array
    {
        return [
            'proforma_id' => Proforma::factory(),
            'tipo_solicitud_id' => TipoSolicitud::factory(),
            'archivo' => null,
            'observaciones' => fake()->sentence(),
        ];
    }
}
