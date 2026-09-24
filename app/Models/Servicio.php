<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'servicios';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio_tentativo',
        'estado',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'precio_tentativo' => 'decimal:2',
            'estado' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function proformaServicios(): HasMany
    {
        return $this->hasMany(ProformaServicio::class, 'servicio_id');
    }
}
