<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                    <i class="fas fa-file-medical-alt text-base"></i>
                </span>
                Tipos de Solicitud y Exámenes
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catálogo maestro de exámenes complementarios, estudios de laboratorio y solicitudes médicas.
            </p>
        </div>

        @permiso('tipos-solicitudes.gestionar')
        <button 
            wire:click="abrirModalCrear" 
            wire:loading.attr="disabled"
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
            <i class="fas fa-plus"></i>
            <span>Nuevo Tipo de Solicitud</span>
        </button>
        @endpermiso
    </div>

    <!-- KPI Metric Cards -->
    {{-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Card 1: Total Tipos -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center gap-4 transition-colors">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xl shrink-0">
                <i class="fas fa-vial"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Tipos</p>
                <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $totalTipos }}</p>
            </div>
        </div>

        <!-- Card 2: Tipos con Solicitudes -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center gap-4 transition-colors">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xl shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">En Uso Clínico</p>
                <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $totalConSolicitudes }}</p>
            </div>
        </div>

        <!-- Card 3: Total Exámenes Emitidos -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center gap-4 transition-colors">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 text-xl shrink-0">
                <i class="fas fa-microscope"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estudios Emitidos</p>
                <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $totalSolicitudesRealizadas }}</p>
            </div>
        </div>
    </div> --}}

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPageTipos" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageTipos" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
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
                        placeholder="Buscar por nombre o descripción..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Tipo de Solicitud / Examen</th>
                        <th class="py-3 px-4">Descripción / Indicaciones</th>
                        <th class="py-3 px-4 text-center">Estudios en Proformas</th>
                        <th class="py-3 px-4 text-center w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($tiposSolicitudes as $tipo)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                {{ $tipo->id }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs">
                                        <i class="fas fa-file-medical"></i>
                                    </span>
                                    <span>{{ $tipo->nombre }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                {{ $tipo->descripcion ?: 'Sin descripción registrada' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($tipo->proforma_solicitudes_count > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60">
                                        <i class="fas fa-microscope text-[10px] me-1.5 text-indigo-500"></i>
                                        {{ $tipo->proforma_solicitudes_count }} {{ $tipo->proforma_solicitudes_count === 1 ? 'estudio' : 'estudios' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                        Sin registros
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @permiso('tipos-solicitudes.gestionar')
                                    <button 
                                        wire:click="abrirModalEditar({{ $tipo->id }})" 
                                        type="button" 
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Editar Tipo de Solicitud"
                                    >
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="$dispatch('swal:confirm', {
                                             title: '¿Eliminar \'{{ addslashes($tipo->nombre) }}\'?',
                                             text: 'Se validará que no tenga estudios vinculados en proformas.',
                                             icon: 'warning',
                                             confirmButtonText: 'Sí, eliminar',
                                             cancelButtonText: 'Cancelar',
                                             event: 'eliminarTipoSolicitud',
                                             componentId: '{{ $this->getId() }}',
                                             method: 'eliminar',
                                             params: [{{ $tipo->id }}]
                                         })"
                                        class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Eliminar Tipo de Solicitud"
                                    >
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-file-medical-alt"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron tipos de solicitud</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        No hay exámenes o tipos de solicitud registrados o coincidentes con la búsqueda.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($tiposSolicitudes->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $tiposSolicitudes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario (Crear / Editar) -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-tipos-solicitudes" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm">
                                <i class="fas {{ $tipoSolicitudId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-tipos-solicitudes">
                                {{ $tipoSolicitudId ? 'Editar Tipo de Solicitud' : 'Nuevo Tipo de Solicitud / Examen' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body / Form -->
                    <form wire:submit="guardar" class="p-6 space-y-4">
                        <div>
                            <label for="nombreTipo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre del Tipo de Solicitud / Examen <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nombreTipo" 
                                wire:model="nombre" 
                                placeholder="Ej. Hemograma Completo, Radiografía de Tórax"
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-2.5 shadow-2xs @error('nombre') border-rose-500 @enderror"
                            />
                            @error('nombre')
                                <p class="text-[11px] text-rose-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcionTipo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Descripción / Alcance Clínico
                            </label>
                            <textarea 
                                id="descripcionTipo" 
                                wire:model="descripcion" 
                                rows="3"
                                placeholder="Indicaciones, requisitos o descripción del examen..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-2.5 shadow-2xs @error('descripcion') border-rose-500 @enderror"
                            ></textarea>
                            @error('descripcion')
                                <p class="text-[11px] text-rose-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Modal Actions -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModal" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardar">
                                    <i class="fas fa-save me-1"></i> Guardar Tipo de Solicitud
                                </span>
                                <span wire:loading wire:target="guardar" class="inline-flex items-center gap-1.5">
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
