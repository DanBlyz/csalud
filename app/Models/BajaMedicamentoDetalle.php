<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BajaMedicamentoDetalle extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'baja_medicamento_detalles';

    protected $fillable = [
        'baja_medicamento_id',
        'producto_id',
        'lote_id',
        'seccion_id',
        'cantidad',
        'forma_farmaceutica',
        'concentracion',
        'fecha_vencimiento',
        'observaciones',
        'movimiento_inventario_id',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function bajaMedicamento(): BelongsTo
    {
        return $this->belongsTo(BajaMedicamento::class, 'baja_medicamento_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

    public function movimientoInventario(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }
}
