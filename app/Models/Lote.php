<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * Reglas de negocio del ciclo de vida del Lote.
     */
    protected static function booted(): void
    {
        // 1. Al guardar un lote, sincronizar el último precio de venta en el Producto
        static::saved(function (Lote $lote): void {
            if ($lote->producto_id && $lote->precio_venta) {
                Producto::where('id', $lote->producto_id)
                    ->update(['ultimo_precio_venta' => $lote->precio_venta]);
            }
        });

        // 2. Al crear un nuevo lote, asignar su stock inicial a la sección principal de la sucursal (Farmacia Central)
        static::created(function (Lote $lote): void {
            if ($lote->cantidad_actual > 0) {
                $seccionPrincipal = Seccion::where('sucursal_id', $lote->sucursal_id)
                    ->where('es_almacen_principal', true)
                    ->first() ?? Seccion::where('sucursal_id', $lote->sucursal_id)->first();

                if ($seccionPrincipal) {
                    LoteSeccion::firstOrCreate(
                        ['lote_id' => $lote->id, 'seccion_id' => $seccionPrincipal->id],
                        ['cantidad_actual' => $lote->cantidad_actual]
                    );
                }
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

    public function loteSecciones(): HasMany
    {
        return $this->hasMany(LoteSeccion::class, 'lote_id');
    }

    public function secciones(): BelongsToMany
    {
        return $this->belongsToMany(Seccion::class, 'lote_secciones', 'lote_id', 'seccion_id')
            ->withPivot('cantidad_actual')
            ->withTimestamps();
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }

    /**
     * Devuelve las existencias disponibles de este lote en una sección específica.
     */
    public function stockEnSeccion(?int $seccionId): int
    {
        if (! $seccionId) {
            return (int) $this->cantidad_actual;
        }

        $loteSec = $this->loteSecciones()->where('seccion_id', $seccionId)->first();
        if (! $loteSec) {
            // Si el lote no tiene fila en lote_secciones (ej. lote heredado o creado en factory sin secciones),
            // si la sección es almacén principal o la única de la sucursal, consideramos su stock total disponible allí.
            $esPrincipal = Seccion::where('id', $seccionId)->value('es_almacen_principal');

            return $esPrincipal ? (int) $this->cantidad_actual : 0;
        }

        return (int) $loteSec->cantidad_actual;
    }

    /**
     * Realiza la transferencia interna de existencias entre dos secciones hospitalarias con asiento en Kardex.
     */
    public function transferirASeccion(int $seccionOrigenId, int $seccionDestinoId, int $cantidad, ?string $motivo = null, ?int $userId = null): void
    {
        if ($seccionOrigenId === $seccionDestinoId) {
            throw new \InvalidArgumentException('La sección origen y destino no pueden ser iguales.');
        }

        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a transferir debe ser mayor a cero.');
        }

        DB::transaction(function () use ($seccionOrigenId, $seccionDestinoId, $cantidad, $userId) {
            $origen = LoteSeccion::where('lote_id', $this->id)
                ->where('seccion_id', $seccionOrigenId)
                ->lockForUpdate()
                ->first();

            if (! $origen || $origen->cantidad_actual < $cantidad) {
                $disp = $origen ? $origen->cantidad_actual : 0;
                throw new \RuntimeException("Stock insuficiente en la sección origen. Disponible en esa área: {$disp} unidades.");
            }

            $origen->decrement('cantidad_actual', $cantidad);

            $destino = LoteSeccion::firstOrCreate(
                ['lote_id' => $this->id, 'seccion_id' => $seccionDestinoId],
                ['cantidad_actual' => 0]
            );
            $destino->increment('cantidad_actual', $cantidad);

            // Asiento inmutable en Kardex
            MovimientoInventario::create([
                'sucursal_id' => $this->sucursal_id,
                'producto_id' => $this->producto_id,
                'lote_id' => $this->id,
                'cantidad' => $cantidad,
                'tipo_movimiento' => 'Transferencia Interna',
                'seccion_origen_id' => $seccionOrigenId,
                'seccion_destino_id' => $seccionDestinoId,
                'user_id' => $userId ?? Auth::id() ?? 1,
            ]);
        });
    }

    /**
     * Descuenta existencias de este lote en una sección específica y registra el movimiento de salida en Kardex.
     */
    public function descontarDeSeccion(
        ?int $seccionId,
        int $cantidad,
        string $tipoMovimiento,
        ?int $proformaId = null,
        ?int $recetaId = null,
        ?int $userId = null
    ): MovimientoInventario {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a descontar debe ser mayor a cero.');
        }

        if (! $seccionId) {
            $central = Seccion::where('sucursal_id', $this->sucursal_id)->where('es_almacen_principal', true)->first()
                ?? Seccion::where('sucursal_id', $this->sucursal_id)->first();
            if (! $central) {
                $central = Seccion::create([
                    'sucursal_id' => $this->sucursal_id,
                    'nombre' => 'Farmacia Central',
                    'es_almacen_principal' => true,
                    'activo' => true,
                ]);
            }
            $seccionId = $central->id;
        }

        return DB::transaction(function () use ($seccionId, $cantidad, $tipoMovimiento, $proformaId, $recetaId, $userId) {
            $loteSeccion = LoteSeccion::where('lote_id', $this->id)
                ->where('seccion_id', $seccionId)
                ->lockForUpdate()
                ->first();

            if (! $loteSeccion) {
                // Si aún no existía el registro en la sección pero el lote consolidado tiene stock, inicializarlo
                if ($this->cantidad_actual >= $cantidad) {
                    $loteSeccion = LoteSeccion::create([
                        'lote_id' => $this->id,
                        'seccion_id' => $seccionId,
                        'cantidad_actual' => $this->cantidad_actual,
                    ]);
                } else {
                    throw new \RuntimeException('Stock insuficiente en la sección seleccionada. Disponible: 0 unidades.');
                }
            } elseif ($loteSeccion->cantidad_actual < $cantidad) {
                $disp = $loteSeccion->cantidad_actual;
                throw new \RuntimeException("Stock insuficiente en la sección seleccionada. Disponible en esa área: {$disp} unidades.");
            }

            // Descontar tanto del lote consolidado como de la sección satélite
            $this->decrement('cantidad_actual', $cantidad);
            $loteSeccion->decrement('cantidad_actual', $cantidad);

            return MovimientoInventario::create([
                'sucursal_id' => $this->sucursal_id,
                'producto_id' => $this->producto_id,
                'lote_id' => $this->id,
                'cantidad' => $cantidad,
                'tipo_movimiento' => $tipoMovimiento,
                'receta_id' => $recetaId,
                'proforma_id' => $proformaId,
                'seccion_origen_id' => $seccionId,
                'seccion_destino_id' => null,
                'user_id' => $userId ?? Auth::id() ?? 1,
            ]);
        });
    }

    /**
     * Reintegra existencias a este lote en una sección específica (por anulación de despacho o consumo).
     */
    public function reintegrarASeccion(
        ?int $seccionId,
        int $cantidad,
        string $tipoMovimiento = 'Ajuste',
        ?int $proformaId = null,
        ?int $recetaId = null,
        ?int $userId = null
    ): MovimientoInventario {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a reintegrar debe ser mayor a cero.');
        }

        if (! $seccionId) {
            $central = Seccion::where('sucursal_id', $this->sucursal_id)->where('es_almacen_principal', true)->first()
                ?? Seccion::where('sucursal_id', $this->sucursal_id)->first();
            if (! $central) {
                $central = Seccion::create([
                    'sucursal_id' => $this->sucursal_id,
                    'nombre' => 'Farmacia Central',
                    'es_almacen_principal' => true,
                    'activo' => true,
                ]);
            }
            $seccionId = $central->id;
        }

        return DB::transaction(function () use ($seccionId, $cantidad, $tipoMovimiento, $proformaId, $recetaId, $userId) {
            $this->increment('cantidad_actual', $cantidad);

            $loteSeccion = LoteSeccion::firstOrCreate(
                ['lote_id' => $this->id, 'seccion_id' => $seccionId],
                ['cantidad_actual' => 0]
            );
            $loteSeccion->increment('cantidad_actual', $cantidad);

            return MovimientoInventario::create([
                'sucursal_id' => $this->sucursal_id,
                'producto_id' => $this->producto_id,
                'lote_id' => $this->id,
                'cantidad' => $cantidad,
                'tipo_movimiento' => $tipoMovimiento,
                'receta_id' => $recetaId,
                'proforma_id' => $proformaId,
                'seccion_origen_id' => null,
                'seccion_destino_id' => $seccionId,
                'user_id' => $userId ?? Auth::id() ?? 1,
            ]);
        });
    }

    /**
     * Días enteros restantes para el vencimiento (positivo: faltan días, 0: hoy, negativo: ya venció).
     */
    public function diasParaVencer(): ?int
    {
        if (! $this->fecha_vencimiento) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->fecha_vencimiento->startOfDay(), false);
    }

    /**
     * Comprueba si el lote ya expiró.
     */
    public function isVencido(): bool
    {
        $dias = $this->diasParaVencer();

        return $dias !== null && $dias < 0;
    }

    /**
     * Comprueba si el lote vence próximamente dentro del umbral en días (por defecto 60).
     */
    public function isPorVencer(int $umbral = 60): bool
    {
        $dias = $this->diasParaVencer();

        return $dias !== null && $dias >= 0 && $dias <= $umbral;
    }

    /**
     * Comprueba si el lote está vigente y fuera del periodo de alerta.
     */
    public function isVigente(int $umbral = 60): bool
    {
        $dias = $this->diasParaVencer();

        return $dias === null || $dias > $umbral;
    }

    /**
     * Texto legible formateado para la interfaz sobre el tiempo restante o transcurrido.
     */
    public function textoVencimiento(): string
    {
        $dias = $this->diasParaVencer();

        if ($dias === null) {
            return 'No perecedero';
        }

        if ($dias < 0) {
            $abs = abs($dias);

            return $abs === 1 ? 'Venció ayer' : "Venció hace {$abs} días";
        }

        if ($dias === 0) {
            return 'Vence hoy';
        }

        if ($dias === 1) {
            return 'Vence mañana (1 día)';
        }

        return "Vence en {$dias} días";
    }
}
