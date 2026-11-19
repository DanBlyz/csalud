<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumoExtra extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'consumos_extras';

    protected $fillable = [
        'proforma_id',
        'producto_id',
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

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Subtotal calculado con el precio unitario registrado en el consumo.
     */
    protected function precioTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((int) $this->cantidad * (float) $this->precio_unitario, 2)
        );
    }
}
