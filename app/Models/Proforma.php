<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proforma extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'proformas';

    protected $fillable = [
        'sucursal_id',
        'paciente_id',
        'tipo_atencion',
        'fecha_ingreso',
        'fecha_salida',
        'motivo_consulta',
        'diagnostico',
        'pieza',
        'estado',
        'costo_total',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'datetime',
            'fecha_salida' => 'datetime',
            'costo_total' => 'decimal:2',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medicos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'proforma_medicos', 'proforma_id', 'medico_id')
            ->withTimestamps();
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(ProformaServicio::class, 'proforma_id');
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(ProformaSolicitud::class, 'proforma_id');
    }

    public function calendarios(): HasMany
    {
        return $this->hasMany(ProformaCalendario::class, 'proforma_id');
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'proforma_id');
    }

    public function recetaActiva(): HasOne
    {
        return $this->hasOne(Receta::class, 'proforma_id')
            ->where('activo', true);
    }

    public function consumosExtras(): HasMany
    {
        return $this->hasMany(ConsumoExtra::class, 'proforma_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(ProformaPago::class, 'proforma_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'proforma_id');
    }

    /**
     * Total abonado / pagado en la proforma.
     */
    public function totalPagado(): float
    {
        return (float) $this->pagos()->sum('monto');
    }

    /**
     * Saldo pendiente de pago.
     */
    public function saldoPendiente(): float
    {
        return max(0.00, (float) $this->costo_total - $this->totalPagado());
    }

    /**
     * Total de salidas/despachos de farmacia efectivamente entregados para esta proforma.
     */
    public function totalDespachosFarmacia(): float
    {
        return (float) $this->movimientosInventario()
            ->whereIn('tipo_movimiento', ['Salida Receta', 'Salida Farmacia'])
            ->join('productos', 'movimientos_inventario.producto_id', '=', 'productos.id')
            ->selectRaw('SUM(movimientos_inventario.cantidad * COALESCE(productos.ultimo_precio_venta, 0)) as total')
            ->value('total');
    }

    /**
     * Total referencial de medicamentos prescritos en la receta activa (indicación médica teórica).
     * No se cobra de antemano al paciente; el cobro se liquida según los despachos reales de Farmacia.
     */
    public function totalPrescripcionReferencial(): float
    {
        if (! $this->recetaActiva) {
            return 0.00;
        }

        return (float) $this->recetaActiva->detalles()
            ->join('productos', 'receta_detalles.producto_id', '=', 'productos.id')
            ->selectRaw('SUM(receta_detalles.cantidad * COALESCE(productos.ultimo_precio_venta, 0)) as total')
            ->value('total');
    }

    /**
     * Recalcula el costo total sumando servicios clínicos, consumos extras de piso y despachos reales de farmacia.
     * La prescripción médica se mantiene como guía clínica referencial hasta su despacho efectivo.
     */
    public function recalcularTotal(): void
    {
        $totalServicios = (float) $this->servicios()->sum('costo_final');
        $totalConsumos = (float) $this->consumosExtras()->selectRaw('SUM(cantidad * COALESCE(precio_unitario, 0)) as total')->value('total');
        $totalFarmacia = $this->totalDespachosFarmacia();

        $this->costo_total = $totalServicios + $totalConsumos + $totalFarmacia;
        $this->saveQuietly();
    }
}
