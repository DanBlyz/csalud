<div class="space-y-6">
    <!-- Breadcrumb y Encabezado del Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Inicio</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800 dark:text-slate-200">Cobranzas</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/20">
                    <i class="fas fa-cash-register text-lg"></i>
                </span>
                Cobro y Liquidación de Pagos
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Liquidación en tiempo real, pagos divididos (Efectivo, QR, Transferencia) y emisión de comprobantes contables.
            </p>
        </div>

        <!-- Selector de Sucursal (si es multi-sucursal) -->
        <div class="flex items-center gap-3">
            @if(auth()->user()->esAdmin() || $sucursales->count() > 1)
                <div class="flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-1.5 shadow-xs">
                    <i class="fas fa-hospital text-xs text-emerald-500"></i>
                    <select wire:model.live="sucursalId" class="text-xs bg-transparent border-0 font-medium text-slate-700 dark:text-slate-300 focus:ring-0 py-0 ps-1 pe-6 cursor-pointer">
                        <option value="">Todas las Sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <!-- Tarjetas de Arqueo y Recaudación Diaria -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total General del Día -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg shadow-emerald-600/10 p-5 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Recaudación de Hoy</p>
                    <h3 class="text-2xl sm:text-3xl font-black font-mono mt-1">
                        Bs. {{ number_format($arqueoHoy['total_general'], 2) }}
                    </h3>
                    <p class="text-[11px] text-emerald-100/90 mt-1 flex items-center gap-1">
                        <i class="fas fa-receipt text-[10px]"></i> {{ $arqueoHoy['cantidad_transacciones'] }} cobros registrados hoy
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-coins text-2xl text-emerald-100"></i>
                </div>
            </div>
        </div>

        <!-- Efectivo Hoy -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total en Efectivo</p>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-800 dark:text-slate-100 mt-1">
                        Bs. {{ number_format($arqueoHoy['total_efectivo'], 2) }}
                    </h3>
                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                        <i class="fas fa-money-bill-wave"></i> En mostrador
                    </span>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <i class="fas fa-money-bill-alt text-xl"></i>
                </div>
            </div>
        </div>

        <!-- QR Hoy -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Código QR</p>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-800 dark:text-slate-100 mt-1">
                        Bs. {{ number_format($arqueoHoy['total_qr'], 2) }}
                    </h3>
                    <span class="inline-flex items-center gap-1 text-[11px] text-blue-600 dark:text-blue-400 font-semibold mt-1">
                        <i class="fas fa-qrcode"></i> Cobros electrónicos
                    </span>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl">
                    <i class="fas fa-qrcode text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Transferencia Hoy -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Transferencias</p>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-800 dark:text-slate-100 mt-1">
                        Bs. {{ number_format($arqueoHoy['total_transferencia'], 2) }}
                    </h3>
                    <span class="inline-flex items-center gap-1 text-[11px] text-purple-600 dark:text-purple-400 font-semibold mt-1">
                        <i class="fas fa-university"></i> Depósitos bancarios
                    </span>
                </div>
                <div class="p-3 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-xl">
                    <i class="fas fa-exchange-alt text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Navegación de Caja -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Tabs -->
            <div class="flex items-center gap-2 border-b md:border-b-0 border-slate-200 dark:border-slate-800 pb-2 md:pb-0 overflow-x-auto">
                <button 
                    type="button" 
                    wire:click="cambiarTab('pendientes')" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'pendientes' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    <i class="fas fa-hand-holding-usd text-xs"></i>
                    <span>Cuentas por Cobrar (Pendientes)</span>
                </button>

                <button 
                    type="button" 
                    wire:click="cambiarTab('pagadas')" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'pagadas' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    <i class="fas fa-check-circle text-xs"></i>
                    <span>Historial de Liquidaciones (Pagadas)</span>
                </button>
            </div>

            <!-- Buscador y Selector de Cantidad -->
            <div class="flex items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por paciente, CI o N°..."
                        class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 ps-9 pe-3 py-2 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    >
                    <i class="fas fa-search absolute left-3 top-2.5 text-xs text-slate-400"></i>
                </div>

                <select 
                    wire:model.live="perPage" 
                    class="text-xs rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 py-2 px-3 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- TABLA: Cuentas por Cobrar (Pendientes) -->
        @if($activeTab === 'pendientes')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Proforma / Fecha</th>
                            <th class="px-5 py-3.5">Paciente</th>
                            <th class="px-5 py-3.5">Sede & Atención</th>
                            <th class="px-5 py-3.5">Composición de Costo</th>
                            <th class="px-5 py-3.5 text-right">Total Cuenta</th>
                            <th class="px-5 py-3.5 text-right">Saldo Pendiente</th>
                            <th class="px-5 py-3.5 text-center">Acciones de Cobro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($proformas as $proforma)
                            @php
                                $saldo = $proforma->saldoPendiente();
                                $total = $proforma->costo_total;
                                $pagado = $proforma->totalPagado();
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                                <!-- Proforma / Fecha -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">
                                            #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        Ingreso: {{ $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y H:i') : $proforma->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>

                                <!-- Paciente -->
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $proforma->paciente->nombre_completo ?? 'Sin asignar' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                        <span>CI: {{ $proforma->paciente->cedula ?? '-' }}</span>
                                        @if($proforma->paciente && $proforma->paciente->antecedentes_alergias)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400">
                                                Alergia
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Sede & Atención -->
                                <td class="px-5 py-4">
                                    <span class="font-medium text-slate-800 dark:text-slate-200 block">
                                        {{ $proforma->sucursal->nombre ?? 'Sede Central' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 inline-block mt-0.5">
                                        {{ $proforma->tipo_atencion }} @if($proforma->pieza) ({{ $proforma->pieza }}) @endif
                                    </span>
                                </td>

                                <!-- Composición de Costo -->
                                <td class="px-5 py-4 text-[11px]">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                            <span>Servicios:</span>
                                            <strong class="font-mono text-slate-800 dark:text-slate-200">Bs. {{ number_format($proforma->servicios->sum('costo_final'), 2) }}</strong>
                                        </div>
                                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                            <span>Farmacia despachada:</span>
                                            <strong class="font-mono text-emerald-600 dark:text-emerald-400">Bs. {{ number_format($proforma->totalDespachosFarmacia(), 2) }}</strong>
                                        </div>
                                        @if($proforma->consumosExtras->count() > 0)
                                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                                <span>Insumos extras:</span>
                                                <strong class="font-mono text-purple-600 dark:text-purple-400">Bs. {{ number_format($proforma->consumosExtras->sum(fn($c) => $c->cantidad * $c->precio_unitario), 2) }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Total Cuenta -->
                                <td class="px-5 py-4 text-right">
                                    <div class="text-sm font-black font-mono text-slate-900 dark:text-white">
                                        Bs. {{ number_format($total, 2) }}
                                    </div>
                                    @if($pagado > 0)
                                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold block">
                                            Abonado: Bs. {{ number_format($pagado, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Saldo Pendiente -->
                                <td class="px-5 py-4 text-right">
                                    <div class="text-base font-black font-mono {{ $saldo > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600' }}">
                                        Bs. {{ number_format($saldo, 2) }}
                                    </div>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $proforma->estado === 'En Proceso' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' }}">
                                        {{ $proforma->estado }}
                                    </span>
                                </td>

                                <!-- Acciones de Cobro -->
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
                                            type="button" 
                                            wire:click="abrirModalCobro({{ $proforma->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm hover:shadow transition transform active:scale-95 cursor-pointer"
                                        >
                                            <i class="fas fa-cash-register text-xs"></i>
                                            <span>Cobrar</span>
                                        </button>

                                        <a 
                                            href="{{ route('proformas.show', $proforma->id) }}" 
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs transition"
                                            title="Ver detalles de la proforma"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a 
                                            href="{{ route('proformas.pdf.detalle', $proforma->id) }}" 
                                            target="_blank"
                                            class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 text-xs transition"
                                            title="Imprimir Detalle Clínico PDF"
                                        >
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <i class="fas fa-check-circle text-3xl mb-2 text-emerald-400/60 block"></i>
                                    No hay proformas pendientes de cobro en esta sucursal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <!-- TABLA: Historial de Liquidaciones (Pagadas) -->
        @if($activeTab === 'pagadas')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Proforma / Cierre</th>
                            <th class="px-5 py-3.5">Paciente</th>
                            <th class="px-5 py-3.5">Sede</th>
                            <th class="px-5 py-3.5">Métodos Utilizados</th>
                            <th class="px-5 py-3.5 text-right">Total Liquidado</th>
                            <th class="px-5 py-3.5 text-center">Estado</th>
                            <th class="px-5 py-3.5 text-center">Documentos e Impresión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($proformas as $proforma)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                                <!-- Proforma / Cierre -->
                                <td class="px-5 py-4">
                                    <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">
                                        #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">
                                        Cierre: {{ $proforma->fecha_salida ? $proforma->fecha_salida->format('d/m/Y H:i') : $proforma->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>

                                <!-- Paciente -->
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $proforma->paciente->nombre_completo ?? 'Sin asignar' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        CI: {{ $proforma->paciente->cedula ?? '-' }}
                                    </div>
                                </td>

                                <!-- Sede -->
                                <td class="px-5 py-4">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ $proforma->sucursal->nombre ?? 'Sede Central' }}
                                    </span>
                                </td>

                                <!-- Métodos Utilizados -->
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($proforma->pagos as $pago)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $pago->tipo_pago === 'Efectivo' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : ($pago->tipo_pago === 'QR' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300') }}">
                                                {{ $pago->tipo_pago }}: Bs. {{ number_format($pago->monto, 2) }}
                                                @if($pago->numero_referencia)
                                                    <span class="opacity-75">({{ $pago->numero_referencia }})</span>
                                                @endif
                                            </span>
                                        @empty
                                            <span class="text-slate-400 text-[11px]">Sin registro de pago</span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Total Liquidado -->
                                <td class="px-5 py-4 text-right">
                                    <div class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400">
                                        Bs. {{ number_format($proforma->costo_total, 2) }}
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i class="fas fa-check-circle text-[9px]"></i> PAGADA
                                    </span>
                                </td>

                                <!-- Documentos e Impresión -->
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Recibo de Caja PDF -->
                                        <a 
                                            href="{{ route('proformas.pdf.recibo', $proforma->id) }}" 
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold text-xs border border-emerald-200 dark:border-emerald-800 transition"
                                            title="Imprimir Recibo Oficial"
                                        >
                                            <i class="fas fa-receipt text-xs"></i>
                                            <span>Recibo</span>
                                        </a>

                                        <!-- Detalle Clínico PDF -->
                                        <a 
                                            href="{{ route('proformas.pdf.detalle', $proforma->id) }}" 
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-xs border border-blue-200 dark:border-blue-800 transition"
                                            title="Imprimir Detalle Clínico-Administrativo"
                                        >
                                            <i class="fas fa-file-medical text-xs"></i>
                                            <span>Detalle</span>
                                        </a>

                                        <!-- Ver Proforma -->
                                        <a 
                                            href="{{ route('proformas.show', $proforma->id) }}" 
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs transition"
                                            title="Ver Proforma"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <i class="fas fa-file-invoice-dollar text-3xl mb-2 text-slate-400 block"></i>
                                    No hay proformas liquidadas en el historial.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Paginación Única Estándar -->
        @if ($proformas->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $proformas->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL REACTIVO DE COBRO Y LIQUIDACIÓN -->
    @if($modalCobroOpen && $proformaCobro)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Fondo oscuro -->
                <div 
                    class="fixed inset-0 transition-opacity bg-slate-950/70 backdrop-blur-xs" 
                    wire:click="cerrarModalCobro" 
                    aria-hidden="true"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-800">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 text-white flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Cobro y Liquidaciones</span>
                            <h3 class="text-lg font-black flex items-center gap-2">
                                <i class="fas fa-cash-register"></i>
                                Liquidar Proforma #{{ str_pad($proformaCobro->id, 5, '0', STR_PAD_LEFT) }}
                            </h3>
                            <p class="text-xs text-emerald-100 mt-0.5">
                                Paciente: <strong>{{ $proformaCobro->paciente->nombre_completo ?? 'N/D' }}</strong> (CI: {{ $proformaCobro->paciente->cedula ?? '-' }})
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="cerrarModalCobro" 
                            class="text-emerald-100 hover:text-white p-2 rounded-xl hover:bg-white/10 transition"
                        >
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Desglose de Cuenta y Saldos -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60">
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Costo Total</span>
                                <div class="text-xl font-black font-mono text-slate-800 dark:text-slate-100">
                                    Bs. {{ number_format($proformaCobro->costo_total, 2) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Ya Abonado</span>
                                <div class="text-xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                                    Bs. {{ number_format($proformaCobro->totalPagado(), 2) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Pendiente</span>
                                <div class="text-xl font-black font-mono text-rose-600 dark:text-rose-400">
                                    Bs. {{ number_format($proformaCobro->saldoPendiente(), 2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Líneas de Pago Divididas -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="fas fa-layer-group text-emerald-500"></i> Métodos y Líneas de Pago
                                </label>
                                <button 
                                    type="button" 
                                    wire:click="agregarLineaPago" 
                                    class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition cursor-pointer"
                                >
                                    <i class="fas fa-plus-circle"></i> Agregar Método
                                </button>
                            </div>

                            <!-- Filas dinámicas -->
                            <div class="space-y-3">
                                @php $sumaTemporal = 0.00; @endphp
                                @foreach($lineasPago as $index => $linea)
                                    @php $sumaTemporal += (float) ($linea['monto'] ?? 0); @endphp
                                    <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs space-y-2">
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                            <!-- Tipo de Pago -->
                                            <div class="sm:col-span-4">
                                                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Método</label>
                                                <select 
                                                    wire:model.live="lineasPago.{{ $index }}.tipo_pago" 
                                                    class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                                >
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="QR">QR</option>
                                                    <option value="Transferencia">Transferencia Bancaria</option>
                                                </select>
                                                @error("lineasPago.{$index}.tipo_pago")
                                                    <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Monto -->
                                            <div class="sm:col-span-4">
                                                <div class="flex items-center justify-between mb-1">
                                                    <label class="text-[10px] font-bold uppercase text-slate-400 block">Monto (Bs.)</label>
                                                    <button 
                                                        type="button" 
                                                        wire:click="llenarSaldoRestante({{ $index }})" 
                                                        class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline"
                                                    >
                                                        Auto-llenar
                                                    </button>
                                                </div>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    min="0.01" 
                                                    wire:model.live="lineasPago.{{ $index }}.monto" 
                                                    placeholder="0.00"
                                                    class="w-full text-xs font-mono font-bold rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                                >
                                                @error("lineasPago.{$index}.monto")
                                                    <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Nro. Referencia -->
                                            <div class="sm:col-span-3">
                                                <label class="text-[10px] font-bold uppercase text-slate-400 block mb-1">
                                                    N° Referencia 
                                                    @if(in_array($linea['tipo_pago'], ['QR', 'Transferencia']))
                                                        <span class="text-rose-500">*</span>
                                                    @endif
                                                </label>
                                                <input 
                                                    type="text" 
                                                    wire:model.live="lineasPago.{{ $index }}.numero_referencia" 
                                                    placeholder="{{ $linea['tipo_pago'] === 'Efectivo' ? 'Opcional' : 'N° Comprobante' }}"
                                                    class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 py-2 px-3 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                                >
                                                @error("lineasPago.{$index}.numero_referencia")
                                                    <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <!-- Botón Eliminar Fila -->
                                            <div class="sm:col-span-1 text-center pt-4 sm:pt-3">
                                                @if(count($lineasPago) > 1)
                                                    <button 
                                                        type="button" 
                                                        wire:click="eliminarLineaPago({{ $index }})" 
                                                        class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition"
                                                        title="Eliminar método"
                                                    >
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Barra de Balance de Pago en Vivo -->
                            <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Total a Pagar en estas líneas:</span>
                                    <div class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                                        Bs. {{ number_format($sumaTemporal, 2) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Saldo Restante tras cobro:</span>
                                    @php $restante = max(0.00, $proformaCobro->saldoPendiente() - $sumaTemporal); @endphp
                                    <div class="text-lg font-black font-mono {{ $restante > 0 ? 'text-amber-500' : 'text-emerald-500' }}">
                                        Bs. {{ number_format($restante, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-slate-50 dark:bg-slate-800/60 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                        <button 
                            type="button" 
                            wire:click="cerrarModalCobro" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer"
                        >
                            Cancelar
                        </button>

                        <button 
                            type="button" 
                            wire:click="procesarCobro" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 hover:shadow-lg transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="procesarCobro">
                                <i class="fas fa-check-circle text-xs"></i>
                                Confirmar y Procesar Cobro
                            </span>
                            <span wire:loading wire:target="procesarCobro" class="inline-flex items-center gap-2">
                                <i class="fas fa-circle-notch fa-spin text-xs"></i>
                                Procesando transacción...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
