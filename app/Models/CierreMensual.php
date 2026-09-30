<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\CierreMensualFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CierreMensual extends Model
{
    /** @use HasFactory<CierreMensualFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'cierres_mensuales';

    protected $fillable = [
        'sucursal_id',
        'anio',
        'mes',
        'fecha_inicio',
        'fecha_fin',
        'total_ingresos',
        'total_egresos',
        'utilidad_neta',
        'estado',
        'observaciones',
        'user_id',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'total_ingresos' => 'decimal:2',
            'total_egresos' => 'decimal:2',
            'utilidad_neta' => 'decimal:2',
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

    public function detalles(): HasMany
    {
        return $this->hasMany(CierreDetalle::class, 'cierre_mensual_id');
    }

    public function ingresos(): HasMany
    {
        return $this->hasMany(CierreDetalle::class, 'cierre_mensual_id')->where('tipo', 'Ingreso');
    }

    public function egresos(): HasMany
    {
        return $this->hasMany(CierreDetalle::class, 'cierre_mensual_id')->where('tipo', 'Egreso');
    }

    /**
     * Recalcula los totales consolidados a partir de los detalles registrados.
     */
    public function recalcularTotales(): void
    {
        $this->total_ingresos = (float) $this->ingresos()->sum('monto');
        $this->total_egresos = (float) $this->egresos()->sum('monto');
        $this->utilidad_neta = $this->total_ingresos - $this->total_egresos;
        $this->saveQuietly();
    }

    /**
     * Retorna el nombre en texto del mes del cierre.
     */
    public function getNombreMesAttribute(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return $meses[(int) $this->mes] ?? 'Mes '.$this->mes;
    }
}
