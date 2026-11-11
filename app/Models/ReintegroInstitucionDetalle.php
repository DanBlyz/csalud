<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReintegroInstitucionDetalle extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'reintegro_institucion_detalles';

    protected $fillable = [
        'reintegro_institucion_id',
        'producto_id',
        'lote_id',
        'seccion_id',
        'codigo_lote',
        'fecha_vencimiento',
        'cantidad_usada_institucion',
        'cantidad_reintegrada',
        'precio_compra',
        'precio_venta',
        'observaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'cantidad_usada_institucion' => 'integer',
            'cantidad_reintegrada' => 'integer',
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
        ];
    }

    public function reintegro(): BelongsTo
    {
        return $this->belongsTo(ReintegroInstitucion::class, 'reintegro_institucion_id');
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

    public function aplicaciones(): HasMany
    {
        return $this->hasMany(ReintegroInstitucionAplicacion::class, 'reintegro_institucion_detalle_id');
    }
}
