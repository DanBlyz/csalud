<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-stethoscope text-base"></i>
                </span>
                Especialidades Médicas
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catálogo de ramas de atención y especialidades del cuerpo médico.
            </p>
        </div>

        <button 
            wire:click="abrirModalCrear" 
            wire:loading.attr="disabled"
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        >
            <i class="fas fa-plus"></i>
            <span>Nueva Especialidad</span>
        </button>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <label for="perPageEsp" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                    <select 
                        id="perPageEsp" 
                        wire:model.live="perPage" 
                        class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 py-1.5 px-2.5 shadow-2xs"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                </div>

                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por especialidad..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs"
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
                        <th class="py-3 px-4">Especialidad</th>
                        <th class="py-3 px-4">Descripción / Alcance</th>
                        <th class="py-3 px-4 text-center">Médicos Asignados</th>
                        <th class="py-3 px-4 text-center w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($especialidades as $esp)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                {{ $esp->id }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-xs">
                                        <i class="fas fa-user-md"></i>
                                    </span>
                                    <span>{{ $esp->nombre }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                {{ $esp->descripcion ?: 'Sin descripción registrada' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    <i class="fas fa-user-md text-[10px] me-1 text-teal-500"></i> {{ $esp->users_count }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        wire:click="abrirModalEditar({{ $esp->id }})" 
                                        type="button" 
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Editar Especialidad"
                                    >
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>

                                    <button 
                                        type="button" 
                                        @click="$dispatch('swal:confirm', {
                                            title: '¿Eliminar especialidad \'{{ addslashes($esp->nombre) }}\'?',
                                            text: 'Se verificará que no tenga médicos asignados.',
                                            icon: 'warning',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar',
                                            event: 'eliminarEspecialidad',
                                            params: {{ $esp->id }}
                                        })"
                                        class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Eliminar Especialidad"
                                    >
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-stethoscope"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron especialidades</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        No hay especialidades registradas o coincidentes con la búsqueda.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($especialidades->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $especialidades->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-esp" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $especialidadId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-esp">
                                {{ $especialidadId ? 'Editar Especialidad' : 'Nueva Especialidad Médica' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModal" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardar" class="p-6 space-y-4">
                        <div>
                            <label for="nombreEsp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre de la Especialidad <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nombreEsp" 
                                wire:model="nombre" 
                                placeholder="Ej. Pediatría, Cardiología"
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-2.5 shadow-2xs @error('nombre') border-rose-500 @enderror"
                            />
                            @error('nombre')
                                <p class="text-[11px] text-rose-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcionEsp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Descripción
                            </label>
                            <textarea 
                                id="descripcionEsp" 
                                wire:model="descripcion" 
                                rows="3"
                                placeholder="Alcance y tipos de consulta de esta rama..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-2.5 shadow-2xs @error('descripcion') border-rose-500 @enderror"
                            ></textarea>
                            @error('descripcion')
                                <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
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
                                    <i class="fas fa-save me-1"></i> Guardar Especialidad
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
