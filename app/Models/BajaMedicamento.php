<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BajaMedicamento extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'bajas_medicamentos';

    protected $fillable = [
        'sucursal_id',
        'codigo',
        'fecha_registro',
        'area_solicitante',
        'responsable',
        'motivo',
        'justificacion',
        'estado',
        'aprobado_por_id',
        'fecha_aprobacion',
        'observaciones_aprobacion',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
            'fecha_aprobacion' => 'datetime',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(BajaMedicamentoDetalle::class, 'baja_medicamento_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'baja_medicamento_id');
    }

    public function esPendiente(): bool
    {
        return $this->estado === 'Pendiente';
    }

    public function esAprobado(): bool
    {
        return $this->estado === 'Aprobado';
    }

    public function esRechazado(): bool
    {
        return $this->estado === 'Rechazado';
    }

    public function totalUnidades(): int
    {
        return (int) $this->detalles->sum('cantidad');
    }

    /**
     * Genera el siguiente código correlativo anual (ej. BM-2026-0001).
     */
    public static function generarSiguienteCodigo(): string
    {
        $anio = now()->format('Y');
        $ultimo = self::whereYear('fecha_registro', $anio)
            ->orWhere('codigo', 'like', "BM-{$anio}-%")
            ->withTrashed()
            ->latest('id')
            ->first();

        $correlativo = 1;
        if ($ultimo && preg_match('/BM-\d{4}-(\d+)/', $ultimo->codigo, $matches)) {
            $correlativo = ((int) $matches[1]) + 1;
        }

        return sprintf('BM-%s-%04d', $anio, $correlativo);
    }

    /**
     * Procesa la aprobación de la baja y ejecuta el descuento real en lotes,
     * secciones y genera los registros de movimientos de inventario.
     *
     * @throws \RuntimeException
     */
    public function aprobar(User $usuarioAprobador, ?string $observaciones = null): void
    {
        if ($this->estado !== 'Pendiente') {
            throw new \RuntimeException("Solo se pueden aprobar actas de baja en estado 'Pendiente'. Estado actual: {$this->estado}");
        }

        if ($this->detalles()->count() === 0) {
            throw new \RuntimeException('No se puede aprobar un acta de baja sin medicamentos o insumos detallados.');
        }

        DB::transaction(function () use ($usuarioAprobador, $observaciones): void {
            $this->load(['detalles.lote', 'detalles.seccion', 'detalles.producto']);

            foreach ($this->detalles as $detalle) {
                $lote = Lote::where('id', $detalle->lote_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $loteSeccion = LoteSeccion::where('lote_id', $detalle->lote_id)
                    ->where('seccion_id', $detalle->seccion_id)
                    ->lockForUpdate()
                    ->first();

                // Validación de stock disponible consolidado
                if ($lote->cantidad_actual < $detalle->cantidad) {
                    throw new \RuntimeException(
                        "Stock insuficiente en lote '{$lote->codigo_lote}' del producto '{$detalle->producto?->nombre}'. ".
                        "Disponible consolidado: {$lote->cantidad_actual}, Requerido para baja: {$detalle->cantidad}."
                    );
                }

                // Validación de stock en la sección de origen específica
                $dispSeccion = $loteSeccion ? (int) $loteSeccion->cantidad_actual : 0;
                if ($dispSeccion < $detalle->cantidad) {
                    $nombreSec = $detalle->seccion?->nombre ?? 'Sección seleccionada';
                    throw new \RuntimeException(
                        "Stock insuficiente en la sección '{$nombreSec}' para el lote '{$lote->codigo_lote}'. ".
                        "Disponible en esa área: {$dispSeccion}, Requerido para baja: {$detalle->cantidad}."
                    );
                }

                // Descuento físico en el lote consolidado y en la sección satélite
                $lote->decrement('cantidad_actual', $detalle->cantidad);
                $loteSeccion->decrement('cantidad_actual', $detalle->cantidad);

                // Registro del movimiento de inventario (Kardex)
                $movimiento = MovimientoInventario::create([
                    'sucursal_id' => $this->sucursal_id,
                    'producto_id' => $detalle->producto_id,
                    'lote_id' => $detalle->lote_id,
                    'baja_medicamento_id' => $this->id,
                    'cantidad' => $detalle->cantidad,
                    'tipo_movimiento' => 'Baja Medicamento',
                    'seccion_origen_id' => $detalle->seccion_id,
                    'seccion_destino_id' => null,
                    'user_id' => $usuarioAprobador->id,
                    'observaciones' => "Baja por {$this->motivo} - Acta {$this->codigo}".($detalle->observaciones ? " - {$detalle->observaciones}" : ''),
                ]);

                $detalle->update([
                    'movimiento_inventario_id' => $movimiento->id,
                ]);
            }

            $this->update([
                'estado' => 'Aprobado',
                'aprobado_por_id' => $usuarioAprobador->id,
                'fecha_aprobacion' => now(),
                'observaciones_aprobacion' => $observaciones ? trim($observaciones) : null,
            ]);
        });
    }

    /**
     * Rechaza una solicitud de baja pendiente sin alterar inventarios.
     *
     * @throws \RuntimeException
     */
    public function rechazar(User $usuarioRechazador, ?string $motivoRechazo = null): void
    {
        if ($this->estado !== 'Pendiente') {
            throw new \RuntimeException("Solo se pueden rechazar solicitudes en estado 'Pendiente'.");
        }

        $this->update([
            'estado' => 'Rechazado',
            'aprobado_por_id' => $usuarioRechazador->id,
            'fecha_aprobacion' => now(),
            'observaciones_aprobacion' => $motivoRechazo ? trim($motivoRechazo) : 'Rechazado por supervisión.',
        ]);
    }
}
