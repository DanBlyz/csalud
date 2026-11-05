<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Paciente extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'pacientes';

    protected $fillable = [
        'institucion_id',
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

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
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
     * Edad calculada del paciente en años (a la fecha actual).
     */
    protected function edad(): Attribute
    {
        return Attribute::make(
            get: fn (): ?int => $this->edadEnFecha(now())
        );
    }

    /**
     * Número de historia clínica: fecha de nacimiento (dmY) seguido de las iniciales del paciente en mayúsculas.
     * Ejemplo: 23102000LDLM para fecha 23-10-2000 y Luis David Loza Mamani.
     */
    protected function numeroHistoriaClinica(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $fechaStr = $this->fecha_nacimiento ? $this->fecha_nacimiento->format('dmY') : '';
                $nombresPartes = array_filter(explode(' ', trim((string) $this->nombres)));
                $iniciales = '';
                foreach ($nombresPartes as $part) {
                    $iniciales .= mb_substr($part, 0, 1);
                }
                if ($this->apellido_paterno) {
                    $iniciales .= mb_substr(trim($this->apellido_paterno), 0, 1);
                }
                if ($this->apellido_materno) {
                    $iniciales .= mb_substr(trim($this->apellido_materno), 0, 1);
                }

                return $fechaStr.mb_strtoupper($iniciales);
            }
        );
    }

    /**
     * Edad del paciente en años calculada a una fecha de referencia específica.
     * Si no se provee fecha, calcula la edad respecto a la fecha actual.
     */
    public function edadEnFecha(CarbonInterface|string|null $fecha = null): ?int
    {
        if (! $this->fecha_nacimiento) {
            return null;
        }

        $fechaReferencia = $fecha ? Carbon::parse($fecha) : now();

        if ($fechaReferencia->lt($this->fecha_nacimiento)) {
            return 0;
        }

        return (int) $this->fecha_nacimiento->diffInYears($fechaReferencia);
    }
}
