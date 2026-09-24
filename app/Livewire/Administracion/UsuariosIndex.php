<?php

namespace App\Livewire\Administracion;

use App\Models\Especialidad;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UsuariosIndex extends Component
{
    use WithPagination;

    // Controles estándar de tabla
    public int $perPage = 10;

    public string $search = '';

    public string $filtroRol = '';

    public string $filtroSucursal = '';

    public string $filtroEspecialidad = '';

    public string $filtroActivo = '';

    // Modal de Creación / Edición
    public bool $modalUsuarioOpen = false;

    public ?int $usuarioId = null;

    public string $nombres = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public string $cedula = '';

    public string $email = '';

    public string $celular = '';

    public string $direccion = '';

    public ?int $sucursal_id = null;

    public ?int $rol_id = null;

    public ?int $especialidad_id = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $activo = true;

    // Modal Rápido de Reset de Contraseña (fas fa-key)
    public bool $modalPasswordOpen = false;

    public ?int $resetUsuarioId = null;

    public string $resetUsuarioNombre = '';

    public string $nuevaPassword = '';

    public string $nuevaPassword_confirmation = '';

    // Modal de Asignación de Permisos (fas fa-user-shield)
    public bool $modalPermisosOpen = false;

    public ?int $permisosUsuarioId = null;

    public string $permisosUsuarioNombre = '';

    public array $permisosSeleccionados = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroRol(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEspecialidad(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroActivo(): void
    {
        $this->resetPage();
    }

    /* -------------------------------------------------------------
     * GESTIÓN DE USUARIO (Crear / Editar)
     * ------------------------------------------------------------- */
    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->reset([
            'usuarioId',
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'cedula',
            'email',
            'celular',
            'direccion',
            'sucursal_id',
            'rol_id',
            'especialidad_id',
            'password',
            'password_confirmation',
        ]);
        $this->activo = true;

        // Asignar primera sucursal y rol por defecto si existen
        $this->sucursal_id = Sucursal::where('estado', true)->first()?->id;
        $this->rol_id = Rol::first()?->id;

        $this->modalUsuarioOpen = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetValidation();
        $user = User::findOrFail($id);

        $this->usuarioId = $user->id;
        $this->nombres = $user->nombres ?? '';
        $this->apellido_paterno = $user->apellido_paterno ?? '';
        $this->apellido_materno = $user->apellido_materno ?? '';
        $this->cedula = $user->cedula ?? '';
        $this->email = $user->email ?? '';
        $this->celular = $user->celular ?? '';
        $this->direccion = $user->direccion ?? '';
        $this->sucursal_id = $user->sucursal_id;
        $this->rol_id = $user->rol_id;
        $this->especialidad_id = $user->especialidad_id;
        $this->activo = (bool) $user->activo;
        $this->password = '';
        $this->password_confirmation = '';

        $this->modalUsuarioOpen = true;
    }

    public function cerrarModalUsuario(): void
    {
        $this->modalUsuarioOpen = false;
        $this->resetValidation();
    }

    public function guardarUsuario(): void
    {
        $rules = [
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'cedula' => ['required', 'string', 'max:30', Rule::unique('users', 'cedula')->ignore($this->usuarioId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->usuarioId)],
            'celular' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'sucursal_id' => 'required|exists:sucursales,id',
            'rol_id' => 'required|exists:roles,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'activo' => 'boolean',
        ];

        if (! $this->usuarioId) {
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:6|confirmed';
        }

        $validated = $this->validate($rules, [
            'nombres.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'cedula.required' => 'El número de cédula es obligatorio.',
            'cedula.unique' => 'Ya existe un usuario registrado con esta cédula.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'rol_id.required' => 'Debe asignar un rol al usuario.',
            'password.required' => 'La contraseña es requerida para nuevos usuarios.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $data = [
            'name' => trim("{$this->nombres} {$this->apellido_paterno}"),
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno ?: null,
            'cedula' => $this->cedula,
            'email' => $this->email,
            'celular' => $this->celular ?: null,
            'direccion' => $this->direccion ?: null,
            'sucursal_id' => $this->sucursal_id,
            'rol_id' => $this->rol_id,
            'especialidad_id' => $this->especialidad_id ?: null,
            'activo' => $this->activo,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->usuarioId) {
            $user = User::findOrFail($this->usuarioId);
            $user->update($data);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Usuario Actualizado',
                'text' => "Los datos de {$user->name} fueron actualizados correctamente.",
            ]);
        } else {
            $user = User::create($data);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Usuario Registrado',
                'text' => "El usuario {$user->name} fue registrado con éxito.",
            ]);
        }

        $this->cerrarModalUsuario();
    }

    public function toggleActivo(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Operación no permitida',
                'text' => 'No puedes desactivar tu propia cuenta activa.',
            ]);

            return;
        }

        $user->activo = ! $user->activo;
        $user->save();

        $estado = $user->activo ? 'activada' : 'suspendida';

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Estado Actualizado',
            'text' => "La cuenta de {$user->name} fue {$estado}.",
        ]);
    }

    /* -------------------------------------------------------------
     * RESET RÁPIDO DE CONTRASEÑA (Modal fas fa-key)
     * ------------------------------------------------------------- */
    public function abrirModalPassword(int $id): void
    {
        $this->resetValidation();
        $user = User::findOrFail($id);

        $this->resetUsuarioId = $user->id;
        $this->resetUsuarioNombre = $user->nombre_completo ?? $user->name;
        $this->nuevaPassword = '';
        $this->nuevaPassword_confirmation = '';

        $this->modalPasswordOpen = true;
    }

    public function cerrarModalPassword(): void
    {
        $this->modalPasswordOpen = false;
        $this->resetValidation();
    }

    public function guardarPassword(): void
    {
        $this->validate([
            'nuevaPassword' => 'required|string|min:6|confirmed',
        ], [
            'nuevaPassword.required' => 'La nueva contraseña es obligatoria.',
            'nuevaPassword.min' => 'La contraseña debe contener al menos 6 caracteres.',
            'nuevaPassword.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::findOrFail($this->resetUsuarioId);
        $user->password = Hash::make($this->nuevaPassword);
        $user->save();

        $this->cerrarModalPassword();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Contraseña Restablecida!',
            'text' => "La contraseña de {$this->resetUsuarioNombre} fue actualizada con éxito.",
        ]);
    }

    /* -------------------------------------------------------------
     * ASIGNACIÓN DE PERMISOS (Modal fas fa-user-shield)
     * ------------------------------------------------------------- */
    public function abrirModalPermisos(int $id): void
    {
        $user = User::with('permisos')->findOrFail($id);

        $this->permisosUsuarioId = $user->id;
        $this->permisosUsuarioNombre = $user->nombre_completo ?? $user->name;
        $this->permisosSeleccionados = $user->permisos->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        $this->modalPermisosOpen = true;
    }

    public function cerrarModalPermisos(): void
    {
        $this->modalPermisosOpen = false;
    }

    public function seleccionarTodosPermisos(): void
    {
        $this->permisosSeleccionados = Permiso::pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function deseleccionarTodosPermisos(): void
    {
        $this->permisosSeleccionados = [];
    }

    public function guardarPermisos(): void
    {
        $user = User::findOrFail($this->permisosUsuarioId);
        $user->permisos()->sync($this->permisosSeleccionados);

        $this->cerrarModalPermisos();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Permisos Sincronizados',
            'text' => "Los permisos para {$this->permisosUsuarioNombre} fueron guardados correctamente.",
        ]);
    }

    /* -------------------------------------------------------------
     * ELIMINACIÓN LÓGICA (SoftDeletes)
     * ------------------------------------------------------------- */
    #[On('eliminarUsuario')]
    public function eliminar(int $id): void
    {
        if ($id === Auth::id()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción Bloqueada',
                'text' => 'No puedes eliminar tu propia cuenta mientras estás en sesión.',
                'toast' => false,
            ]);

            return;
        }

        $user = User::findOrFail($id);
        $user->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Usuario Eliminado',
            'text' => "El usuario {$user->name} fue dado de baja del sistema.",
        ]);
    }

    public function render(): View
    {
        $query = User::query()
            ->with(['sucursal', 'rol', 'especialidad', 'permisos'])
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nombres', 'like', "%{$this->search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$this->search}%")
                        ->orWhere('apellido_materno', 'like', "%{$this->search}%")
                        ->orWhere('cedula', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filtroRol !== '', function ($q): void {
                $q->where('rol_id', $this->filtroRol);
            })
            ->when($this->filtroSucursal !== '', function ($q): void {
                $q->where('sucursal_id', $this->filtroSucursal);
            })
            ->when($this->filtroEspecialidad !== '', function ($q): void {
                $q->where('especialidad_id', $this->filtroEspecialidad);
            })
            ->when($this->filtroActivo !== '', function ($q): void {
                $q->where('activo', (bool) $this->filtroActivo);
            })
            ->latest();

        return view('livewire.administracion.usuarios-index', [
            'usuarios' => $query->paginate($this->perPage),
            'roles' => Rol::all(),
            'sucursales' => Sucursal::where('estado', true)->get(),
            'especialidades' => Especialidad::all(),
            'todosPermisos' => Permiso::all(),
        ]);
    }
}
