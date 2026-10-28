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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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

        <!-- Devoluciones a Stock -->
        <button 
            wire:click="$set('filtroTipo', 'Devolución')" 
            type="button"
            class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all hover:border-indigo-400 shadow-xs flex items-center justify-between {{ $filtroTipo === 'Devolución' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-800' }}"
        >
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-400">Devoluciones</span>
                    <h4 class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $totalDevoluciones }}</h4>
                </div>
            </div>
            @if ($filtroTipo === 'Devolución')
                <i class="fas fa-check text-indigo-500 text-xs"></i>
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

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 space-y-3.5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Selector de Paginación -->
                    <div class="flex items-center gap-2">
                        <label for="perPageMov" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageMov" 
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

                    <!-- Filtro por Sede -->
                    <div class="flex items-center gap-2">
                        <label for="filtroSucMov" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sede:</label>
                        <select 
                            id="filtroSucMov" 
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

                    <!-- Filtro por Sección / Área Hospitalaria -->
                    <div class="flex items-center gap-2">
                        <label for="filtroSecMov" class="text-xs font-medium text-slate-600 dark:text-slate-400">Área:</label>
                        <select 
                            id="filtroSecMov" 
                            wire:model.live="filtroSeccion" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todas las áreas</option>
                            @foreach ($secciones as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Principal)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro por Tipo de Movimiento -->
                    <div class="flex items-center gap-2">
                        <label for="filtroTipoMov" class="text-xs font-medium text-slate-600 dark:text-slate-400">Concepto:</label>
                        <select 
                            id="filtroTipoMov" 
                            wire:model.live="filtroTipo" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos los conceptos</option>
                            <option value="Entrada Compra">Entrada Compra</option>
                            <option value="Salida Receta">Salida Receta (Proforma)</option>
                            <option value="Consumo Extra">Consumo Extra (Piso)</option>
                            <option value="Transferencia Interna">Transferencia Interna</option>
                            <option value="Devolución">Devolución a Stock</option>
                            <option value="Merma por Vencimiento">Merma por Vencimiento</option>
                            <option value="Ajuste de Inventario">Ajuste de Inventario</option>
                            <option value="Baja por Deterioro">Baja por Deterioro</option>
                        </select>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro por Medicamento -->
                    <div class="flex items-center gap-2">
                        <label for="filtroProdMov" class="text-xs font-medium text-slate-600 dark:text-slate-400">Medicamento:</label>
                        <select 
                            id="filtroProdMov" 
                            wire:model.live="filtroProducto" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs max-w-xs"
                        >
                            <option value="">Todos los medicamentos</option>
                            @foreach ($productos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
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
                        placeholder="Buscar por lote, medicamento, paciente..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
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
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($movimientos as $mov)
                        @php
                            $esDevolucion = in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion'], true);
                            $esEntrada = str_contains($mov->tipo_movimiento, 'Entrada') || str_contains($mov->tipo_movimiento, 'Ajuste') || $esDevolucion;
                            $esMerma = str_contains($mov->tipo_movimiento, 'Merma') || str_contains($mov->tipo_movimiento, 'Baja');
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                {{ $mov->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($esDevolucion)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                        <i class="fas fa-undo-alt text-[9px]"></i>
                                        Devolución a Stock
                                    </span>
                                @elseif ($mov->tipo_movimiento === 'Transferencia Interna')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                        <i class="fas fa-dolly text-[9px]"></i>
                                        Transferencia Interna
                                    </span>
                                @elseif ($esEntrada)
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
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
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
                                @if ($mov->tipo_movimiento === 'Transferencia Interna')
                                    <span class="text-blue-600 dark:text-blue-400">
                                        &plusmn;{{ $mov->cantidad }}
                                    </span>
                                @elseif ($esEntrada)
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
                                @if ($mov->tipo_movimiento === 'Transferencia Interna')
                                    <div class="flex items-center gap-1 text-[11px] font-medium text-slate-700 dark:text-slate-200">
                                        <span class="text-slate-500">{{ $mov->seccionOrigen?->nombre ?? 'Área Origen' }}</span>
                                        <i class="fas fa-arrow-right text-[10px] text-blue-500"></i>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $mov->seccionDestino?->nombre ?? 'Área Destino' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Traspaso interno hospitalario
                                    </div>
                                @elseif ($mov->proforma)
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
                                    @if ($mov->seccionOrigen)
                                        <div class="text-[10px] text-amber-600 dark:text-amber-400 font-medium mt-0.5">
                                            <i class="fas fa-map-marker-alt text-[9px] me-0.5"></i> Salida de: <strong>{{ $mov->seccionOrigen->nombre }}</strong>
                                        </div>
                                    @endif
                                    @if ($esDevolucion && $mov->seccionDestino)
                                        <div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium mt-0.5">
                                            <i class="fas fa-undo text-[9px] me-0.5"></i> Reingresó a: <strong>{{ $mov->seccionDestino->nombre }}</strong>
                                        </div>
                                    @endif
                                @else
                                    @if ($mov->seccionDestino)
                                        <div class="text-[11px]">
                                            Destino: <strong class="text-teal-600 dark:text-teal-400">{{ $mov->seccionDestino->nombre }}</strong>
                                        </div>
                                    @elseif ($mov->seccionOrigen)
                                        <div class="text-[11px]">
                                            Origen: <strong class="text-amber-600 dark:text-amber-400">{{ $mov->seccionOrigen->nombre }}</strong>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Abastecimiento / Ajuste General</span>
                                    @endif
                                @endif

                                @if (!empty($mov->observaciones))
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 italic mt-0.5 flex items-center gap-1">
                                        <i class="fas fa-info-circle text-[9px] text-slate-400"></i>
                                        <span>{{ $mov->observaciones }}</span>
                                    </div>
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
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if ($mov->puedeSerDevuelto())
                                    @if (Auth::user()?->tieneAlgunPermiso(['farmacia.despachos.revertir', 'despachar-farmacia']))
                                        <button 
                                            type="button" 
                                            wire:click="abrirModalDevolucion({{ $mov->id }})" 
                                            title="Procesar devolución de dosis no administrada o alta anticipada (Disponibles: {{ $mov->cantidadDisponibleParaDevolucion() }} unids)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold shadow-2xs transition-all cursor-pointer transform active:scale-95"
                                        >
                                            <i class="fas fa-undo text-[10px]"></i>
                                            <span>Devolver</span>
                                        </button>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Sin permiso</span>
                                    @endif
                                @elseif ($mov->proforma_id && in_array($mov->tipo_movimiento, ['Salida Receta', 'Consumo Extra'], true))
                                    @if ($mov->totalDevuelto() >= $mov->cantidad)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                            <i class="fas fa-check-double text-[9px] text-emerald-500"></i> Devuelto
                                        </span>
                                    @elseif ($mov->proforma && $mov->proforma->estado !== 'En Curso')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500" title="Proforma {{ $mov->proforma->estado }}">
                                            <i class="fas fa-lock text-[9px]"></i> {{ $mov->proforma->estado }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">-</span>
                                    @endif
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center text-slate-400">
                                <i class="fas fa-clipboard-list text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No se encontraron movimientos registrados en Kardex</p>
                                <p class="text-xs text-slate-400 mt-1">Las entradas por lotes, salidas por recetas y devoluciones aparecerán listadas aquí.</p>
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

    <!-- ========================================== -->
    <!-- MODAL DE DEVOLUCIÓN DE MEDICAMENTO/INSUMO  -->
    <!-- ========================================== -->
    @if ($modalDevolucionOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4 sm:p-6">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-xs transition-opacity" wire:click="cerrarModalDevolucion"></div>

            <!-- Modal Panel -->
            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden z-10 transition-all transform animate-in fade-in zoom-in-95 duration-150">
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/40 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 shadow-xs">
                            <i class="fas fa-undo-alt text-lg"></i>
                        </span>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                Devolución de Medicamento / Insumo
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Reingreso a inventario por alta anticipada o tratamiento no completado.
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="cerrarModalDevolucion" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-5 space-y-4">
                    <!-- Tarjeta Informativa del Despacho Original -->
                    <div class="p-3.5 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 space-y-2">
                        <div class="flex items-center justify-between gap-2 border-b border-indigo-200/50 dark:border-indigo-800/50 pb-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                    Paciente y Proforma
                                </span>
                                <div class="font-bold text-xs text-slate-900 dark:text-white">
                                    {{ $dev_paciente_nombre }}
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                Proforma #{{ $dev_proforma_id }} (En Curso)
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs pt-0.5">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Fármaco / Presentación:</span>
                                <strong class="text-slate-800 dark:text-slate-100 text-[11px]">{{ $dev_producto_nombre }}</strong>
                                <span class="text-[10px] text-slate-400 block">{{ $dev_producto_unidad }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Lote Físico:</span>
                                <strong class="font-mono text-slate-800 dark:text-slate-100 text-[11px]">{{ $dev_lote_codigo }}</strong>
                                <span class="text-[10px] text-slate-400 block">Vto: {{ $dev_lote_vence }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-indigo-200/50 dark:border-indigo-800/50 text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400">Área Origen: <strong>{{ $dev_seccion_origen_nombre }}</strong></span>
                            <span class="text-slate-600 dark:text-slate-300">
                                Despachado: <strong>{{ $dev_cantidad_original }}</strong> | Retornable: <strong class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $dev_cantidad_disponible }} unids.</strong>
                            </span>
                        </div>
                    </div>

                    <!-- Formulario de Devolución -->
                    <form wire:submit.prevent="procesarDevolucion" class="space-y-4">
                        <!-- Cantidad a Devolver con Selector Rápido -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="dev_cantidad" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Cantidad a Devolver <span class="text-rose-500">*</span>
                                </label>
                                <button 
                                    type="button" 
                                    wire:click="setDevolverTodo"
                                    class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
                                >
                                    Devolver Todo ({{ $dev_cantidad_disponible }} unids.)
                                </button>
                            </div>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    id="dev_cantidad" 
                                    wire:model="devolucion_cantidad"
                                    min="1" 
                                    max="{{ $dev_cantidad_disponible }}"
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 font-mono font-bold focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                                    required
                                />
                                <span class="absolute inset-y-0 right-3 flex items-center text-xs text-slate-400 font-medium pointer-events-none">
                                    unidades
                                </span>
                            </div>
                            @error('devolucion_cantidad')
                                <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-[10px] text-slate-400 mt-1">
                                Puede devolver la totalidad o solo las unidades sobrantes no administradas.
                            </p>
                        </div>

                        <!-- Sección / Depósito de Reingreso -->
                        <div>
                            <label for="dev_seccion" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Área / Sección de Reingreso <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                id="dev_seccion" 
                                wire:model="devolucion_seccion_id"
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                                required
                            >
                                <option value="">Seleccione sección hospitalaria</option>
                                @foreach ($secciones as $sec)
                                    <option value="{{ $sec->id }}">
                                        {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Almacén Principal)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('devolucion_seccion_id')
                                <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-[10px] text-slate-400 mt-1">
                                El stock reingresará físicamente a la sección seleccionada y se sumará a su kardex.
                            </p>
                        </div>

                        <!-- Motivo / Justificación -->
                        <div>
                            <label for="dev_motivo" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Motivo de la Devolución (Justificación)
                            </label>
                            <textarea 
                                id="dev_motivo" 
                                wire:model="devolucion_motivo" 
                                rows="2"
                                placeholder="Ej: Alta médica anticipada, suspensión de tratamiento, sobrante de dosis..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-indigo-500 shadow-2xs resize-none"
                            ></textarea>
                            @error('devolucion_motivo')
                                <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Banner Informativo Financiero -->
                        <div class="p-3 rounded-xl bg-slate-100/80 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                            <i class="fas fa-calculator text-indigo-500 mt-0.5 text-sm shrink-0"></i>
                            <div class="text-[11px] leading-relaxed">
                                <strong>Actualización Automática de Cobro:</strong> Al registrar la devolución, el costo total de la <strong>Proforma #{{ $dev_proforma_id }}</strong> se recalculará instantáneamente para que el paciente solo cancele lo consumido.
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button 
                                type="button" 
                                wire:click="cerrarModalDevolucion" 
                                class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>

                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <i class="fas fa-check" wire:loading.remove wire:target="procesarDevolucion"></i>
                                <i class="fas fa-spinner fa-spin" wire:loading wire:target="procesarDevolucion"></i>
                                <span>Confirmar Devolución a Stock</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
