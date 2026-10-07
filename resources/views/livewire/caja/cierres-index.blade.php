<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>Caja y Facturación</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 dark:text-slate-200 font-semibold">Cierres y Utilidades</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fas fa-chart-pie text-violet-600 dark:text-violet-400"></i>
                <span>Cierres Mensuales y Liquidación de Utilidades</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Consolide los ingresos por cobros de proformas, deduzca honorarios médicos, compras de inventario y gastos operativos para determinar la utilidad neta.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button 
                type="button" 
                wire:click="abrirModalCrear" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-500/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer"
            >
                <i class="fas fa-plus-circle text-sm"></i>
                <span>Nuevo Cierre Mensual</span>
            </button>
        </div>
    </div>

    <!-- Metricas Superiores (Colores Solidos) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-lg">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Períodos Registrados</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 font-mono">{{ number_format($totalCierres) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Cierres Finalizados</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 font-mono">{{ number_format($totalCierresCerrados) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 text-lg">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Utilidad Acumulada (Cierres)</span>
                <h3 class="text-lg font-bold text-violet-600 dark:text-violet-400 font-mono">
                    Bs. {{ number_format($utilidadAcumulada, 2) }}
                </h3>
            </div>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selector de Registros -->
                    <div class="flex items-center gap-2">
                        <label for="perPageCierres" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageCierres" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-violet-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro Año -->
                    <div class="flex items-center gap-2">
                        <label for="filtroAnio" class="text-xs font-medium text-slate-600 dark:text-slate-400">Año:</label>
                        <select 
                            id="filtroAnio" 
                            wire:model.live="filtroAnio" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-violet-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            @for ($y = (int) date('Y') + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Filtro Estado -->
                    <div class="flex items-center gap-2">
                        <label for="filtroEst" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEst" 
                            wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-violet-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos</option>
                            <option value="Borrador">Borrador</option>
                            <option value="Cerrado">Cerrado</option>
                        </select>
                    </div>

                    <!-- Filtro Sucursal -->
                    @if ($sucursales->count() > 1)
                        <div class="flex items-center gap-2">
                            <label for="filtroSuc" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sede:</label>
                            <select 
                                id="filtroSuc" 
                                wire:model.live="filtroSucursal" 
                                class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-violet-500 py-1.5 px-2.5 shadow-2xs"
                            >
                                <option value="">Todas las Sedes</option>
                                @foreach ($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Buscador General -->
                <div class="relative w-full md:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por notas o responsable..." 
                        class="w-full text-xs pl-8 pr-8 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all shadow-2xs"
                    >
                    @if ($search)
                        <button 
                            type="button" 
                            wire:click="$set('search', '')" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla Principal de Cierres -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">Período / Mes</th>
                        <th class="py-3 px-4">Rango de Fechas</th>
                        <th class="py-3 px-4">Sede / Sucursal</th>
                        <th class="py-3 px-4 text-right">Total Ingresos</th>
                        <th class="py-3 px-4 text-right">Total Egresos</th>
                        <th class="py-3 px-4 text-right">Utilidad Neta</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-center w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($cierres as $idx => $cierre)
                        @php
                            $isPositiva = $cierre->utilidad_neta >= 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]">
                                {{ $cierres->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                                    <i class="fas fa-calendar-check text-violet-500"></i>
                                    <span>{{ $cierre->nombre_mes }} {{ $cierre->anio }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $cierre->detalles_count }} partida(s) registradas
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $cierre->fecha_inicio?->format('d/m/Y') }} - {{ $cierre->fecha_fin?->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-slate-800 dark:text-slate-200 text-xs">
                                    {{ $cierre->sucursal->nombre ?? 'Sede Central' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                Bs. {{ number_format((float) $cierre->total_ingresos, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                                Bs. {{ number_format((float) $cierre->total_egresos, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-sm {{ $isPositiva ? 'text-violet-600 dark:text-violet-400' : 'text-rose-600 dark:text-rose-400' }}">
                                Bs. {{ number_format((float) $cierre->utilidad_neta, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($cierre->estado === 'Cerrado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50">
                                        <i class="fas fa-lock text-[9px]"></i> Cerrado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
                                        <i class="fas fa-pen text-[9px]"></i> Borrador
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        type="button" 
                                        wire:click="verCierre({{ $cierre->id }})" 
                                        class="p-1.5 rounded-lg text-violet-600 dark:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-950/50 transition cursor-pointer"
                                        title="Ver y Gestionar Balance de Cierre"
                                    >
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>

                                    @if ($cierre->estado === 'Borrador')
                                        <button 
                                            type="button" 
                                            wire:click="eliminarCierre({{ $cierre->id }})" 
                                            wire:loading.attr="disabled"
                                            wire:confirm="¿Está seguro de eliminar este cierre mensual? Se removerán todas las partidas asociadas."
                                            class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Eliminar Cierre"
                                        >
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 px-4 text-center text-slate-400">
                                <i class="fas fa-chart-pie text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="font-medium text-slate-600 dark:text-slate-300">No se encontraron cierres mensuales registrados.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Aperture un nuevo cierre para consolidar cobros, honorarios médicos y gastos del mes.</p>
                                <button 
                                    type="button" 
                                    wire:click="abrirModalCrear" 
                                    class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-violet-600 text-white text-xs font-semibold hover:bg-violet-700 cursor-pointer shadow-md shadow-violet-500/20"
                                >
                                    <i class="fas fa-plus-circle"></i> Nuevo Cierre
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Estándar -->
        @if ($cierres->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $cierres->links() }}
            </div>
        @endif
    </div>

    <!-- ======================================================================= -->
    <!-- MODAL 1: APERTURA DE NUEVO CIERRE MENSUAL -->
    <!-- ======================================================================= -->
    @if ($modalCrearOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-chart-pie text-violet-600 dark:text-violet-400"></i>
                            <span>Apertura de Nuevo Cierre Mensual</span>
                        </h3>
                        <button wire:click="cerrarModalCrear" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="crearCierre" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="nuevo_anio" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Año Fiscal <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="nuevo_anio" 
                                    wire:model.live="nuevo_anio" 
                                    min="2020" 
                                    max="2035" 
                                    class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                                >
                                @error('nuevo_anio') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nuevo_mes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Mes a Liquidar <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="nuevo_mes" 
                                    wire:model.live="nuevo_mes" 
                                    class="w-full text-xs font-semibold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                                >
                                    @php
                                        $mesesArray = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                                    @endphp
                                    @foreach ($mesesArray as $num => $nom)
                                        <option value="{{ $num }}">{{ $nom }}</option>
                                    @endforeach
                                </select>
                                @error('nuevo_mes') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Rango de Fechas -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="nuevo_fecha_inicio" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha Inicio <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="nuevo_fecha_inicio" 
                                    wire:model="nuevo_fecha_inicio" 
                                    class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                                >
                                @error('nuevo_fecha_inicio') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nuevo_fecha_fin" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha Fin <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="nuevo_fecha_fin" 
                                    wire:model="nuevo_fecha_fin" 
                                    class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                                >
                                @error('nuevo_fecha_fin') <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Sede / Sucursal -->
                        <div>
                            <label for="nuevo_sucursal_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Sede / Sucursal de Aplicación
                            </label>
                            <select 
                                id="nuevo_sucursal_id" 
                                wire:model="nuevo_sucursal_id" 
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                            >
                                @foreach ($sucursales as $sOption)
                                    <option value="{{ $sOption->id }}">{{ $sOption->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="nuevo_observaciones" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Observaciones / Notas de Apertura
                            </label>
                            <textarea 
                                id="nuevo_observaciones" 
                                wire:model="nuevo_observaciones" 
                                rows="2" 
                                placeholder="Notas opcionales sobre este período contable..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs resize-none"
                            ></textarea>
                        </div>

                        <!-- Caja Informativa -->
                        <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-950/40 border border-violet-200 dark:border-violet-900/60 text-xs text-violet-800 dark:text-violet-300 flex items-start gap-2.5">
                            <i class="fas fa-info-circle text-base mt-0.5 shrink-0 text-violet-600 dark:text-violet-400"></i>
                            <div class="text-[11px] leading-relaxed">
                                <span class="font-bold">Consolidación Automática:</span> Al crear el cierre, el sistema importará en bloque los cobros de proformas registrados en caja, los honorarios médicos asignados y las compras de lotes en farmacia dentro de este rango de fechas.
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="cerrarModalCrear" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="crearCierre">
                                    <i class="fas fa-check-circle me-1"></i> Crear y Consolidar
                                </span>
                                <span wire:loading wire:target="crearCierre">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL 2: GESTIÓN Y DETALLE INTEGRAL DEL CIERRE -->
    <!-- ======================================================================= -->
    @if ($modalDetalleOpen && $cierreSeleccionado)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-3 sm:p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all my-6 w-full max-w-5xl flex flex-col max-h-[92vh]">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 shrink-0">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-chart-pie text-violet-600 dark:text-violet-400"></i>
                                    <span>Balance Mensual: {{ $cierreSeleccionado->nombre_mes }} {{ $cierreSeleccionado->anio }}</span>
                                </h3>
                                @if ($cierreSeleccionado->estado === 'Cerrado')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i class="fas fa-lock text-[9px]"></i> Cerrado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i class="fas fa-pen text-[9px]"></i> Borrador
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5 font-mono">
                                {{ $cierreSeleccionado->fecha_inicio?->format('d/m/Y') }} al {{ $cierreSeleccionado->fecha_fin?->format('d/m/Y') }} • Sede: {{ $cierreSeleccionado->sucursal->nombre ?? 'Central' }}
                            </p>
                        </div>

                        <!-- Botones de Acción del Cierre -->
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($cierreSeleccionado->estado === 'Borrador')
                                <button 
                                    type="button" 
                                    wire:click="sincronizarAutomaticos" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shadow-2xs"
                                    title="Sincronizar y actualizar cobros, honorarios y compras del rango"
                                >
                                    <span wire:loading.remove wire:target="sincronizarAutomaticos">
                                        <i class="fas fa-sync-alt text-xs text-blue-500"></i> Sincronizar
                                    </span>
                                    <span wire:loading wire:target="sincronizarAutomaticos">
                                        <i class="fas fa-spinner fa-spin text-xs"></i> Actualizando...
                                    </span>
                                </button>

                                <button 
                                    type="button" 
                                    wire:click="abrirModalPartida('Egreso')" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold cursor-pointer shadow-2xs transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <i class="fas fa-minus-circle text-xs"></i>
                                    <span>Agregar Gasto Extra</span>
                                </button>

                                <button 
                                    type="button" 
                                    wire:click="abrirModalPartida('Ingreso')" 
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold cursor-pointer shadow-2xs transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <i class="fas fa-plus-circle text-xs"></i>
                                    <span>Agregar Ingreso Extra</span>
                                </button>

                                <button 
                                    type="button" 
                                    wire:click="cambiarEstadoCierre('Cerrado')" 
                                    wire:confirm="¿Desea cerrar definitivamente el período contable? Se bloquearán ediciones manuales."
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-black text-white text-xs font-bold cursor-pointer shadow-2xs"
                                >
                                    <i class="fas fa-lock text-xs text-amber-400"></i>
                                    <span>Finalizar Cierre</span>
                                </button>
                            @else
                                <button 
                                    type="button" 
                                    wire:click="cambiarEstadoCierre('Borrador')" 
                                    wire:confirm="¿Desea reabrir este cierre mensual para realizar ajustes?"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 text-xs font-semibold hover:bg-amber-50 dark:hover:bg-amber-950/40 cursor-pointer"
                                >
                                    <i class="fas fa-lock-open text-xs"></i>
                                    <span>Reabrir Período</span>
                                </button>
                            @endif

                            <button wire:click="cerrarModalDetalle" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Métricas del Cierre Seleccionado (Colores Sólidos) -->
                    <div class="p-6 bg-slate-900 text-white border-b border-slate-800 shrink-0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Total Ingresos -->
                            <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                    <span>Ingresos Totales (Caja)</span>
                                    <i class="fas fa-arrow-down text-emerald-400"></i>
                                </div>
                                <span class="text-lg font-bold font-mono text-emerald-400">
                                    Bs. {{ number_format((float) $cierreSeleccionado->total_ingresos, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    {{ $cierreSeleccionado->ingresos->count() }} cobro(s)
                                </span>
                            </div>

                            <!-- Total Egresos -->
                            <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                    <span>Egresos Totales</span>
                                    <i class="fas fa-arrow-up text-rose-400"></i>
                                </div>
                                <span class="text-lg font-bold font-mono text-rose-400">
                                    Bs. {{ number_format((float) $cierreSeleccionado->total_egresos, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    {{ $cierreSeleccionado->egresos->count() }} salida(s)
                                </span>
                            </div>

                            <!-- Egresos Clínicos vs Operativos -->
                            @php
                                $egresosClinicos = (float) $cierreSeleccionado->egresos->whereIn('categoria', ['Honorario Médico', 'Compra Farmacia'])->sum('monto');
                                $egresosOperativos = (float) $cierreSeleccionado->egresos->whereNotIn('categoria', ['Honorario Médico', 'Compra Farmacia'])->sum('monto');
                            @endphp
                            <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                    <span>Honorarios & Farmacia</span>
                                    <i class="fas fa-user-md text-blue-400"></i>
                                </div>
                                <span class="text-lg font-bold font-mono text-white">
                                    Bs. {{ number_format($egresosClinicos, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    Operativos/Servicios: Bs. {{ number_format($egresosOperativos, 2) }}
                                </span>
                            </div>

                            <!-- Utilidad Neta Real -->
                            @php
                                $utilidadPositiva = $cierreSeleccionado->utilidad_neta >= 0;
                            @endphp
                            <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                    <span>Utilidad Neta Real</span>
                                    <i class="fas fa-wallet text-violet-400"></i>
                                </div>
                                <span class="text-xl font-black font-mono {{ $utilidadPositiva ? 'text-violet-400' : 'text-rose-400' }}">
                                    Bs. {{ number_format((float) $cierreSeleccionado->utilidad_neta, 2) }}
                                </span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    {{ $utilidadPositiva ? 'Ganancia Neta Centro de Salud' : 'Déficit Financiero' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-Tabs de Desglose -->
                    <div class="px-6 pt-3 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shrink-0">
                        <div class="flex items-center space-x-1 overflow-x-auto pb-px">
                            <button 
                                wire:click="cambiarTabDetalle('resumen')" 
                                type="button" 
                                class="px-4 py-2 text-xs font-bold border-b-2 transition cursor-pointer {{ $tabDetalle === 'resumen' ? 'border-violet-600 text-violet-600 dark:text-violet-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                            >
                                <i class="fas fa-list-alt text-xs me-1"></i> Resumen de Partidas ({{ $cierreSeleccionado->detalles->count() }})
                            </button>

                            <button 
                                wire:click="cambiarTabDetalle('ingresos')" 
                                type="button" 
                                class="px-4 py-2 text-xs font-bold border-b-2 transition cursor-pointer {{ $tabDetalle === 'ingresos' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                            >
                                <i class="fas fa-arrow-down text-xs me-1"></i> Cobros e Ingresos ({{ $cierreSeleccionado->ingresos->count() }})
                            </button>

                            <button 
                                wire:click="cambiarTabDetalle('egresos_clinicos')" 
                                type="button" 
                                class="px-4 py-2 text-xs font-bold border-b-2 transition cursor-pointer {{ $tabDetalle === 'egresos_clinicos' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                            >
                                <i class="fas fa-user-md text-xs me-1"></i> Honorarios y Farmacia ({{ $cierreSeleccionado->egresos->whereIn('categoria', ['Honorario Médico', 'Compra Farmacia'])->count() }})
                            </button>

                            <button 
                                wire:click="cambiarTabDetalle('egresos_operativos')" 
                                type="button" 
                                class="px-4 py-2 text-xs font-bold border-b-2 transition cursor-pointer {{ $tabDetalle === 'egresos_operativos' ? 'border-amber-600 text-amber-600 dark:text-amber-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                            >
                                <i class="fas fa-lightbulb text-xs me-1"></i> Servicios Básicos & Sueldos ({{ $cierreSeleccionado->egresos->whereNotIn('categoria', ['Honorario Médico', 'Compra Farmacia'])->count() }})
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de Detalle Scrollable -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        @php
                            $partidasFiltradas = match($tabDetalle) {
                                'ingresos' => $cierreSeleccionado->ingresos,
                                'egresos_clinicos' => $cierreSeleccionado->egresos->whereIn('categoria', ['Honorario Médico', 'Compra Farmacia']),
                                'egresos_operativos' => $cierreSeleccionado->egresos->whereNotIn('categoria', ['Honorario Médico', 'Compra Farmacia']),
                                default => $cierreSeleccionado->detalles,
                            };
                        @endphp

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-2.5 px-3 w-10 text-center">#</th>
                                        <th class="py-2.5 px-3">Fecha</th>
                                        <th class="py-2.5 px-3 text-center">Tipo</th>
                                        <th class="py-2.5 px-3">Categoría</th>
                                        <th class="py-2.5 px-3">Concepto / Detalle</th>
                                        <th class="py-2.5 px-3 text-right">Monto</th>
                                        <th class="py-2.5 px-3 text-center w-20">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse ($partidasFiltradas as $pIdx => $det)
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                            <td class="py-2.5 px-3 text-center font-mono text-slate-400 text-[11px]">{{ $pIdx + 1 }}</td>
                                            <td class="py-2.5 px-3 font-mono text-slate-500 text-[11px]">
                                                {{ $det->fecha?->format('d/m/Y') }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                @if ($det->tipo === 'Ingreso')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                        <i class="fas fa-plus text-[8px]"></i> Ingreso
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                        <i class="fas fa-minus text-[8px]"></i> Egreso
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 font-medium text-slate-800 dark:text-slate-200">
                                                {{ $det->categoria }}
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <div class="text-xs font-semibold text-slate-900 dark:text-white">
                                                    {{ $det->concepto }}
                                                </div>
                                                @if ($det->observaciones)
                                                    <div class="text-[10px] text-slate-400 italic mt-0.5">
                                                        {{ $det->observaciones }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono font-bold text-xs {{ $det->tipo === 'Ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ $det->tipo === 'Ingreso' ? '+' : '-' }} Bs. {{ number_format((float) $det->monto, 2) }}
                                            </td>
                                            <td class="py-2.5 px-3 text-center">
                                                @if ($cierreSeleccionado->estado === 'Borrador')
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button 
                                                            type="button" 
                                                            wire:click="editarPartida({{ $det->id }})" 
                                                            class="p-1 rounded text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 cursor-pointer"
                                                            title="Editar Partida"
                                                        >
                                                            <i class="fas fa-edit text-xs"></i>
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="eliminarPartida({{ $det->id }})" 
                                                            wire:confirm="¿Desea eliminar esta partida del balance?"
                                                            wire:loading.attr="disabled"
                                                            class="p-1 rounded text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                                            title="Eliminar Partida"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs" wire:loading.remove wire:target="eliminarPartida({{ $det->id }})"></i>
                                                            <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarPartida({{ $det->id }})"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 text-[10px] italic">Bloqueado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 px-4 text-center text-slate-400 italic">
                                                No hay partidas registradas en esta vista.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL 3: AGREGAR / EDITAR PARTIDA (INGRESO EXTRA / SALIDA DE CAJA)       -->
    <!-- ======================================================================= -->
    @if ($modalPartidaOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity" role="dialog" aria-modal="true">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <!-- Header Estilo Cobros y Liquidaciones -->
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between {{ $partida_tipo === 'Ingreso' ? 'bg-blue-50/50 dark:bg-blue-950/20' : 'bg-rose-50/50 dark:bg-rose-950/20' }}">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl {{ $partida_tipo === 'Ingreso' ? 'bg-blue-600' : 'bg-rose-600' }} text-white shadow-sm">
                            <i class="fas {{ $partida_tipo === 'Ingreso' ? 'fa-plus-circle' : 'fa-arrow-circle-up' }} text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ $editando_partida_id ? 'Modificar Partida' : ($partida_tipo === 'Ingreso' ? 'Registrar Ingreso Extra' : 'Registrar Salida / Gasto de Caja') }}
                            </h2>
                            <p class="text-[11px] text-slate-500">
                                Asiento manual para el balance {{ $cierreSeleccionado ? '(' . $cierreSeleccionado->nombre_mes . ' ' . $cierreSeleccionado->anio . ')' : '' }}
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="cerrarModalPartida" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg transition cursor-pointer"
                    >
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit="guardarPartida" class="p-6 space-y-4">
                    <!-- Selector de Tipo Interactivo -->
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            type="button" 
                            wire:click="setPartidaTipo('Ingreso')" 
                            class="p-3 rounded-xl border text-center transition font-bold text-xs flex flex-col items-center gap-1.5 cursor-pointer {{ $partida_tipo === 'Ingreso' ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}"
                        >
                            <i class="fas fa-arrow-down text-sm text-blue-500"></i>
                            <span>Ingreso Extra</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="setPartidaTipo('Egreso')" 
                            class="p-3 rounded-xl border text-center transition font-bold text-xs flex flex-col items-center gap-1.5 cursor-pointer {{ $partida_tipo === 'Egreso' ? 'border-rose-600 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}"
                        >
                            <i class="fas fa-arrow-up text-sm text-rose-500"></i>
                            <span>Salida de Caja</span>
                        </button>
                    </div>

                    <!-- Categoría y Fecha -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Categoría <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                wire:model="partida_categoria" 
                                class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:ring-2 {{ $partida_tipo === 'Ingreso' ? 'focus:ring-blue-500' : 'focus:ring-rose-500' }}"
                            >
                                @if($partida_tipo === 'Ingreso')
                                    <option value="Extra">Extra General</option>
                                    <option value="Certificados">Certificados Médicos</option>
                                    <option value="Fotocopias/Trámites">Fotocopias / Trámites</option>
                                    <option value="Donación">Donación</option>
                                    <option value="Cobro Proforma">Cobro Proforma</option>
                                    <option value="Otro">Otro Ingreso</option>
                                @else
                                    <option value="Servicio Básico">Servicio Básico</option>
                                    <option value="Sueldo">Sueldo / Planilla Personal</option>
                                    <option value="Gasto Operativo">Gasto Operativo</option>
                                    <option value="Insumos">Insumos de Limpieza/Aseo</option>
                                    <option value="Transporte">Transporte / Encomienda</option>
                                    <option value="Honorario Médico">Honorario Médico</option>
                                    <option value="Compra Farmacia">Compra Farmacia</option>
                                    <option value="Otro">Otro Gasto</option>
                                @endif
                            </select>
                            @error('partida_categoria')
                                <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Fecha de Asiento <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                wire:model="partida_fecha" 
                                class="w-full text-xs font-mono font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-900 dark:text-slate-100 focus:ring-2 {{ $partida_tipo === 'Ingreso' ? 'focus:ring-blue-500' : 'focus:ring-rose-500' }}"
                            >
                            @error('partida_fecha')
                                <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Monto y Referencia -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Monto (Bs.) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">Bs.</span>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0.01" 
                                    wire:model="partida_monto" 
                                    placeholder="0.00"
                                    class="w-full text-sm font-mono font-bold rounded-xl pl-10 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 {{ $partida_tipo === 'Ingreso' ? 'focus:ring-blue-500' : 'focus:ring-rose-500' }}"
                                >
                            </div>
                            @error('partida_monto')
                                <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                N° Referencia / Comprobante
                            </label>
                            <input 
                                type="text" 
                                wire:model="partida_referencia" 
                                placeholder="Opcional (Ej. FAC-1204)"
                                class="w-full text-xs rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 {{ $partida_tipo === 'Ingreso' ? 'focus:ring-blue-500' : 'focus:ring-rose-500' }}"
                            >
                            @error('partida_referencia')
                                <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Concepto -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Concepto / Motivo Detallado <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="partida_concepto" 
                            placeholder="Ej. Pago de luz eléctrica ENDE, Sueldo personal..."
                            class="w-full text-xs rounded-xl px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 {{ $partida_tipo === 'Ingreso' ? 'focus:ring-blue-500' : 'focus:ring-rose-500' }}"
                        >
                        @error('partida_concepto')
                            <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Observaciones Adicionales -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones Adicionales
                        </label>
                        <textarea 
                            wire:model="partida_observaciones" 
                            rows="2" 
                            placeholder="Notas o justificación adicional opcional..."
                            class="w-full text-xs rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 {{ $partida_tipo === 'Ingreso' ? 'focus:ring-blue-500' : 'focus:ring-rose-500' }} resize-none"
                        ></textarea>
                        @error('partida_observaciones')
                            <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="cerrarModalPartida" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            wire:target="guardarPartida"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl {{ $partida_tipo === 'Ingreso' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white font-bold text-xs shadow-md transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="guardarPartida">
                                <i class="fas fa-save text-xs"></i> {{ $editando_partida_id ? 'Actualizar Partida' : 'Guardar Asiento' }}
                            </span>
                            <span wire:loading wire:target="guardarPartida" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-circle-notch fa-spin text-xs"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
