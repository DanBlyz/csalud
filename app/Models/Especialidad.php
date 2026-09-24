<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidad extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'especialidad_id');
    }
}
