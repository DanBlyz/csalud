<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'baja_medicamento_id',
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

    protected static function booted(): void
    {
        static::created(function (MovimientoInventario $mov): void {
            if (! $mov->proforma_id) {
                return;
            }

            // 1. Al registrar una Salida Receta / Salida Farmacia, congelar la cantidad y el precio unitario de esa fecha en despachos_farmacia
            if (in_array($mov->tipo_movimiento, ['Salida Receta', 'Salida Farmacia'], true)) {
                if (! DespachoFarmacia::withTrashed()->where('movimiento_inventario_id', $mov->id)->exists()) {
                    $recetaDetalle = null;
                    if ($mov->receta_id) {
                        $recetaDetalle = RecetaDetalle::where('receta_id', $mov->receta_id)
                            ->where('producto_id', $mov->producto_id)
                            ->first();
                    }

                    $producto = Producto::find($mov->producto_id);
                    $precioUnitario = (float) ($producto?->ultimo_precio_venta ?? 0);

                    DespachoFarmacia::create([
                        'proforma_id' => $mov->proforma_id,
                        'receta_id' => $mov->receta_id,
                        'receta_detalle_id' => $recetaDetalle?->id,
                        'producto_id' => $mov->producto_id,
                        'lote_id' => $mov->lote_id,
                        'seccion_id' => $mov->seccion_origen_id,
                        'movimiento_inventario_id' => $mov->id,
                        'cantidad' => $mov->cantidad,
                        'precio_unitario' => $precioUnitario,
                        'user_id' => $mov->user_id,
                        'observaciones' => $mov->observaciones,
                    ]);
                }

                return;
            }

            // 2. Al registrar una Devolución de Salida Receta / Salida Farmacia, descontar la cantidad en despachos_farmacia
            if (in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion', 'Devolución Farmacia', 'Devolucion Farmacia'], true)) {
                $origen = $mov->movimiento_origen_id ? self::find($mov->movimiento_origen_id) : null;
                $esDevolucionReceta = ($origen && in_array($origen->tipo_movimiento, ['Salida Receta', 'Salida Farmacia'], true))
                    || (! $origen && $mov->receta_id !== null);

                if ($esDevolucionReceta) {
                    $restante = (int) $mov->cantidad;

                    if ($mov->movimiento_origen_id) {
                        $despachoOrigen = DespachoFarmacia::where('movimiento_inventario_id', $mov->movimiento_origen_id)->first();
                        if ($despachoOrigen) {
                            if ($despachoOrigen->cantidad > $restante) {
                                $despachoOrigen->decrement('cantidad', $restante);
                                $restante = 0;
                            } else {
                                $restante -= $despachoOrigen->cantidad;
                                $despachoOrigen->update(['cantidad' => 0]);
                                $despachoOrigen->delete();
                            }
                        }
                    }

                    if ($restante > 0) {
                        $despachos = DespachoFarmacia::where('proforma_id', $mov->proforma_id)
                            ->where('producto_id', $mov->producto_id)
                            ->orderBy('id', 'asc')
                            ->get();

                        foreach ($despachos as $desp) {
                            if ($restante <= 0) {
                                break;
                            }

                            if ($desp->cantidad > $restante) {
                                $desp->decrement('cantidad', $restante);
                                $restante = 0;
                            } else {
                                $restante -= $desp->cantidad;
                                $desp->update(['cantidad' => 0]);
                                $desp->delete();
                            }
                        }
                    }
                }
            }
        });
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

    public function bajaMedicamento(): BelongsTo
    {
        return $this->belongsTo(BajaMedicamento::class, 'baja_medicamento_id');
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

    public function despachoFarmacia(): HasOne
    {
        return $this->hasOne(DespachoFarmacia::class, 'movimiento_inventario_id')->withTrashed();
    }

    /**
     * Obtiene el precio unitario congelado a la fecha en que se realizó el despacho o consumo extra.
     */
    public function precioUnitarioAplicado(): float
    {
        if ($this->despachoFarmacia && (float) $this->despachoFarmacia->precio_unitario > 0) {
            return (float) $this->despachoFarmacia->precio_unitario;
        }

        if (in_array($this->tipo_movimiento, ['Devolución', 'Devolucion', 'Devolución Farmacia', 'Devolucion Farmacia'], true)) {
            $origen = $this->relationLoaded('movimientoOrigen')
                ? $this->movimientoOrigen
                : ($this->movimiento_origen_id ? self::with('despachoFarmacia')->find($this->movimiento_origen_id) : null);

            if ($origen?->despachoFarmacia && (float) $origen->despachoFarmacia->precio_unitario > 0) {
                return (float) $origen->despachoFarmacia->precio_unitario;
            }

            if ($origen && $origen->tipo_movimiento === 'Consumo Extra' && $this->proforma_id) {
                $precioConsumo = ConsumoExtra::withTrashed()
                    ->where('proforma_id', $this->proforma_id)
                    ->where('producto_id', $this->producto_id)
                    ->latest('id')
                    ->value('precio_unitario');

                if ($precioConsumo !== null && (float) $precioConsumo > 0) {
                    return (float) $precioConsumo;
                }
            }
        }

        if ($this->tipo_movimiento === 'Consumo Extra' && $this->proforma_id) {
            $precioConsumo = ConsumoExtra::withTrashed()
                ->where('proforma_id', $this->proforma_id)
                ->where('producto_id', $this->producto_id)
                ->latest('id')
                ->value('precio_unitario');

            if ($precioConsumo !== null && (float) $precioConsumo > 0) {
                return (float) $precioConsumo;
            }
        }

        if ($this->proforma_id) {
            $precioDespacho = DespachoFarmacia::withTrashed()
                ->where('proforma_id', $this->proforma_id)
                ->where('producto_id', $this->producto_id)
                ->latest('id')
                ->value('precio_unitario');

            if ($precioDespacho !== null && (float) $precioDespacho > 0) {
                return (float) $precioDespacho;
            }
        }

        return (float) ($this->producto?->ultimo_precio_venta ?? 0);
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
