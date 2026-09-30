<div class="space-y-6">
    <!-- Breadcrumb & Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>Administración</span>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700 dark:text-slate-200 font-semibold">Reportes</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white shadow-md shadow-blue-500/20">
                    <i class="fas fa-file-invoice text-base"></i>
                </span>
                <span>Centro de Reportes del Sistema</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Consolidación analítica de movimientos de inventario, auditorías de stock, valorizaciones financieras y reportes administrativos.
            </p>
        </div>
    </div>

    <!-- Pestañas / Catálogo de Reportes Disponibles -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto">
        <button 
            type="button" 
            wire:click="$set('reporteActivo', 'kardex')" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $reporteActivo === 'kardex' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
        >
            <i class="fas fa-boxes"></i>
            <span>Kardex y Movimientos de Inventario</span>
        </button>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 cursor-not-allowed" title="Próximamente">
            <i class="fas fa-chart-line text-[11px]"></i>
            <span>Ventas y Recaudación Clínica</span>
            <span class="text-[9px] bg-slate-200 dark:bg-slate-800 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">Próx.</span>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 cursor-not-allowed" title="Próximamente">
            <i class="fas fa-user-md text-[11px]"></i>
            <span>Honorarios y Producción Médica</span>
            <span class="text-[9px] bg-slate-200 dark:bg-slate-800 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">Próx.</span>
        </div>
    </div>

    <!-- Panel de Configuración y Parámetros del Reporte (Kardex) -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fas fa-filter text-blue-600 dark:text-blue-400"></i>
                    <span>Criterios del Reporte de Kardex y Movimientos</span>
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Establezca el rango de fechas para calcular el saldo inicial, los movimientos cronológicos de entrada/salida y el balance final valorizado.
                </p>
            </div>

            <!-- Accesos Rápidos de Rango -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] text-slate-500 dark:text-slate-400 me-1">Rango:</span>
                <button 
                    type="button" 
                    wire:click="aplicarRango('mes_actual')" 
                    class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                >
                    Mes Actual
                </button>
                <button 
                    type="button" 
                    wire:click="aplicarRango('mes_anterior')" 
                    class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                >
                    Mes Anterior
                </button>
                <button 
                    type="button" 
                    wire:click="aplicarRango('ultimos_30')" 
                    class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                >
                    Últimos 30 días
                </button>
                <button 
                    type="button" 
                    wire:click="aplicarRango('anio_actual')" 
                    class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                >
                    Año Actual
                </button>
            </div>
        </div>

        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Fecha Inicio -->
                <div>
                    <label for="repFechaIni" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <i class="fas fa-calendar-alt text-slate-400 me-1"></i> Fecha Inicio:
                    </label>
                    <input 
                        type="date" 
                        id="repFechaIni" 
                        wire:model.live="fecha_inicio" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                    />
                    @error('fecha_inicio')
                        <span class="text-[11px] text-red-500 font-medium mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label for="repFechaFin" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <i class="fas fa-calendar-check text-slate-400 me-1"></i> Fecha Fin:
                    </label>
                    <input 
                        type="date" 
                        id="repFechaFin" 
                        wire:model.live="fecha_fin" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                    />
                    @error('fecha_fin')
                        <span class="text-[11px] text-red-500 font-medium mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Sucursal -->
                <div>
                    <label for="repSucursal" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <i class="fas fa-hospital text-slate-400 me-1"></i> Sucursal:
                    </label>
                    <select 
                        id="repSucursal" 
                        wire:model.live="sucursal_id" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                    >
                        <option value="">Todas las Sucursales</option>
                        @foreach ($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Producto -->
                <div>
                    <label for="repProducto" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <i class="fas fa-pills text-slate-400 me-1"></i> Producto:
                    </label>
                    <select 
                        id="repProducto" 
                        wire:model.live="producto_id" 
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                    >
                        <option value="">Todos los Productos (Consolidado)</option>
                        @foreach ($productos as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->nombre }} ({{ $prod->marca?->nombre ?? 'S/M' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Opciones Secundarias y Botones de Acción -->
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <input 
                        type="checkbox" 
                        id="chkSoloActivos" 
                        wire:model.live="solo_con_actividad" 
                        class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 h-4 w-4"
                    />
                    <label for="chkSoloActivos" class="text-xs text-slate-600 dark:text-slate-400 select-none cursor-pointer">
                        Solo incluir productos que registren movimientos o saldo en el período
                    </label>
                </div>

                <div class="flex items-center gap-2.5">
                    <button 
                        type="button" 
                        wire:click="limpiarFiltros" 
                        class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    >
                        <i class="fas fa-undo me-1"></i> Limpiar
                    </button>

                    <button 
                        type="button" 
                        wire:click="previsualizarReporte" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 cursor-pointer"
                    >
                        <i class="fas fa-eye" wire:loading.remove wire:target="previsualizarReporte"></i>
                        <i class="fas fa-spinner fa-spin" wire:loading wire:target="previsualizarReporte"></i>
                        <span>Previsualizar Reporte</span>
                    </button>

                    <!-- Botón para Abrir/Descargar PDF -->
                    @php
                        $pdfUrl = route('administracion.reportes.movimientos.pdf', [
                            'fecha_inicio' => $fecha_inicio,
                            'fecha_fin' => $fecha_fin,
                            'producto_id' => $producto_id,
                            'sucursal_id' => $sucursal_id,
                            'solo_con_actividad' => $solo_con_actividad ? 1 : 0,
                        ]);
                    @endphp
                    <a 
                        href="{{ $pdfUrl }}" 
                        target="_blank" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer"
                    >
                        <i class="fas fa-file-pdf text-sm"></i>
                        <span>Generar PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Previsualización del Reporte en Pantalla -->
    @if ($reporteGenerado && $datosReporte)
        <!-- Resumen General en Tarjetas Métricas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-lg">
                    <i class="fas fa-pills"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Productos Evaluados</span>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 font-mono">
                        {{ number_format($datosReporte['resumen_general']['total_productos']) }}
                    </h3>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-lg">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Entradas vs Salidas</span>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 font-mono mt-1">
                        <span class="text-emerald-600 dark:text-emerald-400">+{{ number_format($datosReporte['resumen_general']['gran_total_entradas']) }}</span> / 
                        <span class="text-red-500 dark:text-red-400">-{{ number_format($datosReporte['resumen_general']['gran_total_salidas']) }}</span>
                    </h3>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-lg">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Stock Total al Corte</span>
                    <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                        {{ number_format($datosReporte['resumen_general']['gran_total_saldo_final']) }} unids.
                    </h3>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-lg">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Valorización Venta Total</span>
                    <h3 class="text-sm font-bold text-teal-600 dark:text-teal-400 font-mono">
                        Bs. {{ number_format($datosReporte['resumen_general']['gran_total_valor_venta'], 2) }}
                    </h3>
                    <span class="text-[10px] text-slate-400">
                        Compra: Bs. {{ number_format($datosReporte['resumen_general']['gran_total_valor_compra'], 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Lista Detallada por Producto -->
        @if (empty($datosReporte['items']))
            <div class="p-8 text-center bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500">
                <i class="fas fa-info-circle text-2xl text-blue-500 mb-2"></i>
                <p class="text-sm font-semibold">No se encontraron productos con movimientos o stock en este período.</p>
                <p class="text-xs text-slate-400 mt-1">Pruebe desmarcando la opción de "Solo productos con movimientos" o amplíe el rango de fechas.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($datosReporte['items'] as $item)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
                        <!-- Cabecera de Producto -->
                        <div class="p-3 sm:p-4 bg-slate-900 text-white flex flex-col md:flex-row md:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-sky-400 flex items-center gap-2">
                                    <i class="fas fa-capsules"></i>
                                    <span>{{ $item['producto_nombre'] }}</span>
                                    <span class="text-[11px] font-normal text-slate-300">({{ $item['marca_nombre'] }})</span>
                                </h3>
                                <div class="text-[11px] text-slate-400 flex items-center gap-3 mt-0.5">
                                    <span>Presentación: <strong class="text-slate-200">{{ $item['unidad_medida'] }}</strong></span>
                                    <span>Stock Mínimo: <strong class="text-slate-200">{{ $item['stock_minimo'] }}</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 text-xs font-mono">
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Último P. Compra</span>
                                    <span class="font-bold text-slate-200">Bs. {{ number_format($item['ultimo_precio_compra'], 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] block">Último P. Venta</span>
                                    <span class="font-bold text-emerald-400">Bs. {{ number_format($item['ultimo_precio_venta'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Movimientos del Kardex -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="py-2 px-3">Fecha/Hora</th>
                                        <th class="py-2 px-3">Lote / Vto.</th>
                                        <th class="py-2 px-3">Concepto / Movimiento</th>
                                        <th class="py-2 px-3">Referencia / Origen</th>
                                        <th class="py-2 px-3 text-right text-emerald-600 dark:text-emerald-400">Entrada</th>
                                        <th class="py-2 px-3 text-right text-red-500 dark:text-red-400">Salida</th>
                                        <th class="py-2 px-3 text-right font-black">Saldo</th>
                                        <th class="py-2 px-3">Responsable</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono text-[11px]">
                                    <!-- Fila Saldo Inicial al corte de fecha_inicio -->
                                    <tr class="bg-blue-50/70 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 font-bold font-sans">
                                        <td class="py-2 px-3">{{ \Carbon\Carbon::parse($datosReporte['fecha_inicio'])->format('d/m/Y') }} 00:00</td>
                                        <td class="py-2 px-3 text-slate-400">-</td>
                                        <td class="py-2 px-3" colspan="2">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 font-bold">
                                                <i class="fas fa-play-circle text-[9px]"></i> Saldo Inicial Previo
                                            </span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 ms-1 font-normal">(Cálculo consolidado antes de {{ \Carbon\Carbon::parse($datosReporte['fecha_inicio'])->format('d/m/Y') }})</span>
                                        </td>
                                        <td class="py-2 px-3 text-right text-slate-400">-</td>
                                        <td class="py-2 px-3 text-right text-slate-400">-</td>
                                        <td class="py-2 px-3 text-right font-black font-mono text-sm text-blue-700 dark:text-blue-300">
                                            {{ number_format($item['saldo_inicial']) }}
                                        </td>
                                        <td class="py-2 px-3 text-slate-400">-</td>
                                    </tr>

                                    <!-- Movimientos dentro del período -->
                                    @forelse ($item['movimientos'] as $mov)
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                            <td class="py-2 px-3 font-sans text-slate-600 dark:text-slate-400">{{ $mov['fecha'] }}</td>
                                            <td class="py-2 px-3 font-sans">
                                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $mov['lote_codigo'] }}</span>
                                                @if ($mov['lote_vencimiento'])
                                                    <span class="text-[10px] text-slate-400 block font-normal">Vto: {{ $mov['lote_vencimiento'] }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 font-sans">
                                                @if ($mov['es_entrada'])
                                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold">
                                                        <i class="fas fa-arrow-down text-[9px]"></i> {{ $mov['tipo_movimiento'] }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-red-500 dark:text-red-400 font-bold">
                                                        <i class="fas fa-arrow-up text-[9px]"></i> {{ $mov['tipo_movimiento'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 font-sans text-slate-600 dark:text-slate-400">{{ $mov['referencia'] }}</td>
                                            <td class="py-2 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $mov['cantidad_entrada'] > 0 ? '+'.number_format($mov['cantidad_entrada']) : '-' }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-bold text-red-500 dark:text-red-400">
                                                {{ $mov['cantidad_salida'] > 0 ? '-'.number_format($mov['cantidad_salida']) : '-' }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-black text-slate-800 dark:text-slate-100">
                                                {{ number_format($mov['saldo_acumulado']) }}
                                            </td>
                                            <td class="py-2 px-3 font-sans text-[10px] text-slate-500 dark:text-slate-400">{{ $mov['usuario'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-3 px-3 text-center text-slate-400 font-sans text-xs italic">
                                                Sin movimientos en el rango de fechas seleccionado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer de Resumen y Valorización del Producto -->
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-4 text-slate-600 dark:text-slate-300">
                                <span>Entradas: <strong class="text-emerald-600 dark:text-emerald-400 font-mono">+{{ number_format($item['total_entradas']) }}</strong></span>
                                <span>Salidas: <strong class="text-red-500 dark:text-red-400 font-mono">-{{ number_format($item['total_salidas']) }}</strong></span>
                                <span>Stock Final: <strong class="text-blue-600 dark:text-blue-400 font-mono font-bold">{{ number_format($item['saldo_final']) }} {{ $item['unidad_medida'] }}</strong></span>
                            </div>

                            <div class="flex items-center gap-4 text-xs font-mono">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400 text-[10px]">Val. Compra:</span>
                                    <strong class="text-slate-700 dark:text-slate-200">Bs. {{ number_format($item['valor_total_compra'], 2) }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400 text-[10px]">Val. Venta:</span>
                                    <strong class="text-emerald-600 dark:text-emerald-400">Bs. {{ number_format($item['valor_total_venta'], 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
