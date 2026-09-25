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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

        <!-- Valor Total Inventario -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400">Valorización Stock</span>
                <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100 font-mono">
                    Bs. {{ number_format($valorTotalInventario, 2) }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Card Principal: Controles y Tabla -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <!-- Barra de Búsqueda y Filtros -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Selector de Paginación -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <span>Mostrar:</span>
                    <select wire:model.live="perPage" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Filtro por Sucursal -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <span>Sede:</span>
                    <select wire:model.live="filtroSucursal" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs">
                        <option value="">Todas las sedes</option>
                        @foreach ($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Proveedor -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <span>Proveedor:</span>
                    <select wire:model.live="filtroProveedor" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs">
                        <option value="">Todos los distribuidores</option>
                        @foreach ($proveedores as $pr)
                            <option value="{{ $pr->id }}">{{ $pr->razon_social }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Limpiar Filtros Rápidos -->
                @if ($filtroVencimiento)
                    <button 
                        wire:click="$set('filtroVencimiento', '')" 
                        type="button" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-300 cursor-pointer"
                    >
                        <span>Todos los vencimientos</span>
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                @endif
            </div>

            <!-- Buscador Reactivo -->
            <div class="relative w-full md:w-80">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por lote, medicamento, proveedor..." 
                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 pl-9 pr-3 py-2 focus:ring-2 focus:ring-teal-500 shadow-2xs placeholder-slate-400"
                />
                <span class="absolute left-3 top-2.5 text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                @if ($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                @endif
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
                                    <button 
                                        wire:click="abrirModalAjuste({{ $lote->id }})" 
                                        type="button" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold transition-colors cursor-pointer"
                                        title="Dar de baja por vencimiento o registrar merma/ajuste"
                                    >
                                        <i class="fas fa-minus-circle text-amber-600"></i>
                                        <span>Ajuste / Merma</span>
                                    </button>
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
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="buscarProducto" 
                                placeholder="Escriba para buscar medicamento o insumo..." 
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs mb-2"
                            />
                            
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
                            @error('producto_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Sucursal y Proveedor -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="sucursal_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Sede de Destino <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    id="sucursal_id" 
                                    wire:model="sucursal_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                >
                                    @foreach ($sucursales as $s)
                                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('sucursal_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
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
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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
</div>
