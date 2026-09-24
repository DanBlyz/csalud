<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'lotes';

    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'proveedor_id',
        'codigo_lote',
        'cantidad_ingresada',
        'cantidad_actual',
        'fecha_vencimiento',
        'precio_compra',
        'precio_venta',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'cantidad_ingresada' => 'integer',
            'cantidad_actual' => 'integer',
        ];
    }

    /**
     * Regla de negocio: Al guardar un lote, sincronizar el último precio de venta en el Producto.
     */
    protected static function booted(): void
    {
        static::saved(function (Lote $lote): void {
            if ($lote->producto_id && $lote->precio_venta) {
                Producto::where('id', $lote->producto_id)
                    ->update(['ultimo_precio_venta' => $lote->precio_venta]);
            }
        });
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }
}
