<div class="space-y-6">
    <!-- Header del Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-boxes text-lg"></i>
                </span>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                    Control de Lotes y Abastecimiento
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Ingreso de compras, seguimiento por fecha de vencimiento y trazabilidad física por sede.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('farmacia.productos') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-750 shadow-xs transition-all"
            >
                <i class="fas fa-pills text-teal-600 dark:text-teal-400"></i>
                <span>Catálogo de Fármacos</span>
            </a>

            <button 
                wire:click="abrirModalLote" 
                wire:loading.attr="disabled"
                type="button" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
                <i class="fas fa-plus"></i>
                <span>Ingresar Nuevo Lote</span>
            </button>
        </div>
    </div>

    <!-- Métricas de Lotes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <!-- Lotes Activos -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-teal-50 dark:bg-teal-950/60 flex items-center justify-center text-teal-600 dark:text-teal-400 text-lg">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400">Lotes con Stock</span>
                <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalLotesActivos }}</h4>
            </div>
        </div>

        <!-- Próximos a Vencer (<= 60 días) -->
        <button 
            wire:click="$set('filtroVencimiento', 'proximos')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-amber-400 shadow-xs flex items-center justify-between {{ $filtroVencimiento === 'proximos' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Por Vencer (&le; 60d)</span>
                    <h4 class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $lotesPorVencer }}</h4>
                </div>
            </div>
            @if ($filtroVencimiento === 'proximos')
                <i class="fas fa-check text-amber-500 text-xs"></i>
            @endif
        </button>

        <!-- Lotes Vencidos -->
        <button 
            wire:click="$set('filtroVencimiento', 'vencidos')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-rose-400 shadow-xs flex items-center justify-between {{ $filtroVencimiento === 'vencidos' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Lotes Vencidos</span>
                    <h4 class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ $lotesVencidos }}</h4>
                </div>
            </div>
            @if ($filtroVencimiento === 'vencidos')
                <i class="fas fa-check text-rose-500 text-xs"></i>
            @endif
        </button>

        <!-- Valorización Stock - Precio Compra -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-400 block leading-tight">Valorización Stock - Precio Compra</span>
                <h4 class="text-base sm:text-lg font-bold text-slate-800 dark:text-slate-100 font-mono mt-0.5">
                    Bs. {{ number_format($valorStockCompra, 2) }}
                </h4>
            </div>
        </div>

        <!-- Valorización Stock - Precio Venta -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <span class="text-[11px] font-medium text-slate-400 block leading-tight">Valorización Stock - Precio Venta</span>
                <h4 class="text-base sm:text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">
                    Bs. {{ number_format($valorStockVenta, 2) }}
                </h4>
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
                        <label for="perPageLotes" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageLotes" 
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

                    <!-- Filtro por Sucursal -->
                    <div class="flex items-center gap-2">
                        <label for="filtroSucLotes" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sede:</label>
                        <select 
                            id="filtroSucLotes" 
                            wire:model.live="filtroSucursal" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todas las sedes</option>
                            @foreach ($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro por Área / Sección -->
                    <div class="flex items-center gap-2">
                        <label for="filtroSecLotes" class="text-xs font-medium text-slate-600 dark:text-slate-400">Área:</label>
                        <select 
                            id="filtroSecLotes" 
                            wire:model.live="filtroSeccion" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todas las áreas (Consolidado)</option>
                            @foreach ($secciones as $sec)
                                <option value="{{ $sec->id }}">
                                    {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '★ (Principal)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro por Proveedor -->
                    <div class="flex items-center gap-2">
                        <label for="filtroProvLotes" class="text-xs font-medium text-slate-600 dark:text-slate-400">Proveedor:</label>
                        <select 
                            id="filtroProvLotes" 
                            wire:model.live="filtroProveedor" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos los distribuidores</option>
                            @foreach ($proveedores as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->razon_social }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Limpiar Filtros Rápidos -->
                    @if ($filtroVencimiento)
                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                        <button 
                            wire:click="$set('filtroVencimiento', '')" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:bg-slate-300 dark:hover:bg-slate-700 cursor-pointer"
                        >
                            <span>Todos los vencimientos</span>
                            <i class="fas fa-times text-[10px]"></i>
                        </button>
                    @endif
                </div>

                <!-- Buscador Debounce -->
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por lote, medicamento, proveedor..." 
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

        <!-- Tabla de Lotes -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Lote / Sede</th>
                        <th class="py-3 px-4">Medicamento / Presentación</th>
                        <th class="py-3 px-4">Proveedor</th>
                        <th class="py-3 px-4 text-center">Existencias Físicas</th>
                        <th class="py-3 px-4 text-center">Fecha Vencimiento</th>
                        <th class="py-3 px-4 text-right">P. Compra / Venta</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($lotes as $lote)
                        @php
                            $vencido = $lote->isVencido();
                            $porVencer = $lote->isPorVencer();
                            $porcentaje = $lote->cantidad_ingresada > 0 ? round(($lote->cantidad_actual / $lote->cantidad_ingresada) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <i class="fas fa-barcode text-teal-600 dark:text-teal-400 text-xs"></i>
                                    {{ $lote->codigo_lote ?? 'S/C' }}
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-hospital text-[10px]"></i>
                                    {{ $lote->sucursal->nombre ?? 'Sede Central' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">
                                    {{ $lote->producto->nombre ?? 'Producto no encontrado' }}
                                </div>
                                <div class="text-[10px] text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span>{{ $lote->producto?->unidad_medida ?? 'Unidad' }}</span>
                                    <span>•</span>
                                    <span class="text-teal-600 dark:text-teal-400">{{ $lote->producto?->marca->nombre ?? 'Genérico' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                {{ $lote->proveedor->razon_social ?? 'Adquisición Directa' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="font-mono font-bold text-xs {{ $lote->cantidad_actual > 0 ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">
                                    {{ $lote->cantidad_actual }} <span class="text-[10px] font-normal text-slate-400">/ {{ $lote->cantidad_ingresada }}</span>
                                </div>
                                <!-- Mini barra de progreso -->
                                <div class="w-20 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mx-auto mt-1.5 overflow-hidden">
                                    <div 
                                        class="h-full rounded-full {{ $lote->cantidad_actual <= 0 ? 'bg-slate-400' : ($porcentaje <= 20 ? 'bg-amber-500' : 'bg-teal-500') }}" 
                                        style="width: {{ min(100, $porcentaje) }}%"
                                    ></div>
                                </div>

                                <!-- Distribución de existencias por áreas/secciones hospitalarias -->
                                @if ($lote->loteSecciones->isNotEmpty())
                                    <div class="flex flex-wrap items-center justify-center gap-1 mt-2 max-w-[220px] mx-auto">
                                        @foreach ($lote->loteSecciones as $ls)
                                            @if ($ls->cantidad_actual > 0)
                                                @php
                                                    $esFiltrada = $filtroSeccion && $filtroSeccion === $ls->seccion_id;
                                                @endphp
                                                <span 
                                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium border transition-all {{ $esFiltrada ? 'bg-teal-100 text-teal-800 border-teal-400 dark:bg-teal-900/60 dark:text-teal-200 ring-1 ring-teal-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}" 
                                                    title="{{ $ls->seccion->nombre }}: {{ $ls->cantidad_actual }} unidades disponibles"
                                                >
                                                    <span class="font-bold text-teal-600 dark:text-teal-400">{{ $ls->cantidad_actual }}</span>
                                                    <span class="truncate max-w-[65px]">{{ $ls->seccion->nombre }}</span>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($lote->fecha_vencimiento)
                                    <div class="font-mono font-semibold {{ $vencido ? 'text-rose-600 dark:text-rose-400' : ($porVencer ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300') }}">
                                        {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                    </div>
                                    <div class="text-[10px] {{ $vencido ? 'text-rose-500 font-bold' : ($porVencer ? 'text-amber-500 font-bold' : 'text-slate-400') }}">
                                        {{ $lote->textoVencimiento() }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">No perecedero</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="text-[11px] text-slate-400 font-mono">
                                    C: Bs. {{ number_format($lote->precio_compra, 2) }}
                                </div>
                                <div class="font-bold text-slate-900 dark:text-emerald-400 font-mono">
                                    V: Bs. {{ number_format($lote->precio_venta, 2) }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($lote->cantidad_actual <= 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        Agotado
                                    </span>
                                @elseif ($vencido)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <i class="fas fa-times-circle me-0.5"></i> Vencido
                                    </span>
                                @elseif ($porVencer)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse">
                                        <i class="fas fa-exclamation-triangle me-0.5"></i> Por Vencer
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <i class="fas fa-check me-0.5"></i> Vigente
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if ($lote->cantidad_actual > 0)
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            wire:click="abrirModalTransferencia({{ $lote->id }})" 
                                            type="button" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 text-teal-700 dark:text-teal-300 text-[11px] font-semibold transition-colors cursor-pointer"
                                            title="Transferir existencias a otra área (ej. Emergencias, Quirófano, Enfermería)"
                                        >
                                            <i class="fas fa-dolly text-teal-600 dark:text-teal-400"></i>
                                            <span>Transferir</span>
                                        </button>

                                        <button 
                                            wire:click="abrirModalAjuste({{ $lote->id }})" 
                                            type="button" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold transition-colors cursor-pointer"
                                            title="Dar de baja por vencimiento o registrar merma/ajuste"
                                        >
                                            <i class="fas fa-minus-circle text-amber-600"></i>
                                            <span>Ajuste</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Sin existencias</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center text-slate-400">
                                <i class="fas fa-boxes text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No se encontraron lotes registrados</p>
                                <p class="text-xs text-slate-400 mt-1">Registre un nuevo lote para abastecer el stock físico de medicamentos.</p>
                                <button 
                                    wire:click="abrirModalLote" 
                                    type="button" 
                                    class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i> Ingresar Primer Lote
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Estándar de Livewire (Sin footers duplicados) -->
        @if ($lotes->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $lotes->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: REGISTRO DE NUEVO LOTE / COMPRA -->
    @if ($modalLoteOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-lote-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas fa-boxes"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-lote-title">
                                Ingreso de Lote y Abastecimiento de Stock
                            </h3>
                        </div>
                        <button wire:click="cerrarModalLote" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit="guardarLote" class="p-6 space-y-4">
                        <!-- Selección de Medicamento / Insumo con Buscador -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Seleccionar Medicamento / Insumo <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative w-full mb-2">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="buscarProducto" 
                                    placeholder="Escriba para buscar medicamento o insumo..." 
                                    class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                />
                                @if ($buscarProducto !== '')
                                    <button wire:click="$set('buscarProducto', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                        <i class="fas fa-times-circle text-xs"></i>
                                    </button>
                                @endif
                            </div>
                            
                            <div class="max-h-36 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                                @forelse ($productosEncontrados as $p)
                                    <button 
                                        type="button" 
                                        wire:click="seleccionarProducto({{ $p->id }})"
                                        class="w-full px-3 py-2 text-left text-xs flex items-center justify-between transition-colors {{ $producto_id === $p->id ? 'bg-teal-100 dark:bg-teal-950/80 font-bold text-teal-900 dark:text-teal-200' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}"
                                    >
                                        <div>
                                            <span>{{ $p->nombre }}</span>
                                            <span class="text-[10px] text-slate-400 ms-1">({{ $p->unidad_medida }} • {{ $p->marca->nombre ?? 'Genérico' }})</span>
                                        </div>
                                        <div class="text-[11px] font-mono text-slate-500">
                                            Venta: Bs. {{ number_format($p->ultimo_precio_venta, 2) }}
                                        </div>
                                    </button>
                                @empty
                                    <div class="p-3 text-center text-xs text-slate-400">
                                        No se encontraron medicamentos coincidentes.
                                    </div>
                                @endforelse
                            </div>
                            @if ($productoSeleccionado)
                                <div class="mt-2 p-2.5 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-check-circle text-teal-600 dark:text-teal-400 text-sm"></i>
                                        <div>
                                            <span class="font-bold text-slate-800 dark:text-slate-100">{{ $productoSeleccionado->nombre }}</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 ms-1">({{ $productoSeleccionado->unidad_medida }} • {{ $productoSeleccionado->marca->nombre ?? 'Genérico' }})</span>
                                        </div>
                                    </div>
                                    <button wire:click="$set('producto_id', null)" type="button" class="text-slate-400 hover:text-rose-500 text-xs p-1" title="Cambiar selección">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                            @error('producto_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sucursal, Área de Ingreso y Proveedor -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="sucursal_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Sede de Destino <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="sucursal_id" 
                                    wire:model.live="sucursal_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                >
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('sucursal_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="seccion_ingreso_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Área Hospitalaria de Entrada <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="seccion_ingreso_id" 
                                    wire:model="seccion_ingreso_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-medium"
                                >
                                    @foreach ($seccionesModalIngreso as $sec)
                                        <option value="{{ $sec->id }}">
                                            {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '★ (Principal)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('seccion_ingreso_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="proveedor_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Proveedor / Distribuidor
                                </label>
                                <select 
                                    id="proveedor_id" 
                                    wire:model="proveedor_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                >
                                    <option value="">Adquisición Directa / Sin Proveedor</option>
                                    @foreach ($proveedores as $pr)
                                        <option value="{{ $pr->id }}">{{ $pr->razon_social }}</option>
                                    @endforeach
                                </select>
                                @error('proveedor_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Código de Lote, Cantidad y Fecha de Vencimiento -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="codigo_lote" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Código de Lote <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="codigo_lote" 
                                    wire:model="codigo_lote" 
                                    placeholder="LOT-2026-X" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                                />
                                @error('codigo_lote') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="cantidad_ingresada" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Cantidad Ingresada <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    min="1" 
                                    id="cantidad_ingresada" 
                                    wire:model="cantidad_ingresada" 
                                    placeholder="10"
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                                />
                                @error('cantidad_ingresada') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="fecha_vencimiento" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha de Vencimiento
                                </label>
                                <input 
                                    type="date" 
                                    id="fecha_vencimiento" 
                                    wire:model="fecha_vencimiento" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono"
                                />
                                @error('fecha_vencimiento') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Precios de Compra y Venta -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="precio_compra" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Precio de Compra Unitario (Bs.) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-bold">Bs.</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        id="precio_compra" 
                                        wire:model="precio_compra" 
                                        placeholder="0.00"
                                        class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 pl-9 pr-3 py-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono"
                                    />
                                </div>
                                @error('precio_compra') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="precio_venta" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Precio de Venta al Paciente (Bs.) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-bold">Bs.</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        id="precio_venta" 
                                        wire:model="precio_venta" 
                                        placeholder="0.00"
                                        class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 pl-9 pr-3 py-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold text-teal-600 dark:text-teal-400"
                                    />
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Actualiza el precio base del catálogo.</span>
                                @error('precio_venta') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Banner Informativo -->
                        <div class="p-3 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex items-start gap-2.5 text-xs text-teal-900 dark:text-teal-200">
                            <i class="fas fa-info-circle text-teal-600 dark:text-teal-400 mt-0.5 shrink-0"></i>
                            <div>
                                <strong>Regla de Inventario:</strong> Al confirmar, se creará el asiento automático de <strong class="font-bold underline">Entrada Compra</strong> en el Kardex y se actualizará el precio de venta del producto inicial.
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                wire:click="cerrarModalLote" 
                                type="button" 
                                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarLote">
                                    <i class="fas fa-save me-1"></i> Registrar Lote e Ingreso
                                </span>
                                <span wire:loading wire:target="guardarLote">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Registrando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: AJUSTE DE INVENTARIO / MERMA DE LOTE -->
    @if ($modalAjusteOpen && $loteAjuste)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-ajuste-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-white text-sm">
                                <i class="fas fa-minus-circle"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-ajuste-title">
                                Dar de Baja / Ajuste de Stock
                            </h3>
                        </div>
                        <button wire:click="cerrarModalAjuste" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit="procesarAjuste" class="p-6 space-y-4">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs">
                            <div class="font-bold text-slate-800 dark:text-white">{{ $loteAjuste->producto->nombre }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Lote: <strong class="font-mono">{{ $loteAjuste->codigo_lote }}</strong> • Stock actual disponible: <strong class="text-teal-600 font-mono">{{ $loteAjuste->cantidad_actual }}</strong> unidades
                            </div>
                        </div>

                        <!-- Área Hospitalaria del Ajuste / Baja -->
                        <div>
                            <label for="seccionAjusteId" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Área Hospitalaria donde ocurrió la Merma/Baja <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                id="seccionAjusteId" 
                                wire:model="seccionAjusteId" 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-amber-500 shadow-2xs font-medium"
                            >
                                @foreach ($loteAjuste->loteSecciones as $ls)
                                    <option value="{{ $ls->seccion_id }}" {{ $ls->cantidad_actual <= 0 ? 'disabled' : '' }}>
                                        {{ $ls->seccion->nombre }} (Stock en área: {{ $ls->cantidad_actual }} un.)
                                    </option>
                                @endforeach
                            </select>
                            @error('seccionAjusteId') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tipo de Ajuste -->
                        <div>
                            <label for="tipoAjuste" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Concepto de la Baja / Ajuste <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                id="tipoAjuste" 
                                wire:model="tipoAjuste" 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-amber-500 shadow-2xs"
                            >
                                <option value="Merma por Vencimiento">Merma por Vencimiento</option>
                                <option value="Ajuste de Inventario">Ajuste de Inventario (Conteo Físico)</option>
                                <option value="Baja por Deterioro">Baja por Deterioro / Rotura de Envase</option>
                            </select>
                            @error('tipoAjuste') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Cantidad a Dar de Baja -->
                        <div>
                            <label for="cantidadAjuste" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Cantidad de Unidades a Descontar <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                min="1" 
                                max="{{ $loteAjuste->cantidad_actual }}"
                                id="cantidadAjuste" 
                                wire:model="cantidadAjuste" 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-amber-500 shadow-2xs font-mono font-bold"
                            />
                            @error('cantidadAjuste') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Motivo / Justificación -->
                        <div>
                            <label for="motivoAjuste" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Motivo / Justificación Clínica <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                id="motivoAjuste" 
                                wire:model="motivoAjuste" 
                                rows="3" 
                                placeholder="Indique la causa de la merma o número de acta de baja..." 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-amber-500 shadow-2xs"
                            ></textarea>
                            @error('motivoAjuste') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                wire:click="cerrarModalAjuste" 
                                type="button" 
                                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-md shadow-amber-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="procesarAjuste">
                                    <i class="fas fa-check me-1"></i> Aplicar Ajuste
                                </span>
                                <span wire:loading wire:target="procesarAjuste">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: TRANSFERENCIA ENTRE SECCIONES HOSPITALARIAS -->
    @if ($modalTransferenciaOpen && $loteTransferencia)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-transf-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-teal-50 dark:bg-teal-950/40 border-b border-teal-200 dark:border-teal-900/60 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm shadow-xs">
                                <i class="fas fa-dolly"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-transf-title">
                                    Transferencia Interna entre Áreas
                                </h3>
                                <p class="text-[11px] text-teal-700 dark:text-teal-300">
                                    Distribución de existencias con trazabilidad y Kardex
                                </p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalTransferencia" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit="transferirStock" class="p-6 space-y-4">
                        <!-- Ficha del Medicamento y Lote -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-white text-sm">
                                        {{ $loteTransferencia->producto->nombre }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $loteTransferencia->producto?->unidad_medida }} • {{ $loteTransferencia->producto?->marca->nombre ?? 'Genérico' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-xs bg-teal-100 dark:bg-teal-950/80 text-teal-800 dark:text-teal-200 px-2 py-0.5 rounded border border-teal-200 dark:border-teal-800">
                                        {{ $loteTransferencia->codigo_lote }}
                                    </span>
                                </div>
                            </div>

                            <!-- Tabla de distribución actual -->
                            <div class="mt-3 pt-2.5 border-t border-slate-200 dark:border-slate-700/80">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
                                    Existencias actuales de este lote por área:
                                </span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5">
                                    @foreach ($loteTransferencia->loteSecciones as $ls)
                                        <div class="px-2 py-1 rounded bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/60 text-[11px] flex items-center justify-between">
                                            <span class="text-slate-600 dark:text-slate-400 truncate max-w-[90px]">{{ $ls->seccion->nombre }}</span>
                                            <span class="font-bold font-mono {{ $ls->cantidad_actual > 0 ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}">{{ $ls->cantidad_actual }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Sección Origen y Sección Destino -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="seccion_origen_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Área de Origen (Emisora) <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="seccion_origen_id" 
                                    wire:model.live="seccion_origen_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-medium"
                                >
                                    @foreach ($loteTransferencia->loteSecciones as $ls)
                                        <option value="{{ $ls->seccion_id }}" {{ $ls->cantidad_actual <= 0 ? 'disabled' : '' }}>
                                            {{ $ls->seccion->nombre }} (Disp: {{ $ls->cantidad_actual }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('seccion_origen_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="seccion_destino_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Área de Destino (Receptora) <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="seccion_destino_id" 
                                    wire:model="seccion_destino_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-medium"
                                >
                                    <option value="">Seleccione área destino...</option>
                                    @foreach ($seccionesDestino as $sDest)
                                        <option value="{{ $sDest->id }}">
                                            {{ $sDest->nombre }} {{ $sDest->es_almacen_principal ? '★ (Principal)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('seccion_destino_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Cantidad a Transferir -->
                        <div>
                            @php
                                $stockOrigenActual = $loteTransferencia->loteSecciones->firstWhere('seccion_id', $seccion_origen_id)?->cantidad_actual ?? 0;
                            @endphp
                            <div class="flex items-center justify-between mb-1">
                                <label for="cantidad_transferir" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Cantidad a Transferir <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[11px] text-slate-400">
                                    Disponible en origen: <strong class="text-teal-600 font-mono">{{ $stockOrigenActual }}</strong> unidades
                                </span>
                            </div>
                            <input 
                                type="number" 
                                min="1" 
                                max="{{ $stockOrigenActual }}"
                                id="cantidad_transferir" 
                                wire:model="cantidad_transferir" 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                            />
                            @error('cantidad_transferir') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Motivo / Justificación -->
                        <div>
                            <label for="motivo_transferencia" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Motivo de la Transferencia / Solicitante
                            </label>
                            <input 
                                type="text" 
                                id="motivo_transferencia" 
                                wire:model="motivo_transferencia" 
                                placeholder="Ej: Reposición para Quirófano, pedido de Emergencias..." 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                            />
                            @error('motivo_transferencia') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                wire:click="cerrarModalTransferencia" 
                                type="button" 
                                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="transferirStock">
                                    <i class="fas fa-dolly me-1"></i> Confirmar Transferencia
                                </span>
                                <span wire:loading wire:target="transferirStock">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Transfiriendo...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
