<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'cajas';

    protected $fillable = [
        'sucursal_id',
        'user_id',
        'monto_apertura',
        'fecha_apertura',
        'fecha_cierre',
        'estado',
        'monto_cierre_efectivo',
        'monto_cierre_qr',
        'monto_cierre_transferencia',
        'total_ingresos_efectivo',
        'total_ingresos_qr',
        'total_ingresos_transferencia',
        'total_egresos_efectivo',
        'saldo_esperado_efectivo',
        'diferencia_efectivo',
        'observaciones_apertura',
        'observaciones_cierre',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'monto_apertura' => 'decimal:2',
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_cierre_efectivo' => 'decimal:2',
            'monto_cierre_qr' => 'decimal:2',
            'monto_cierre_transferencia' => 'decimal:2',
            'total_ingresos_efectivo' => 'decimal:2',
            'total_ingresos_qr' => 'decimal:2',
            'total_ingresos_transferencia' => 'decimal:2',
            'total_egresos_efectivo' => 'decimal:2',
            'saldo_esperado_efectivo' => 'decimal:2',
            'diferencia_efectivo' => 'decimal:2',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'caja_id');
    }

    public function isAbierta(): bool
    {
        return $this->estado === 'Abierta';
    }

    public function isCerrada(): bool
    {
        return $this->estado === 'Cerrada';
    }

    /**
     * Total de ingresos recibidos en Efectivo (proformas y extras).
     */
    public function totalIngresosEfectivo(): float
    {
        return (float) $this->pagos()
            ->where('tipo_movimiento', '!=', 'Egreso Caja')
            ->where('tipo_pago', 'Efectivo')
            ->sum('monto');
    }

    /**
     * Total de ingresos recibidos por QR.
     */
    public function totalIngresosQr(): float
    {
        return (float) $this->pagos()
            ->where('tipo_movimiento', '!=', 'Egreso Caja')
            ->where('tipo_pago', 'QR')
            ->sum('monto');
    }

    /**
     * Total de ingresos recibidos por Transferencia bancaria.
     */
    public function totalIngresosTransferencia(): float
    {
        return (float) $this->pagos()
            ->where('tipo_movimiento', '!=', 'Egreso Caja')
            ->where('tipo_pago', 'Transferencia')
            ->sum('monto');
    }

    /**
     * Sumatoria global de ingresos de la caja (Efectivo + QR + Transferencia).
     */
    public function totalIngresos(): float
    {
        return $this->totalIngresosEfectivo() + $this->totalIngresosQr() + $this->totalIngresosTransferencia();
    }

    /**
     * Total de salidas/egresos de efectivo de la caja.
     */
    public function totalEgresosEfectivo(): float
    {
        return (float) $this->pagos()
            ->where('tipo_movimiento', 'Egreso Caja')
            ->sum('monto');
    }

    /**
     * Saldo que debería haber físicamente en gaveta en efectivo:
     * Monto Apertura + Ingresos Efectivo - Egresos Efectivo.
     */
    public function saldoEsperadoEfectivo(): float
    {
        return (float) $this->monto_apertura + $this->totalIngresosEfectivo() - $this->totalEgresosEfectivo();
    }

    /**
     * Cierra formalmente la caja calculando arqueo y discrepancias.
     */
    public function cerrar(
        float $conteoEfectivo,
        float $conteoQr,
        float $conteoTransferencia,
        ?string $observaciones = null
    ): void {
        $ingresosEf = $this->totalIngresosEfectivo();
        $ingresosQr = $this->totalIngresosQr();
        $ingresosTr = $this->totalIngresosTransferencia();
        $egresosEf = $this->totalEgresosEfectivo();
        $saldoEsperado = (float) $this->monto_apertura + $ingresosEf - $egresosEf;
        $diferencia = $conteoEfectivo - $saldoEsperado;

        $this->update([
            'estado' => 'Cerrada',
            'fecha_cierre' => Carbon::now(),
            'monto_cierre_efectivo' => $conteoEfectivo,
            'monto_cierre_qr' => $conteoQr,
            'monto_cierre_transferencia' => $conteoTransferencia,
            'total_ingresos_efectivo' => $ingresosEf,
            'total_ingresos_qr' => $ingresosQr,
            'total_ingresos_transferencia' => $ingresosTr,
            'total_egresos_efectivo' => $egresosEf,
            'saldo_esperado_efectivo' => $saldoEsperado,
            'diferencia_efectivo' => $diferencia,
            'observaciones_cierre' => $observaciones ? trim($observaciones) : null,
        ]);
    }
}
