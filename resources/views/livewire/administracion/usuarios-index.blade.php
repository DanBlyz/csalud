<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white shadow-md shadow-blue-500/20">
                    <i class="fas fa-users-cog text-base"></i>
                </span>
                Personal y Usuarios
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Control de colaboradores, credenciales, especialidades médicas y permisos granulares.
            </p>
        </div>

        <button 
            wire:click="abrirModalCrear" 
            wire:loading.attr="disabled"
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
            <i class="fas fa-user-plus"></i>
            <span>Nuevo Usuario</span>
        </button>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Barra Superior de Control y Filtros -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 space-y-3.5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Selector de Paginación -->
                <div class="flex items-center gap-2">
                    <label for="perPageUsuarios" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                    <select 
                        id="perPageUsuarios" 
                        wire:model.live="perPage" 
                        class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                </div>

                <!-- Buscador en tiempo real con debounce -->
                <div class="relative w-full lg:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por nombre, cédula o email..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button 
                            wire:click="$set('search', '')" 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filtros Específicos Modulares (Rol, Sucursal, Especialidad, Activo) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-800/60">
                <!-- Filtro Rol -->
                <div>
                    <select 
                        wire:model.live="filtroRol" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                    >
                        <option value="">Todos los Roles</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Sucursal -->
                <div>
                    <select 
                        wire:model.live="filtroSucursal" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                    >
                        <option value="">Todas las Sedes</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Especialidad -->
                <div>
                    <select 
                        wire:model.live="filtroEspecialidad" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                    >
                        <option value="">Todas las Especialidades</option>
                        @foreach ($especialidades as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Estado -->
                <div>
                    <select 
                        wire:model.live="filtroActivo" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-1.5 px-2.5 shadow-2xs"
                    >
                        <option value="">Todos los Estados</option>
                        <option value="1">Solo Activos</option>
                        <option value="0">Solo Suspendidos</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Colaborador</th>
                        <th class="py-3 px-4">Identificación</th>
                        <th class="py-3 px-4">Rol & Sede</th>
                        <th class="py-3 px-4">Especialidad</th>
                        <th class="py-3 px-4 text-center">Permisos</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($usuarios as $user)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Colaborador / Avatar & Nombre -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-blue-600 to-teal-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                        {{ strtoupper(substr($user->nombres ?? $user->name, 0, 1) . substr($user->apellido_paterno ?? '', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate">
                                            {{ $user->nombre_completo ?? $user->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 truncate flex items-center gap-1">
                                            <i class="far fa-envelope text-[10px]"></i> {{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Cédula y Contacto -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-xs text-slate-800 dark:text-slate-200">
                                    {{ $user->cedula ?: 'Sin cédula' }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $user->celular ? 'Cel: ' . $user->celular : 'Sin celular' }}
                                </div>
                            </td>

                            <!-- Rol y Sede -->
                            <td class="py-3.5 px-4">
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $user->rol?->nombre === 'Admin' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' }}">
                                        {{ $user->rol?->nombre ?? 'Sin Rol' }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                                    <i class="fas fa-hospital text-[10px] text-slate-400"></i>
                                    <span>{{ $user->sucursal?->nombre ?? 'Sin sede' }}</span>
                                </div>
                            </td>

                            <!-- Especialidad Médica -->
                            <td class="py-3.5 px-4">
                                @if ($user->especialidad)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                                        <i class="fas fa-stethoscope text-[9px] me-1"></i> {{ $user->especialidad->nombre }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-[11px] italic">No aplica</span>
                                @endif
                            </td>

                            <!-- Permisos Asignados -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($user->rol?->nombre === 'Admin')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                                        <i class="fas fa-infinity text-[8px] me-1"></i> Acceso Total
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $user->permisos->count() }} permisos
                                    </span>
                                @endif
                            </td>

                            <!-- Estado (Toggle) -->
                            <td class="py-3.5 px-4 text-center">
                                <button 
                                    wire:click="toggleActivo({{ $user->id }})" 
                                    wire:loading.attr="disabled"
                                    type="button" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $user->activo ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}"
                                    title="Click para cambiar estado"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->activo ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $user->activo ? 'Activo' : 'Suspendido' }}
                                </button>
                            </td>

                            <!-- Acciones Rápidas -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Editar -->
                                    <button 
                                        wire:click="abrirModalEditar({{ $user->id }})" 
                                        type="button" 
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Editar Usuario"
                                    >
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>

                                    <!-- Reset Rápido de Contraseña (fas fa-key) -->
                                    <button 
                                        wire:click="abrirModalPassword({{ $user->id }})" 
                                        type="button" 
                                        class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Restablecer Contraseña"
                                    >
                                        <i class="fas fa-key text-xs"></i>
                                    </button>

                                    <!-- Permisos Granulares (fas fa-user-shield) -->
                                    <button 
                                        wire:click="abrirModalPermisos({{ $user->id }})" 
                                        type="button" 
                                        class="p-1.5 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Gestionar Permisos"
                                    >
                                        <i class="fas fa-user-shield text-xs"></i>
                                    </button>

                                    <!-- Eliminar (con swal:confirm) -->
                                    @if ($user->id !== auth()->id())
                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Dar de baja a \'{{ addslashes($user->nombre_completo ?? $user->name) }}\'?',
                                                text: 'El usuario no podrá acceder al sistema y conservará su historial clínico.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, dar de baja',
                                                cancelButtonText: 'Cancelar',
                                                event: 'eliminarUsuario',
                                                params: {{ $user->id }}
                                            })"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Eliminar Usuario"
                                        >
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron colaboradores</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        No hay usuarios que coincidan con los filtros o el texto ingresado.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($usuarios->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: Formulario Crear / Editar Usuario -->
    @if ($modalUsuarioOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-usuario-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-teal-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white text-sm">
                                <i class="fas {{ $usuarioId ? 'fa-user-edit' : 'fa-user-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-usuario-title">
                                {{ $usuarioId ? 'Editar Información del Colaborador' : 'Registrar Nuevo Colaborador' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModalUsuario" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarUsuario" class="p-6 space-y-4">
                        <!-- Nombres y Apellidos (3 columnas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="nombres" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Nombres <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="nombres" wire:model="nombres" placeholder="Ej. Juan Carlos" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('nombres') border-rose-500 @enderror">
                                @error('nombres') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="apellido_paterno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Ap. Paterno <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="apellido_paterno" wire:model="apellido_paterno" placeholder="Ej. Pérez" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('apellido_paterno') border-rose-500 @enderror">
                                @error('apellido_paterno') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="apellido_materno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Ap. Materno
                                </label>
                                <input type="text" id="apellido_materno" wire:model="apellido_materno" placeholder="Ej. Gómez" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs">
                            </div>
                        </div>

                        <!-- Cédula, Email, Celular (3 columnas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="cedula" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Cédula / DNI <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="cedula" wire:model="cedula" placeholder="Ej. 1234567" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('cedula') border-rose-500 @enderror">
                                @error('cedula') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Correo de Acceso <span class="text-rose-500">*</span>
                                </label>
                                <input type="email" id="email" wire:model="email" placeholder="colaborador@csalud.com" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('email') border-rose-500 @enderror">
                                @error('email') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="celular" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Celular
                                </label>
                                <input type="text" id="celular" wire:model="celular" placeholder="Ej. 77001122" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs">
                            </div>
                        </div>

                        <!-- Sucursal, Rol y Especialidad (3 columnas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="sucursal_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Sucursal / Sede <span class="text-rose-500">*</span>
                                </label>
                                <select id="sucursal_id" wire:model="sucursal_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('sucursal_id') border-rose-500 @enderror">
                                    <option value="">Seleccione una sede...</option>
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('sucursal_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="rol_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Rol Asignado <span class="text-rose-500">*</span>
                                </label>
                                <select id="rol_id" wire:model.live="rol_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('rol_id') border-rose-500 @enderror">
                                    <option value="">Seleccione un rol...</option>
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->id }}">{{ $r->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('rol_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="especialidad_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Especialidad Médica
                                </label>
                                <select id="especialidad_id" wire:model="especialidad_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs">
                                    <option value="">Sin especialidad</option>
                                    @foreach ($especialidades as $e)
                                        <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Dirección de Domicilio
                            </label>
                            <input type="text" id="direccion" wire:model="direccion" placeholder="Calle, Barrio o Zona..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs">
                        </div>

                        <!-- Contraseñas (Obligatorias en creación, opcionales en edición) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800">
                            <div>
                                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    {{ $usuarioId ? 'Cambiar Contraseña (Opcional)' : 'Contraseña de Acceso *' }}
                                </label>
                                <input type="password" id="password" wire:model="password" placeholder="Mínimo 6 caracteres" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs @error('password') border-rose-500 @enderror">
                                @error('password') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Confirmar Contraseña
                                </label>
                                <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Repita la contraseña" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2 shadow-2xs">
                            </div>
                        </div>

                        <!-- Estado Activo Toggle -->
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Acceso al Sistema Habilitado</span>
                                <span class="text-[11px] text-slate-400">Si está inactivo, el usuario no podrá iniciar sesión en la plataforma.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="activo" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-blue-500 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Botones Modal -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalUsuario" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarUsuario"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarUsuario">
                                    <i class="fas fa-save me-1"></i> Guardar Usuario
                                </span>
                                <span wire:loading wire:target="guardarUsuario" class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: Reset Rápido de Contraseña (fas fa-key) -->
    @if ($modalPasswordOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-password-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white text-sm">
                                <i class="fas fa-key"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-password-title">
                                    Restablecer Contraseña
                                </h3>
                                <p class="text-[11px] text-slate-400">Usuario: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $resetUsuarioNombre }}</span></p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalPassword" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarPassword" class="p-6 space-y-4">
                        <div>
                            <label for="nuevaPassword" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nueva Contraseña <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="nuevaPassword" 
                                wire:model="nuevaPassword" 
                                placeholder="Mínimo 6 caracteres..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 p-2.5 shadow-2xs @error('nuevaPassword') border-rose-500 @enderror"
                            />
                            @error('nuevaPassword') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nuevaPassword_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Confirmar Nueva Contraseña <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="nuevaPassword_confirmation" 
                                wire:model="nuevaPassword_confirmation" 
                                placeholder="Repita la nueva contraseña..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 p-2.5 shadow-2xs"
                            />
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalPassword" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarPassword"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-md shadow-amber-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarPassword">
                                    <i class="fas fa-check me-1"></i> Actualizar Contraseña
                                </span>
                                <span wire:loading wire:target="guardarPassword" class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-spinner fa-spin"></i> Actualizando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: Asignación de Permisos Granulares (fas fa-user-shield) -->
    @if ($modalPermisosOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-permisos-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-600 text-white text-sm">
                                <i class="fas fa-user-shield"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-permisos-title">
                                    Permisos Asignados
                                </h3>
                                <p class="text-[11px] text-slate-400">Usuario: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $permisosUsuarioNombre }}</span></p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalPermisos" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarPermisos" class="p-6">
                        <!-- Botones de selección rápida -->
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200 dark:border-slate-800 text-xs">
                            <span class="text-slate-500 font-medium">Marque los permisos autorizados:</span>
                            <div class="flex items-center gap-2">
                                <button wire:click="seleccionarTodosPermisos" type="button" class="text-blue-600 dark:text-blue-400 hover:underline text-[11px] font-semibold">
                                    Todos
                                </button>
                                <span class="text-slate-300 dark:text-slate-700">|</span>
                                <button wire:click="deseleccionarTodosPermisos" type="button" class="text-slate-500 dark:text-slate-400 hover:underline text-[11px] font-semibold">
                                    Ninguno
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Permisos con Checkbox -->
                        <div class="space-y-2.5 max-h-72 overflow-y-auto pe-1 custom-scrollbar">
                            @foreach ($todosPermisos as $permiso)
                                <label class="flex items-start gap-3 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition-colors">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $permiso->id }}" 
                                        wire:model="permisosSeleccionados"
                                        class="mt-0.5 rounded-sm border-slate-300 dark:border-slate-700 text-purple-600 focus:ring-purple-500"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs text-slate-800 dark:text-slate-200">
                                                {{ $permiso->nombre }}
                                            </span>
                                            <span class="font-mono text-[10px] text-purple-600 dark:text-purple-400 font-semibold bg-purple-50 dark:bg-purple-950/60 px-1.5 py-0.2 rounded">
                                                ID: {{ $permiso->id }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $permiso->descripcion }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalPermisos" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarPermisos"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarPermisos">
                                    <i class="fas fa-check me-1"></i> Guardar Permisos
                                </span>
                                <span wire:loading wire:target="guardarPermisos" class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
