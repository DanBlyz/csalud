<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'medico_id',
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

    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
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
     * Recalcula el costo total sumando servicios y consumos extras.
     */
    public function recalcularTotal(): void
    {
        $totalServicios = (float) $this->servicios()->sum('costo_final');
        $totalConsumos = (float) $this->consumosExtras()->selectRaw('SUM(cantidad * COALESCE(precio_unitario, 0)) as total')->value('total');

        $this->costo_total = $totalServicios + $totalConsumos;
        $this->saveQuietly();
    }
}
