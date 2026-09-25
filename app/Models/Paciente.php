<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'cedula',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'celular',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'antecedentes_alergias',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function proformas(): HasMany
    {
        return $this->hasMany(Proforma::class, 'paciente_id');
    }

    /**
     * Nombre completo formateado del paciente.
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}")
        );
    }

    /**
     * Edad calculada del paciente en años.
     */
    protected function edad(): Attribute
    {
        return Attribute::make(
            get: fn (): ?int => $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null
        );
    }
}
