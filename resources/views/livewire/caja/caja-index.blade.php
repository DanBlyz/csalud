<div class="space-y-6">
    <!-- Breadcrumb y Encabezado del Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Inicio</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800 dark:text-slate-200">Caja y Finanzas</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-500/20">
                    <i class="fas fa-cash-register text-lg"></i>
                </span>
                Cobro y Gestión de Caja
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Apertura y cierre de turnos, arqueo con desgloses (Efectivo, QR, Transferencia), cobro de proformas y movimientos extras.
            </p>
        </div>

        <!-- Acciones Globales y Selector de Sucursal -->
        <div class="flex flex-wrap items-center gap-3">
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

            @if($cajaActiva)
                <button 
                    type="button" 
                    wire:click="abrirModalMovimiento('Ingreso Extra')" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer"
                >
                    <i class="fas fa-plus-circle text-xs"></i>
                    <span>Ingreso Extra</span>
                </button>
                <button 
                    type="button" 
                    wire:click="abrirModalMovimiento('Egreso Caja')" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition cursor-pointer"
                >
                    <i class="fas fa-arrow-circle-up text-xs"></i>
                    <span>Salida / Gasto</span>
                </button>
                <button 
                    type="button" 
                    wire:click="abrirModalCierre" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-black dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs shadow-xs transition cursor-pointer"
                >
                    <i class="fas fa-lock text-xs"></i>
                    <span>Cerrar Caja</span>
                </button>
            @else
                <button 
                    type="button" 
                    wire:click="abrirModalApertura" 
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 hover:shadow-lg transition cursor-pointer"
                >
                    <i class="fas fa-key text-xs"></i>
                    <span>Aperturar Caja</span>
                </button>
            @endif
        </div>
    </div>

    <!-- BANNER DE ESTADO DE LA CAJA ACTIVA -->
    @if($cajaActiva)
        <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="p-3 rounded-xl bg-emerald-500 text-white shadow-sm shrink-0">
                    <i class="fas fa-door-open text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            TURNO ABIERTO #{{ $cajaActiva->id }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            Iniciado el {{ $cajaActiva->fecha_apertura->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-700 dark:text-slate-300 mt-1">
                        <span><strong>Cajero:</strong> {{ $cajaActiva->user->name }}</span>
                        <span><strong>Sucursal:</strong> {{ $cajaActiva->sucursal->nombre ?? 'Principal' }}</span>
                        <span><strong>Fondo Apertura:</strong> Bs. {{ number_format($cajaActiva->monto_apertura, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-emerald-200 dark:border-emerald-800/60">
                <div class="text-left md:text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block tracking-wider">Efectivo en Gaveta</span>
                    <div class="text-xl font-black font-mono text-emerald-700 dark:text-emerald-400">
                        Bs. {{ number_format($cajaActiva->saldoEsperadoEfectivo(), 2) }}
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="abrirModalCierre" 
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer"
                >
                    Arqueo y Cierre
                </button>
            </div>
        </div>
    @else
        <div class="rounded-2xl p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="p-3 rounded-xl bg-amber-500 text-white shadow-sm shrink-0">
                    <i class="fas fa-lock text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900 dark:text-amber-300">
                        No tiene una caja abierta en esta sucursal
                    </h3>
                    <p class="text-xs text-amber-800/90 dark:text-amber-400/90 mt-0.5">
                        Debe realizar la apertura de su turno para habilitar la recepción de cobros, pagos extras y salidas de gaveta.
                    </p>
                </div>
            </div>
            <button 
                type="button" 
                wire:click="abrirModalApertura" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition cursor-pointer"
            >
                <i class="fas fa-key text-xs"></i>
                <span>Aperturar Caja Ahora</span>
            </button>
        </div>
    @endif

    <!-- Tarjetas de Arqueo y Recaudación Diaria -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Ingresos -->
        <div class="relative overflow-hidden rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/10 p-5 group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100">
                        {{ $cajaActiva ? 'Ingresos de este Turno' : 'Ingresos de Hoy' }}
                    </p>
                    <h3 class="text-2xl sm:text-3xl font-black font-mono mt-1">
                        Bs. {{ number_format($arqueoHoy['total_general'], 2) }}
                    </h3>
                    <p class="text-[11px] text-emerald-100/90 mt-1 flex items-center gap-1">
                        <i class="fas fa-receipt text-[10px]"></i> {{ $arqueoHoy['cantidad_transacciones'] }} operaciones registradas
                    </p>
                </div>
                <div class="p-3 bg-white/10 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-coins text-2xl text-emerald-100"></i>
                </div>
            </div>
        </div>

        <!-- Efectivo en Gaveta -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Efectivo en Gaveta</p>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-800 dark:text-slate-100 mt-1">
                        Bs. {{ number_format($arqueoHoy['saldo_esperado_efectivo'], 2) }}
                    </h3>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                        <span>Ing: Bs. {{ number_format($arqueoHoy['total_efectivo'], 2) }}</span>
                        <span class="text-rose-500">Sal: Bs. {{ number_format($arqueoHoy['total_egresos'], 2) }}</span>
                    </div>
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

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Navegación por Tabs -->
        <div class="p-3 sm:px-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/80 flex items-center gap-2 overflow-x-auto">
            <button 
                type="button" 
                wire:click="cambiarTab('pendientes')" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'pendientes' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800' }}"
            >
                <i class="fas fa-hand-holding-usd text-xs"></i>
                <span>Cuentas por Cobrar (Pendientes)</span>
            </button>

            <button 
                type="button" 
                wire:click="cambiarTab('pagadas')" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'pagadas' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800' }}"
            >
                <i class="fas fa-check-circle text-xs"></i>
                <span>Historial de Liquidaciones (Pagadas)</span>
            </button>

            <button 
                type="button" 
                wire:click="cambiarTab('movimientos')" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'movimientos' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800' }}"
            >
                <i class="fas fa-stream text-xs"></i>
                <span>Libro de Movimientos (Kardex)</span>
            </button>

            <button 
                type="button" 
                wire:click="cambiarTab('arqueo')" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'arqueo' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800' }}"
            >
                <i class="fas fa-history text-xs"></i>
                <span>Historial de Sesiones / Turnos</span>
            </button>
        </div>

        <!-- Card Header: Controles Estándar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPageCaja" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageCaja" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Buscador Universal -->
                <div class="w-full md:w-80">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="{{ $activeTab === 'movimientos' ? 'Buscar concepto, ref, categoría...' : 'Buscar proforma, paciente o CI...' }}"
                            class="w-full text-xs rounded-lg pl-9 pr-8 py-2 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 placeholder-slate-400 shadow-2xs transition"
                        >
                        @if($search)
                            <button 
                                wire:click="$set('search', '')" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            >
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO SEGÚN LA PESTAÑA ACTIVA -->

        <!-- TAB 1 & 2: PROFORMAS (PENDIENTES O PAGADAS) -->
        @if($activeTab === 'pendientes' || $activeTab === 'pagadas')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-[10px]">
                            <th class="py-3 px-4">Proforma</th>
                            <th class="py-3 px-4">Paciente</th>
                            <th class="py-3 px-4">Sucursal / Tipo</th>
                            <th class="py-3 px-4 text-right">Total Liquidado</th>
                            <th class="py-3 px-4 text-right">Total Abonado</th>
                            <th class="py-3 px-4 text-right">Saldo Pendiente</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($proformas as $prof)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ str_pad($prof->id, 5, '0', STR_PAD_LEFT) }}
                                    <div class="text-[10px] font-normal text-slate-400 mt-0.5">
                                        {{ $prof->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $prof->paciente->nombre_completo ?? 'N/D' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        CI: {{ $prof->paciente->cedula ?? 'S/N' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $prof->sucursal->nombre ?? 'Sede Central' }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $prof->tipo_atencion }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    Bs. {{ number_format($prof->costo_total, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    Bs. {{ number_format($prof->totalPagado(), 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $prof->saldoPendiente() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                    Bs. {{ number_format($prof->saldoPendiente(), 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($prof->estado === 'Pagada' || $prof->saldoPendiente() <= 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <i class="fas fa-check-circle text-[9px]"></i> Pagada
                                        </span>
                                    @elseif($prof->totalPagado() > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            <i class="fas fa-adjust text-[9px]"></i> Abono Parcial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <i class="fas fa-clock text-[9px]"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($prof->saldoPendiente() > 0)
                                            <button 
                                                type="button" 
                                                wire:click="abrirModalCobro({{ $prof->id }})" 
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer"
                                                title="Liquidar / Cobrar Saldo"
                                            >
                                                <i class="fas fa-cash-register text-xs"></i>
                                                <span>Cobrar</span>
                                            </button>
                                        @endif

                                        @if($prof->totalPagado() > 0)
                                            <a 
                                                href="{{ route('proformas.pdf.recibo', $prof->id) }}" 
                                                target="_blank" 
                                                class="p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                                title="Recibo Oficial de Caja (PDF)"
                                            >
                                                <i class="fas fa-receipt text-xs text-emerald-600 dark:text-emerald-400"></i>
                                            </a>
                                        @endif

                                        <a 
                                            href="{{ route('proformas.pdf.detalle', $prof->id) }}" 
                                            target="_blank" 
                                            class="p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                            title="Detalle Clínico-Administrativo (PDF)"
                                        >
                                            <i class="fas fa-file-pdf text-xs text-rose-600 dark:text-rose-400"></i>
                                        </a>

                                        <a 
                                            href="{{ route('proformas.show', $prof->id) }}" 
                                            class="p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                            title="Ver Proforma"
                                        >
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-inbox text-3xl"></i>
                                        <p class="text-xs font-semibold">No se encontraron proformas en este listado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $proformas->links() }}
            </div>

        <!-- TAB 3: LIBRO DE MOVIMIENTOS (KARDEX DE PAGOS, EXTRAS Y EGRESOS) -->
        @elseif($activeTab === 'movimientos')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-[10px]">
                            <th class="py-3 px-4">Fecha / Hora</th>
                            <th class="py-3 px-4">Tipo Movimiento</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4">Concepto / Descripción</th>
                            <th class="py-3 px-4">Método de Pago</th>
                            <th class="py-3 px-4">N° Referencia</th>
                            <th class="py-3 px-4">Cajero</th>
                            <th class="py-3 px-4 text-right">Monto (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($movimientos as $mov)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($mov->tipo_movimiento === 'Ingreso Proforma')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <i class="fas fa-file-invoice-dollar text-[9px]"></i> Ingreso Proforma
                                        </span>
                                    @elseif($mov->tipo_movimiento === 'Ingreso Extra')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            <i class="fas fa-plus-circle text-[9px]"></i> Ingreso Extra
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <i class="fas fa-arrow-circle-up text-[9px]"></i> Salida de Caja
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $mov->categoria }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-900 dark:text-white max-w-xs truncate" title="{{ $mov->concepto }}">
                                    {{ $mov->concepto }}
                                    @if($mov->proforma_id)
                                        <a href="{{ route('proformas.show', $mov->proforma_id) }}" class="text-[10px] text-teal-600 hover:underline block font-normal">
                                            Ver Proforma #{{ $mov->proforma_id }}
                                        </a>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-semibold">
                                    @if($mov->tipo_pago === 'Efectivo')
                                        <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                            <i class="fas fa-money-bill-wave text-xs"></i> Efectivo
                                        </span>
                                    @elseif($mov->tipo_pago === 'QR')
                                        <span class="text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                            <i class="fas fa-qrcode text-xs"></i> QR
                                        </span>
                                    @else
                                        <span class="text-purple-600 dark:text-purple-400 flex items-center gap-1">
                                            <i class="fas fa-university text-xs"></i> Transferencia
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                    {{ $mov->numero_referencia ?: '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $mov->user->name ?? 'Cajero' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $mov->tipo_movimiento === 'Egreso Caja' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $mov->tipo_movimiento === 'Egreso Caja' ? '-' : '+' }} Bs. {{ number_format($mov->monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-receipt text-3xl"></i>
                                        <p class="text-xs font-semibold">No se registran movimientos en este período.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $movimientos->links() }}
            </div>

        <!-- TAB 4: HISTORIAL DE SESIONES / TURNOS DE CAJA Y ARQUEOS -->
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 text-[10px]">
                            <th class="py-3 px-4">Turno</th>
                            <th class="py-3 px-4">Cajero</th>
                            <th class="py-3 px-4">Apertura</th>
                            <th class="py-3 px-4">Cierre</th>
                            <th class="py-3 px-4 text-right">Fondo Apertura</th>
                            <th class="py-3 px-4 text-right">Ingresos Totales</th>
                            <th class="py-3 px-4 text-right">Efectivo en Cierre</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($cajasHistoricas as $cj)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    #{{ str_pad($cj->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $cj->user->name ?? 'N/D' }}
                                    <div class="text-[10px] font-normal text-slate-400">
                                        {{ $cj->sucursal->nombre ?? 'Principal' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $cj->fecha_apertura ? $cj->fecha_apertura->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $cj->fecha_cierre ? $cj->fecha_cierre->format('d/m/Y H:i') : 'En curso' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                    Bs. {{ number_format($cj->monto_apertura, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    Bs. {{ number_format($cj->totalIngresos(), 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    @if($cj->isCerrada())
                                        Bs. {{ number_format($cj->monto_cierre_efectivo ?? 0, 2) }}
                                        @if($cj->diferencia_efectivo != 0)
                                            <div class="text-[10px] {{ $cj->diferencia_efectivo > 0 ? 'text-blue-500' : 'text-rose-500' }}">
                                                {{ $cj->diferencia_efectivo > 0 ? '+' : '' }}Bs. {{ number_format($cj->diferencia_efectivo, 2) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-amber-500 text-[11px]">En operación</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($cj->isAbierta())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Abierta
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                            <i class="fas fa-lock text-[9px]"></i> Cerrada
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($cj->isCerrada())
                                        <a 
                                            href="{{ route('caja.pdf.arqueo', $cj->id) }}" 
                                            target="_blank" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition"
                                            title="Imprimir Acta de Arqueo y Cierre"
                                        >
                                            <i class="fas fa-file-pdf text-rose-500 text-xs"></i>
                                            <span>Acta PDF</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fas fa-history text-3xl"></i>
                                        <p class="text-xs font-semibold">No se registran turnos de caja anteriores.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $cajasHistoricas->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: APERTURA DE CAJA                                                 -->
    <!-- ========================================================================= -->
    @if($modalAperturaOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-emerald-600 text-white shadow-sm">
                            <i class="fas fa-cash-register text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Apertura de Turno de Caja</h2>
                            <p class="text-[11px] text-slate-500">Inicie su sesión para habilitar las operaciones financieras</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModalApertura" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit="aperturarCaja" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Fondo Inicial de Apertura (Bs.) <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-slate-500 mb-2">
                            Monto en efectivo entregado para dar cambio o fondo fijo. Ingrese 0 si inicia sin dinero en gaveta.
                        </p>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">Bs.</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                wire:model="monto_apertura" 
                                placeholder="0.00"
                                class="w-full text-base font-mono font-bold rounded-xl pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>
                        @error('monto_apertura')
                            <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones de Apertura (Opcional)
                        </label>
                        <textarea 
                            wire:model="observaciones_apertura" 
                            rows="2" 
                            placeholder="Detalles del fondo recibido o anotaciones del turno..."
                            class="w-full text-xs rounded-xl p-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                        ></textarea>
                        @error('observaciones_apertura')
                            <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="cerrarModalApertura" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="aperturarCaja">
                                <i class="fas fa-check-circle text-xs"></i> Confirmar Apertura
                            </span>
                            <span wire:loading wire:target="aperturarCaja" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-circle-notch fa-spin text-xs"></i> Aperturando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 2: REGISTRAR MOVIMIENTO EXTRA (INGRESO EXTRA O SALIDA DE CAJA)      -->
    <!-- ========================================================================= -->
    @if($modalMovimientoOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between {{ $mov_tipo_movimiento === 'Ingreso Extra' ? 'bg-blue-50/50 dark:bg-blue-950/20' : 'bg-rose-50/50 dark:bg-rose-950/20' }}">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl {{ $mov_tipo_movimiento === 'Ingreso Extra' ? 'bg-blue-600' : 'bg-rose-600' }} text-white shadow-sm">
                            <i class="fas {{ $mov_tipo_movimiento === 'Ingreso Extra' ? 'fa-plus-circle' : 'fa-arrow-circle-up' }} text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ $mov_tipo_movimiento === 'Ingreso Extra' ? 'Registrar Ingreso Extra' : 'Registrar Salida / Gasto de Caja' }}
                            </h2>
                            <p class="text-[11px] text-slate-500">Asiento directo en la gaveta de la caja activa</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModalMovimiento" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit="guardarMovimiento" class="p-6 space-y-4">
                    <!-- Selector de Tipo -->
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            type="button" 
                            wire:click="$set('mov_tipo_movimiento', 'Ingreso Extra')" 
                            class="p-3 rounded-xl border text-center transition font-bold text-xs flex flex-col items-center gap-1.5 cursor-pointer {{ $mov_tipo_movimiento === 'Ingreso Extra' ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}"
                        >
                            <i class="fas fa-arrow-down text-sm text-blue-500"></i>
                            <span>Ingreso Extra</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('mov_tipo_movimiento', 'Egreso Caja')" 
                            class="p-3 rounded-xl border text-center transition font-bold text-xs flex flex-col items-center gap-1.5 cursor-pointer {{ $mov_tipo_movimiento === 'Egreso Caja' ? 'border-rose-600 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 shadow-xs' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}"
                        >
                            <i class="fas fa-arrow-up text-sm text-rose-500"></i>
                            <span>Salida de Caja</span>
                        </button>
                    </div>

                    <!-- Categoría y Método -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Categoría <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                wire:model="mov_categoria" 
                                class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                            >
                                @if($mov_tipo_movimiento === 'Ingreso Extra')
                                    <option value="Extra">Extra General</option>
                                    <option value="Certificados">Certificados Médicos</option>
                                    <option value="Fotocopias/Trámites">Fotocopias / Trámites</option>
                                    <option value="Donación">Donación</option>
                                    <option value="Otro">Otro Ingreso</option>
                                @else
                                    <option value="Gasto Operativo">Gasto Operativo</option>
                                    <option value="Servicio Básico">Servicio Básico</option>
                                    <option value="Insumos">Insumos de Limpieza/Aseo</option>
                                    <option value="Transporte">Transporte / Encomienda</option>
                                    <option value="Sueldo">Adelanto / Sueldo</option>
                                    <option value="Honorario">Honorario Médico</option>
                                    <option value="Otro">Otro Gasto</option>
                                @endif
                            </select>
                            @error('mov_categoria')
                                <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Método de Pago <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                wire:model="mov_tipo_pago" 
                                class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 py-2.5 px-3 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                            >
                                <option value="Efectivo">Efectivo</option>
                                <option value="QR">Código QR</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                            </select>
                            @error('mov_tipo_pago')
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
                                    wire:model="mov_monto" 
                                    placeholder="0.00"
                                    class="w-full text-sm font-mono font-bold rounded-xl pl-10 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                >
                            </div>
                            @error('mov_monto')
                                <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                N° Referencia / Comprobante
                                @if($mov_tipo_pago === 'Transferencia')
                                    <span class="text-rose-500">*</span>
                                @endif
                            </label>
                            <input 
                                type="text" 
                                wire:model="mov_numero_referencia" 
                                placeholder="{{ $mov_tipo_pago === 'Efectivo' ? 'Opcional' : 'N° Transacción' }}"
                                class="w-full text-xs rounded-xl px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                            >
                            @error('mov_numero_referencia')
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
                            wire:model="mov_concepto" 
                            placeholder="Ej. Compra urgente de alcohol y papel higiénico..."
                            class="w-full text-xs rounded-xl px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                        >
                        @error('mov_concepto')
                            <span class="text-[11px] text-rose-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="cerrarModalMovimiento" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl {{ $mov_tipo_movimiento === 'Ingreso Extra' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white font-bold text-xs shadow-md transition cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="guardarMovimiento">
                                <i class="fas fa-save text-xs"></i> Guardar Asiento
                            </span>
                            <span wire:loading wire:target="guardarMovimiento" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-circle-notch fa-spin text-xs"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 3: CIERRE DE CAJA Y ARQUEO DE TURNO                                 -->
    <!-- ========================================================================= -->
    @if($modalCierreOpen && $cajaActiva)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-900 text-white">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-emerald-500 text-white shadow-sm">
                            <i class="fas fa-balance-scale text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold">Cierre y Arqueo de Turno #{{ $cajaActiva->id }}</h2>
                            <p class="text-[11px] text-slate-300">Conteo físico de gaveta y balance de ingresos electrónicos</p>
                        </div>
                    </div>
                    <button type="button" wire:click="cerrarModalCierre" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit="ejecutarCierreCaja" class="p-6 space-y-5">
                    <!-- Resumen del Sistema (Calculado) -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                            Valores Calculados por el Sistema
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <span class="text-[10px] text-slate-400 block font-semibold">Fondo Apertura</span>
                                <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200">
                                    Bs. {{ number_format($cajaActiva->monto_apertura, 2) }}
                                </div>
                            </div>
                            <div class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <span class="text-[10px] text-slate-400 block font-semibold">(+) Ingresos Ef.</span>
                                <div class="font-mono font-bold text-xs text-emerald-600 dark:text-emerald-400">
                                    Bs. {{ number_format($cajaActiva->totalIngresosEfectivo(), 2) }}
                                </div>
                            </div>
                            <div class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <span class="text-[10px] text-slate-400 block font-semibold">(-) Salidas Ef.</span>
                                <div class="font-mono font-bold text-xs text-rose-600 dark:text-rose-400">
                                    Bs. {{ number_format($cajaActiva->totalEgresosEfectivo(), 2) }}
                                </div>
                            </div>
                            <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800">
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-300 block font-bold">Esperado en Gaveta</span>
                                <div class="font-mono font-black text-xs text-emerald-700 dark:text-emerald-400">
                                    Bs. {{ number_format($cajaActiva->saldoEsperadoEfectivo(), 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Declaración de Montos Físicos -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Conteo y Declaración de Fondos del Cajero
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                    Efectivo Contado (Físico) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400 text-xs">Bs.</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model.live="cierre_efectivo" 
                                        class="w-full text-xs font-mono font-bold rounded-xl pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                    >
                                </div>
                                @error('cierre_efectivo')
                                    <span class="text-[10px] text-rose-500 block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                    Total QR Declarado <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400 text-xs">Bs.</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model="cierre_qr" 
                                        class="w-full text-xs font-mono font-bold rounded-xl pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                    >
                                </div>
                                @error('cierre_qr')
                                    <span class="text-[10px] text-rose-500 block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                                    Total Transferencias <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400 text-xs">Bs.</span>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        wire:model="cierre_transferencia" 
                                        class="w-full text-xs font-mono font-bold rounded-xl pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                                    >
                                </div>
                                @error('cierre_transferencia')
                                    <span class="text-[10px] text-rose-500 block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Comparador en Vivo de Diferencia de Efectivo -->
                        @php
                            $efDeclarado = (float) $cierre_efectivo;
                            $efEsperado = $cajaActiva->saldoEsperadoEfectivo();
                            $diferenciaEnVivo = $efDeclarado - $efEsperado;
                        @endphp
                        <div class="p-3 rounded-xl border {{ round($diferenciaEnVivo, 2) == 0 ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-300 text-emerald-800 dark:text-emerald-300' : ($diferenciaEnVivo > 0 ? 'bg-blue-50 dark:bg-blue-950/30 border-blue-300 text-blue-800 dark:text-blue-300' : 'bg-rose-50 dark:bg-rose-950/30 border-rose-300 text-rose-800 dark:text-rose-300') }} flex items-center justify-between text-xs font-bold">
                            <span class="flex items-center gap-1.5">
                                <i class="fas {{ round($diferenciaEnVivo, 2) == 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                                Diferencia en Efectivo:
                            </span>
                            <span class="font-mono text-sm">
                                @if(round($diferenciaEnVivo, 2) == 0)
                                    Bs. 0.00 (Cuadrada)
                                @elseif($diferenciaEnVivo > 0)
                                    + Bs. {{ number_format($diferenciaEnVivo, 2) }} (Sobrante)
                                @else
                                    - Bs. {{ number_format(abs($diferenciaEnVivo), 2) }} (Faltante)
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Observaciones de Cierre -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                            Observaciones de Cierre (Opcional)
                        </label>
                        <textarea 
                            wire:model="cierre_observaciones" 
                            rows="2" 
                            placeholder="Detalle o justificación de diferencias si existiesen..."
                            class="w-full text-xs rounded-xl p-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                        ></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-800">
                        <button 
                            type="button" 
                            wire:click="cerrarModalCierre" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="ejecutarCierreCaja">
                                <i class="fas fa-lock text-xs"></i> Finalizar y Cerrar Turno
                            </span>
                            <span wire:loading wire:target="ejecutarCierreCaja" class="inline-flex items-center gap-1.5">
                                <i class="fas fa-circle-notch fa-spin text-xs"></i> Cerrando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 4: COBRO DE PROFORMA (SPLIT PAYMENTS CON LÍNEAS DINÁMICAS)          -->
    <!-- ========================================================================= -->
    @if($modalCobroOpen && $proformaCobro)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150 max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-xl bg-emerald-600 text-white shadow-sm">
                            <i class="fas fa-cash-register text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                Liquidación de Proforma #{{ str_pad($proformaCobro->id, 5, '0', STR_PAD_LEFT) }}
                            </h2>
                            <p class="text-[11px] text-slate-500">
                                Paciente: <strong class="text-slate-700 dark:text-slate-300">{{ $proformaCobro->paciente->nombre_completo ?? 'N/D' }}</strong> 
                                &bull; CI: {{ $proformaCobro->paciente->cedula ?? 'S/N' }}
                            </p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        wire:click="cerrarModalCobro" 
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg"
                    >
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6 overflow-y-auto">
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
                                                <option value="QR">Código QR</option>
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
                                                    class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition cursor-pointer"
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
                                <span class="text-xs text-slate-500 dark:text-slate-400">Total en estas líneas de pago:</span>
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
                <div class="bg-slate-50 dark:bg-slate-800/60 px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3 shrink-0">
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
    @endif
</div>
