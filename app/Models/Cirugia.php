<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cirugia extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'cirugias';

    protected $fillable = [
        'proforma_id',
        'numero',
        'es_institucional',
        'es_convenio',
        'convenio_nombre',
        'fecha',
        'especialidad',
        'sala',
        'cama',
        'hora_ingreso_quirofano',
        'hora_salida_quirofano',
        'hora_inicio_anestesia',
        'hora_fin_anestesia',
        'hora_inicio_cirugia',
        'hora_fin_cirugia',
        'diagnostico_operacion',
        'cirugia_efectuada',
        'tipo_cirugia',
        'tipo_anestesia',
        'analgesia',
        'cirujano_id',
        'primer_ayudante_id',
        'segundo_ayudante_id',
        'anestesiologo_id',
        'instrumentador_id',
        'circulante_id',
        'torre_laparoscopia',
        'bomba_infusion',
        'maquina_aspiracion',
        'material_importadora',
        'intensificador_imagenes',
        'maquina_anestesiologia',
        'monitor_multiparametrico',
        'ecografo',
        'rayos_x',
        'oxigeno_central',
        'electrobisturi',
        'patologia',
        'otros_equipos',
        'observaciones',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'es_institucional' => 'boolean',
            'es_convenio' => 'boolean',
            'torre_laparoscopia' => 'boolean',
            'bomba_infusion' => 'boolean',
            'maquina_aspiracion' => 'boolean',
            'intensificador_imagenes' => 'boolean',
            'maquina_anestesiologia' => 'boolean',
            'monitor_multiparametrico' => 'boolean',
            'ecografo' => 'boolean',
            'rayos_x' => 'boolean',
            'oxigeno_central' => 'boolean',
            'electrobisturi' => 'boolean',
        ];
    }

    public function proforma(): BelongsTo
    {
        return $this->belongsTo(Proforma::class, 'proforma_id');
    }

    public function cirujano(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cirujano_id');
    }

    public function primerAyudante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primer_ayudante_id');
    }

    public function segundoAyudante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'segundo_ayudante_id');
    }

    public function anestesiologo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anestesiologo_id');
    }

    public function instrumentador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instrumentador_id');
    }

    public function circulante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'circulante_id');
    }

    /**
     * Receta de medicamentos e insumos de quirófano vinculada a la cirugía.
     */
    public function receta(): HasOne
    {
        return $this->hasOne(Receta::class, 'cirugia_id');
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'cirugia_id');
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
