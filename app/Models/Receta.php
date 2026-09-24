<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receta extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'recetas';

    protected $fillable = [
        'proforma_id',
        'user_id',
        'activo',
        'observaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Regla de negocio: Solo una receta activa por proforma.
     */
    protected static function booted(): void
    {
        static::saving(function (Receta $receta): void {
            if ($receta->activo && $receta->proforma_id) {
                static::where('proforma_id', $receta->proforma_id)
                    ->where('id', '!=', $receta->id ?? 0)
                    ->update(['activo' => false]);
            }
        });
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RecetaDetalle::class, 'receta_id');
    }
}
