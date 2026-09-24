<?php

namespace App\Models;

use App\Traits\Auditable;
use Database\Factories\UserFactory;
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
     * Comprueba si el usuario tiene un permiso específico por ID numérico.
     */
    public function tienePermiso(int $permisoId): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        return $this->permisos()->where('permisos.id', $permisoId)->exists();
    }

    public function proformasComoMedico(): HasMany
    {
        return $this->hasMany(Proforma::class, 'medico_id');
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'user_id');
    }

    public function pagosRegistrados(): HasMany
    {
        return $this->hasMany(ProformaPago::class, 'user_id');
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
