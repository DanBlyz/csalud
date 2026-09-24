<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSolicitud extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'tipos_solicitudes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    public function proformaSolicitudes(): HasMany
    {
        return $this->hasMany(ProformaSolicitud::class, 'tipo_solicitud_id');
    }
}
