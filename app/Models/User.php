<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'cedula',
        'email',
        'celular',
        'direccion',
        'activo',
        'sucursal_id',
        'rol_id',
        'especialidad_id',
        'password',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Sucursal a la que pertenece el usuario.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Rol asignado al usuario.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Especialidad médica (si aplica).
     */
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    /**
     * Permisos específicos asignados al usuario.
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_usuario', 'user_id', 'permiso_id')
            ->withTimestamps();
    }

    /**
     * Determina si el usuario tiene el rol de Administrador (Superusuario).
     */
    public function esAdmin(): bool
    {
        return $this->rol?->nombre === 'Admin';
    }

    /**
     * Comprueba si el usuario tiene un permiso específico por ID numérico o slug/nombre.
     */
    public function tienePermiso(int|string $permiso): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        if (is_numeric($permiso)) {
            return $this->permisos()->where('permisos.id', (int) $permiso)->exists();
        }

        if ($this->permisos()->where('permisos.nombre', $permiso)->exists()) {
            return true;
        }

        // Retrocompatibilidad con permisos generales/módulos base (IDs 1 al 10)
        $legacyPrefixMap = [
            'usuarios.' => [1, 'gestion-usuarios'],
            'sucursales.' => [2, 'gestion-sucursales'],
            'roles.' => [3, 'gestion-roles'],
            'catalogos.' => [4, 'gestion-catalogos'],
            'pacientes.' => [5, 'gestion-pacientes'],
            'instituciones.' => [5, 'gestion-pacientes'],
            'farmacia.' => [9, 'despachar-farmacia'],
            'caja.' => [10, 'cobro-caja'],
        ];

        foreach ($legacyPrefixMap as $prefix => $legacyIdentifiers) {
            if (str_starts_with($permiso, $prefix)) {
                return $this->permisos()->where(function ($q) use ($legacyIdentifiers) {
                    $q->whereIn('permisos.id', array_filter($legacyIdentifiers, 'is_int'))
                        ->orWhereIn('permisos.nombre', array_filter($legacyIdentifiers, 'is_string'));
                })->exists();
            }
        }

        return false;
    }

    /**
     * Comprueba si el usuario tiene al menos uno de los permisos dados.
     *
     * @param  array<int|string>  $permisos
     */
    public function tieneAlgunPermiso(array $permisos): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        foreach ($permisos as $permiso) {
            if ($this->tienePermiso($permiso)) {
                return true;
            }
        }

        return false;
    }

    public function proformasAtendidas(): BelongsToMany
    {
        return $this->belongsToMany(Proforma::class, 'proforma_medicos', 'medico_id', 'proforma_id')
            ->withTimestamps();
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'user_id');
    }

    public function pagosRegistrados(): HasMany
    {
        return $this->hasMany(Pago::class, 'user_id');
    }

    public function pagosHonorariosRecibidos(): HasMany
    {
        return $this->hasMany(ProformaPagoMedico::class, 'medico_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'user_id');
    }

    /**
     * Nombre completo formateado del usuario.
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}") ?: ($this->name ?? '')
        );
    }
}
