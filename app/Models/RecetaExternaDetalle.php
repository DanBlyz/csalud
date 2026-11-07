<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecetaExternaDetalle extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'receta_externa_detalles';

    protected $fillable = [
        'receta_externa_id',
        'medicamento',
        'cantidad',
        'indicaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    public function recetaExterna(): BelongsTo
    {
        return $this->belongsTo(RecetaExterna::class, 'receta_externa_id');
    }
}

