<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'razon_social',
        'nit_ruc',
        'contacto_nombre',
        'telefono',
        'celular',
        'direccion',
        'correo',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'proveedor_id');
    }
}
