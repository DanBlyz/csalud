<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-calendar-times text-base"></i>
                </span>
                Baja de Medicamentos e Insumos
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Formularios y actas oficiales de baja por vencimiento o merma, con control de aprobación y descuento físico de existencias.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="{{ route('farmacia.lotes') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-750 shadow-xs transition-all"
            >
                <i class="fas fa-boxes text-teal-600 dark:text-teal-400"></i>
                <span>Control de Lotes</span>
            </a>

            @permiso('farmacia.bajas.crear')
            <button 
                wire:click="abrirModalCrear" 
                wire:loading.attr="disabled"
                type="button" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
                <i class="fas fa-plus"></i>
                <span>Nuevo Formulario de Baja</span>
            </button>
            @endpermiso
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-lg">
                <i class="fas fa-folder-open"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Formularios</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalBajas) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-lg">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pendientes</span>
                <h3 class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ number_format($totalPendientes) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Aprobadas</span>
                <h3 class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($totalAprobadas) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 text-lg">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Unidades Dadas de Baja</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalUnidadesDadasDeBaja) }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Izquierda: Paginación y Filtros -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selector de Paginación -->
                    <div class="flex items-center gap-2">
                        <label for="perPageBajas" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageBajas" 
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
                        <label for="filtroEstadoBajas" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEstadoBajas" 
                            wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="Pendiente">Pendientes</option>
                            <option value="Aprobado">Aprobadas</option>
                            <option value="Rechazado">Rechazadas</option>
                        </select>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro por Motivo -->
                    <div class="flex items-center gap-2">
                        <label for="filtroMotivoBajas" class="text-xs font-medium text-slate-600 dark:text-slate-400">Motivo:</label>
                        <select 
                            id="filtroMotivoBajas" 
                            wire:model.live="filtroMotivo" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="Vencimiento">Por Vencimiento</option>
                            <option value="Deterioro / Rotura">Deterioro / Rotura</option>
                            <option value="Merma">Merma</option>
                            <option value="Defecto de Fábrica / Lote">Defecto de Fábrica / Lote</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <!-- Filtro por Sede (si hay más de 1 sucursal) -->
                    @if ($sucursales->count() > 1)
                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                        <div class="flex items-center gap-2">
                            <label for="filtroSucBajas" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sede:</label>
                            <select 
                                id="filtroSucBajas" 
                                wire:model.live="filtroSucursal" 
                                class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                            >
                                <option value="">Todas</option>
                                @foreach ($sucursales as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Limpiar Filtros Rápidos -->
                    @if ($filtroEstado || $filtroMotivo || $filtroSucursal || $filtroFechaDesde || $filtroFechaHasta)
                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                        <button 
                            wire:click="$set('filtroEstado', ''); $set('filtroMotivo', ''); $set('filtroSucursal', null); $set('filtroFechaDesde', null); $set('filtroFechaHasta', null);" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-300 dark:hover:bg-slate-700 cursor-pointer"
                        >
                            <span>Limpiar filtros</span>
                            <i class="fas fa-times text-[10px]"></i>
                        </button>
                    @endif
                </div>

                <!-- Derecha: Buscador Debounce -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por código, fármaco, lote..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button 
                            wire:click="$set('search', '')" 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                            title="Limpiar búsqueda"
                        >
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de Formularios de Baja -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Código</th>
                        <th class="py-3 px-4">Fecha Registro</th>
                        <th class="py-3 px-4">Área / Servicio</th>
                        <th class="py-3 px-4">Responsable</th>
                        <th class="py-3 px-4">Motivo</th>
                        <th class="py-3 px-4 text-center">Ítems / Unidades</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($bajas as $baja)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Código -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-slate-100">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-teal-700 dark:text-teal-300">
                                    {{ $baja->codigo }}
                                </span>
                            </td>

                            <!-- Fecha Registro -->
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-mono">
                                {{ $baja->fecha_registro->format('d/m/Y') }}
                            </td>

                            <!-- Área / Servicio -->
                            <td class="py-3.5 px-4 font-medium text-slate-800 dark:text-slate-200">
                                {{ $baja->area_solicitante }}
                            </td>

                            <!-- Responsable -->
                            <td class="py-3.5 px-4">
                                <span class="block text-slate-800 dark:text-slate-200 font-medium">{{ $baja->responsable }}</span>
                                <span class="text-[10px] text-slate-400">{{ $baja->sucursal?->nombre }}</span>
                            </td>

                            <!-- Motivo -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $baja->motivo === 'Vencimiento' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $baja->motivo }}
                                </span>
                            </td>

                            <!-- Ítems / Unidades -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold text-slate-800 dark:text-slate-100 font-mono">{{ $baja->detalles->count() }} ítems</span>
                                <span class="block text-[10px] text-slate-400 font-mono">({{ $baja->totalUnidades() }} uds.)</span>
                            </td>

                            <!-- Estado -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($baja->estado === 'Aprobado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <i class="fas fa-check-circle text-[9px]"></i>
                                        <span>Aprobado</span>
                                    </span>
                                    @if ($baja->aprobadoPor)
                                        <span class="block text-[9px] text-slate-400 mt-0.5 truncate max-w-[120px] mx-auto" title="Aprobado por {{ $baja->aprobadoPor->nombre_completo }}">
                                            {{ $baja->aprobadoPor->name }}
                                        </span>
                                    @endif
                                @elseif ($baja->estado === 'Rechazado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <i class="fas fa-times-circle text-[9px]"></i>
                                        <span>Rechazado</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse">
                                        <i class="fas fa-clock text-[9px]"></i>
                                        <span>Pendiente</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Ver Detalle -->
                                    <button 
                                        type="button" 
                                        wire:click="abrirModalDetalle({{ $baja->id }})" 
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Ver Detalle Completo"
                                    >
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>

                                    <!-- Imprimir PDF -->
                                    <a 
                                        href="{{ route('farmacia.bajas.pdf', $baja->id) }}" 
                                        target="_blank" 
                                        class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                        title="Imprimir Formulario Oficial en PDF"
                                    >
                                        <i class="fas fa-file-pdf text-sm"></i>
                                    </a>

                                    @if ($baja->esPendiente())
                                        <!-- Botón Aprobar -->
                                        @permiso('farmacia.bajas.aprobar')
                                        <button 
                                            type="button" 
                                            wire:click="abrirModalAprobar({{ $baja->id }})" 
                                            class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Aprobar Formulario y Descontar Stock"
                                        >
                                            <i class="fas fa-check-circle text-sm"></i>
                                        </button>

                                        <!-- Botón Rechazar -->
                                        <button 
                                            type="button" 
                                            wire:click="abrirModalRechazar({{ $baja->id }})" 
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Rechazar Solicitud"
                                        >
                                            <i class="fas fa-ban text-sm"></i>
                                        </button>
                                        @endpermiso

                                        <!-- Botón Editar -->
                                        @permiso('farmacia.bajas.editar')
                                        <button 
                                            type="button" 
                                            wire:click="abrirModalEditar({{ $baja->id }})" 
                                            class="p-1.5 text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300 hover:bg-teal-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Editar Formulario"
                                        >
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        @endpermiso

                                        <!-- Botón Eliminar (Cumpliendo Regla Estricta SweetAlert2) -->
                                        @permiso('farmacia.bajas.eliminar')
                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Eliminar formulario \'{{ $baja->codigo }}\'?',
                                                text: 'Esta acción cancelará el formulario de baja en estado pendiente y registrará auditoría.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'eliminarBaja',
                                                componentId: '{{ $this->getId() }}',
                                                method: 'eliminarBaja',
                                                params: [{{ $baja->id }}]
                                            })"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Eliminar Formulario"
                                        >
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                        @endpermiso
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron formularios de baja</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        @if ($search !== '')
                                            No hay registros que coincidan con "{{ $search }}". Intente con otro criterio de búsqueda.
                                        @else
                                            No existen formularios registrados aún. Comience agregando uno nuevo.
                                        @endif
                                    </p>
                                    @permiso('farmacia.bajas.crear')
                                    <button 
                                        wire:click="abrirModalCrear" 
                                        type="button" 
                                        class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 cursor-pointer shadow-xs"
                                    >
                                        <i class="fas fa-plus"></i> Registrar Primer Formulario
                                    </button>
                                    @endpermiso
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Estándar de Livewire (Regla de No Duplicidad) -->
        @if ($bajas->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $bajas->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: REGISTRO / EDICIÓN DE FORMULARIO DE BAJA -->
    <!-- (Regla 7: Cabecera Simple, Sobria y Compacta) -->
    <!-- ========================================== -->
    @if ($modalBajaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-baja-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalBaja"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $baja_id_en_edicion ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-baja-title">
                                {{ $baja_id_en_edicion ? 'Editar Formulario de Baja de Medicamentos' : 'Nuevo Formulario de Baja de Medicamentos' }}
                            </h3>
                        </div>
                        <button 
                            wire:click="cerrarModalBaja" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                        <!-- Bloque de Filiación y Datos de Cabecera -->
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-750">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Fecha de Registro -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Fecha Registro <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="date" 
                                        wire:model="fecha_registro" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs font-mono"
                                    />
                                    @error('fecha_registro') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Área / Servicio Solicitante -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Área / Servicio <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="area_solicitante" 
                                        placeholder="Ej: Farmacia Central, Quirófano..."
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs"
                                    />
                                    @error('area_solicitante') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Responsable -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Responsable <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        wire:model="responsable" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs"
                                    />
                                    @error('responsable') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Motivo de Baja -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                        Motivo <span class="text-rose-500">*</span>
                                    </label>
                                    <select 
                                        wire:model="motivo" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs font-medium"
                                    >
                                        <option value="Vencimiento">Por Vencimiento</option>
                                        <option value="Deterioro / Rotura">Deterioro / Rotura</option>
                                        <option value="Merma">Merma</option>
                                        <option value="Defecto de Fábrica / Lote">Defecto de Fábrica / Lote</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    @error('motivo') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Panel de Detalle de Medicamentos Dados de Baja -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <i class="fas fa-pills text-teal-600"></i>
                                    <span>Detalle de Medicamentos Dados de Baja</span>
                                </h4>
                                <span class="text-[11px] text-slate-400">
                                    Seleccione los lotes físicos a descartar antes de registrar el formulario.
                                </span>
                            </div>

                            <!-- Selector Rápido de Medicamento / Lote -->
                            <div class="p-4 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <!-- Buscador Predictivo (Regla 9.C) -->
                                    <div class="sm:col-span-5 relative">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Buscar Fármaco / Lote <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <i class="fas fa-search text-xs"></i>
                                            </div>
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.300ms="buscarProductoLote" 
                                                placeholder="Nombre del medicamento o lote..." 
                                                class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                            />
                                            @if ($buscarProductoLote !== '')
                                                <button 
                                                    wire:click="$set('buscarProductoLote', '')" 
                                                    type="button" 
                                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                                                >
                                                    <i class="fas fa-times-circle text-xs"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Desplegable Predictivo de Lotes -->
                                        @if (! empty($buscarProductoLote) && $sugerenciasLotes->count() > 0)
                                            <div class="absolute z-30 left-0 right-0 mt-1 max-h-52 overflow-y-auto bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl divide-y divide-slate-100 dark:divide-slate-800">
                                                @foreach ($sugerenciasLotes as $l)
                                                    <button 
                                                        type="button" 
                                                        wire:click="seleccionarLoteParaItem({{ $l->id }})" 
                                                        class="w-full text-left px-3 py-2 text-xs hover:bg-teal-50 dark:hover:bg-slate-800 flex items-center justify-between cursor-pointer"
                                                    >
                                                        <div>
                                                            <span class="font-bold text-slate-800 dark:text-slate-100 block">{{ $l->producto->nombre }}</span>
                                                            <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                                                <span>Lote: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $l->codigo_lote }}</strong></span>
                                                                <span>Vence: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $l->fecha_vencimiento ? $l->fecha_vencimiento->format('d/m/Y') : 'S/F' }}</strong></span>
                                                            </div>
                                                        </div>
                                                        <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold shrink-0">
                                                            Stock: {{ $l->cantidad_actual }}
                                                        </span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Fármaco y Lote Seleccionado -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Lote Seleccionado
                                        </label>
                                        <input 
                                            type="text" 
                                            readonly 
                                            value="{{ $item_producto_nombre ? ($item_producto_nombre . ' [' . $item_lote_codigo . ']') : 'Ninguno' }}" 
                                            class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-750 bg-slate-100 dark:bg-slate-900/60 p-2.5 text-slate-600 dark:text-slate-300 font-medium truncate"
                                        />
                                    </div>

                                    <!-- Stock / Inventario de Origen (Sección) -->
                                    <div class="sm:col-span-4">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Stock Origen (Sección) <span class="text-rose-500">*</span>
                                            @if ($item_lote_id)
                                                <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">(Disp: {{ $item_stock_disponible }})</span>
                                            @endif
                                        </label>
                                        <select 
                                            wire:model.live="item_seccion_id" 
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2.5 shadow-2xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                        >
                                            <option value="">Seleccione sección...</option>
                                            @if ($seccionesDelLoteSeleccionado->count() > 0)
                                                @foreach ($seccionesDelLoteSeleccionado as $ls)
                                                    <option value="{{ $ls->seccion_id }}">
                                                        {{ $ls->seccion?->nombre ?? 'Sección' }} (Stock: {{ $ls->cantidad_actual }})
                                                    </option>
                                                @endforeach
                                            @else
                                                @foreach ($seccionesSucursal as $sec)
                                                    <option value="{{ $sec->id }}">{{ $sec->nombre }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1">
                                    <!-- Forma Farmacéutica -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Forma Farmacéutica
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="item_forma_farmaceutica" 
                                            placeholder="Ej: Comprimido, Ampolla..." 
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2.5 shadow-2xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                        />
                                    </div>

                                    <!-- Concentración -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Concentración
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="item_concentracion" 
                                            placeholder="Ej: 1g, 500mg, 10ml..." 
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2.5 shadow-2xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                        />
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Cantidad <span class="text-rose-500">*</span>
                                        </label>
                                        <input 
                                            type="number" 
                                            min="1" 
                                            max="{{ $item_stock_disponible > 0 ? $item_stock_disponible : 1 }}" 
                                            wire:model="item_cantidad" 
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2.5 shadow-2xs text-center font-bold font-mono text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                        />
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Observaciones
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="item_observaciones" 
                                            placeholder="Detalle o estado físico..." 
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2.5 shadow-2xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                        />
                                    </div>

                                    <!-- Botón Añadir Ítem -->
                                    <div class="sm:col-span-1 flex items-end">
                                        <button 
                                            type="button" 
                                            wire:click="agregarItemACola" 
                                            class="w-full py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs cursor-pointer flex items-center justify-center transition-colors"
                                            title="Añadir a la lista"
                                        >
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla Acumulativa de Ítems en Cola -->
                            @if (count($cola_detalles) > 0)
                                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 max-h-56 overflow-y-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] uppercase font-bold sticky top-0">
                                            <tr>
                                                <th class="py-2 px-3">Nº</th>
                                                <th class="py-2 px-3">Medicamento</th>
                                                <th class="py-2 px-3">Forma</th>
                                                <th class="py-2 px-3">Concentración</th>
                                                <th class="py-2 px-3">Lote</th>
                                                <th class="py-2 px-3">Vencimiento</th>
                                                <th class="py-2 px-3 text-center">Cant.</th>
                                                <th class="py-2 px-3">Sección Origen</th>
                                                <th class="py-2 px-3">Observaciones</th>
                                                <th class="py-2 px-3 text-center">Quitar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 text-[11px]">
                                            @foreach ($cola_detalles as $idx => $item)
                                                <tr>
                                                    <td class="py-2 px-3 font-mono font-bold">{{ $idx + 1 }}</td>
                                                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                                        {{ $item['producto_nombre'] }}
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-500">{{ $item['forma_farmaceutica'] ?: '---' }}</td>
                                                    <td class="py-2 px-3 text-slate-500">{{ $item['concentracion'] ?: '---' }}</td>
                                                    <td class="py-2 px-3 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $item['lote_codigo'] }}</td>
                                                    <td class="py-2 px-3 font-mono text-slate-500">{{ $item['fecha_vencimiento'] ? date('d/m/Y', strtotime($item['fecha_vencimiento'])) : 'S/F' }}</td>
                                                    <td class="py-2 px-3 text-center font-bold text-teal-600 dark:text-teal-400 font-mono">{{ $item['cantidad'] }}</td>
                                                    <td class="py-2 px-3 text-slate-600 dark:text-slate-300">{{ $item['seccion_nombre'] }}</td>
                                                    <td class="py-2 px-3 text-slate-400 text-[10px]">{{ $item['observaciones'] ?: '---' }}</td>
                                                    <td class="py-2 px-3 text-center">
                                                        <button 
                                                            type="button" 
                                                            wire:click="quitarItemDeCola({{ $idx }})" 
                                                            class="text-rose-500 hover:text-rose-700 p-1 cursor-pointer transition-colors"
                                                            title="Quitar"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-slate-50 dark:bg-slate-800/80 font-bold text-[10px] text-slate-700 dark:text-slate-300">
                                                <td colspan="6" class="py-2 px-3 text-right uppercase">Total Unidades:</td>
                                                <td class="py-2 px-3 text-center font-mono text-teal-600 dark:text-teal-400 text-xs">
                                                    {{ collect($cola_detalles)->sum('cantidad') }}
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="p-6 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-center text-slate-400 text-xs">
                                    <i class="fas fa-pills text-xl mb-1 block opacity-40"></i>
                                    <span>No se han añadido medicamentos a la lista aún. Use el buscador para agregar ítems.</span>
                                </div>
                            @endif
                            @error('cola_detalles') <span class="text-rose-500 text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Justificación de la Baja -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Justificación de la Baja <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                wire:model="justificacion" 
                                rows="3" 
                                placeholder="Describa detalladamente el motivo y justificación técnica de la baja..." 
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs"
                            ></textarea>
                            @error('justificacion') <span class="text-rose-500 text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="cerrarModalBaja" 
                            class="px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            Cancelar
                        </button>

                        <button 
                            type="button" 
                            wire:click="guardarBaja" 
                            wire:loading.attr="disabled"
                            wire:target="guardarBaja"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="guardarBaja">
                                <i class="fas fa-save me-1"></i> {{ $baja_id_en_edicion ? 'Guardar Cambios' : 'Registrar Formulario' }}
                            </span>
                            <span wire:loading wire:target="guardarBaja" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 2: DETALLE / VISUALIZACIÓN DE LA BAJA -->
    <!-- ========================================== -->
    @if ($modalDetalleOpen && $bajaSeleccionada)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-ver-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalDetalle"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas fa-file-alt"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-ver-title">
                                Formulario de Baja: {{ $bajaSeleccionada->codigo }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('farmacia.bajas.pdf', $bajaSeleccionada->id) }}" 
                                target="_blank" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs"
                            >
                                <i class="fas fa-file-pdf"></i>
                                <span>Imprimir PDF</span>
                            </a>
                            <button 
                                wire:click="cerrarModalDetalle" 
                                type="button" 
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                            >
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs">
                        <!-- Metadatos de Cabecera -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-750">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Área Solicitante</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $bajaSeleccionada->area_solicitante }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Motivo</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $bajaSeleccionada->motivo }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Estado</span>
                                <span class="font-bold {{ $bajaSeleccionada->estado === 'Aprobado' ? 'text-emerald-600' : ($bajaSeleccionada->estado === 'Rechazado' ? 'text-rose-600' : 'text-amber-600') }}">
                                    {{ $bajaSeleccionada->estado }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Unidades</span>
                                <span class="font-bold font-mono text-slate-800 dark:text-slate-200">{{ $bajaSeleccionada->totalUnidades() }}</span>
                            </div>
                        </div>

                        @if ($bajaSeleccionada->esAprobado())
                            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs">
                                <i class="fas fa-check-circle me-1"></i>
                                <strong>Aprobado por:</strong> {{ $bajaSeleccionada->aprobadoPor?->nombre_completo }} el {{ $bajaSeleccionada->fecha_aprobacion?->format('d/m/Y H:i') }}
                                @if ($bajaSeleccionada->observaciones_aprobacion)
                                    <div class="mt-1 text-[11px] text-emerald-700 dark:text-emerald-400">
                                        Nota: {{ $bajaSeleccionada->observaciones_aprobacion }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Tabla de Ítems -->
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-2">Detalle de Medicamentos</h4>
                            <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300">
                                        <tr>
                                            <th class="py-2.5 px-3">#</th>
                                            <th class="py-2.5 px-3">Medicamento</th>
                                            <th class="py-2.5 px-3">Forma / Conc.</th>
                                            <th class="py-2.5 px-3">Lote</th>
                                            <th class="py-2.5 px-3">Vencimiento</th>
                                            <th class="py-2.5 px-3 text-center">Cant.</th>
                                            <th class="py-2.5 px-3">Sección Origen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 font-mono text-[11px]">
                                        @foreach ($bajaSeleccionada->detalles as $idx => $d)
                                            <tr>
                                                <td class="py-2 px-3 font-sans">{{ $idx + 1 }}</td>
                                                <td class="py-2 px-3 font-sans font-semibold text-slate-800 dark:text-slate-200">
                                                    {{ $d->producto?->nombre }}
                                                </td>
                                                <td class="py-2 px-3 font-sans text-slate-500">
                                                    {{ $d->forma_farmaceutica ?: $d->producto?->unidad_medida }}
                                                    @if ($d->concentracion)
                                                        - {{ $d->concentracion }}
                                                    @endif
                                                </td>
                                                <td class="py-2 px-3 font-bold">{{ $d->lote?->codigo_lote }}</td>
                                                <td class="py-2 px-3 text-slate-500">{{ $d->fecha_vencimiento ? $d->fecha_vencimiento->format('d/m/Y') : 'S/F' }}</td>
                                                <td class="py-2 px-3 text-center font-bold text-teal-600 dark:text-teal-400">{{ $d->cantidad }}</td>
                                                <td class="py-2 px-3 font-sans text-slate-600 dark:text-slate-300">{{ $d->seccion?->nombre }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Justificación -->
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">Justificación de la Baja</h4>
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-750 text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $bajaSeleccionada->justificacion }}
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                        <button 
                            type="button" 
                            wire:click="cerrarModalDetalle" 
                            class="px-4 py-2.5 rounded-lg bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold cursor-pointer"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 3: CONFIRMACIÓN DE APROBACIÓN DE LA BAJA -->
    <!-- ========================================== -->
    @if ($modalAprobarOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-aprobar-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalAprobar"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white text-sm">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-aprobar-title">
                                Aprobar Formulario de Baja
                            </h3>
                        </div>
                        <button 
                            wire:click="cerrarModalAprobar" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-amber-800 dark:text-amber-300 text-xs leading-relaxed">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Atención:</strong> Al confirmar la aprobación, las unidades de cada lote se descontarán formalmente del stock consolidado y de la sección de origen, registrando la salida en el Kardex.
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Observaciones de Aprobación (Opcional)
                            </label>
                            <textarea 
                                wire:model="observaciones_aprobacion" 
                                rows="2" 
                                placeholder="Nota o resolución de supervisión..." 
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="cerrarModalAprobar" 
                            class="px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                        >
                            Cancelar
                        </button>

                        <button 
                            type="button" 
                            wire:click="procesarAprobacion" 
                            wire:loading.attr="disabled"
                            wire:target="procesarAprobacion"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="procesarAprobacion">
                                <i class="fas fa-check me-1"></i> Confirmar Aprobación
                            </span>
                            <span wire:loading wire:target="procesarAprobacion" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- MODAL 4: CONFIRMACIÓN DE RECHAZO DE LA BAJA -->
    <!-- ========================================== -->
    @if ($modalRechazarOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-rechazar-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalRechazar"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white text-sm">
                                <i class="fas fa-ban"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-rechazar-title">
                                Rechazar Formulario de Baja
                            </h3>
                        </div>
                        <button 
                            wire:click="cerrarModalRechazar" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        >
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Motivo del Rechazo <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                wire:model="motivo_rechazo" 
                                rows="3" 
                                placeholder="Indique el motivo por el cual no procede la baja..." 
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-2xs"
                            ></textarea>
                            @error('motivo_rechazo') <span class="text-rose-500 text-[11px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="cerrarModalRechazar" 
                            class="px-4 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                        >
                            Cancelar
                        </button>

                        <button 
                            type="button" 
                            wire:click="procesarRechazo" 
                            wire:loading.attr="disabled"
                            wire:target="procesarRechazo"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="procesarRechazo">
                                <i class="fas fa-ban me-1"></i> Confirmar Rechazo
                            </span>
                            <span wire:loading wire:target="procesarRechazo" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
