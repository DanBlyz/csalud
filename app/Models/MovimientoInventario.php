<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoInventario extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'sucursal_id',
        'producto_id',
        'lote_id',
        'cantidad',
        'tipo_movimiento',
        'receta_id',
        'proforma_id',
        'movimiento_origen_id',
        'seccion_origen_id',
        'seccion_destino_id',
        'user_id',
        'observaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function seccionOrigen(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_origen_id');
    }

    public function seccionDestino(): BelongsTo
    {
        return $this->belongsTo(Seccion::class, 'seccion_destino_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function movimientoOrigen(): BelongsTo
    {
        return $this->belongsTo(self::class, 'movimiento_origen_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(self::class, 'movimiento_origen_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calcula la cantidad total devuelta vinculada a este movimiento.
     */
    public function totalDevuelto(): int
    {
        return (int) $this->devoluciones()
            ->whereIn('tipo_movimiento', ['Devolución', 'Devolucion'])
            ->sum('cantidad');
    }

    /**
     * Calcula la cantidad restante que aún puede devolverse de este movimiento.
     */
    public function cantidadDisponibleParaDevolucion(): int
    {
        return max(0, (int) $this->cantidad - $this->totalDevuelto());
    }

    /**
     * Determina si el movimiento está habilitado para procesar una devolución.
     */
    public function puedeSerDevuelto(): bool
    {
        if (! $this->proforma_id || ! $this->proforma) {
            return false;
        }

        if ($this->proforma->estado !== 'En Curso') {
            return false;
        }

        if (! in_array($this->tipo_movimiento, ['Salida Receta', 'Consumo Extra'], true)) {
            return false;
        }

        return $this->cantidadDisponibleParaDevolucion() > 0;
    }
}
