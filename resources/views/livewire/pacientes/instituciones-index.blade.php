<div class="space-y-6">
    <!-- Header de la Sección (Colores Sólidos) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white shadow-xs">
                    <i class="fas fa-hospital-alt text-base"></i>
                </span>
                <span>Instituciones y Seguros</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Aseguradoras, entidades corporativas y convenios institucionales para facturación y atención de pacientes.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button 
                wire:click="abrirModal" 
                type="button" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-teal-600/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer"
            >
                <i class="fas fa-plus"></i>
                <span>Nueva Institución</span>
            </button>
        </div>
    </div>

    <!-- Tarjetas de Métricas (Colores Sólidos) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Total Instituciones</span>
                <span class="text-2xl font-bold text-slate-900 dark:text-white mt-1 block">{{ $totalInstituciones }}</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                <i class="fas fa-building text-lg"></i>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Instituciones Activas</span>
                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $totalActivas }}</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Pacientes Vinculados</span>
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 block">{{ $totalPacientesVinculados }}</span>
            </div>
            <div class="h-10 w-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="fas fa-user-injured text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selector de Paginación -->
                    <div class="flex items-center gap-2">
                        <label for="perPageInst" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageInst" 
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

                    <!-- Filtro por Estado -->
                    <div class="flex items-center gap-2">
                        <label for="filtroEstadoInst" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEstadoInst" 
                            wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="Activo">Solo Activos</option>
                            <option value="Inactivo">Solo Inactivos</option>
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
                        placeholder="Buscar por institución o descripción..." 
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

        <!-- Tabla de Instituciones -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Institución / Convenio</th>
                        <th class="py-3 px-4">Descripción / Alcance</th>
                        <th class="py-3 px-4 text-center">Pacientes Vinculados</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4">Fecha Registro</th>
                        <th class="py-3 px-4 text-center w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($instituciones as $inst)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                {{ $inst->id }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-teal-100 dark:bg-teal-950 text-teal-700 dark:text-teal-300 text-xs">
                                        <i class="fas fa-hospital-user text-[11px]"></i>
                                    </span>
                                    <span>{{ $inst->nombre }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                {{ $inst->descripcion ?: 'Sin descripción registrada' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                    <i class="fas fa-users text-[10px]"></i>
                                    {{ $inst->pacientes_count }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button 
                                    wire:click="toggleEstado({{ $inst->id }})" 
                                    wire:loading.attr="disabled"
                                    type="button" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ ($inst->estado === 'Activo' || $inst->estado === null) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-200' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 hover:bg-slate-300' }}"
                                    title="Clic para cambiar estado"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($inst->estado === 'Activo' || $inst->estado === null) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span>{{ $inst->estado ?: 'Activo' }}</span>
                                </button>
                            </td>
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $inst->created_at ? $inst->created_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Editar -->
                                    <button 
                                        wire:click="abrirModal({{ $inst->id }})" 
                                        type="button" 
                                        class="p-1.5 text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Editar Institución"
                                    >
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>

                                    <!-- Eliminar con SweetAlert2 Seguro -->
                                    <button 
                                        type="button" 
                                        @click="$dispatch('swal:confirm', {
                                            title: '¿Eliminar Institución?',
                                            text: 'Se eliminará la institución {{ addslashes($inst->nombre) }}. Esta acción es reversible.',
                                            icon: 'warning',
                                            confirmButtonText: 'Sí, eliminar',
                                            componentId: '{{ $this->getId() }}',
                                            method: 'eliminarInstitucion',
                                            params: [{{ $inst->id }}],
                                            event: 'eliminarInstitucion'
                                        })" 
                                        class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Eliminar Institución"
                                    >
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-hospital-alt text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                    <p class="font-medium text-xs">No se encontraron instituciones o convenios registrados.</p>
                                    @if ($search !== '' || $filtroEstado !== '')
                                        <p class="text-[11px] text-slate-500 mt-1">Pruebe ajustando o limpiando los filtros de búsqueda.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($instituciones->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $instituciones->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Crear / Editar (Cabecera Simple, Sobria y Compacta - Regla 7) -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModal"></div>

                <!-- Modal Dialog -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <!-- Modal Header (Regla 7: Sobrio y Compacto) -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $institucionId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-title">
                                {{ $institucionId ? 'Editar Institución' : 'Nueva Institución' }}
                            </h3>
                        </div>
                        <button 
                            wire:click="cerrarModal" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body / Form -->
                    <form wire:submit="guardar">
                        <div class="p-6 space-y-4">
                            <!-- Nombre -->
                            <div>
                                <label for="inst_nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Nombre de la Institución / Seguro <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="inst_nombre" 
                                    wire:model="nombre" 
                                    placeholder="Ej. BISA Seguros, Caja Nacional de Salud, Minera San Cristóbal..." 
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('nombre') border-rose-500 @enderror"
                                />
                                @error('nombre') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label for="inst_descripcion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Descripción / Alcance del Convenio
                                </label>
                                <textarea 
                                    id="inst_descripcion" 
                                    wire:model="descripcion" 
                                    rows="3" 
                                    placeholder="Detalles de cobertura, contacto institucional, requisitos o acuerdos..." 
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs"
                                ></textarea>
                                @error('descripcion') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="inst_estado" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Estado Operativo <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="inst_estado" 
                                    wire:model="estado" 
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs"
                                >
                                    <option value="Activo">Activo (Habilitado para asignar a pacientes)</option>
                                    <option value="Inactivo">Inactivo (Suspendido temporalmente)</option>
                                </select>
                                @error('estado') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Modal Footer (Regla 6: Bloqueo de Botones) -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                type="button" 
                                wire:click="cerrarModal" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>

                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-600/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardar">
                                    <i class="fas fa-save me-1"></i> {{ $institucionId ? 'Actualizar Institución' : 'Guardar Institución' }}
                                </span>
                                <span wire:loading wire:target="guardar" class="inline-flex items-center gap-1.5">
                                    <i class="fas fa-spinner fa-spin"></i> Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
