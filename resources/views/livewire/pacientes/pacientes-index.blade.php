<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-user-injured text-base"></i>
                </span>
                Directorio y Registro de Pacientes
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Ficha clínica de pacientes, antecedentes, contactos de emergencia y apertura de proformas.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button 
                wire:click="abrirModalCrear" 
                wire:loading.attr="disabled"
                type="button" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Paciente</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-lg">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Pacientes</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalPacientes) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-lg">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Con Proforma En Curso</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($pacientesConProformaActiva) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Nuevos Este Mes</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($pacientesNuevosMes) }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPagePac" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPagePac" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro Género -->
                    <div class="flex items-center gap-2">
                        <label for="filtroGen" class="text-xs font-medium text-slate-600 dark:text-slate-400">Género:</label>
                        <select 
                            id="filtroGen" 
                            wire:model.live="filtroGenero" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                </div>

                <!-- Buscador Debounce -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por nombre, CI, teléfono..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de Pacientes -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Paciente</th>
                        <th class="py-3 px-4">Documento (CI)</th>
                        <th class="py-3 px-4">Contacto</th>
                        <th class="py-3 px-4">Antecedentes / Alergias</th>
                        <th class="py-3 px-4 text-center">Proformas</th>
                        <th class="py-3 px-4 text-center w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($pacientes as $paciente)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                {{ $paciente->id }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $paciente->nombre_completo }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                    <span>
                                        <i class="fas fa-birthday-cake text-[10px] me-1 text-slate-400"></i>
                                        {{ $paciente->edad !== null ? "{$paciente->edad} años" : 'Edad no reg.' }}
                                    </span>
                                    <span>•</span>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium {{ $paciente->genero === 'Femenino' ? 'bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300' : ($paciente->genero === 'Masculino' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $paciente->genero ?? 'N/E' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800 dark:text-slate-200">
                                {{ $paciente->cedula ?: 'Sin documento' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                    <i class="fas fa-phone-alt text-[10px] text-teal-500"></i>
                                    <span>{{ $paciente->celular ?: 'Sin celular' }}</span>
                                </div>
                                @if ($paciente->contacto_emergencia_nombre)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5" title="Emergencia: {{ $paciente->contacto_emergencia_nombre }} ({{ $paciente->contacto_emergencia_telefono }})">
                                        <span class="font-medium text-amber-600 dark:text-amber-400">Emerg:</span> {{ $paciente->contacto_emergencia_nombre }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if ($paciente->antecedentes_alergias)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 max-w-xs truncate" title="{{ $paciente->antecedentes_alergias }}">
                                        <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                        <span class="truncate">{{ $paciente->antecedentes_alergias }}</span>
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Ninguna reportada</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($paciente->proformas_activas_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $paciente->proformas_activas_count }} activa(s)
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px] font-medium">Sin activas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Abrir Proforma -->
                                    <a 
                                        href="{{ route('proformas.crear', ['paciente_id' => $paciente->id]) }}" 
                                        class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Abrir Nueva Proforma"
                                    >
                                        <i class="fas fa-file-medical text-sm"></i>
                                    </a>

                                    <!-- Ver Historial de Proformas -->
                                    <button 
                                        wire:click="verHistorial({{ $paciente->id }})" 
                                        type="button" 
                                        class="p-1.5 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Historial de Atenciones / Proformas"
                                    >
                                        <i class="fas fa-history text-sm"></i>
                                    </button>

                                    <!-- Editar -->
                                    <button 
                                        wire:click="abrirModalEditar({{ $paciente->id }})" 
                                        type="button" 
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Editar Ficha de Paciente"
                                    >
                                        <i class="fas fa-user-edit text-sm"></i>
                                    </button>

                                    <!-- Eliminar -->
                                    <button 
                                        type="button" 
                                        @click="$dispatch('swal:confirm', {
                                            title: '¿Eliminar a \'{{ addslashes($paciente->nombre_completo) }}\'?',
                                            text: 'Se verificará que no posea atenciones clínicas en curso.',
                                            icon: 'warning',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar',
                                            event: 'eliminarPaciente',
                                            params: {{ $paciente->id }}
                                        })"
                                        class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Eliminar Paciente"
                                    >
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-user-injured"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron pacientes</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        No hay pacientes registrados que coincidan con el criterio de búsqueda.
                                    </p>
                                    <button 
                                        wire:click="abrirModalCrear" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="mt-4 px-3.5 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                    >
                                        <i class="fas fa-plus me-1"></i> Registrar Primer Paciente
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($pacientes->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $pacientes->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL REGISTRO / EDICIÓN DE PACIENTE -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-pac" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $pacienteId ? 'fa-user-edit' : 'fa-user-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-pac">
                                {{ $pacienteId ? 'Modificar Ficha de Paciente' : 'Registrar Nuevo Paciente' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardar" class="p-6 space-y-4">
                        <!-- Nombres y Apellidos -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="nombres" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Nombres <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="nombres" wire:model="nombres" placeholder="Ej. Roberto" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('nombres') border-rose-500 @enderror">
                                @error('nombres') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="apellido_paterno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Apellido Paterno <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="apellido_paterno" wire:model="apellido_paterno" placeholder="Ej. Morales" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('apellido_paterno') border-rose-500 @enderror">
                                @error('apellido_paterno') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="apellido_materno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Apellido Materno
                                </label>
                                <input type="text" id="apellido_materno" wire:model="apellido_materno" placeholder="Ej. Vargas" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>
                        </div>

                        <!-- Cédula, Fecha Nacimiento, Género -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="cedula" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Documento / CI <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="cedula" wire:model="cedula" placeholder="Ej. 6543210-LP" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('cedula') border-rose-500 @enderror">
                                @error('cedula') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="fecha_nacimiento" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha de Nacimiento
                                </label>
                                <input type="date" id="fecha_nacimiento" wire:model="fecha_nacimiento" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('fecha_nacimiento') border-rose-500 @enderror">
                                @error('fecha_nacimiento') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="genero" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Género <span class="text-rose-500">*</span>
                                </label>
                                <select id="genero" wire:model="genero" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                        </div>

                        <!-- Celular y Dirección -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="celular" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Celular / Teléfono
                                </label>
                                <input type="text" id="celular" wire:model="celular" placeholder="Ej. 77012345" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>

                            <div>
                                <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Dirección Domiciliaria
                                </label>
                                <input type="text" id="direccion" wire:model="direccion" placeholder="Calle, Nro., Zona..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>
                        </div>

                        <!-- Contacto de Emergencia -->
                        <div class="p-3.5 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/40">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 block mb-2">
                                <i class="fas fa-phone-volume me-1"></i> Contacto de Emergencia (Familiar o Tutor)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="contacto_emergencia_nombre" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                        Nombre del Contacto
                                    </label>
                                    <input type="text" id="contacto_emergencia_nombre" wire:model="contacto_emergencia_nombre" placeholder="Ej. María Morales (Hermana)" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label for="contacto_emergencia_telefono" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                        Teléfono de Emergencia
                                    </label>
                                    <input type="text" id="contacto_emergencia_telefono" wire:model="contacto_emergencia_telefono" placeholder="Ej. 70098765" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- Antecedentes de Alergias -->
                        <div>
                            <label for="antecedentes_alergias" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                <i class="fas fa-allergies text-rose-500 me-1"></i> Antecedentes Médicos / Alergias Conocidas
                            </label>
                            <textarea id="antecedentes_alergias" wire:model="antecedentes_alergias" rows="2" placeholder="Ej. Alérgico a la Penicilina, AINEs, Asma bronquial, Diabetes tipo 2..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs"></textarea>
                            <p class="text-[11px] text-slate-400 mt-0.5">Esta información se mostrará de forma destacada en la cabecera de todas sus proformas clínicas.</p>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModal" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardar">
                                    <i class="fas fa-save me-1"></i> Guardar Paciente
                                </span>
                                <span wire:loading wire:target="guardar">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL HISTORIAL DE PROFORMAS DEL PACIENTE -->
    @if ($modalHistorialOpen && $pacienteHistorial)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-hist" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm">
                                <i class="fas fa-history"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-hist">
                                    Historial Clínico de {{ $pacienteHistorial->nombre_completo }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">CI: {{ $pacienteHistorial->cedula ?: 'Sin CI' }} • Total atenciones registradas: {{ $pacienteHistorial->proformas->count() }}</p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalHistorial" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <div class="p-6">
                        @if ($pacienteHistorial->proformas->isEmpty())
                            <div class="py-8 text-center text-slate-400">
                                <i class="fas fa-notes-medical text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-medium">Este paciente aún no tiene proformas registradas.</p>
                                <a 
                                    href="{{ route('proformas.crear', ['paciente_id' => $pacienteHistorial->id]) }}" 
                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700"
                                >
                                    <i class="fas fa-plus"></i> Abrir Primera Proforma
                                </a>
                            </div>
                        @else
                            <div class="space-y-3 max-h-[60vh] overflow-y-auto pe-1">
                                @foreach ($pacienteHistorial->proformas as $prof)
                                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400">
                                                    Proforma #{{ str_pad($prof->id, 5, '0', STR_PAD_LEFT) }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $prof->tipo_atencion === 'Internacion' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                                    {{ $prof->tipo_atencion }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $prof->estado === 'En Curso' ? 'bg-amber-100 text-amber-800' : ($prof->estado === 'Pagada' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">
                                                    {{ $prof->estado }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-700 dark:text-slate-300">
                                                <span class="font-semibold">Motivo:</span> {{ $prof->motivo_consulta ?: 'Sin motivo registrado' }}
                                            </p>
                                            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
                                                <span><i class="far fa-calendar-alt me-1"></i> {{ $prof->fecha_ingreso?->format('d/m/Y H:i') }}</span>
                                                <span>•</span>
                                                <span><i class="fas fa-user-md me-1 text-teal-500"></i> {{ $prof->medicos->isNotEmpty() ? $prof->medicos->pluck('nombre_completo')->map(fn($n) => 'Dr(a). '.$n)->join(', ') : 'Sin médico asignado' }}</span>
                                                @if ($prof->pieza)
                                                    <span>•</span>
                                                    <span><i class="fas fa-bed me-1 text-purple-500"></i> {{ $prof->pieza }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-200 dark:border-slate-700">
                                            <div class="text-right">
                                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Costo Total</span>
                                                <span class="text-sm font-bold font-mono text-slate-900 dark:text-white">
                                                    Bs. {{ number_format($prof->costo_total, 2) }}
                                                </span>
                                            </div>
                                            <a 
                                                href="{{ route('proformas.show', $prof->id) }}" 
                                                class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                <span>Gestionar</span>
                                                <i class="fas fa-arrow-right text-[10px]"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                        <button wire:click="cerrarModalHistorial" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
