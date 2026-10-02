<div class="space-y-6">
    <!-- Breadcrumb y Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-teal-600 transition">Inicio</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-400">Farmacia e Inventario</span>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800 dark:text-slate-200">Secciones y Áreas</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-sitemap text-lg"></i>
                </span>
                Secciones y Áreas Hospitalarias
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Control de almacenes, botiquines y áreas de consumo (Farmacia Central, Emergencias, Quirófano, Enfermería, etc.).
            </p>
        </div>

        <button 
            type="button" 
            wire:click="abrirModal" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer self-start sm:self-auto"
        >
            <i class="fas fa-plus text-xs"></i>
            <span>Nueva Sección / Área</span>
        </button>
    </div>

    <!-- Métricas Rápidas -->
    {{-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Secciones</span>
                <div class="text-2xl font-black font-mono text-slate-900 dark:text-white mt-0.5">
                    {{ $totalSecciones }}
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $seccionesActivas }} áreas operativas</span>
            </div>
            <div class="p-3 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl">
                <i class="fas fa-hospital-alt text-xl"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Almacén Principal</span>
                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 truncate max-w-[200px]" title="{{ $almacenPrincipal }}">
                    {{ $almacenPrincipal }}
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Recepción de compras y lotes nuevos</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <i class="fas fa-warehouse text-xl"></i>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Distribución Interna</span>
                <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">
                    Botiquines Satélite
                </div>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Emergencia, Quirófano, Enfermería</span>
            </div>
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <i class="fas fa-truck-ramp-box text-xl"></i>
            </div>
        </div>
    </div> --}}

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selector de Paginación -->
                    <div class="flex items-center gap-2">
                        <label for="perPageSec" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageSec" 
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

                    @if ($sucursales->count() > 1)
                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                        <!-- Filtro por Sucursal -->
                        <div class="flex items-center gap-2">
                            <label for="filtroSucSec" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sede:</label>
                            <select 
                                id="filtroSucSec" 
                                wire:model.live="filtroSucursal" 
                                class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                            >
                                @foreach ($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Buscador Debounce (A la derecha) -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar sección por nombre o descripción..." 
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
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">Sección / Área</th>
                        <th class="py-3.5 px-4">Descripción</th>
                        <th class="py-3.5 px-4 text-center">Rol en Inventario</th>
                        <th class="py-3.5 px-4 text-right">Existencias Físicas</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4 text-center w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($secciones as $index => $sec)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors {{ $sec->es_almacen_principal ? 'bg-teal-50/20 dark:bg-teal-950/10' : '' }}">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-400 text-[11px]">
                                {{ $secciones->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $sec->es_almacen_principal ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-xs shrink-0">
                                        <i class="fas {{ $sec->es_almacen_principal ? 'fa-warehouse' : 'fa-door-open' }}"></i>
                                    </span>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white text-sm">
                                            {{ $sec->nombre }}
                                        </span>
                                        @if ($sec->sucursal)
                                            <span class="text-[10px] text-slate-400 block">
                                                {{ $sec->sucursal->nombre }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-xs">
                                {{ $sec->descripcion ?: 'Sin descripción detallada' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($sec->es_almacen_principal)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                                        <i class="fas fa-star text-[9px] text-teal-600"></i> Almacén Central
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Área / Botiquín Satélite
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white text-sm">
                                {{ number_format((int) ($sec->stock_total ?? 0)) }} <span class="text-[10px] font-normal text-slate-400">un.</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button 
                                    type="button" 
                                    wire:click="toggleActivo({{ $sec->id }})" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $sec->activo ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}"
                                    title="Alternar estado"
                                >
                                    <i class="fas {{ $sec->activo ? 'fa-check-circle text-emerald-600' : 'fa-times-circle text-slate-400' }}"></i>
                                    <span>{{ $sec->activo ? 'Activa' : 'Inactiva' }}</span>
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button 
                                        type="button" 
                                        wire:click="abrirModal({{ $sec->id }})" 
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-teal-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                                        title="Editar Sección"
                                    >
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>

                                    @if (! $sec->es_almacen_principal)
                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Archivar sección \'{{ addslashes($sec->nombre) }}\'?',
                                                text: 'No debe contener existencias activas para poder archivarse.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, archivar',
                                                cancelButtonText: 'Cancelar',
                                                componentId: '{{ $this->getId() }}',
                                                method: 'eliminar',
                                                params: [{{ $sec->id }}],
                                                event: 'eliminarSeccion'
                                            })"
                                            class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer"
                                            title="Eliminar Sección"
                                        >
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center text-slate-400">
                                <i class="fas fa-sitemap text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="font-medium text-slate-600 dark:text-slate-300">No se encontraron secciones o áreas registradas.</p>
                                <button 
                                    wire:click="abrirModal" 
                                    type="button" 
                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i> Crear Primera Sección
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($secciones->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $secciones->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Crear / Editar Sección -->
    @if ($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div 
                @click.outside="$wire.cerrarModal" 
                class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150"
            >
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400">
                            <i class="fas fa-sitemap text-base"></i>
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                {{ $seccionId ? 'Editar Sección / Área' : 'Nueva Sección o Área' }}
                            </h3>
                            <p class="text-xs text-slate-400">Configure el nombre, descripción y tipo de almacén.</p>
                        </div>
                    </div>
                    <button 
                        wire:click="cerrarModal" 
                        class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg transition"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="guardar" class="p-5 space-y-4">
                    @if ($sucursales->count() > 1)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Sucursal <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                wire:model="sucursal_id" 
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                            >
                                @foreach ($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                            @error('sucursal_id') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nombre del Área / Sección <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="nombre" 
                            placeholder="Ej. Farmacia Central, Emergencias, Quirófano, Enfermería..." 
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                        >
                        @error('nombre') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Descripción / Propósito Clínico
                        </label>
                        <textarea 
                            wire:model="descripcion" 
                            rows="2" 
                            placeholder="Detalle el tipo de insumos o atención que se maneja en esta área..."
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20"
                        ></textarea>
                        @error('descripcion') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 space-y-2.5">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model="es_almacen_principal" 
                                class="mt-0.5 rounded text-teal-600 focus:ring-teal-500 h-4 w-4"
                            >
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">
                                    Designar como Almacén Principal (Recepción de Compras)
                                </span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">
                                    Los lotes nuevos ingresados al sistema se asignarán automáticamente a esta sección antes de ser distribuidos internamente.
                                </span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer pt-1 border-t border-slate-200/60 dark:border-slate-700/60">
                            <input 
                                type="checkbox" 
                                wire:model="activo" 
                                class="rounded text-teal-600 focus:ring-teal-500 h-4 w-4"
                            >
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                Área Activa para Movimientos de Inventario
                            </span>
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button 
                            type="button" 
                            wire:click="cerrarModal" 
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            <span wire:loading.remove><i class="fas fa-save me-1"></i> Guardar</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin me-1"></i> Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
