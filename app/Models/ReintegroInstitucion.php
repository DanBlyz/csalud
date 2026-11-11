<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReintegroInstitucion extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'reintegros_instituciones';

    protected $fillable = [
        'sucursal_id',
        'institucion_id',
        'seccion_destino_id',
        'codigo',
        'fecha_reintegro',
        'persona_entrega',
        'ci_persona_entrega',
        'observaciones',
        'total_unidades',
        'monto_total_descontado',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_reintegro' => 'date',
            'total_unidades' => 'integer',
            'monto_total_descontado' => 'decimal:2',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    public function seccionDestino(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_destino_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ReintegroInstitucionDetalle::class, 'reintegro_institucion_id');
    }

    public function aplicaciones(): HasMany
    {
        return $this->hasMany(ReintegroInstitucionAplicacion::class, 'reintegro_institucion_id');
    }

    /**
     * Genera el siguiente código correlativo anual (ej. RI-2026-0001).
     */
    public static function generarSiguienteCodigo(): string
    {
        $anio = now()->format('Y');
        $ultimo = self::whereYear('fecha_reintegro', $anio)
            ->orWhere('codigo', 'like', "RI-{$anio}-%")
            ->withTrashed()
            ->latest('id')
            ->first();

        $correlativo = 1;
        if ($ultimo && preg_match('/RI-\d{4}-(\d+)/', $ultimo->codigo, $matches)) {
            $correlativo = ((int) $matches[1]) + 1;
        }

        return sprintf('RI-%s-%04d', $anio, $correlativo);
    }
}
