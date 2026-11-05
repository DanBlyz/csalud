<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriaClinica extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'historias_clinicas';

    protected $fillable = [
        'proforma_id',
        'procedencia',
        'residencia',
        'fuente_informacion',
        'motivo_consulta',
        'enfermedad_actual',
        'antecedentes_patologicos',
        'antecedentes_no_patologicos',
        'menarca',
        'fum',
        'gestas',
        'partos',
        'cesareas',
        'abortos',
        'pap',
        'gineco_otros',
        'pa',
        'fc',
        'fr',
        'temperatura',
        'sato2',
        'peso',
        'talla',
        'imc',
        'examen_fisico',
        'diagnostico',
        'conducta',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function usuarioModificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_modificador_id');
    }
}
