<div class="space-y-6">
    <!-- Header del Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-pills text-lg"></i>
                </span>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                    Catálogo de Medicamentos e Insumos
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Administración de fármacos, presentaciones, laboratorios, precios de referencia y umbrales de reabastecimiento.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('farmacia.lotes') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-750 shadow-xs transition-all"
            >
                <i class="fas fa-boxes text-teal-600 dark:text-teal-400"></i>
                <span>Gestión de Lotes</span>
            </a>

            <button 
                wire:click="abrirModalProducto" 
                wire:loading.attr="disabled"
                type="button" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
                <i class="fas fa-plus"></i>
                <span>Nuevo Medicamento / Insumo</span>
            </button>
        </div>
    </div>

    <!-- Métricas del Catálogo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Productos -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-teal-50 dark:bg-teal-950/60 flex items-center justify-center text-teal-600 dark:text-teal-400 text-lg">
                <i class="fas fa-prescription-bottle-alt"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400">Total Artículos</span>
                <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalProductos }}</h4>
            </div>
        </div>

        <!-- Filtro Rápido: Stock Normal -->
        <button 
            wire:click="$set('filtroStock', 'normal')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-emerald-400 shadow-xs flex items-center justify-between {{ $filtroStock === 'normal' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Stock Óptimo</span>
                    <h4 class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Filtrar Normales</h4>
                </div>
            </div>
            @if ($filtroStock === 'normal')
                <i class="fas fa-check text-emerald-500 text-xs"></i>
            @endif
        </button>

        <!-- Filtro Rápido: Stock Crítico -->
        <button 
            wire:click="$set('filtroStock', 'critico')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-amber-400 shadow-xs flex items-center justify-between {{ $filtroStock === 'critico' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Stock Crítico</span>
                    <h4 class="text-xs font-bold text-amber-600 dark:text-amber-400 mt-0.5">Bajo Umbral</h4>
                </div>
            </div>
            @if ($filtroStock === 'critico')
                <i class="fas fa-check text-amber-500 text-xs"></i>
            @endif
        </button>

        <!-- Filtro Rápido: Agotados -->
        <button 
            wire:click="$set('filtroStock', 'agotado')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-rose-400 shadow-xs flex items-center justify-between {{ $filtroStock === 'agotado' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-rose-50 dark:bg-rose-950/60 flex items-center justify-center text-rose-600 dark:text-rose-400 text-lg">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Sin Existencias</span>
                    <h4 class="text-xs font-bold text-rose-600 dark:text-rose-400 mt-0.5">Stock Cero</h4>
                </div>
            </div>
            @if ($filtroStock === 'agotado')
                <i class="fas fa-check text-rose-500 text-xs"></i>
            @endif
        </button>
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

                <!-- Filtro por Laboratorio / Marca -->
                <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <span>Marca/Lab:</span>
                    <select wire:model.live="filtroMarca" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs">
                        <option value="">Todas las marcas</option>
                        @foreach ($marcas as $m)
                            <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Estado Stock -->
                @if ($filtroStock)
                    <button 
                        wire:click="$set('filtroStock', '')" 
                        type="button" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-300 cursor-pointer"
                    >
                        <span>Limpiar filtro stock</span>
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                @endif
            </div>

            <!-- Buscador Reactivo -->
            <div class="relative w-full md:w-80">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por fármaco, marca, unidad..." 
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

        <!-- Tabla de Productos -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Medicamento / Insumo</th>
                        <th class="py-3 px-4">Laboratorio / Marca</th>
                        <th class="py-3 px-4">Presentación</th>
                        <th class="py-3 px-4 text-right">Último Precio Venta</th>
                        <th class="py-3 px-4 text-center">Stock Mínimo</th>
                        <th class="py-3 px-4 text-center">Stock Físico Total</th>
                        <th class="py-3 px-4 text-center">Lotes Activos</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($productos as $prod)
                        @php
                            $stock = (int) ($prod->stock_total ?? 0);
                            $minimo = (int) $prod->stock_minimo;
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">
                                    {{ $prod->nombre }}
                                </div>
                                @if ($prod->descripcion)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">
                                        {{ $prod->descripcion }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if ($prod->marca)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        <i class="fas fa-tag text-[9px] text-teal-500"></i>
                                        {{ $prod->marca->nombre }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Genérico / Sin Marca</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700 dark:text-slate-300">
                                <span class="px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 font-mono text-[11px]">
                                    {{ $prod->unidad_medida ?? 'Unidad' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-emerald-400">
                                Bs. {{ number_format($prod->ultimo_precio_venta, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-500">
                                {{ $prod->stock_minimo }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($stock <= 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <i class="fas fa-exclamation-circle text-[9px]"></i>
                                        Agotado (0)
                                    </span>
                                @elseif ($stock <= $minimo)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse">
                                        <i class="fas fa-exclamation-triangle text-[9px]"></i>
                                        Crítico ({{ $stock }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <i class="fas fa-check text-[9px]"></i>
                                        Disponible ({{ $stock }})
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button 
                                    wire:click="verLotes({{ $prod->id }})" 
                                    type="button" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[11px] font-semibold transition-colors cursor-pointer"
                                    title="Ver desglose de lotes físicos y fechas de vencimiento"
                                >
                                    <i class="fas fa-layer-group text-teal-600 dark:text-teal-400"></i>
                                    <span>Ver Lotes</span>
                                </button>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button 
                                        wire:click="abrirModalProducto({{ $prod->id }})" 
                                        type="button" 
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors"
                                        title="Editar medicamento / insumo"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        wire:click="eliminarProducto({{ $prod->id }})" 
                                        wire:confirm="¿Está seguro de que desea eliminar este artículo del catálogo? Esta acción no se puede deshacer si tiene existencias."
                                        type="button" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition-colors"
                                        title="Eliminar del catálogo"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center text-slate-400">
                                <i class="fas fa-prescription-bottle-alt text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No se encontraron medicamentos o insumos</p>
                                <p class="text-xs text-slate-400 mt-1">Pruebe modificando los términos de búsqueda o agregue un nuevo artículo.</p>
                                <button 
                                    wire:click="abrirModalProducto" 
                                    type="button" 
                                    class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i> Registrar Primer Medicamento
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Estándar de Livewire (Sin footers duplicados) -->
        @if ($productos->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $productos->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: REGISTRO / EDICIÓN DE MEDICAMENTO O INSUMO -->
    @if ($modalProductoOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-prod" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas {{ $productoId ? 'fa-edit' : 'fa-plus' }}"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-prod">
                                {{ $productoId ? 'Editar Medicamento / Insumo' : 'Registrar Nuevo Medicamento o Insumo' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarModalProducto" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit="guardarProducto" class="p-6 space-y-4">
                        <!-- Nombre del Medicamento -->
                        <div>
                            <label for="nombre" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nombre del Medicamento / Insumo <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nombre" 
                                wire:model="nombre" 
                                placeholder="Ej: Paracetamol 500mg, Ceftriaxona 1g, Jeringa 10ml"
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                            />
                            @error('nombre') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Marca / Laboratorio y Unidad de Medida -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="marca_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Laboratorio / Fabricante
                                </label>
                                <select 
                                    id="marca_id" 
                                    wire:model="marca_id" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                >
                                    <option value="">Seleccione Laboratorio (Opcional)</option>
                                    @foreach ($marcas as $m)
                                        <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('marca_id') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="unidad_medida" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Presentación / Unidad <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="unidad_medida" 
                                    wire:model="unidad_medida" 
                                    placeholder="Ej: Tableta, Ampolla, Frasco, Unidad, Caja"
                                    list="unidades-sugeridas"
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                />
                                <datalist id="unidades-sugeridas">
                                    <option value="Tableta">
                                    <option value="Cápsula">
                                    <option value="Ampolla">
                                    <option value="Frasco">
                                    <option value="Solución EV">
                                    <option value="Unidad">
                                    <option value="Tubo">
                                    <option value="Gotero">
                                </datalist>
                                @error('unidad_medida') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Precios y Stock Mínimo -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="ultimo_precio_venta" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Precio de Venta Base (Bs.) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs font-bold">Bs.</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        id="ultimo_precio_venta" 
                                        wire:model="ultimo_precio_venta" 
                                        class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 pl-9 pr-3 py-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                                    />
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Se actualiza automáticamente al ingresar lotes nuevos.</span>
                                @error('ultimo_precio_venta') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="stock_minimo" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Stock Mínimo (Alerta) <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="stock_minimo" 
                                    min="0"
                                    wire:model="stock_minimo" 
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono"
                                />
                                <span class="text-[10px] text-slate-400 mt-1 block">Dispara alerta amarilla/roja en el inventario.</span>
                                @error('stock_minimo') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Descripción / Indicaciones Clínicas -->
                        <div>
                            <label for="descripcion" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Descripción / Principio Activo / Notas
                            </label>
                            <textarea 
                                id="descripcion" 
                                wire:model="descripcion" 
                                rows="3" 
                                placeholder="Composición química, vía de administración o indicaciones generales..."
                                class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                            ></textarea>
                            @error('descripcion') <span class="text-rose-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                wire:click="cerrarModalProducto" 
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
                                <span wire:loading.remove wire:target="guardarProducto">
                                    <i class="fas fa-save me-1"></i> {{ $productoId ? 'Guardar Cambios' : 'Registrar Producto' }}
                                </span>
                                <span wire:loading wire:target="guardarProducto">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: DETALLE DE LOTES FÍSICOS Y FECHAS DE VENCIMIENTO -->
    @if ($modalLotesOpen && $productoSeleccionado)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-lotes" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2" id="modal-lotes">
                                <i class="fas fa-boxes text-teal-600 dark:text-teal-400"></i>
                                Lotes Físicos: {{ $productoSeleccionado->nombre }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Presentación: <strong>{{ $productoSeleccionado->unidad_medida }}</strong> • Laboratorio: <strong>{{ $productoSeleccionado->marca->nombre ?? 'Genérico' }}</strong>
                            </p>
                        </div>
                        <button wire:click="cerrarModalLotes" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4">
                        @if ($productoSeleccionado->lotes->isNotEmpty())
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700 text-[10px] uppercase">
                                        <tr>
                                            <th class="py-2.5 px-3">Código Lote</th>
                                            <th class="py-2.5 px-3">Sede / Sucursal</th>
                                            <th class="py-2.5 px-3 text-center">Stock Actual</th>
                                            <th class="py-2.5 px-3 text-center">Vencimiento</th>
                                            <th class="py-2.5 px-3 text-right">Precio Venta</th>
                                            <th class="py-2.5 px-3 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @foreach ($productoSeleccionado->lotes as $lote)
                                            @php
                                                $vencido = $lote->isVencido();
                                                $porVencer = $lote->isPorVencer();
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                                <td class="py-2.5 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                                    {{ $lote->codigo_lote ?? 'SIN CÓDIGO' }}
                                                </td>
                                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                                    {{ $lote->sucursal->nombre ?? 'Sede Central' }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-xs {{ $lote->cantidad_actual > 0 ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}">
                                                    {{ $lote->cantidad_actual }} / {{ $lote->cantidad_ingresada }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono text-[11px]">
                                                    {{ $lote->fecha_vencimiento?->format('d/m/Y') ?? 'Sin fecha' }}
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-800 dark:text-slate-200">
                                                    Bs. {{ number_format($lote->precio_venta, 2) }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    @if ($lote->cantidad_actual <= 0)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                            Agotado
                                                        </span>
                                                    @elseif ($vencido)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                            Vencido
                                                        </span>
                                                    @elseif ($porVencer)
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse">
                                                            Próximo a Vencer
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                            Vigente
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-6 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-center text-slate-400">
                                <i class="fas fa-boxes text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                <p class="font-medium text-slate-700 dark:text-slate-300">No existen lotes registrados para este producto.</p>
                                <p class="text-xs text-slate-400 mt-1">Diríjase al módulo de Lotes para registrar el primer abastecimiento.</p>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                            <button 
                                wire:click="cerrarModalLotes" 
                                type="button" 
                                class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition-colors cursor-pointer"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
