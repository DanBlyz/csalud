<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seccion extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'secciones';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'descripcion',
        'es_almacen_principal',
        'activo',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'es_almacen_principal' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function loteSecciones(): HasMany
    {
        return $this->hasMany(LoteSeccion::class, 'seccion_id');
    }

    public function lotes(): BelongsToMany
    {
        return $this->belongsToMany(Lote::class, 'lote_secciones', 'seccion_id', 'lote_id')
            ->withPivot('cantidad_actual')
            ->withTimestamps();
    }

    /**
     * Calcula la cantidad total de unidades disponibles de todos los lotes en esta sección.
     */
    public function stockTotal(): int
    {
        return (int) $this->loteSecciones()->sum('cantidad_actual');
    }

    /**
     * Calcula la cantidad de un producto específico almacenado en esta sección.
     */
    public function stockDeProducto(int $productoId): int
    {
        return (int) $this->loteSecciones()
            ->whereHas('lote', fn ($q) => $q->where('producto_id', $productoId))
            ->sum('cantidad_actual');
    }
}
