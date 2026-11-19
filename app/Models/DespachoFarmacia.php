<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DespachoFarmacia extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'despachos_farmacia';

    protected $fillable = [
        'proforma_id',
        'receta_id',
        'receta_detalle_id',
        'producto_id',
        'lote_id',
        'seccion_id',
        'movimiento_inventario_id',
        'cantidad',
        'precio_unitario',
        'user_id',
        'observaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function recetaDetalle(): BelongsTo
    {
        return $this->belongsTo(RecetaDetalle::class, 'receta_detalle_id');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Subtotal calculado con el precio unitario congelado al momento del despacho.
     */
    protected function precioTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((int) $this->cantidad * (float) $this->precio_unitario, 2)
        );
    }
}
