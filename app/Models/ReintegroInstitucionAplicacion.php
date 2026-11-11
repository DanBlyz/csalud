<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReintegroInstitucionAplicacion extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'reintegro_institucion_aplicaciones';

    protected $fillable = [
        'reintegro_institucion_id',
        'reintegro_institucion_detalle_id',
        'proforma_id',
        'paciente_id',
        'producto_id',
        'movimiento_origen_id',
        'movimiento_devolucion_id',
        'tipo_origen',
        'cantidad_descontada',
        'precio_unitario',
        'subtotal_descontado',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_descontada' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal_descontado' => 'decimal:2',
        ];
    }

    public function reintegro(): BelongsTo
    {
        return $this->belongsTo(ReintegroInstitucion::class, 'reintegro_institucion_id');
    }

    public function detalle(): BelongsTo
    {
        return $this->belongsTo(ReintegroInstitucionDetalle::class, 'reintegro_institucion_detalle_id');
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function movimientoOrigen(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_origen_id');
    }

    public function movimientoDevolucion(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_devolucion_id');
    }
}
