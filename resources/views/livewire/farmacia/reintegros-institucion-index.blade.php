<div>
    {{-- Encabezado de Página --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-exchange-alt text-base"></i>
                </span>
                Reintegro por Institución
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Reposición física de medicamentos e insumos usados por pacientes de convenios e instituciones (en proformas En Curso), ingreso de nuevos lotes y descuento automático por antigüedad de proforma.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @anypermiso(['farmacia.reintegros.crear', 9])
                <button wire:click="abrirModalReintegro" wire:loading.attr="disabled" type="button"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <i class="fas fa-plus"></i>
                    <span>Registrar Reintegro por Institución</span>
                </button>
            @endanypermiso
        </div>
    </div>

    {{-- Tarjetas de Métricas / KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-800 flex items-center justify-center text-teal-600 dark:text-teal-400 text-lg shrink-0">
                <i class="fas fa-file-medical-alt"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Actas de Reintegro</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($totalActas) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg shrink-0">
                <i class="fas fa-boxes"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unidades Reintegradas (Nuevos Lotes)</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ number_format($totalUnidadesReintegradas) }} <span class="text-xs font-semibold text-slate-400">unds.</span></p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg shrink-0">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Monto Descontado en Proformas</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">Bs. {{ number_format($totalMontoDescontado, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Contenedor Principal de la Tabla --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        {{-- Card Header: Controles de Tabla (Estándar Sistema Farma) --}}
        <div class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-100 dark:border-slate-800">
            {{-- Izquierda: Selector de registros + Filtros adicionales --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                    <span>Mostrar</span>
                    <select wire:model.live="perPage"
                        class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>registros</span>
                </div>

                <div class="h-4 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>

                {{-- Filtro Institución --}}
                <select wire:model.live="filtroInstitucion"
                    class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Todas las Instituciones</option>
                    @foreach ($instituciones as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                    @endforeach
                </select>

                {{-- Filtro Sucursal --}}
                <select wire:model.live="filtroSucursal"
                    class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Todas las Sedes</option>
                    @foreach ($sucursales as $suc)
                        <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                    @endforeach
                </select>

                {{-- Rango de fechas --}}
                <div class="flex items-center gap-1.5">
                    <input type="date" wire:model.live="filtroFechaDesde" title="Desde fecha"
                        class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <span class="text-xs text-slate-400">a</span>
                    <input type="date" wire:model.live="filtroFechaHasta" title="Hasta fecha"
                        class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>

                @if ($filtroInstitucion || $filtroFechaDesde || $filtroFechaHasta || $search)
                    <button wire:click="limpiarFiltros" type="button"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                @endif
            </div>

            {{-- Derecha: Buscador Reactivo --}}
            <div class="relative w-full md:w-80">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Buscar código, institución, paciente, lote..."
                    class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500">
                @if ($search)
                    <button wire:click="$set('search', '')" type="button"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                @endif
            </div>
        </div>

        {{-- Tabla de Reintegros --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3.5">Código / Fecha</th>
                        <th class="px-4 py-3.5">Institución / Sede</th>
                        <th class="px-4 py-3.5">Entregado por / Recepción</th>
                        <th class="px-4 py-3.5">Medicamentos y Nuevos Lotes</th>
                        <th class="px-4 py-3.5">Pacientes / Proformas Descontadas</th>
                        <th class="px-4 py-3.5 text-right">Monto Descontado</th>
                        <th class="px-4 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-xs">
                    @forelse ($reintegros as $item)
                        <tr wire:key="reintegro-row-{{ $item->id }}" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            {{-- Código y Fecha --}}
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-900/30 dark:text-teal-300 dark:border-teal-800">
                                    <i class="fas fa-hashtag text-[9px]"></i>{{ $item->codigo }}
                                </span>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                    <i class="fas fa-calendar-alt mr-1 text-[10px]"></i>{{ $item->fecha_reintegro?->format('d/m/Y') }}
                                </div>
                            </td>

                            {{-- Institución y Sede --}}
                            <td class="px-4 py-3.5 align-top">
                                <div class="font-bold text-slate-800 dark:text-slate-100">
                                    {{ $item->institucion?->nombre ?? 'S/I' }}
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                    <i class="fas fa-building text-[10px]"></i>
                                    <span>{{ $item->sucursal?->nombre ?? '-' }}</span>
                                    @if ($item->seccionDestino)
                                        <span class="text-slate-300 dark:text-slate-600">•</span>
                                        <span class="text-teal-600 dark:text-teal-400 font-medium">{{ $item->seccionDestino->nombre }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Entregado por / Recepción --}}
                            <td class="px-4 py-3.5 align-top">
                                <div class="text-slate-800 dark:text-slate-200 font-semibold">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Entregado por:</span>
                                    {{ $item->persona_entrega }}
                                    @if ($item->ci_persona_entrega)
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 font-normal">(C.I. {{ $item->ci_persona_entrega }})</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    <span class="font-medium">Recibido por:</span> {{ $item->usuarioCreador?->nombre_completo ?? ($item->usuarioCreador?->name ?? 'Farmacia') }}
                                </div>
                            </td>

                            {{-- Medicamentos y Nuevos Lotes --}}
                            <td class="px-4 py-3.5 align-top">
                                <div class="space-y-1.5">
                                    @foreach ($item->detalles->take(3) as $det)
                                        <div class="flex items-center justify-between gap-2 bg-slate-50 dark:bg-slate-800/70 px-2.5 py-1 rounded-lg border border-slate-200/70 dark:border-slate-700/70">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-800 dark:text-slate-200 truncate">
                                                    {{ $det->producto?->nombre ?? 'Producto' }}
                                                </p>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                                    Lote: <span class="font-mono font-bold text-teal-600 dark:text-teal-400">{{ $det->codigo_lote }}</span>
                                                    @if ($det->fecha_vencimiento)
                                                        • Vence: {{ $det->fecha_vencimiento->format('d/m/Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="shrink-0 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800">
                                                +{{ $det->cantidad_reintegrada }} und.
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($item->detalles->count() > 3)
                                        <p class="text-[11px] text-teal-600 dark:text-teal-400 font-semibold pl-1">
                                            +{{ $item->detalles->count() - 3 }} medicamento(s) más...
                                        </p>
                                    @endif
                                </div>
                            </td>

                            {{-- Pacientes / Proformas Descontadas --}}
                            <td class="px-4 py-3.5 align-top">
                                @php
                                    $proformasUnicas = $item->aplicaciones->groupBy('proforma_id');
                                @endphp
                                <div class="space-y-1">
                                    @foreach ($proformasUnicas->take(3) as $profId => $apps)
                                        @php
                                            $primeraApp = $apps->first();
                                            $sumaCantProf = $apps->sum('cantidad_descontada');
                                            $sumaSubProf = $apps->sum('subtotal_descontado');
                                        @endphp
                                        <div class="text-[11px] flex items-center justify-between gap-2 bg-amber-50/60 dark:bg-amber-900/15 px-2 py-1 rounded border border-amber-200/70 dark:border-amber-800/50">
                                            <div class="truncate">
                                                <span class="font-bold text-amber-800 dark:text-amber-300">#{{ $profId }}</span>
                                                <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $primeraApp->paciente?->nombre_completo ?? 'Paciente' }}</span>
                                            </div>
                                            <span class="shrink-0 font-bold text-slate-700 dark:text-slate-300">
                                                -{{ $sumaCantProf }} und. (Bs. {{ number_format((float) $sumaSubProf, 2) }})
                                            </span>
                                        </div>
                                    @endforeach
                                    @if ($proformasUnicas->count() > 3)
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium pl-1">
                                            +{{ $proformasUnicas->count() - 3 }} proforma(s) adicional(es)...
                                        </p>
                                    @endif
                                </div>
                            </td>

                            {{-- Monto Descontado --}}
                            <td class="px-4 py-3.5 align-top text-right whitespace-nowrap">
                                <span class="text-sm font-black text-slate-900 dark:text-white">
                                    Bs. {{ number_format((float) $item->monto_total_descontado, 2) }}
                                </span>
                                <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ $item->total_unidades }} unds. repuestas
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-3.5 align-top text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button wire:click="verDetalle({{ $item->id }})" type="button"
                                        title="Ver detalle del reintegro"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 transition-colors cursor-pointer">
                                        <i class="fas fa-eye text-xs"></i>
                                        <span>Detalle</span>
                                    </button>

                                    <a href="{{ route('farmacia.reintegros.pdf', $item->id) }}" target="_blank"
                                        title="Imprimir Acta de Reintegro en PDF"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 dark:text-rose-300 dark:border-rose-800 transition-colors">
                                        <i class="fas fa-file-pdf text-xs"></i>
                                        <span>PDF</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                                    <div class="h-12 w-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-xl mb-3">
                                        <i class="fas fa-exchange-alt"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No se encontraron actas de reintegro por institución</p>
                                    <p class="text-xs mt-1">Haga clic en "Registrar Reintegro por Institución" para reponer medicamentos usados por pacientes de convenio.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación Estándar --}}
        @if ($reintegros->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $reintegros->links() }}
            </div>
        @endif
    </div>

    {{-- ======================================================================= --}}
    {{-- MODAL 1: REGISTRAR NUEVO REINTEGRO POR INSTITUCIÓN                      --}}
    {{-- ======================================================================= --}}
    @if ($modalReintegroOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-reintegro-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalReintegro"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-6xl max-h-[92vh] flex flex-col">
                    {{-- Header Sobrio Estándar --}}
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white text-sm shrink-0">
                                <i class="fas fa-exchange-alt"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-reintegro-title">
                                    Registrar Reintegro de Medicamentos por Institución
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Solo se consideran proformas con estado <span class="font-bold text-emerald-600 dark:text-emerald-400">En Curso</span>. El descuento se aplica automáticamente desde la proforma más antigua.
                                </p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalReintegro" type="button"
                            title="Cerrar ventana"
                            class="inline-flex items-center justify-center h-8 w-8 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-lg hover:bg-slate-200/70 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    {{-- Body del Modal --}}
                    <div class="p-6 overflow-y-auto space-y-6 flex-1">
                        {{-- Paso 1: Datos de la Institución, Recepción y Persona que Entrega --}}
                        <div class="bg-slate-50/70 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-3 flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[11px] flex items-center justify-center font-bold">1</span>
                                Datos de la Institución y Entrega
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                {{-- Institución --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Institución / Convenio <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model.live="institucion_id"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">-- Seleccione una institución --</option>
                                        @foreach ($instituciones as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('institucion_id')
                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Sucursal --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Sucursal <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model.live="sucursal_id"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        @foreach ($sucursales as $suc)
                                            <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('sucursal_id')
                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Sección Destino para los Nuevos Lotes --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Sección Destino (Ingreso de Lotes) <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model="seccion_destino_id"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <option value="">-- Seleccione sección --</option>
                                        @foreach ($secciones as $sec)
                                            <option value="{{ $sec->id }}">
                                                {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Principal)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('seccion_destino_id')
                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Persona que Entrega --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Nombre de quien entrega los medicamentos <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text" wire:model="persona_entrega"
                                        placeholder="Ej. Lic. Roberto Gómez (Representante Institución)"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    @error('persona_entrega')
                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- C.I. Persona que Entrega --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        C.I. / Documento de quien entrega
                                    </label>
                                    <input type="text" wire:model="ci_persona_entrega"
                                        placeholder="Ej. 4829102 LP"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                </div>

                                {{-- Fecha de Reintegro --}}
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Fecha de Reintegro <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="date" wire:model="fecha_reintegro"
                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    @error('fecha_reintegro')
                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Observaciones Generales del Acta (Opcional)
                                </label>
                                <input type="text" wire:model="observaciones"
                                    placeholder="Ej. Reposición parcial de medicamentos según nota de entrega N° 104..."
                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        {{-- Paso 2: Medicamentos Usados por Pacientes de la Institución (Proformas En Curso) --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                            {{-- Columna Izquierda (7 cols): Lista de Medicamentos Usados en Proformas En Curso --}}
                            <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
                                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[11px] flex items-center justify-center font-bold">2</span>
                                        Medicamentos Usados por Pacientes (Proformas En Curso)
                                    </h4>
                                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                        {{ count($consumos_institucion) }} producto(s)
                                    </span>
                                </div>

                                @if (!$institucion_id)
                                    <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                                        <i class="fas fa-hospital-alt text-3xl mb-2"></i>
                                        <p class="text-xs font-medium">Seleccione primero una Institución / Convenio arriba para verificar los medicamentos usados por sus pacientes.</p>
                                    </div>
                                @elseif (empty($consumos_institucion))
                                    <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                                        <i class="fas fa-check-circle text-3xl text-emerald-500 mb-2"></i>
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">No hay medicamentos pendientes de reintegro</p>
                                        <p class="text-[11px] mt-1">Esta institución no tiene pacientes con proformas <strong>En Curso</strong> que presenten consumos de farmacia pendientes en esta sucursal.</p>
                                    </div>
                                @else
                                    <div class="overflow-y-auto max-h-96 divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($consumos_institucion as $prodId => $consumo)
                                            @php
                                                $yaEnCola = $this->cantidadEnColaPorProducto((int) $prodId);
                                                $pendienteReintegrar = max(0, (int) $consumo['cantidad_total_usada'] - $yaEnCola);
                                                $esSeleccionado = (int) $item_producto_id === (int) $prodId;
                                            @endphp
                                            <div wire:key="consumo-inst-prod-{{ $prodId }}"
                                                class="p-3.5 transition-colors {{ $esSeleccionado ? 'bg-teal-50/70 dark:bg-teal-900/20' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/40' }}">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $consumo['producto_nombre'] }}</span>
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                                {{ $consumo['unidad_medida'] }} • {{ $consumo['marca_nombre'] }}
                                                            </span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-3">
                                                            <span>Total usado: <strong class="text-slate-800 dark:text-slate-200">{{ $consumo['cantidad_total_usada'] }} unds.</strong></span>
                                                            @if ($yaEnCola > 0)
                                                                <span class="text-teal-600 dark:text-teal-400 font-semibold">En cola: {{ $yaEnCola }} unds.</span>
                                                            @endif
                                                            <span>Disponible p/ reintegrar: <strong class="text-emerald-600 dark:text-emerald-400">{{ $pendienteReintegrar }} unds.</strong></span>
                                                        </div>
                                                    </div>

                                                    <button wire:click="seleccionarMedicamentoParaReintegro({{ $prodId }})" type="button"
                                                        @disabled($pendienteReintegrar <= 0)
                                                        class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer {{ $pendienteReintegrar > 0 ? 'bg-teal-600 hover:bg-teal-700 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}">
                                                        <i class="fas fa-arrow-right text-[11px]"></i>
                                                        <span>{{ $pendienteReintegrar > 0 ? 'Reintegrar' : 'Completado' }}</span>
                                                    </button>
                                                </div>

                                                {{-- Desglose de Pacientes ordenado por antigüedad de Proforma --}}
                                                <div class="mt-2.5 pl-2 border-l-2 border-teal-300 dark:border-teal-800 space-y-1">
                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                        Orden de descuento (Proforma más antigua primero):
                                                    </p>
                                                    @foreach ($consumo['desglose_proformas'] as $idxProf => $profItem)
                                                        <div class="flex items-center justify-between text-[11px] bg-white dark:bg-slate-800/80 px-2.5 py-1 rounded border border-slate-200/70 dark:border-slate-700/60">
                                                            <div class="flex items-center gap-1.5 truncate">
                                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px]">
                                                                    {{ $idxProf + 1 }}°
                                                                </span>
                                                                <span class="font-bold text-teal-600 dark:text-teal-400">Prof. #{{ $profItem['proforma_id'] }}</span>
                                                                <span class="text-slate-400">({{ $profItem['fecha_ingreso'] }})</span>
                                                                <span class="font-medium text-slate-700 dark:text-slate-200 truncate">— {{ $profItem['paciente_nombre'] }}</span>
                                                            </div>
                                                            <span class="font-bold text-slate-800 dark:text-slate-200 shrink-0 ml-2">
                                                                {{ $profItem['cantidad_usada'] }} unds.
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Columna Derecha (5 cols): Configurar Nuevo Lote del Medicamento Seleccionado --}}
                            <div class="lg:col-span-5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 p-4 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[11px] flex items-center justify-center font-bold">3</span>
                                        Registrar Nuevo Lote de Reintegro
                                    </h4>

                                    @if (!$item_producto_id)
                                        <div class="py-12 px-4 text-center text-slate-400 dark:text-slate-500 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl">
                                            <i class="fas fa-box-open text-3xl mb-2"></i>
                                            <p class="text-xs font-medium">Haga clic en el botón <strong>"Reintegrar"</strong> de algún medicamento de la lista izquierda para ingresar los datos de su nuevo lote y cantidad.</p>
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            <div class="p-3 rounded-lg bg-teal-50 dark:bg-teal-900/30 border border-teal-200 dark:border-teal-800">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-bold text-teal-950 dark:text-teal-200">{{ $item_producto_nombre }}</span>
                                                    <button wire:click="limpiarItemReintegro" type="button" class="text-xs text-slate-400 hover:text-rose-500 cursor-pointer" title="Quitar selección">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <p class="text-[11px] text-teal-700 dark:text-teal-300 mt-0.5">
                                                    Máximo disponible para reintegrar: <strong>{{ $item_cantidad_disponible_reintegrar }} unidades</strong>
                                                </p>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                {{-- Cantidad a Reintegrar --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                        Cantidad a Reintegrar <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="number" min="1" max="{{ $item_cantidad_disponible_reintegrar }}"
                                                        wire:model="item_cantidad_reintegrar"
                                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                    @error('item_cantidad_reintegrar')
                                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Código del Nuevo Lote --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                        Código Nuevo Lote <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="text" wire:model="item_codigo_lote"
                                                        placeholder="Ej. LOTE-2026-A"
                                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                    @error('item_codigo_lote')
                                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-3">
                                                {{-- Fecha de Vencimiento --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                        Vencimiento Lote
                                                    </label>
                                                    <input type="date" wire:model="item_fecha_vencimiento"
                                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-2.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                    @error('item_fecha_vencimiento')
                                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Precio Compra --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                        P. Compra (Bs) <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="number" step="0.01" min="0" wire:model="item_precio_compra"
                                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-2.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                    @error('item_precio_compra')
                                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Precio Venta --}}
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                        P. Venta (Bs) <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="number" step="0.01" min="0" wire:model="item_precio_venta"
                                                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-2.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                    @error('item_precio_venta')
                                                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                    Observación del Lote (Opcional)
                                                </label>
                                                <input type="text" wire:model="item_observaciones"
                                                    placeholder="Ej. Caja sellada nueva partida..."
                                                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                            </div>

                                            <button wire:click="agregarItemReintegro" type="button"
                                                class="w-full inline-flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-4 rounded-lg shadow-xs transition-colors cursor-pointer">
                                                <i class="fas fa-plus-circle"></i>
                                                <span>Agregar Lote a la Lista de Reintegro</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Paso 4: Cola de Lotes a Ingresar y Previsualización de Descuento por Proforma --}}
                        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-teal-600 text-white text-[11px] flex items-center justify-center font-bold">4</span>
                                    Resumen de Lotes a Reintegrar y Pacientes que recibirán el Descuento
                                </h4>
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                    Total Unidades: {{ $this->totalUnidadesCola }}
                                </span>
                            </div>

                            @error('cola_reintegro')
                                <div class="p-3 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-xs font-semibold border-b border-rose-200 dark:border-rose-800">
                                    {{ $message }}
                                </div>
                            @enderror

                            @if (empty($cola_reintegro))
                                <div class="p-6 text-center text-xs text-slate-400 dark:text-slate-500">
                                    Aún no se han agregado medicamentos a la cola de reintegro.
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse text-xs">
                                        <thead>
                                            <tr class="bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                                <th class="px-4 py-2.5">Medicamento / Insumo</th>
                                                <th class="px-4 py-2.5">Nuevo Lote / Vencimiento</th>
                                                <th class="px-4 py-2.5 text-center">Cant. Reintegrada</th>
                                                <th class="px-4 py-2.5">Pacientes / Proformas a Descontar (Orden Antigüedad)</th>
                                                <th class="px-4 py-2.5 text-right">Quitar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach ($cola_reintegro as $index => $itemCola)
                                                <tr wire:key="item-cola-reint-{{ $index }}">
                                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-100">
                                                        {{ $itemCola['producto_nombre'] }}
                                                        <span class="block text-[10px] font-normal text-slate-500">
                                                            {{ $itemCola['unidad_medida'] }} • {{ $itemCola['marca_nombre'] }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="font-mono font-bold text-teal-600 dark:text-teal-400">{{ $itemCola['codigo_lote'] }}</span>
                                                        <span class="block text-[10px] text-slate-500">
                                                            Vence: {{ $itemCola['fecha_vencimiento'] ? \Carbon\Carbon::parse($itemCola['fecha_vencimiento'])->format('d/m/Y') : 'Sin fecha' }}
                                                            • P.V.: Bs. {{ number_format((float) $itemCola['precio_venta'], 2) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="px-2.5 py-1 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800">
                                                            +{{ $itemCola['cantidad_reintegrada'] }} unds.
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="space-y-1">
                                                            @foreach ($itemCola['simulacion_pacientes'] as $sim)
                                                                <div class="text-[11px] flex items-center justify-between gap-2 bg-amber-50/70 dark:bg-amber-900/20 px-2 py-0.5 rounded border border-amber-200/70 dark:border-amber-800/50">
                                                                    <span>
                                                                        <strong>Prof. #{{ $sim['proforma_id'] }}</strong> ({{ $sim['fecha_ingreso'] }}) — {{ $sim['paciente_nombre'] }}
                                                                    </span>
                                                                    <span class="font-bold text-amber-800 dark:text-amber-300">
                                                                        -{{ $sim['cantidad_descontada'] }} unds.
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <button wire:click="eliminarItemCola({{ $index }})" type="button"
                                                            class="text-rose-500 hover:text-rose-700 p-1 rounded cursor-pointer" title="Quitar de la lista">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer del Modal --}}
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between shrink-0">
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            Al confirmar, se crearán los nuevos lotes y se descontarán los medicamentos en las proformas <strong>En Curso</strong>.
                        </div>
                        <div class="flex items-center gap-2.5">
                            <button wire:click="cerrarModalReintegro" type="button"
                                class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                                Cancelar
                            </button>
                            <button wire:click="guardarReintegro" wire:loading.attr="disabled" type="button"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 disabled:opacity-50 transition-colors cursor-pointer">
                                <span wire:loading.remove wire:target="guardarReintegro">
                                    <i class="fas fa-check-double mr-1"></i>Confirmar Reintegro y Descontar Proformas
                                </span>
                                <span wire:loading wire:target="guardarReintegro">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Procesando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================================= --}}
    {{-- MODAL 2: VER DETALLE DE ACTA DE REINTEGRO                               --}}
    {{-- ======================================================================= --}}
    @if ($modalDetalleOpen && $reintegroSeleccionado)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-detalle-reintegro-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" wire:click="cerrarModalDetalle"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl max-h-[90vh] flex flex-col">
                    {{-- Header Sobrio Estándar --}}
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white text-sm shrink-0">
                                <i class="fas fa-file-medical-alt"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-detalle-reintegro-title">
                                    Detalle del Acta de Reintegro: {{ $reintegroSeleccionado->codigo }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Institución: <strong>{{ $reintegroSeleccionado->institucion?->nombre }}</strong> • Fecha: {{ $reintegroSeleccionado->fecha_reintegro?->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalDetalle" type="button"
                            title="Cerrar ventana"
                            class="inline-flex items-center justify-center h-8 w-8 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-lg hover:bg-slate-200/70 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    {{-- Contenido --}}
                    <div class="p-6 overflow-y-auto space-y-5 flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-800 text-xs">
                            <div>
                                <span class="text-slate-400 block uppercase text-[10px] font-bold">Entregado Por:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $reintegroSeleccionado->persona_entrega }}</span>
                                @if ($reintegroSeleccionado->ci_persona_entrega)
                                    <span class="block text-slate-500">C.I.: {{ $reintegroSeleccionado->ci_persona_entrega }}</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-slate-400 block uppercase text-[10px] font-bold">Recepcionado Por (Farmacia):</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $reintegroSeleccionado->usuarioCreador?->nombre_completo ?? ($reintegroSeleccionado->usuarioCreador?->name ?? 'Encargada de Farmacia') }}</span>
                                <span class="block text-slate-500">{{ $reintegroSeleccionado->sucursal?->nombre }} — {{ $reintegroSeleccionado->seccionDestino?->nombre }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block uppercase text-[10px] font-bold">Total Descontado en Proformas:</span>
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-400">Bs. {{ number_format((float) $reintegroSeleccionado->monto_total_descontado, 2) }}</span>
                                <span class="block text-slate-500">{{ $reintegroSeleccionado->total_unidades }} unidades reintegradas</span>
                            </div>
                        </div>

                        {{-- Tabla 1: Nuevos Lotes Ingresados --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                1. Medicamentos Reintegrados (Nuevos Lotes Ingresados)
                            </h4>
                            <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase text-slate-500">
                                            <th class="px-3 py-2">Medicamento / Insumo</th>
                                            <th class="px-3 py-2">Nuevo Lote</th>
                                            <th class="px-3 py-2">Vencimiento</th>
                                            <th class="px-3 py-2 text-center">Usado Inst.</th>
                                            <th class="px-3 py-2 text-center">Cant. Reintegrada</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($reintegroSeleccionado->detalles as $det)
                                            <tr>
                                                <td class="px-3 py-2 font-bold text-slate-800 dark:text-slate-200">{{ $det->producto?->nombre }}</td>
                                                <td class="px-3 py-2 font-mono font-bold text-teal-600 dark:text-teal-400">{{ $det->codigo_lote }}</td>
                                                <td class="px-3 py-2 text-slate-600 dark:text-slate-400">{{ $det->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }}</td>
                                                <td class="px-3 py-2 text-center">{{ $det->cantidad_usada_institucion }}</td>
                                                <td class="px-3 py-2 text-center font-bold text-emerald-600 dark:text-emerald-400">+{{ $det->cantidad_reintegrada }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Tabla 2: Pacientes y Proformas donde se descontaron los medicamentos --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                2. Pacientes y Proformas Descontadas (Por Orden de Antigüedad)
                            </h4>
                            <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 text-[10px] font-bold uppercase text-slate-500">
                                            <th class="px-3 py-2">Proforma / Fecha Ingreso</th>
                                            <th class="px-3 py-2">Paciente</th>
                                            <th class="px-3 py-2">Medicamento Quit/Disminuido</th>
                                            <th class="px-3 py-2">Origen</th>
                                            <th class="px-3 py-2 text-center">Cant. Descontada</th>
                                            <th class="px-3 py-2 text-right">Subtotal Descontado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($reintegroSeleccionado->aplicaciones as $app)
                                            <tr>
                                                <td class="px-3 py-2 font-bold text-teal-600 dark:text-teal-400">
                                                    #{{ $app->proforma_id }}
                                                    <span class="block text-[10px] font-normal text-slate-500">
                                                        {{ $app->proforma?->fecha_ingreso?->format('d/m/Y H:i') }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 font-semibold text-slate-800 dark:text-slate-200">
                                                    {{ $app->paciente?->nombre_completo }}
                                                    <span class="block text-[10px] font-normal text-slate-500">
                                                        C.I.: {{ $app->paciente?->cedula ?? 'S/C' }}
                                                        @if ($app->paciente?->numero_historia_clinica)
                                                            • HC: {{ $app->paciente->numero_historia_clinica }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ $app->producto?->nombre }}</td>
                                                <td class="px-3 py-2 text-slate-500">{{ $app->tipo_origen }}</td>
                                                <td class="px-3 py-2 text-center font-bold text-amber-700 dark:text-amber-400">-{{ $app->cantidad_descontada }}</td>
                                                <td class="px-3 py-2 text-right font-bold text-slate-900 dark:text-white">Bs. {{ number_format((float) $app->subtotal_descontado, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between shrink-0">
                        <a href="{{ route('farmacia.reintegros.pdf', $reintegroSeleccionado->id) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-colors">
                            <i class="fas fa-file-pdf"></i>
                            <span>Imprimir Acta en PDF</span>
                        </a>
                        <button wire:click="cerrarModalDetalle" type="button"
                            class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
