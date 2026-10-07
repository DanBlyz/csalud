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
                Consolidación analítica de admisiones de pacientes, ocupación de habitaciones, movimientos de inventario y balances clínicos.
            </p>
        </div>
    </div>

    <!-- Pestañas / Catálogo de Reportes Disponibles -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto">
        <button 
            type="button" 
            wire:click="cambiarTipoReporte('kardex')" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $reporteActivo === 'kardex' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
        >
            <i class="fas fa-boxes"></i>
            <span>Kardex y Movimientos de Inventario</span>
        </button>

        <button 
            type="button" 
            wire:click="cambiarTipoReporte('pacientes')" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $reporteActivo === 'pacientes' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
        >
            <i class="fas fa-procedures"></i>
            <span>Ingresos, Salidas y Habitaciones de Pacientes</span>
        </button>

        <button 
            type="button" 
            wire:click="cambiarTipoReporte('convenios')" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $reporteActivo === 'convenios' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
        >
            <i class="fas fa-file-excel {{ $reporteActivo === 'convenios' ? 'text-white' : 'text-emerald-500' }}"></i>
            <span>Planilla de Pacientes por Convenio (Excel)</span>
        </button>

        <button 
            type="button" 
            wire:click="cambiarTipoReporte('flujo_caja')" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $reporteActivo === 'flujo_caja' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
        >
            <i class="fas fa-file-excel {{ $reporteActivo === 'flujo_caja' ? 'text-white' : 'text-emerald-500' }}"></i>
            <span>Flujo Mensual de Caja (Excel)</span>
        </button>

        <button 
            type="button" 
            wire:click="cambiarTipoReporte('resumen_anual')" 
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $reporteActivo === 'resumen_anual' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
        >
            <i class="fas fa-file-excel {{ $reporteActivo === 'resumen_anual' ? 'text-white' : 'text-emerald-500' }}"></i>
            <span>Resumen Anual de Ingresos y Gastos (Excel)</span>
        </button>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500 border border-slate-200 dark:border-slate-800 cursor-not-allowed" title="Próximamente">
            <i class="fas fa-user-md text-[11px]"></i>
            <span>Honorarios y Producción Médica</span>
            <span class="text-[9px] bg-slate-200 dark:bg-slate-800 text-slate-500 px-1.5 py-0.5 rounded font-bold uppercase">Próx.</span>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. REPORTE KARDEX DE INVENTARIO                                          -->
    <!-- ========================================================================= -->
    @if ($reporteActivo === 'kardex')
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
                            <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <label for="repFechaFin" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-calendar-alt text-slate-400 me-1"></i> Fecha Fin:
                        </label>
                        <input 
                            type="date" 
                            id="repFechaFin" 
                            wire:model.live="fecha_fin" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                        />
                        @error('fecha_fin')
                            <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
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

                    <!-- Producto Filtro -->
                    <div>
                        <label for="repProducto" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-pills text-slate-400 me-1"></i> Filtrar Producto:
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
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
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

        <!-- Previsualización del Reporte Kardex en Pantalla -->
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
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Stock Físico Final</span>
                        <h3 class="text-lg font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                            {{ number_format($datosReporte['resumen_general']['gran_total_saldo_final']) }} unids.
                        </h3>
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-lg">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Valorización (Venta)</span>
                        <h3 class="text-lg font-bold text-amber-600 dark:text-amber-400 font-mono">
                            Bs. {{ number_format($datosReporte['resumen_general']['gran_total_valor_venta'], 2) }}
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Listado Detallado por Producto -->
            <div class="space-y-4">
                @foreach ($datosReporte['items'] as $item)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
                        <!-- Header del Producto -->
                        <div class="p-3 sm:px-4 bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white text-xs">
                                    {{ $item['producto_nombre'] }}
                                </span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    ({{ $item['marca_nombre'] }} &bull; {{ $item['unidad_medida'] }})
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-slate-500">Saldo Inicial: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ number_format($item['saldo_inicial']) }}</strong></span>
                                <span class="text-slate-500">Saldo Final: <strong class="text-blue-600 dark:text-blue-400 font-mono font-bold">{{ number_format($item['saldo_final']) }}</strong></span>
                            </div>
                        </div>

                        <!-- Tabla de Movimientos del Producto -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th class="py-2 px-3">Fecha / Hora</th>
                                        <th class="py-2 px-3">Tipo Movimiento</th>
                                        <th class="py-2 px-3">Lote / Vence</th>
                                        <th class="py-2 px-3">Área / Sección</th>
                                        <th class="py-2 px-3">Detalle / Ref</th>
                                        <th class="py-2 px-3 text-right">Entrada</th>
                                        <th class="py-2 px-3 text-right">Salida</th>
                                        <th class="py-2 px-3 text-right font-bold text-blue-600 dark:text-blue-400">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse ($item['movimientos'] as $mov)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                            <td class="py-2 px-3 font-mono text-[11px] text-slate-600 dark:text-slate-400">{{ $mov['fecha'] }}</td>
                                            <td class="py-2 px-3">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold {{ $mov['es_entrada'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                                    {{ $mov['tipo_movimiento'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-3 font-mono text-[11px]">
                                                {{ $mov['lote_codigo'] }}
                                                @if($mov['lote_vencimiento'])
                                                    <span class="text-[10px] text-slate-400 block">{{ $mov['lote_vencimiento'] }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 text-slate-600 dark:text-slate-400">{{ $mov['seccion_nombre'] ?? 'Farmacia Central' }}</td>
                                            <td class="py-2 px-3 text-slate-600 dark:text-slate-400 max-w-xs truncate">{{ $mov['referencia'] }}</td>
                                            <td class="py-2 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $mov['cantidad_entrada'] > 0 ? '+'.number_format($mov['cantidad_entrada']) : '-' }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                                                {{ $mov['cantidad_salida'] > 0 ? '-'.number_format($mov['cantidad_salida']) : '-' }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                                {{ number_format($mov['saldo_acumulado']) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-slate-400 text-xs italic">
                                                Sin movimientos en el período seleccionado. Saldo inicial conservado.
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
                                <span>Stock Final: <strong class="text-blue-600 dark:text-blue-400 font-mono font-bold">{{ number_format($item['saldo_final']) }}</strong></span>
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

                        <!-- Desglose de Existencias por Sección y Total General -->
                        @if (!empty($item['secciones_stock']))
                            <div class="px-3 py-2 bg-slate-100/80 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-2 text-xs">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1 me-1">
                                        <i class="fas fa-hospital-alt text-sky-500"></i>
                                        <span>Existencias por Sección:</span>
                                    </span>
                                    @foreach ($item['secciones_stock'] as $sec)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium {{ $sec['cantidad'] > 0 ? 'bg-sky-50 text-sky-800 border border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800' : 'bg-slate-200/50 text-slate-400 border border-slate-200 dark:bg-slate-800/40 dark:text-slate-500 dark:border-slate-700' }}">
                                            <span>{{ $sec['seccion_nombre'] }}:</span>
                                            <strong class="font-bold font-mono {{ $sec['cantidad'] > 0 ? 'text-sky-900 dark:text-sky-100' : 'text-slate-400 dark:text-slate-500' }}">
                                                {{ number_format($sec['cantidad']) }}
                                            </strong>
                                        </span>
                                    @endforeach
                                </div>

                                <div class="shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-bold bg-indigo-50 text-indigo-900 border border-indigo-200 dark:bg-indigo-950/70 dark:text-indigo-200 dark:border-indigo-800">
                                        <i class="fas fa-boxes text-[10px] text-indigo-500"></i>
                                        <span>Total Producto:</span>
                                        <strong class="font-black font-mono text-indigo-900 dark:text-indigo-100">
                                            {{ number_format($item['total_stock_secciones']) }} unids.
                                        </strong>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <!-- ========================================================================= -->
    <!-- 2. REPORTE DE INGRESOS, SALIDAS Y HABITACIONES DE PACIENTES               -->
    <!-- ========================================================================= -->
    @if ($reporteActivo === 'pacientes')
        <!-- Panel de Filtros para Reporte de Pacientes -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fas fa-filter text-blue-600 dark:text-blue-400"></i>
                        <span>Criterios del Reporte de Ingresos, Salidas y Habitaciones</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Consolide admisiones y altas de pacientes filtrando por rango de fechas, institución/convenio, habitación asignada y tipo de atención.
                    </p>
                </div>

                <!-- Accesos Rápidos de Rango -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 me-1">Rango:</span>
                    <button 
                        type="button" 
                        wire:click="aplicarRangoPacientes('mes_actual')" 
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                    >
                        Mes Actual
                    </button>
                    <button 
                        type="button" 
                        wire:click="aplicarRangoPacientes('mes_anterior')" 
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                    >
                        Mes Anterior
                    </button>
                    <button 
                        type="button" 
                        wire:click="aplicarRangoPacientes('ultimos_30')" 
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                    >
                        Últimos 30 días
                    </button>
                    <button 
                        type="button" 
                        wire:click="aplicarRangoPacientes('anio_actual')" 
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                    >
                        Año Actual
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Fecha Inicio -->
                    <div>
                        <label for="pacFechaIni" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-calendar-alt text-slate-400 me-1"></i> Fecha Inicio:
                        </label>
                        <input 
                            type="date" 
                            id="pacFechaIni" 
                            wire:model.live="pac_fecha_inicio" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                        />
                        @error('pac_fecha_inicio')
                            <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <label for="pacFechaFin" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-calendar-alt text-slate-400 me-1"></i> Fecha Fin:
                        </label>
                        <input 
                            type="date" 
                            id="pacFechaFin" 
                            wire:model.live="pac_fecha_fin" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                        />
                        @error('pac_fecha_fin')
                            <span class="text-[11px] text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Institución / Convenio (Seleccionable solicitado) -->
                    <div>
                        <label for="pacInstitucion" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-landmark text-slate-400 me-1"></i> Institución / Seguro:
                        </label>
                        <select 
                            id="pacInstitucion" 
                            wire:model.live="pac_institucion_id" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3 font-medium"
                        >
                            <option value="">Todas las Instituciones (Incluye Particulares)</option>
                            <option value="-1">Solo Pacientes Particulares (Sin Convenio)</option>
                            @foreach ($instituciones as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Criterio de Movimiento / Evento -->
                    <div>
                        <label for="pacTipoFiltro" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-stream text-slate-400 me-1"></i> Criterio de Búsqueda:
                        </label>
                        <select 
                            id="pacTipoFiltro" 
                            wire:model.live="pac_tipo_filtro" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                        >
                            <option value="todos">Todos (Ingresos, Altas y Estancias activas)</option>
                            <option value="ingresos">Solo Ingresos / Admisiones en el período</option>
                            <option value="salidas">Solo Altas / Salidas en el período</option>
                            <option value="internados">Pacientes Actualmente Internados (En piso)</option>
                        </select>
                    </div>

                    <!-- Tipo de Atención -->
                    <div>
                        <label for="pacTipoAtencion" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-stethoscope text-slate-400 me-1"></i> Tipo de Atención:
                        </label>
                        <select 
                            id="pacTipoAtencion" 
                            wire:model.live="pac_tipo_atencion" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                        >
                            <option value="">Todas las Modalidades</option>
                            <option value="Internacion">Solo Internación / Hospitalaria</option>
                            <option value="Ambulatoria">Solo Ambulatoria</option>
                        </select>
                    </div>

                    <!-- Sucursal -->
                    <div>
                        <label for="pacSucursal" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <i class="fas fa-hospital text-slate-400 me-1"></i> Sucursal:
                        </label>
                        <select 
                            id="pacSucursal" 
                            wire:model.live="pac_sucursal_id" 
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 shadow-2xs py-2 px-3"
                        >
                            <option value="">Todas las Sucursales</option>
                            @foreach ($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        wire:click="limpiarFiltrosPacientes" 
                        class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    >
                        <i class="fas fa-undo me-1"></i> Limpiar Filtros
                    </button>

                    <button 
                        type="button" 
                        wire:click="previsualizarReportePacientes" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <i class="fas fa-search" wire:loading.remove wire:target="previsualizarReportePacientes"></i>
                        <i class="fas fa-spinner fa-spin" wire:loading wire:target="previsualizarReportePacientes"></i>
                        <span>Generar Reporte</span>
                    </button>

                    <!-- Botón Exportar PDF -->
                    @php
                        $pdfUrlPacientes = route('administracion.reportes.pacientes.pdf', [
                            'fecha_inicio' => $pac_fecha_inicio,
                            'fecha_fin' => $pac_fecha_fin,
                            'institucion_id' => $pac_institucion_id,
                            'sucursal_id' => $pac_sucursal_id,
                            'tipo_filtro' => $pac_tipo_filtro,
                            'tipo_atencion' => $pac_tipo_atencion,
                        ]);
                    @endphp
                    <a 
                        href="{{ $pdfUrlPacientes }}" 
                        target="_blank" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md shadow-red-500/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer"
                    >
                        <i class="fas fa-file-pdf text-sm"></i>
                        <span>Descargar PDF</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Previsualización de Pacientes en Pantalla -->
        @if ($pac_reporteGenerado && $pac_datosReporte)
            <!-- Tarjetas Métricas KPI -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs text-center">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Total Atenciones</span>
                    <h3 class="text-xl font-black font-mono text-slate-900 dark:text-white mt-0.5">
                        {{ number_format($pac_datosReporte['totales']['total_registros']) }}
                    </h3>
                </div>

                <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/50 dark:bg-emerald-950/20 shadow-xs text-center">
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase block">Ingresos Período</span>
                    <h3 class="text-xl font-black font-mono text-emerald-700 dark:text-emerald-400 mt-0.5">
                        {{ number_format($pac_datosReporte['totales']['ingresos_periodo']) }}
                    </h3>
                </div>

                <div class="p-3.5 rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/50 dark:bg-indigo-950/20 shadow-xs text-center">
                    <span class="text-[10px] text-indigo-700 dark:text-indigo-400 font-bold uppercase block">Altas / Salidas</span>
                    <h3 class="text-xl font-black font-mono text-indigo-700 dark:text-indigo-400 mt-0.5">
                        {{ number_format($pac_datosReporte['totales']['salidas_periodo']) }}
                    </h3>
                </div>

                <div class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-800/60 bg-amber-50/50 dark:bg-amber-950/20 shadow-xs text-center">
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold uppercase block">Internados Activos</span>
                    <h3 class="text-xl font-black font-mono text-amber-700 dark:text-amber-400 mt-0.5">
                        {{ number_format($pac_datosReporte['totales']['internados_activos']) }}
                    </h3>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs text-center">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Hospitalarios</span>
                    <h3 class="text-xl font-black font-mono text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ number_format($pac_datosReporte['totales']['hospitalarios']) }}
                    </h3>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs text-center">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Ambulatorios</span>
                    <h3 class="text-xl font-black font-mono text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ number_format($pac_datosReporte['totales']['ambulatorios']) }}
                    </h3>
                </div>
            </div>

            <!-- Tabla de Datos de Pacientes -->
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden">
                <div class="p-3 sm:px-4 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="text-xs text-slate-700 dark:text-slate-300 font-bold flex items-center gap-2">
                        <i class="fas fa-list-alt text-blue-500"></i>
                        <span>Listado Detallado de Pacientes ({{ count($pac_datosReporte['items']) }} registros)</span>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        <strong>Filtro:</strong> {{ $pac_datosReporte['institucion_nombre'] }} &bull; {{ $pac_datosReporte['sucursal_nombre'] }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="py-2.5 px-3">Proforma</th>
                                <th class="py-2.5 px-3">Paciente / Identificación</th>
                                <th class="py-2.5 px-3">Institución / Convenio</th>
                                <th class="py-2.5 px-3">Habitación / Pieza</th>
                                <th class="py-2.5 px-3">Modalidad</th>
                                <th class="py-2.5 px-3">Fecha Ingreso</th>
                                <th class="py-2.5 px-3">Fecha Salida / Alta</th>
                                <th class="py-2.5 px-3">Diagnóstico</th>
                                <th class="py-2.5 px-3 text-center">Estado</th>
                                <th class="py-2.5 px-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            @forelse ($pac_datosReporte['items'] as $item)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-white">
                                        #{{ str_pad($item['proforma_id'], 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ $item['paciente_nombre'] }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            CI: {{ $item['paciente_cedula'] }} 
                                            @if($item['paciente_edad']) &bull; {{ $item['paciente_edad'] }} años @endif
                                            @if($item['paciente_celular']) &bull; Cel: {{ $item['paciente_celular'] }} @endif
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            {{ $item['institucion_nombre'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($item['pieza'] && $item['pieza'] !== 'Ambulatorio' && $item['pieza'] !== 'Sin asignar')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shadow-2xs">
                                                <i class="fas fa-bed text-amber-600 dark:text-amber-400 text-[10px]"></i>
                                                {{ $item['pieza'] }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[11px] italic">{{ $item['pieza'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="text-[11px] font-semibold {{ $item['tipo_atencion'] === 'Internacion' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ $item['tipo_atencion'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px]">
                                        {{ $item['fecha_ingreso'] }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px]">
                                        @if($item['fecha_salida'])
                                            <span>{{ $item['fecha_salida'] }}</span>
                                            <span class="text-[10px] text-slate-400 block font-normal">({{ $item['dias_estancia'] }} día{{ $item['dias_estancia'] > 1 ? 's' : '' }})</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                En Curso
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 max-w-xs">
                                        <div class="truncate font-medium text-slate-800 dark:text-slate-200" title="{{ $item['diagnostico'] }}">
                                            {{ $item['diagnostico'] ?: 'Sin diagnóstico' }}
                                        </div>
                                        @if($item['motivo_consulta'])
                                            <div class="truncate text-[10px] text-slate-400" title="{{ $item['motivo_consulta'] }}">
                                                {{ $item['motivo_consulta'] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $item['estado'] === 'Pagada' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                            {{ $item['estado'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <a 
                                            href="{{ route('proformas.show', $item['proforma_id']) }}" 
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-block"
                                            title="Ver Proforma"
                                        >
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-8 text-slate-400 italic">
                                        No se encontraron registros de pacientes para los filtros y fechas seleccionadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    <!-- ========================================================================= -->
    <!-- 3. PLANILLA DE PACIENTES POR CONVENIO / INSTITUCIÓN (EXCEL)              -->
    <!-- ========================================================================= -->
    @if ($reporteActivo === 'convenios')
        <!-- Panel de Configuración y Parámetros del Reporte de Convenios -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fas fa-file-excel text-emerald-600 dark:text-emerald-400"></i>
                        <span>Criterios de la Planilla Mensual por Institución / Convenio</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Genera y consolida la sábana mensual de servicios médicos e insumos de farmacia, quirófano y enfermería por paciente para liquidación con empresas e instituciones aseguradoras.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="limpiarFiltrosConvenio"
                        class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <i class="fas fa-undo text-[10px]"></i>
                        <span>Limpiar</span>
                    </button>
                    @if ($conv_institucion_id)
                        <a 
                            href="{{ route('administracion.reportes.convenios.excel', ['institucion_id' => $conv_institucion_id, 'mes' => $conv_mes, 'anio' => $conv_anio, 'sucursal_id' => $conv_sucursal_id, 'solo_atendidos' => $conv_solo_atendidos ? 1 : 0]) }}"
                            target="_blank"
                            class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="fas fa-file-download text-xs"></i>
                            <span>Descargar Excel (.xlsx)</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Institución o Convenio (Obligatorio) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Institución / Convenio <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="conv_institucion_id"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">-- Seleccionar Institución --</option>
                            @foreach ($instituciones as $inst)
                                <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                            @endforeach
                        </select>
                        @error('conv_institucion_id')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mes de la Planilla -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Mes del Reporte <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="conv_mes"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        @error('conv_mes')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Año del Reporte -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Año <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            wire:model.live="conv_anio"
                            min="2020"
                            max="2050"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                        @error('conv_anio')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sucursal (Opcional) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Sucursal
                        </label>
                        <select 
                            wire:model.live="conv_sucursal_id"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Todas las Sucursales</option>
                            @foreach ($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Criterio de Selección de Pacientes -->
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <label class="flex items-center gap-2.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            wire:model.live="conv_solo_atendidos" 
                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                        >
                        <span class="font-medium">
                            Solo incluir pacientes con atenciones registradas en el mes
                            <span class="text-slate-400 dark:text-slate-500 text-[11px] block sm:inline">(Desmarcar para listar a todos los pacientes afiliados al convenio)</span>
                        </span>
                    </label>

                    <button 
                        type="button" 
                        wire:click="previsualizarReporteConvenio" 
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-table text-xs" wire:loading.remove wire:target="previsualizarReporteConvenio"></i>
                        <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="previsualizarReporteConvenio"></i>
                        <span wire:loading.remove wire:target="previsualizarReporteConvenio">Generar Vista Previa</span>
                        <span wire:loading wire:target="previsualizarReporteConvenio">Generando...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vista Previa de la Planilla tipo Hoja de Cálculo -->
        @if ($conv_reporteGenerado && $conv_datosReporte)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
                <!-- Barra de Título y Descarga -->
                <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800/60 inline-block mb-1">
                            Sábana de Facturación por Convenio
                        </span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            PLANILLA PACIENTES ATENDIDOS {{ $conv_datosReporte['institucion']->nombre }} MES DE {{ $conv_datosReporte['mes_nombre'] }} {{ $conv_datosReporte['anio'] }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ count($conv_datosReporte['columnas_pacientes']) }} atenciones registradas • Gran Total: Bs. {{ number_format($conv_datosReporte['gran_total'], 2) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="descargarExcelConvenio" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i class="fas fa-file-excel text-sm" wire:loading.remove wire:target="descargarExcelConvenio"></i>
                            <i class="fas fa-spinner fa-spin text-sm" wire:loading wire:target="descargarExcelConvenio"></i>
                            <span wire:loading.remove wire:target="descargarExcelConvenio">Exportar a Excel (.xlsx)</span>
                            <span wire:loading wire:target="descargarExcelConvenio">Exportando...</span>
                        </button>
                    </div>
                </div>

                <!-- Tabla Matricial Estructurada -->
                <div class="overflow-x-auto max-h-[700px] border-b border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs border-collapse">
                        <!-- Cabeceras Superiores del Paciente -->
                        <thead class="sticky top-0 z-10 bg-white dark:bg-slate-900 shadow-xs">
                            <!-- Fila 4: Correlativos -->
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60">
                                <th class="py-1 px-3 w-10 text-center font-bold text-slate-500 dark:text-slate-400 border-r border-slate-200 dark:border-slate-800">#</th>
                                <th class="py-1 px-3 min-w-[280px] text-left font-bold text-slate-600 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">Concepto / Servicio</th>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <th class="py-1 px-2 min-w-[130px] text-center font-bold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['index'] }}
                                    </th>
                                @endforeach
                                <th class="py-1 px-3 min-w-[120px] text-center font-bold text-slate-900 dark:text-white bg-slate-200 dark:bg-slate-700/80">TOTALES</th>
                            </tr>

                            <!-- Fila 5: NO. PROF. (Verde) -->
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                <th class="py-1 px-3 border-r border-slate-200 dark:border-slate-800"></th>
                                <th class="py-1.5 px-3 text-left font-bold text-slate-800 dark:text-slate-200 uppercase border-r border-slate-200 dark:border-slate-800">
                                    NO. PROF.
                                </th>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <th class="py-1.5 px-2 text-center font-extrabold bg-emerald-200 text-emerald-950 border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['proforma_numero'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 text-center font-bold bg-slate-100 dark:bg-slate-800 text-slate-500"></th>
                            </tr>

                            <!-- Fila 6: NOMBRE PACIENTE -->
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                                <th class="py-1 px-3 border-r border-slate-200 dark:border-slate-800"></th>
                                <th class="py-2 px-3 text-left font-bold text-slate-800 dark:text-slate-200 uppercase border-r border-slate-200 dark:border-slate-800">
                                    NOMBRE PACIENTE
                                </th>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <th class="py-2 px-2 text-center font-bold text-slate-900 dark:text-slate-100 text-[11px] leading-tight border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['paciente_nombre'] }}
                                    </th>
                                @endforeach
                                <th class="py-2 px-3 text-center font-black text-slate-900 dark:text-white bg-slate-200 dark:bg-slate-800 text-[11px]">
                                    TOTALES
                                </th>
                            </tr>

                            <!-- Fila 7: NUMERO DE FOLEADO (Amarillo) -->
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                <th class="py-1 px-3 border-r border-slate-200 dark:border-slate-800"></th>
                                <th class="py-1.5 px-3 text-left font-bold text-slate-800 dark:text-slate-200 uppercase border-r border-slate-200 dark:border-slate-800">
                                    NUMERO DE FOLEADO
                                </th>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <th class="py-1.5 px-2 text-center font-semibold bg-yellow-200 text-yellow-950 border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['foleado'] ?: '-' }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 text-center bg-slate-100 dark:bg-slate-800 text-slate-500"></th>
                            </tr>

                            <!-- Fila 8: DATOS GENERALES -->
                            <tr class="border-b border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                                <th class="py-1 px-3 font-bold text-slate-600 dark:text-slate-400 text-center border-r border-slate-200 dark:border-slate-800">DET.</th>
                                <th class="py-1.5 px-3 text-left font-extrabold text-slate-900 dark:text-white uppercase border-r border-slate-200 dark:border-slate-800">
                                    DATOS GENERALES
                                </th>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <th class="py-1.5 px-2 text-center font-medium text-slate-600 dark:text-slate-400 text-[10px] leading-tight border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['datos_generales'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 text-center bg-slate-200 dark:bg-slate-800 text-slate-500"></th>
                            </tr>
                        </thead>

                        <!-- Cuerpo: Filas de Servicios e Insumos por Sección -->
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($conv_datosReporte['filas_conceptos'] as $concepto)
                                @php
                                    $esSec = $concepto['es_seccion'];
                                    $rowBg = $esSec 
                                        ? 'bg-emerald-50/70 dark:bg-emerald-950/25 font-bold text-emerald-950 dark:text-emerald-200' 
                                        : 'hover:bg-slate-50 dark:hover:bg-slate-800/40 text-slate-700 dark:text-slate-300';
                                @endphp
                                <tr class="{{ $rowBg }} transition-colors">
                                    <td class="py-1.5 px-3 text-center text-slate-400 text-[10px] border-r border-slate-200 dark:border-slate-800">
                                        @if ($esSec)
                                            <i class="fas fa-boxes text-[9px] text-emerald-600"></i>
                                        @else
                                            <i class="fas fa-stethoscope text-[9px] text-slate-400"></i>
                                        @endif
                                    </td>
                                    <td class="py-1.5 px-3 font-semibold uppercase text-[11px] border-r border-slate-200 dark:border-slate-800">
                                        {{ $concepto['nombre'] }}
                                    </td>
                                    @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                        @php
                                            $val = (float) ($col['valores'][$concepto['clave']] ?? 0.00);
                                        @endphp
                                        <td class="py-1.5 px-2 text-right tabular-nums text-[11px] border-r border-slate-200 dark:border-slate-800 {{ $val > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-600' }}">
                                            {{ $val > 0 ? number_format($val, 2) : '0.00' }}
                                        </td>
                                    @endforeach
                                    <td class="py-1.5 px-3 text-right font-bold tabular-nums text-[11px] bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white">
                                        {{ number_format($concepto['total_fila'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <!-- Pies de Página: TOTAL, DESCUENTOS, TOTAL A CANCELAR -->
                        <tfoot class="sticky bottom-0 z-10 bg-white dark:bg-slate-900 border-t-2 border-slate-300 dark:border-slate-700 shadow-md">
                            <!-- Fila TOTAL -->
                            <tr class="bg-slate-100 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 font-bold">
                                <td class="py-2 px-3 text-center border-r border-slate-200 dark:border-slate-800">Σ</td>
                                <td class="py-2 px-3 text-left font-black uppercase text-xs border-r border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                                    TOTAL
                                </td>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <td class="py-2 px-2 text-right font-black tabular-nums text-xs border-r border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                                        {{ number_format($col['total'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2 px-3 text-right font-black tabular-nums text-xs bg-slate-200 dark:bg-slate-700 text-emerald-700 dark:text-emerald-400">
                                    {{ number_format($conv_datosReporte['gran_total'], 2) }}
                                </td>
                            </tr>

                            <!-- Fila DESCUENTOS -->
                            <tr class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 text-slate-500">
                                <td class="py-1.5 px-3 text-center border-r border-slate-200 dark:border-slate-800">-</td>
                                <td class="py-1.5 px-3 text-left font-semibold uppercase text-xs border-r border-slate-200 dark:border-slate-800">
                                    DESCUENTOS
                                </td>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <td class="py-1.5 px-2 text-right tabular-nums text-xs border-r border-slate-200 dark:border-slate-800">
                                        {{ number_format($col['descuento'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-1.5 px-3 text-right font-semibold tabular-nums text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ number_format($conv_datosReporte['gran_descuento'], 2) }}
                                </td>
                            </tr>

                            <!-- Fila TOTAL A CANCELAR -->
                            <tr class="bg-emerald-100/70 dark:bg-emerald-950/60 font-black text-emerald-950 dark:text-emerald-200">
                                <td class="py-2.5 px-3 text-center border-r border-emerald-200 dark:border-emerald-800/60">
                                    <i class="fas fa-check-double text-emerald-600"></i>
                                </td>
                                <td class="py-2.5 px-3 text-left font-black uppercase text-xs tracking-wider border-r border-emerald-200 dark:border-emerald-800/60">
                                    TOTAL A CANCELAR
                                </td>
                                @foreach ($conv_datosReporte['columnas_pacientes'] as $col)
                                    <td class="py-2.5 px-2 text-right font-black tabular-nums text-xs border-r border-emerald-200 dark:border-emerald-800/60">
                                        {{ number_format($col['total_a_cancelar'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2.5 px-3 text-right font-black tabular-nums text-sm bg-emerald-200 dark:bg-emerald-900 text-emerald-950 dark:text-emerald-100">
                                    Bs. {{ number_format($conv_datosReporte['gran_total_a_cancelar'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Resumen de Totales al Pie -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500 dark:text-slate-400">
                        Mostrando <strong class="text-slate-800 dark:text-slate-200">{{ count($conv_datosReporte['columnas_pacientes']) }}</strong> atenciones y <strong class="text-slate-800 dark:text-slate-200">{{ count($conv_datosReporte['filas_conceptos']) }}</strong> conceptos de servicio e insumos.
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-slate-600 dark:text-slate-300">
                            Total Facturado Convenio: <strong class="text-base text-emerald-600 dark:text-emerald-400">Bs. {{ number_format($conv_datosReporte['gran_total_a_cancelar'], 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- ========================================================================= -->
    <!-- 4. REPORTE MENSUAL DE FLUJO DE CAJA, INGRESOS Y EGRESOS (EXCEL)           -->
    <!-- ========================================================================= -->
    @if ($reporteActivo === 'flujo_caja')
        <!-- Panel de Configuración y Parámetros del Reporte de Flujo de Caja -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fas fa-file-excel text-emerald-600 dark:text-emerald-400"></i>
                        <span>Criterios del Flujo Mensual de Caja, Ingresos y Egresos</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Consolida el flujo diario de caja distinguiendo ingresos de proformas (medicamentos y servicios), ingresos extraordinarios y egresos operativos con liquidación de saldos por jornada.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="limpiarFiltrosFlujoCaja"
                        class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <i class="fas fa-undo text-[10px]"></i>
                        <span>Limpiar</span>
                    </button>
                    <a 
                        href="{{ route('administracion.reportes.flujo_caja.excel', ['mes' => $flujo_mes, 'anio' => $flujo_anio, 'sucursal_id' => $flujo_sucursal_id]) }}"
                        target="_blank"
                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                    >
                        <i class="fas fa-file-download text-xs"></i>
                        <span>Descargar Excel (.xlsx)</span>
                    </a>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Mes del Reporte -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Mes del Reporte <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="flujo_mes"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        @error('flujo_mes')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Año del Reporte -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Año <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            wire:model.live="flujo_anio"
                            min="2020"
                            max="2050"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                        @error('flujo_anio')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sucursal (Opcional) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Sucursal
                        </label>
                        <select 
                            wire:model.live="flujo_sucursal_id"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Todas las Sucursales</option>
                            @foreach ($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="previsualizarReporteFlujoCaja" 
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-table text-xs" wire:loading.remove wire:target="previsualizarReporteFlujoCaja"></i>
                        <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="previsualizarReporteFlujoCaja"></i>
                        <span wire:loading.remove wire:target="previsualizarReporteFlujoCaja">Generar Vista Previa</span>
                        <span wire:loading wire:target="previsualizarReporteFlujoCaja">Generando...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vista Previa de Flujo de Caja e Ingresos/Egresos -->
        @if ($flujo_reporteGenerado && $flujo_datosReporte)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
                <!-- Barra de Título y Descarga -->
                <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800/60 inline-block mb-1">
                            Balance Operativo Diario
                        </span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            FLUJO MENSUAL DE CAJA - MES DE {{ $flujo_datosReporte['mes_nombre'] }} {{ $flujo_datosReporte['anio'] }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ count($flujo_datosReporte['columnas_cajas']) }} sesiones de caja • Ingresos: Bs. {{ number_format($flujo_datosReporte['gran_total_ingresos'], 2) }} • Egresos: Bs. {{ number_format($flujo_datosReporte['gran_total_egresos'], 2) }} • Saldo Neto: <strong class="{{ $flujo_datosReporte['gran_saldo_neto'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">Bs. {{ number_format($flujo_datosReporte['gran_saldo_neto'], 2) }}</strong>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="descargarExcelFlujoCaja" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i class="fas fa-file-excel text-sm" wire:loading.remove wire:target="descargarExcelFlujoCaja"></i>
                            <i class="fas fa-spinner fa-spin text-sm" wire:loading wire:target="descargarExcelFlujoCaja"></i>
                            <span wire:loading.remove wire:target="descargarExcelFlujoCaja">Exportar a Excel (.xlsx)</span>
                            <span wire:loading wire:target="descargarExcelFlujoCaja">Exportando...</span>
                        </button>
                    </div>
                </div>

                <!-- Tabla Matricial de Ingresos, Egresos y Saldos -->
                <div class="overflow-x-auto max-h-[750px] border-b border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs border-collapse">
                        <!-- ================= TABLA 1: INGRESOS ================= -->
                        <thead class="sticky top-0 z-10 bg-white dark:bg-slate-900 shadow-xs">
                            <tr class="bg-slate-800 text-white text-left font-black">
                                <th colspan="{{ count($flujo_datosReporte['columnas_cajas']) + 2 }}" class="py-1.5 px-3 uppercase tracking-wider text-xs bg-slate-900">
                                    INGRESOS
                                </th>
                            </tr>
                            <!-- Fila: TIPO DE INGRESOS y FECHAS -->
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60">
                                <th class="py-1.5 px-3 min-w-[280px] text-left font-bold text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                    TIPO DE INGRESOS
                                </th>
                                @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                    <th class="py-1.5 px-2 min-w-[100px] text-center font-bold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['fecha'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 min-w-[120px] text-center font-bold text-slate-900 dark:text-white bg-slate-200 dark:bg-slate-700">TOTALES</th>
                            </tr>
                            <!-- Fila: Nro.Reporte Caja (Fondo amarillo) -->
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                <th class="py-1.5 px-3 text-left font-bold text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                    Nro.Reporte Caja
                                </th>
                                @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                    <th class="py-1.5 px-2 text-center font-extrabold bg-amber-100 text-amber-950 border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['nro_reporte'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 text-center bg-slate-100 dark:bg-slate-800 text-slate-400"></th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($flujo_datosReporte['filas_ingresos'] as $ing)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-1.5 px-3 font-semibold uppercase text-[11px] border-r border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $ing['nombre'] }}
                                    </td>
                                    @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                        @php
                                            $val = (float) ($col['valores_ingresos'][$ing['clave']] ?? 0.00);
                                        @endphp
                                        <td class="py-1.5 px-2 text-right tabular-nums text-[11px] border-r border-slate-200 dark:border-slate-800 {{ $val > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-600' }}">
                                            {{ $val > 0 ? number_format($val, 2) : '0.00' }}
                                        </td>
                                    @endforeach
                                    <td class="py-1.5 px-3 text-right font-bold tabular-nums text-[11px] bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white">
                                        {{ number_format($ing['total_fila'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Fila TOTALES INGRESOS -->
                            <tr class="bg-slate-100 dark:bg-slate-800/80 font-black border-y-2 border-slate-300 dark:border-slate-700">
                                <td class="py-2 px-3 text-left uppercase text-xs border-r border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                                    TOTALES INGRESOS
                                </td>
                                @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                    <td class="py-2 px-2 text-right font-black tabular-nums text-xs border-r border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                                        {{ number_format($col['total_ingresos'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2 px-3 text-right font-black tabular-nums text-xs bg-slate-200 dark:bg-slate-700 text-emerald-700 dark:text-emerald-400">
                                    {{ number_format($flujo_datosReporte['gran_total_ingresos'], 2) }}
                                </td>
                            </tr>

                            <!-- Espacio separador -->
                            <tr class="h-6 bg-slate-200/40 dark:bg-slate-950/60">
                                <td colspan="{{ count($flujo_datosReporte['columnas_cajas']) + 2 }}" class="py-2 px-3"></td>
                            </tr>

                            <!-- ================= TABLA 2: EGRESOS ================= -->
                            <tr class="bg-rose-900 text-white text-left font-black">
                                <th colspan="{{ count($flujo_datosReporte['columnas_cajas']) + 2 }}" class="py-1.5 px-3 uppercase tracking-wider text-xs">
                                    EGRESOS
                                </th>
                            </tr>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60">
                                <th class="py-1.5 px-3 text-left font-bold text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                    TIPO DE EGRESOS
                                </th>
                                @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                    <th class="py-1.5 px-2 text-center font-bold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                        {{ $col['fecha'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 text-center font-bold text-slate-900 dark:text-white bg-slate-200 dark:bg-slate-700">TOTALES</th>
                            </tr>

                            @foreach ($flujo_datosReporte['filas_egresos'] as $egr)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-1.5 px-3 font-semibold uppercase text-[11px] border-r border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $egr['nombre'] }}
                                    </td>
                                    @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                        @php
                                            $valEgr = (float) ($col['valores_egresos'][$egr['clave']] ?? 0.00);
                                        @endphp
                                        <td class="py-1.5 px-2 text-right tabular-nums text-[11px] border-r border-slate-200 dark:border-slate-800 {{ $valEgr > 0 ? 'font-bold text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-600' }}">
                                            {{ $valEgr > 0 ? number_format($valEgr, 2) : '0.00' }}
                                        </td>
                                    @endforeach
                                    <td class="py-1.5 px-3 text-right font-bold tabular-nums text-[11px] bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white">
                                        {{ number_format($egr['total_fila'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Fila TOTALES EGRESOS -->
                            <tr class="bg-rose-50 dark:bg-rose-950/30 font-black border-y-2 border-slate-300 dark:border-slate-700">
                                <td class="py-2 px-3 text-left uppercase text-xs border-r border-slate-200 dark:border-slate-800 text-rose-900 dark:text-rose-200">
                                    TOTALES EGRESOS
                                </td>
                                @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                    <td class="py-2 px-2 text-right font-black tabular-nums text-xs border-r border-slate-200 dark:border-slate-800 text-rose-900 dark:text-rose-200">
                                        {{ number_format($col['total_egresos'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2 px-3 text-right font-black tabular-nums text-xs bg-rose-100 dark:bg-rose-900/60 text-rose-950 dark:text-rose-100">
                                    {{ number_format($flujo_datosReporte['gran_total_egresos'], 2) }}
                                </td>
                            </tr>

                            <!-- Espacio separador -->
                            <tr class="h-4 bg-transparent">
                                <td colspan="{{ count($flujo_datosReporte['columnas_cajas']) + 2 }}"></td>
                            </tr>

                            <!-- ================= FILA ESPECIAL: SALDOS ================= -->
                            <tr class="bg-indigo-100/80 dark:bg-indigo-950/60 font-black text-indigo-950 dark:text-indigo-200 border-2 border-indigo-300 dark:border-indigo-800">
                                <td class="py-2.5 px-3 text-left font-black uppercase text-xs tracking-wider border-r border-indigo-200 dark:border-indigo-800">
                                    <i class="fas fa-coins me-1 text-indigo-600 dark:text-indigo-400"></i>
                                    SALDOS (INGRESOS - EGRESOS)
                                </td>
                                @foreach ($flujo_datosReporte['columnas_cajas'] as $col)
                                    <td class="py-2.5 px-2 text-right font-black tabular-nums text-xs border-r border-indigo-200 dark:border-indigo-800 {{ $col['saldo_neto'] >= 0 ? 'text-indigo-950 dark:text-indigo-100' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ number_format($col['saldo_neto'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2.5 px-3 text-right font-black tabular-nums text-sm bg-indigo-200 dark:bg-indigo-900 text-indigo-950 dark:text-indigo-100">
                                    Bs. {{ number_format($flujo_datosReporte['gran_saldo_neto'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen inferior -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500 dark:text-slate-400">
                        Consolidando <strong class="text-slate-800 dark:text-slate-200">{{ count($flujo_datosReporte['columnas_cajas']) }}</strong> reportes/sesiones de caja del período.
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-slate-600 dark:text-slate-300">
                            Saldo Neto Consolidado: <strong class="text-base {{ $flujo_datosReporte['gran_saldo_neto'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">Bs. {{ number_format($flujo_datosReporte['gran_saldo_neto'], 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- ========================================================================= -->
    <!-- 5. RESUMEN ANUAL DE INGRESOS Y GASTOS (EXCEL)                            -->
    <!-- ========================================================================= -->
    @if ($reporteActivo === 'resumen_anual')
        <!-- Panel de Configuración y Parámetros del Resumen Anual -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
            <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="fas fa-file-excel text-emerald-600 dark:text-emerald-400"></i>
                        <span>Criterios del Resumen Anual de Ingresos y Gastos</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Consolida las 12 gestiones mensuales del año calendario (Enero a Diciembre) distinguiendo ingresos clínicos y extraordinarios, egresos operativos y liquidación de saldo en caja.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="limpiarFiltrosAnual"
                        class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <i class="fas fa-undo text-[10px]"></i>
                        <span>Limpiar</span>
                    </button>
                    <a 
                        href="{{ route('administracion.reportes.resumen_anual.excel', ['anio' => $anual_anio, 'sucursal_id' => $anual_sucursal_id]) }}"
                        target="_blank"
                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer"
                    >
                        <i class="fas fa-file-download text-xs"></i>
                        <span>Descargar Excel (.xlsx)</span>
                    </a>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
                    <!-- Año del Reporte -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Gestión / Año <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            wire:model.live="anual_anio"
                            min="2020"
                            max="2050"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        />
                        @error('anual_anio')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sucursal (Opcional) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Sucursal
                        </label>
                        <select 
                            wire:model.live="anual_sucursal_id"
                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                            <option value="">Todas las Sucursales</option>
                            @foreach ($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="previsualizarReporteAnual" 
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-table text-xs" wire:loading.remove wire:target="previsualizarReporteAnual"></i>
                        <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="previsualizarReporteAnual"></i>
                        <span wire:loading.remove wire:target="previsualizarReporteAnual">Generar Vista Previa</span>
                        <span wire:loading wire:target="previsualizarReporteAnual">Generando...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vista Previa de Resumen Anual de Ingresos y Gastos -->
        @if ($anual_reporteGenerado && $anual_datosReporte)
            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
                <!-- Barra de Título y Descarga -->
                <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800/60 inline-block mb-1">
                            Resumen Económico Anual
                        </span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            RESUMEN INGRESOS Y GASTOS GESTIÓN {{ $anual_datosReporte['anio'] }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            12 Meses Calendario • Ingresos Anuales: Bs. {{ number_format($anual_datosReporte['gran_total_ingresos'], 2) }} • Gastos Anuales: Bs. {{ number_format($anual_datosReporte['gran_total_egresos'], 2) }} • Saldo Anual: <strong class="{{ $anual_datosReporte['gran_saldo_neto'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">Bs. {{ number_format($anual_datosReporte['gran_saldo_neto'], 2) }}</strong>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            wire:click="descargarExcelAnual" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i class="fas fa-file-excel text-sm" wire:loading.remove wire:target="descargarExcelAnual"></i>
                            <i class="fas fa-spinner fa-spin text-sm" wire:loading wire:target="descargarExcelAnual"></i>
                            <span wire:loading.remove wire:target="descargarExcelAnual">Exportar a Excel (.xlsx)</span>
                            <span wire:loading wire:target="descargarExcelAnual">Exportando...</span>
                        </button>
                    </div>
                </div>

                <!-- Tabla Matricial Anual (12 Meses) -->
                <div class="overflow-x-auto max-h-[750px] border-b border-slate-200 dark:border-slate-800">
                    <table class="w-full text-xs border-collapse">
                        <!-- ================= TABLA 1: INGRESOS ================= -->
                        <thead class="sticky top-0 z-10 bg-white dark:bg-slate-900 shadow-xs">
                            <tr class="bg-slate-800 text-white text-left font-black">
                                <th colspan="14" class="py-1.5 px-3 uppercase tracking-wider text-xs bg-slate-900">
                                    INGRESOS
                                </th>
                            </tr>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60">
                                <th class="py-1.5 px-3 min-w-[280px] text-left font-bold text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                    TIPO DE INGRESOS
                                </th>
                                @foreach ($anual_datosReporte['meses'] as $mInfo)
                                    <th class="py-1.5 px-2 min-w-[95px] text-center font-bold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                        {{ $mInfo['mes_nombre'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 min-w-[120px] text-center font-bold text-slate-900 dark:text-white bg-slate-200 dark:bg-slate-700">TOTALES ANUAL</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach ($anual_datosReporte['filas_ingresos'] as $ing)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-1.5 px-3 font-semibold uppercase text-[11px] border-r border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $ing['nombre'] }}
                                    </td>
                                    @foreach ($anual_datosReporte['meses'] as $mInfo)
                                        @php
                                            $val = (float) ($mInfo['valores_ingresos'][$ing['clave']] ?? 0.00);
                                        @endphp
                                        <td class="py-1.5 px-2 text-right tabular-nums text-[11px] border-r border-slate-200 dark:border-slate-800 {{ $val > 0 ? 'font-bold text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-600' }}">
                                            {{ $val > 0 ? number_format($val, 2) : '0.00' }}
                                        </td>
                                    @endforeach
                                    <td class="py-1.5 px-3 text-right font-bold tabular-nums text-[11px] bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white">
                                        {{ number_format($ing['total_fila'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Fila TOTALES INGRESOS -->
                            <tr class="bg-slate-100 dark:bg-slate-800/80 font-black border-y-2 border-slate-300 dark:border-slate-700">
                                <td class="py-2 px-3 text-left uppercase text-xs border-r border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                                    TOTALES INGRESOS
                                </td>
                                @foreach ($anual_datosReporte['meses'] as $mInfo)
                                    <td class="py-2 px-2 text-right font-black tabular-nums text-xs border-r border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                                        {{ number_format($mInfo['total_ingresos'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2 px-3 text-right font-black tabular-nums text-xs bg-slate-200 dark:bg-slate-700 text-emerald-700 dark:text-emerald-400">
                                    {{ number_format($anual_datosReporte['gran_total_ingresos'], 2) }}
                                </td>
                            </tr>

                            <!-- Espacio separador -->
                            <tr class="h-6 bg-slate-200/40 dark:bg-slate-950/60">
                                <td colspan="14" class="py-2 px-3"></td>
                            </tr>

                            <!-- ================= TABLA 2: EGRESOS ================= -->
                            <tr class="bg-rose-900 text-white text-left font-black">
                                <th colspan="14" class="py-1.5 px-3 uppercase tracking-wider text-xs">
                                    EGRESOS
                                </th>
                            </tr>
                            <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800/60">
                                <th class="py-1.5 px-3 text-left font-bold text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-800">
                                    TIPO DE EGRESOS
                                </th>
                                @foreach ($anual_datosReporte['meses'] as $mInfo)
                                    <th class="py-1.5 px-2 text-center font-bold text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800">
                                        {{ $mInfo['mes_nombre'] }}
                                    </th>
                                @endforeach
                                <th class="py-1.5 px-3 text-center font-bold text-slate-900 dark:text-white bg-slate-200 dark:bg-slate-700">TOTALES ANUAL</th>
                            </tr>

                            @foreach ($anual_datosReporte['filas_egresos'] as $egr)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="py-1.5 px-3 font-semibold uppercase text-[11px] border-r border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $egr['nombre'] }}
                                    </td>
                                    @foreach ($anual_datosReporte['meses'] as $mInfo)
                                        @php
                                            $valEgr = (float) ($mInfo['valores_egresos'][$egr['clave']] ?? 0.00);
                                        @endphp
                                        <td class="py-1.5 px-2 text-right tabular-nums text-[11px] border-r border-slate-200 dark:border-slate-800 {{ $valEgr > 0 ? 'font-bold text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-600' }}">
                                            {{ $valEgr > 0 ? number_format($valEgr, 2) : '0.00' }}
                                        </td>
                                    @endforeach
                                    <td class="py-1.5 px-3 text-right font-bold tabular-nums text-[11px] bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white">
                                        {{ number_format($egr['total_fila'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <!-- Fila TOTALES EGRESOS -->
                            <tr class="bg-rose-50 dark:bg-rose-950/30 font-black border-y-2 border-slate-300 dark:border-slate-700">
                                <td class="py-2 px-3 text-left uppercase text-xs border-r border-slate-200 dark:border-slate-800 text-rose-900 dark:text-rose-200">
                                    TOTALES EGRESOS
                                </td>
                                @foreach ($anual_datosReporte['meses'] as $mInfo)
                                    <td class="py-2 px-2 text-right font-black tabular-nums text-xs border-r border-slate-200 dark:border-slate-800 text-rose-900 dark:text-rose-200">
                                        {{ number_format($mInfo['total_egresos'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2 px-3 text-right font-black tabular-nums text-xs bg-rose-100 dark:bg-rose-900/60 text-rose-950 dark:text-rose-100">
                                    {{ number_format($anual_datosReporte['gran_total_egresos'], 2) }}
                                </td>
                            </tr>

                            <!-- Espacio separador -->
                            <tr class="h-4 bg-transparent">
                                <td colspan="14"></td>
                            </tr>

                            <!-- ================= FILA ESPECIAL: SALDO EN CAJA ================= -->
                            <tr class="bg-blue-100/90 dark:bg-blue-950/70 font-black text-blue-950 dark:text-blue-100 border-2 border-blue-300 dark:border-blue-800">
                                <td class="py-2.5 px-3 text-left font-black uppercase text-xs tracking-wider border-r border-blue-200 dark:border-blue-800">
                                    <i class="fas fa-coins me-1 text-blue-600 dark:text-blue-400"></i>
                                    SALDO EN CAJA (INGRESOS - EGRESOS)
                                </td>
                                @foreach ($anual_datosReporte['meses'] as $mInfo)
                                    <td class="py-2.5 px-2 text-right font-black tabular-nums text-xs border-r border-blue-200 dark:border-blue-800 {{ $mInfo['saldo_neto'] >= 0 ? 'text-blue-950 dark:text-blue-100' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ number_format($mInfo['saldo_neto'], 2) }}
                                    </td>
                                @endforeach
                                <td class="py-2.5 px-3 text-right font-black tabular-nums text-sm bg-blue-200 dark:bg-blue-900 text-blue-950 dark:text-blue-100">
                                    Bs. {{ number_format($anual_datosReporte['gran_saldo_neto'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen inferior -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500 dark:text-slate-400">
                        Consolidación anual de los 12 meses de la gestión <strong class="text-slate-800 dark:text-slate-200">{{ $anual_datosReporte['anio'] }}</strong>.
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-slate-600 dark:text-slate-300">
                            Saldo Anual Consolidado: <strong class="text-base {{ $anual_datosReporte['gran_saldo_neto'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">Bs. {{ number_format($anual_datosReporte['gran_saldo_neto'], 2) }}</strong>
                        </span>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
