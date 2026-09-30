<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\CierreDetalleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CierreDetalle extends Model
{
    /** @use HasFactory<CierreDetalleFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'cierre_detalles';

    protected $fillable = [
        'cierre_mensual_id',
        'tipo',
        'categoria',
        'concepto',
        'monto',
        'fecha',
        'comprobante_referencia',
        'origen_tipo',
        'origen_id',
        'observaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function cierreMensual(): BelongsTo
    {
        return $this->belongsTo(CierreMensual::class, 'cierre_mensual_id');
    }

    public function origen(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'origen_tipo', 'origen_id');
    }
}
