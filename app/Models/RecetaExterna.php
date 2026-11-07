<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecetaExterna extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'recetas_externas';

    protected $fillable = [
        'proforma_id',
        'user_id',
        'medico_nombre',
        'fecha',
        'diagnostico',
        'observaciones',
        'activo',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'activo' => 'boolean',
        ];
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

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(RecetaExternaDetalle::class, 'receta_externa_id');
    }

    /**
     * Nombre legible del médico prescriptor (usuario asignado o texto libre).
     */
    public function getNombreMedicoPrescriptorAttribute(): string
    {
        if ($this->doctor) {
            return $this->doctor->nombre_completo ?? $this->doctor->name;
        }

        return $this->medico_nombre ?: 'Médico Tratante';
    }
}

