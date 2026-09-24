<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'marca_id',
        'nombre',
        'descripcion',
        'unidad_medida',
        'ultimo_precio_venta',
        'stock_minimo',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_precio_venta' => 'decimal:2',
            'stock_minimo' => 'integer',
        ];
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    /**
     * Lotes con stock disponible ordenados por fecha de vencimiento (FEFO).
     */
    public function lotesDisponibles(): HasMany
    {
        return $this->hasMany(Lote::class, 'producto_id')
            ->where('cantidad_actual', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc');
    }

    public function recetaDetalles(): HasMany
    {
        return $this->hasMany(RecetaDetalle::class, 'producto_id');
    }

    public function consumosExtras(): HasMany
    {
        return $this->hasMany(ConsumoExtra::class, 'producto_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    /**
     * Cantidad total en stock sumando todos los lotes.
     */
    protected function stockActual(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) $this->lotes()->sum('cantidad_actual')
        );
    }
}
