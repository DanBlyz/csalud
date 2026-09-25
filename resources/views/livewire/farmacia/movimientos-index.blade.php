<div class="space-y-6">
    <!-- Header del Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-clipboard-list text-lg"></i>
                </span>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                    Kardex y Trazabilidad de Inventario
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Auditoría completa de movimientos de stock: entradas por compra, salidas por recetas de proformas, consumos y mermas.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('farmacia.despachos') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-750 shadow-xs transition-all"
            >
                <i class="fas fa-hand-holding-medical text-teal-600 dark:text-teal-400"></i>
                <span>Bandeja de Despacho</span>
            </a>

            <a 
                href="{{ route('farmacia.lotes') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all"
            >
                <i class="fas fa-plus"></i>
                <span>Ingreso de Lote</span>
            </a>
        </div>
    </div>

    <!-- Métricas del Kardex -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Movimientos -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="h-11 w-11 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 text-lg">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400">Total Operaciones</span>
                <h4 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalMovimientos }}</h4>
            </div>
        </div>

        <!-- Entradas (Compras) -->
        <button 
            wire:click="$set('filtroTipo', 'Entrada Compra')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-emerald-400 shadow-xs flex items-center justify-between {{ $filtroTipo === 'Entrada Compra' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Entradas / Compras</span>
                    <h4 class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $totalEntradas }}</h4>
                </div>
            </div>
            @if ($filtroTipo === 'Entrada Compra')
                <i class="fas fa-check text-emerald-500 text-xs"></i>
            @endif
        </button>

        <!-- Salidas Clínicas (Recetas + Consumos) -->
        <button 
            wire:click="$set('filtroTipo', 'Salida Receta')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-teal-400 shadow-xs flex items-center justify-between {{ $filtroTipo === 'Salida Receta' ? 'border-teal-500 ring-2 ring-teal-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-teal-50 dark:bg-teal-950/60 flex items-center justify-center text-teal-600 dark:text-teal-400 text-lg">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Salidas por Recetas</span>
                    <h4 class="text-lg font-bold text-teal-600 dark:text-teal-400">{{ $totalSalidas }}</h4>
                </div>
            </div>
            @if ($filtroTipo === 'Salida Receta')
                <i class="fas fa-check text-teal-500 text-xs"></i>
            @endif
        </button>

        <!-- Mermas y Ajustes -->
        <button 
            wire:click="$set('filtroTipo', 'Merma por Vencimiento')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-amber-400 shadow-xs flex items-center justify-between {{ $filtroTipo === 'Merma por Vencimiento' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Mermas / Bajas</span>
                    <h4 class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $totalMermas }}</h4>
                </div>
            </div>
            @if ($filtroTipo === 'Merma por Vencimiento')
                <i class="fas fa-check text-amber-500 text-xs"></i>
            @endif
        </button>
    </div>

    <!-- Card Principal: Controles y Tabla Kardex -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <!-- Filtros Multicriterio -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col gap-3 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5">
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

                    <!-- Filtro por Sede -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span>Sede:</span>
                        <select wire:model.live="filtroSucursal" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs">
                            <option value="">Todas las sedes</option>
                            @foreach ($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Tipo de Movimiento -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span>Concepto:</span>
                        <select wire:model.live="filtroTipo" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs">
                            <option value="">Todos los conceptos</option>
                            <option value="Entrada Compra">Entrada Compra</option>
                            <option value="Salida Receta">Salida Receta (Proforma)</option>
                            <option value="Consumo Extra">Consumo Extra (Piso)</option>
                            <option value="Merma por Vencimiento">Merma por Vencimiento</option>
                            <option value="Ajuste de Inventario">Ajuste de Inventario</option>
                            <option value="Baja por Deterioro">Baja por Deterioro</option>
                        </select>
                    </div>

                    <!-- Filtro por Medicamento -->
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span>Medicamento:</span>
                        <select wire:model.live="filtroProducto" class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-1.5 shadow-2xs max-w-xs">
                            <option value="">Todos los medicamentos</option>
                            @foreach ($productos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Buscador Reactivo -->
                <div class="relative w-full sm:w-72">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por lote, medicamento, paciente..." 
                        class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 pl-9 pr-3 py-1.5 focus:ring-2 focus:ring-teal-500 shadow-2xs placeholder-slate-400"
                    />
                    <span class="absolute left-3 top-2 text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    @if ($search)
                        <button wire:click="$set('search', '')" class="absolute right-3 top-2 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filtro de Fechas -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-200/60 dark:border-slate-800/60 text-xs">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                        <span>Desde:</span>
                        <input 
                            type="date" 
                            wire:model.live="fechaDesde" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 shadow-2xs font-mono"
                        />
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                        <span>Hasta:</span>
                        <input 
                            type="date" 
                            wire:model.live="fechaHasta" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 shadow-2xs font-mono"
                        />
                    </div>
                </div>

                @if ($filtroTipo || $filtroProducto || $fechaDesde || $fechaHasta || $search)
                    <button 
                        wire:click="limpiarFiltros" 
                        type="button" 
                        class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 font-semibold cursor-pointer"
                    >
                        <i class="fas fa-trash-alt text-[10px]"></i>
                        <span>Limpiar todos los filtros</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Tabla Kardex -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Fecha y Hora</th>
                        <th class="py-3 px-4">Concepto / Tipo</th>
                        <th class="py-3 px-4">Medicamento / Insumo</th>
                        <th class="py-3 px-4">Lote Físico</th>
                        <th class="py-3 px-4 text-center">Cantidad</th>
                        <th class="py-3 px-4">Destino / Paciente / Proforma</th>
                        <th class="py-3 px-4 text-right">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($movimientos as $mov)
                        @php
                            $esEntrada = str_contains($mov->tipo_movimiento, 'Entrada');
                            $esMerma = str_contains($mov->tipo_movimiento, 'Merma') || str_contains($mov->tipo_movimiento, 'Baja');
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                {{ $mov->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($esEntrada)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <i class="fas fa-arrow-down text-[9px]"></i>
                                        {{ $mov->tipo_movimiento }}
                                    </span>
                                @elseif ($mov->tipo_movimiento === 'Salida Receta')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                        <i class="fas fa-arrow-up text-[9px]"></i>
                                        Salida Receta
                                    </span>
                                @elseif ($mov->tipo_movimiento === 'Consumo Extra')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                        <i class="fas fa-syringe text-[9px]"></i>
                                        Consumo Piso
                                    </span>
                                @elseif ($esMerma)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <i class="fas fa-calendar-times text-[9px]"></i>
                                        {{ $mov->tipo_movimiento }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        <i class="fas fa-sliders-h text-[9px]"></i>
                                        {{ $mov->tipo_movimiento }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">
                                    {{ $mov->producto->nombre ?? 'Producto eliminado' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $mov->producto?->unidad_medida }} • {{ $mov->producto?->marca->nombre ?? 'Genérico' }}
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                {{ $mov->lote->codigo_lote ?? 'S/L' }}
                                <div class="text-[10px] text-slate-400 font-normal">
                                    Vence: {{ $mov->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-xs">
                                @if ($esEntrada)
                                    <span class="text-emerald-600 dark:text-emerald-400">
                                        +{{ $mov->cantidad }}
                                    </span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400">
                                        -{{ $mov->cantidad }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                @if ($mov->proforma)
                                    <div>
                                        <a 
                                            href="{{ route('proformas.show', $mov->proforma_id) }}" 
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold"
                                        >
                                            Proforma #{{ $mov->proforma_id }}
                                        </a>
                                        <span class="text-slate-400 text-[10px]">
                                            ({{ $mov->proforma->paciente->nombre_completo ?? 'Paciente' }})
                                        </span>
                                    </div>
                                    @if ($mov->receta_id)
                                        <div class="text-[10px] text-slate-400">
                                            Receta médica vinculada #{{ $mov->receta_id }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Abastecimiento / Ajuste General</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-300">
                                <div class="font-medium">
                                    {{ $mov->usuario->name ?? 'Sistema' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $mov->sucursal->nombre ?? 'Central' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center text-slate-400">
                                <i class="fas fa-clipboard-list text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No se encontraron movimientos registrados en Kardex</p>
                                <p class="text-xs text-slate-400 mt-1">Las entradas por lotes y las salidas por recetas aparecerán listadas aquí.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Estándar de Livewire (Sin footers duplicados) -->
        @if ($movimientos->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>
</div>
