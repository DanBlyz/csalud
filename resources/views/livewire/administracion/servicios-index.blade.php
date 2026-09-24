<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                    <i class="fas fa-hand-holding-medical text-base"></i>
                </span>
                Servicios y Categorías Médicas
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catálogo de prestaciones, consultas, procedimientos y sus agrupaciones operativas.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if ($tab === 'servicios')
                <button 
                    wire:click="abrirModalServicioCrear" 
                    wire:loading.attr="disabled"
                    type="button" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Servicio</span>
                </button>
            @else
                <button 
                    wire:click="abrirModalCategoriaCrear" 
                    wire:loading.attr="disabled"
                    type="button" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-purple-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <i class="fas fa-plus"></i>
                    <span>Nueva Categoría</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Navegación por Tabs -->
    <div class="flex items-center space-x-1 border-b border-slate-200 dark:border-slate-800 mb-6">
        <button 
            wire:click="cambiarTab('servicios')" 
            type="button" 
            class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 cursor-pointer {{ $tab === 'servicios' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
        >
            <i class="fas fa-briefcase-medical text-xs"></i>
            <span>Catálogo de Servicios Médicos</span>
        </button>
        <button 
            wire:click="cambiarTab('categorias')" 
            type="button" 
            class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 cursor-pointer {{ $tab === 'categorias' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
        >
            <i class="fas fa-layer-group text-xs"></i>
            <span>Categorías de Prestaciones</span>
        </button>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPageServ" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageServ" 
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

                    @if ($tab === 'servicios')
                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                        <!-- Filtro Categoría -->
                        <div class="flex items-center gap-2">
                            <label for="filtroCat" class="text-xs font-medium text-slate-600 dark:text-slate-400">Categoría:</label>
                            <select 
                                id="filtroCat" 
                                wire:model.live="filtroCategoria" 
                                class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 py-1.5 px-2.5 shadow-2xs"
                            >
                                <option value="">Todas</option>
                                @foreach ($todasCategorias as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                    <!-- Filtro Estado -->
                    <div class="flex items-center gap-2">
                        <label for="filtroEst" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEst" 
                            wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>

                <!-- Buscador -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="{{ $tab === 'servicios' ? 'Buscar servicio o precio...' : 'Buscar categoría...' }}" 
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

        <!-- Tabla Dependiente del Tab Activo -->
        <div class="overflow-x-auto">
            @if ($tab === 'servicios')
                <!-- TABLA SERVICIOS -->
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Servicio Médico</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4 text-right">Precio Sugerido</th>
                            <th class="py-3 px-4 text-center">En Proformas</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($items as $servicio)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                    {{ $servicio->id }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">
                                        {{ $servicio->nombre }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-sm">
                                        {{ $servicio->descripcion ?: 'Sin observaciones' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60">
                                        {{ $servicio->categoria->nombre ?? 'Sin Categoría' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-emerald-400">
                                    Bs. {{ number_format($servicio->precio_tentativo, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $servicio->proforma_servicios_count }} usos
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button 
                                        wire:click="toggleEstadoServicio({{ $servicio->id }})" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $servicio->estado ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}"
                                        title="Click para cambiar estado"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full {{ $servicio->estado ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $servicio->estado ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            wire:click="abrirModalServicioEditar({{ $servicio->id }})" 
                                            type="button" 
                                            class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                            title="Editar Servicio"
                                        >
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>

                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Eliminar servicio \'{{ addslashes($servicio->nombre) }}\'?',
                                                text: 'No debe poseer proformas clínicas asociadas.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'eliminarServicio',
                                                params: {{ $servicio->id }}
                                            })"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Eliminar Servicio"
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
                                            <i class="fas fa-briefcase-medical"></i>
                                        </div>
                                        <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron servicios</p>
                                        <p class="text-xs text-slate-400 max-w-sm mt-1">
                                            No hay registros coincidentes con los filtros aplicados.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <!-- TABLA CATEGORÍAS -->
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4">Descripción</th>
                            <th class="py-3 px-4 text-center">Servicios Registrados</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-center w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse ($items as $categoria)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 dark:text-slate-500 font-mono text-[11px]">
                                    {{ $categoria->id }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">
                                        {{ $categoria->nombre }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $categoria->descripcion ?: 'Sin descripción' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        <i class="fas fa-stethoscope text-[10px] me-1 text-indigo-500"></i> {{ $categoria->servicios_count }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <button 
                                        wire:click="toggleEstadoCategoria({{ $categoria->id }})" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $categoria->estado ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}"
                                        title="Click para cambiar estado"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full {{ $categoria->estado ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $categoria->estado ? 'Activa' : 'Inactiva' }}
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button 
                                            wire:click="abrirModalCategoriaEditar({{ $categoria->id }})" 
                                            type="button" 
                                            class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors"
                                            title="Editar Categoría"
                                        >
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>

                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Eliminar categoría \'{{ addslashes($categoria->nombre) }}\'?',
                                                text: 'No debe poseer servicios vinculados.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'eliminarCategoria',
                                                params: {{ $categoria->id }}
                                            })"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Eliminar Categoría"
                                        >
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron categorías</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="text-slate-500 dark:text-slate-400">
                Mostrando del <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $items->firstItem() ?? 0 }}</span> al 
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $items->lastItem() ?? 0 }}</span> de 
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $items->total() }}</span> registros
            </div>

            <div>
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL SERVICIO -->
    @if ($modalServicioOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-serv" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm">
                                <i class="fas {{ $servicioId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-serv">
                                {{ $servicioId ? 'Editar Servicio Médico' : 'Nuevo Servicio Médico' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModalServicio" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarServicio" class="p-6 space-y-4">
                        <!-- Categoría -->
                        <div>
                            <label for="categoria_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Categoría <span class="text-rose-500">*</span>
                            </label>
                            <select id="categoria_id" wire:model="categoria_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2.5 shadow-2xs @error('categoria_id') border-rose-500 @enderror">
                                <option value="">Seleccione categoría...</option>
                                @foreach ($categorias as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                            @error('categoria_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label for="nombreServicio" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nombre del Servicio / Procedimiento <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="nombreServicio" wire:model="nombreServicio" placeholder="Ej. Consulta Médica General, Sutura Menor" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2.5 shadow-2xs @error('nombreServicio') border-rose-500 @enderror">
                            @error('nombreServicio') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Precio Tentativo -->
                        <div>
                            <label for="precio_tentativo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Precio Base Sugerido (Bs.) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" id="precio_tentativo" wire:model="precio_tentativo" placeholder="0.00" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2.5 shadow-2xs @error('precio_tentativo') border-rose-500 @enderror">
                            @error('precio_tentativo') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcionServicio" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Descripción / Observaciones
                            </label>
                            <textarea id="descripcionServicio" wire:model="descripcionServicio" rows="3" placeholder="Detalles clínicos o insumos incluidos..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <!-- Estado -->
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Servicio Habilitado</span>
                                <span class="text-[11px] text-slate-400">Disponible para ser agregado a proformas clínicas.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="estadoServicio" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalServicio" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarServicio"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarServicio">
                                    <i class="fas fa-save me-1"></i> Guardar Servicio
                                </span>
                                <span wire:loading wire:target="guardarServicio">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL CATEGORÍA -->
    @if ($modalCategoriaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-cat" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-600 text-white text-sm">
                                <i class="fas {{ $categoriaId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-cat">
                                {{ $categoriaId ? 'Editar Categoría' : 'Nueva Categoría' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModalCategoria" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarCategoria" class="p-6 space-y-4">
                        <div>
                            <label for="nombreCategoria" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nombre de la Categoría <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="nombreCategoria" wire:model="nombreCategoria" placeholder="Ej. Consultas, Cirugías, Laboratorio" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500 p-2.5 shadow-2xs @error('nombreCategoria') border-rose-500 @enderror">
                            @error('nombreCategoria') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="descripcionCategoria" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Descripción
                            </label>
                            <textarea id="descripcionCategoria" wire:model="descripcionCategoria" rows="3" placeholder="Detalle de prestaciones agrupadas..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Categoría Activa</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="estadoCategoria" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-hidden peer-focus:ring-2 peer-focus:ring-purple-500 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalCategoria" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarCategoria"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarCategoria">
                                    <i class="fas fa-save me-1"></i> Guardar Categoría
                                </span>
                                <span wire:loading wire:target="guardarCategoria">
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
