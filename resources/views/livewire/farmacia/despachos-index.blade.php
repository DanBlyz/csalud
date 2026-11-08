<div class="space-y-6">
    <!-- Header del Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-600 text-white shadow-md shadow-teal-500/20">
                    <i class="fas fa-hand-holding-medical text-lg"></i>
                </span>
                <h1 class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
                    Bandeja de Despacho de Farmacia
                </h1>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Atención y dispensación de medicamentos según prescripciones médicas activas y política.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <span class="px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-900 text-teal-800 dark:text-teal-300 text-xs font-semibold flex items-center gap-2">
                <i class="fas fa-clock text-teal-600"></i>
                <span>Pendientes por Atender: <strong>{{ $totalPendientes }}</strong></span>
            </span>

            @if (Auth::user()?->tienePermiso('despachar-farmacia') || Auth::user()?->tienePermiso('emitir-receta'))
                <button 
                    type="button" 
                    wire:click="abrirModalCrearReceta" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm shadow-teal-500/20 transition-all transform active:scale-95 cursor-pointer"
                >
                    <i class="fas fa-file-prescription"></i>
                    <span>Crear Receta</span>
                </button>
            @endif

            @if (Auth::user()?->tienePermiso('despachar-farmacia') || Auth::user()?->tienePermiso('proformas.consumos.agregar'))
                <button 
                    type="button" 
                    wire:click="abrirModalDespachoExtraDirecto" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm shadow-indigo-500/20 transition-all transform active:scale-95 cursor-pointer"
                >
                    <i class="fas fa-boxes-packing"></i>
                    <span>Despachar Insumos Extras</span>
                </button>
            @endif
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
                        <label for="perPageDesp" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageDesp" 
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

                    <!-- Filtro de Sucursal -->
                    <div class="flex items-center gap-2">
                        <label for="filtroSucDesp" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sede:</label>
                        <select 
                            id="filtroSucDesp" 
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

                    <!-- Filtro Estado Despacho -->
                    <div class="flex items-center gap-2">
                        <label for="filtroEstDesp" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEstDesp" 
                            wire:model.live="filtroEstadoDespacho" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-teal-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="pendientes">Pendientes de Despacho</option>
                            <option value="completadas">Completadas Totalmente</option>
                            <option value="todas">Todas las Recetas Activas</option>
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
                        placeholder="Buscar por paciente, CI, médico, receta #..." 
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

        <!-- Tabla Principal de Recetas -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Receta / Fecha</th>
                        <th class="py-3 px-4">Paciente</th>
                        <th class="py-3 px-4">Proforma / Sede</th>
                        <th class="py-3 px-4">Médico Prescriptor</th>
                        <th class="py-3 px-4 text-center">Fármacos / Progreso</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($recetas as $receta)
                        @php
                            $paciente = $receta->proforma->paciente;
                            $detallesCount = $receta->detalles->count();
                            $despachadosCount = $receta->detalles->where('despachado', true)->count();
                            $tienePendientes = $receta->detalles->contains('despachado', false);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-mono font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <i class="fas fa-prescription text-teal-600 dark:text-teal-400"></i>
                                    Receta #{{ $receta->id }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    {{ $receta->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 dark:text-white">
                                    {{ $paciente->nombre_completo ?? 'Paciente no asignado' }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono flex flex-wrap items-center gap-2 mt-0.5">
                                    <span>CI: {{ $paciente->cedula ?? 'S/N' }}</span>
                                    @if (!empty($paciente->alergias))
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            <i class="fas fa-exclamation-triangle text-[9px]"></i>
                                            Alergia: {{ \Illuminate\Support\Str::limit($paciente->alergias, 25) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                    <a href="{{ route('proformas.show', $receta->proforma_id) }}" class="hover:underline">
                                        Proforma #{{ $receta->proforma_id }}
                                    </a>
                                    @if ($receta->proforma->tipo_atencion)
                                        <span class="text-slate-400 font-normal text-[11px]">({{ $receta->proforma->tipo_atencion }})</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <i class="fas fa-hospital text-[10px]"></i>
                                    {{ $receta->proforma->sucursal->nombre ?? 'Sede Central' }}
                                    @if ($receta->proforma->pieza)
                                        <span class="text-slate-500">• Sala {{ $receta->proforma->pieza }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <i class="fas fa-user-md text-teal-600 text-xs"></i>
                                    Dr(a). {{ $receta->doctor->name ?? 'Médico Tratante' }}
                                </div>
                                @if ($receta->observaciones)
                                    <div class="text-[10px] text-slate-400 truncate max-w-xs mt-0.5" title="{{ $receta->observaciones }}">
                                        {{ $receta->observaciones }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1 font-mono font-bold text-xs {{ $tienePendientes ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $despachadosCount }} / {{ $detallesCount }}
                                    <span class="text-[10px] font-normal text-slate-400 font-sans">entregados</span>
                                </div>
                                @if ($detallesCount > 0)
                                    <div class="w-24 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full mx-auto mt-1 overflow-hidden">
                                        <div class="h-full {{ $tienePendientes ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ round(($despachadosCount / $detallesCount) * 100) }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($tienePendientes)
                                    @if ($despachadosCount > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            <i class="fas fa-adjust text-[9px]"></i> Parcial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            <i class="fas fa-clock text-[9px]"></i> Pendiente
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <i class="fas fa-check-circle text-[9px]"></i> Completado
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @permiso('despachar-farmacia')
                                    <button 
                                        wire:click="abrirModalDespacho({{ $receta->id }})" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                        title="Atender despacho de medicamentos e insumos"
                                    >
                                        <i class="fas fa-dolly-flatbed text-[11px]"></i>
                                        <span>Atender Despacho</span>
                                    </button>
                                    @endpermiso
                                    <a 
                                        href="{{ route('proformas.show', $receta->proforma_id) }}" 
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition-colors"
                                        title="Ver Proforma"
                                    >
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400">
                                <i class="fas fa-prescription text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No hay recetas médicas pendientes de despacho</p>
                                <p class="text-xs text-slate-400 mt-1">Todas las prescripciones activas de la sede se encuentran al día o no hay proformas en curso.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación Estándar de Livewire (Sin footers duplicados) -->
        @if ($recetas->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $recetas->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL DE DESPACHO INTERACTIVO CON INSUMOS EXTRAS -->
    @if ($modalDespachoOpen && $recetaId)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-despacho-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas fa-hand-holding-medical"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-despacho-title">
                                    Dispensación y Despacho de Farmacia: Receta #{{ $recetaId }}
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    Asignación de lotes por vencimiento, despacho de prescripción médica e insumos hospitalarios extras.
                                </p>
                            </div>
                        </div>
                        <button wire:click="cerrarModalDespacho" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Banner Selector de Área / Almacén por Defecto -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-teal-50/70 dark:bg-teal-950/40 rounded-xl border border-teal-200 dark:border-teal-800/60 shadow-2xs">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-600 text-white text-xs shadow-xs">
                                    <i class="fas fa-boxes-stacked"></i>
                                </span>
                                <div>
                                    <label for="seccionDefectoModal" class="block text-xs font-bold text-slate-800 dark:text-slate-100">
                                        Área / Almacén de Despacho Sugerido:
                                    </label>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Asigna el origen por defecto para los medicamentos de la receta e insumos extras.
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <select 
                                    id="seccionDefectoModal"
                                    wire:model.live="seccion_defecto_id"
                                    class="text-xs rounded-lg border-teal-300 dark:border-teal-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 py-1.5 px-3 focus:ring-2 focus:ring-teal-500 font-semibold shadow-2xs"
                                >
                                    @foreach ($seccionesSucursal as $sec)
                                        <option value="{{ $sec->id }}">
                                            {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '★ (Principal)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- SECCIÓN 1: Medicamentos Prescritos en la Receta -->
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-pills text-teal-600 dark:text-teal-400"></i>
                                    1. Medicamentos Prescritos en la Receta
                                </h4>
                                <span class="text-[11px] text-slate-400">
                                    Priorización sugerida de lotes por fecha de vencimiento y existencias en el área
                                </span>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-700 text-[10px] uppercase">
                                        <tr>
                                            <th class="py-2.5 px-3">Medicamento</th>
                                            <th class="py-2.5 px-3 text-center">Prescrito / Saldo</th>
                                            <th class="py-2.5 px-3 w-40">Área / Sección de Origen</th>
                                            <th class="py-2.5 px-3">Lote a Descargar</th>
                                            <th class="py-2.5 px-3 text-right">P. Unitario</th>
                                            <th class="py-2.5 px-3 text-center w-24">Cant. a Despachar</th>
                                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @foreach ($despachosItems as $detId => $item)
                                            @php
                                                $lotesDisponibles = $lotesDisponiblesPorItem[$detId] ?? collect();
                                                $saldo = $item['saldo_pendiente'];
                                                $cantDesp = (int) ($item['cantidad_despachar'] ?? 0);
                                                $pUnit = (float) ($item['precio_unitario'] ?? 0);
                                                $subtotalItem = $cantDesp * $pUnit;
                                            @endphp
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                                <td class="py-2.5 px-3">
                                                    <div class="font-bold text-slate-800 dark:text-white">
                                                        {{ $item['producto_nombre'] }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400">
                                                        {{ $item['unidad_medida'] }} • {{ $item['indicaciones'] }}
                                                    </div>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono">
                                                    <div class="font-bold text-slate-800 dark:text-white">
                                                        Total: {{ $item['cantidad_prescrita'] }}
                                                    </div>
                                                    <div class="text-[10px] {{ $saldo > 0 ? 'text-amber-600 font-semibold' : 'text-emerald-600 font-bold' }}">
                                                        {{ $saldo > 0 ? "Saldo: {$saldo}" : 'Completado' }}
                                                    </div>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if ($saldo <= 0)
                                                        <span class="text-xs text-slate-400 italic">Dispensado</span>
                                                    @else
                                                        <select 
                                                            wire:change="cambiarSeccionItem({{ $detId }}, $event.target.value)"
                                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 focus:ring-2 focus:ring-teal-500 shadow-2xs font-medium"
                                                        >
                                                            @foreach ($seccionesSucursal as $sec)
                                                                <option value="{{ $sec->id }}" {{ ($item['seccion_id'] ?? null) == $sec->id ? 'selected' : '' }}>
                                                                    {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '★' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    @if ($saldo <= 0)
                                                        <span class="text-xs text-emerald-600 font-semibold italic flex items-center gap-1">
                                                            <i class="fas fa-check-circle"></i> Prescripción entregada en su totalidad
                                                        </span>
                                                    @elseif ($lotesDisponibles->isNotEmpty())
                                                        <select 
                                                            wire:model="despachosItems.{{ $detId }}.lote_id"
                                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono"
                                                        >
                                                            @foreach ($lotesDisponibles as $l)
                                                                @php
                                                                    $stkSec = $l->stockEnSeccion($item['seccion_id']);
                                                                @endphp
                                                                <option value="{{ $l->id }}">
                                                                    Stock en área: {{ $stkSec }} un. (Vence: {{ $l->fecha_vencimiento?->format('d/m/y') ?? 'S/F' }} - {{ $l->codigo_lote }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <span class="text-xs text-rose-500 font-bold flex items-center gap-1">
                                                            <i class="fas fa-exclamation-circle"></i> Sin stock en esta área
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono text-slate-600 dark:text-slate-300">
                                                    Bs. {{ number_format($pUnit, 2) }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    @if ($saldo > 0 && $lotesDisponibles->isNotEmpty())
                                                        <input 
                                                            type="number" 
                                                            min="0" 
                                                            max="{{ $saldo }}"
                                                            wire:model.live.debounce.250ms="despachosItems.{{ $detId }}.cantidad_despachar"
                                                            class="w-20 text-xs text-center rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                                                        />
                                                    @else
                                                        <span class="font-mono text-slate-400 font-bold">0</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                                                    Bs. {{ number_format($subtotalItem, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-slate-50 dark:bg-slate-800/80 font-semibold border-t border-slate-200 dark:border-slate-700 text-xs">
                                        <tr>
                                            <td colspan="5" class="py-2.5 px-3 text-right text-slate-600 dark:text-slate-400">
                                                Subtotal Medicamentos a Despachar:
                                            </td>
                                            <td colspan="2" class="py-2.5 px-3 text-right font-mono font-bold text-teal-600 dark:text-teal-400 text-sm">
                                                Bs. {{ number_format($this->totalRecetaDespachar, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: Insumos y Medicamentos Adicionales (Extras) -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-plus-circle text-indigo-600 dark:text-indigo-400"></i>
                                    2. Insumos y Medicamentos Adicionales / Extras
                                </h4>
                                @if (!empty($extrasItems))
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-0.5 rounded-full">
                                        {{ count($extrasItems) }} extra(s) añadido(s)
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500">
                                Despacho de gasas, jeringas, sueros o materiales hospitalarios requeridos para la atención y cargados a la proforma.
                            </p>

                            <!-- Barra de Adición Rápida de Insumos Extras -->
                            <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/80 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                    <!-- Selector de Sección / Área de Origen -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                            Área / Botiquín de Origen:
                                        </label>
                                        <select 
                                            wire:model.live="extra_seccion_id"
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 shadow-2xs font-semibold"
                                        >
                                            @foreach ($seccionesSucursal as $sec)
                                                <option value="{{ $sec->id }}">
                                                    {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '★' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Buscador Reactivo de Insumo / Medicamento Extra -->
                                    <div class="sm:col-span-4 relative" x-data="{ openDropdown: false }" @click.outside="openDropdown = false">
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                            Insumo / Fármaco Extra:
                                        </label>

                                        @if ($productoExtraSeleccionado)
                                            <!-- Producto Seleccionado -->
                                            <div class="flex items-center justify-between p-2 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 shadow-2xs">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-teal-600 text-white text-xs">
                                                        <i class="fas fa-pills"></i>
                                                    </span>
                                                    <div class="truncate">
                                                        <div class="font-bold text-xs text-slate-800 dark:text-white truncate">
                                                            {{ $productoExtraSeleccionado->nombre }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                                            {{ $productoExtraSeleccionado->unidad_medida }} • {{ $productoExtraSeleccionado->marca->nombre ?? 'Genérico' }} • Bs. {{ number_format($productoExtraSeleccionado->ultimo_precio_venta ?? 0, 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <button 
                                                    wire:click="limpiarProductoExtra" 
                                                    type="button" 
                                                    class="text-slate-400 hover:text-rose-500 p-1 transition-colors"
                                                    title="Cambiar producto"
                                                >
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </div>
                                        @else
                                            <!-- Buscador Reactivo con Sugerencias -->
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                    <i class="fas fa-search text-xs"></i>
                                                </div>
                                                <input 
                                                    type="text" 
                                                    wire:model.live.debounce.250ms="buscarExtraProducto"
                                                    @focus="openDropdown = true"
                                                    placeholder="Buscar insumo en esta área..."
                                                    class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                                />
                                                @if ($buscarExtraProducto)
                                                    <button 
                                                        wire:click="$set('buscarExtraProducto', '')" 
                                                        type="button" 
                                                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                                    >
                                                        <i class="fas fa-times-circle text-xs"></i>
                                                    </button>
                                                @endif

                                                <!-- Dropdown Flotante de Resultados -->
                                                <div 
                                                    x-show="openDropdown"
                                                    x-transition
                                                    class="absolute z-30 left-0 right-0 top-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60"
                                                    style="display: none;"
                                                >
                                                    @forelse ($productosParaExtras as $prod)
                                                        @php
                                                            $stockEnSec = $prod->lotes->sum(fn ($lt) => $lt->loteSecciones->sum('cantidad_actual'));
                                                        @endphp
                                                        <button 
                                                            wire:click="seleccionarProductoExtra({{ $prod->id }})" 
                                                            @click="openDropdown = false"
                                                            type="button" 
                                                            class="w-full text-left p-2.5 hover:bg-teal-50/70 dark:hover:bg-teal-950/40 flex items-center justify-between gap-2 transition-colors cursor-pointer"
                                                        >
                                                            <div class="min-w-0">
                                                                <div class="font-bold text-xs text-slate-800 dark:text-slate-100 truncate">
                                                                    {{ $prod->nombre }}
                                                                </div>
                                                                <div class="text-[10px] text-slate-400">
                                                                    {{ $prod->unidad_medida }} • {{ $prod->marca->nombre ?? 'Genérico' }}
                                                                </div>
                                                            </div>
                                                            <div class="text-right shrink-0">
                                                                <div class="font-mono font-bold text-teal-600 dark:text-teal-400 text-xs">
                                                                    Bs. {{ number_format($prod->ultimo_precio_venta ?? 0, 2) }}
                                                                </div>
                                                                <div class="text-[10px] text-slate-500 font-mono">
                                                                    Stock: {{ $stockEnSec }} un.
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @empty
                                                        <div class="p-3 text-center text-xs text-slate-400">
                                                            <i class="fas fa-box-open me-1"></i> No se encontraron insumos con existencias en esta área
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        @endif
                                        @error('extra_producto_id') <span class="text-[10px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Selector de Lote en la Sección -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                            Lote con Stock:
                                        </label>
                                        <select 
                                            wire:model="extra_lote_id"
                                            @disabled(empty($extra_producto_id) || $lotesParaExtra->isEmpty())
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono disabled:opacity-50"
                                        >
                                            @if ($lotesParaExtra->isEmpty())
                                                <option value="">-- Sin lotes en esta área --</option>
                                            @else
                                                @foreach ($lotesParaExtra as $le)
                                                    @php
                                                        $stkLoteSec = $le->loteSecciones->where('seccion_id', $extra_seccion_id)->sum('cantidad_actual');
                                                    @endphp
                                                    <option value="{{ $le->id }}">
                                                        Stock: {{ $stkLoteSec }} (Lote: {{ $le->codigo_lote }} - Vto: {{ $le->fecha_vencimiento?->format('d/m/y') ?? 'S/F' }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('extra_lote_id') <span class="text-[10px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="sm:col-span-1">
                                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1 text-center">
                                            Cant:
                                        </label>
                                        <input 
                                            type="number" 
                                            min="1" 
                                            wire:model="extra_cantidad"
                                            class="w-full text-xs text-center rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                                        />
                                        @error('extra_cantidad') <span class="text-[10px] text-rose-500 font-semibold">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Botón Añadir -->
                                    <div class="sm:col-span-1 flex items-end">
                                        <button 
                                            wire:click="agregarItemExtra" 
                                            type="button" 
                                            wire:loading.attr="disabled"
                                            @disabled(empty($extra_producto_id) || empty($extra_lote_id))
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-all"
                                            title="Añadir insumo extra"
                                        >
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <input 
                                        type="text" 
                                        wire:model="extra_observaciones"
                                        placeholder="Observación o justificación del insumo extra (opcional, ej: Curación de herida, sutura, hidratación...)"
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 placeholder-slate-400"
                                    />
                                </div>
                            </div>

                            <!-- Tabla de Insumos Extras Agregados -->
                            @if (!empty($extrasItems))
                                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-semibold border-b border-indigo-100 dark:border-slate-800 text-[10px] uppercase">
                                            <tr>
                                                <th class="py-2 px-3">Insumo / Medicamento Extra</th>
                                                <th class="py-2 px-3">Área de Origen</th>
                                                <th class="py-2 px-3">Lote</th>
                                                <th class="py-2 px-3 text-center">Cant.</th>
                                                <th class="py-2 px-3 text-right">P. Unitario</th>
                                                <th class="py-2 px-3 text-right">Subtotal</th>
                                                <th class="py-2 px-3">Observación</th>
                                                <th class="py-2 px-3 text-center w-12">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach ($extrasItems as $idx => $extra)
                                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-white">
                                                        {{ $extra['producto_nombre'] }}
                                                        <span class="text-[10px] text-slate-400 font-normal">({{ $extra['unidad_medida'] }})</span>
                                                    </td>
                                                    <td class="py-2 px-3">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-800/60">
                                                            <i class="fas fa-hospital-user text-[9px]"></i> {{ $extra['seccion_nombre'] }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                                                        {{ $extra['lote_codigo'] }}
                                                        <span class="text-[10px] text-slate-400">({{ $extra['lote_vencimiento'] }})</span>
                                                    </td>
                                                    <td class="py-2 px-3 text-center font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                        {{ $extra['cantidad'] }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-slate-600 dark:text-slate-300">
                                                        Bs. {{ number_format($extra['precio_unitario'], 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                                                        Bs. {{ number_format($extra['subtotal'], 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-500 text-[11px] truncate max-w-xs">
                                                        {{ $extra['observaciones'] ?? '—' }}
                                                    </td>
                                                    <td class="py-2 px-3 text-center">
                                                        <button 
                                                            wire:click="eliminarItemExtra({{ $idx }})" 
                                                            wire:loading.attr="disabled"
                                                            type="button" 
                                                            class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                            title="Quitar este insumo extra"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs" wire:loading.remove wire:target="eliminarItemExtra({{ $idx }})"></i>
                                                            <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarItemExtra({{ $idx }})"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2.5 text-xs w-full sm:w-auto">
                                <div class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center gap-1.5">
                                    <span class="text-slate-500">Receta:</span>
                                    <strong class="font-mono text-teal-600 dark:text-teal-400 font-bold">Bs. {{ number_format($this->totalRecetaDespachar, 2) }}</strong>
                                </div>
                                @if ($this->totalExtrasDespachar > 0)
                                    <div class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/60 flex items-center gap-1.5">
                                        <span class="text-indigo-700 dark:text-indigo-300">Extras:</span>
                                        <strong class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">Bs. {{ number_format($this->totalExtrasDespachar, 2) }}</strong>
                                    </div>
                                @endif
                                <div class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1.5 shadow-2xs">
                                    <span class="text-emerald-800 dark:text-emerald-300 font-bold">Costo Total a Despachar:</span>
                                    <strong class="font-mono text-emerald-600 dark:text-emerald-400 font-bold text-sm">Bs. {{ number_format($this->costoTotalDespacho, 2) }}</strong>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto justify-end">
                                <button 
                                    wire:click="cerrarModalDespacho" 
                                    type="button" 
                                    class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                                >
                                    Cancelar
                                </button>
                                <button 
                                    wire:click="procesarDespacho" 
                                    wire:loading.attr="disabled"
                                    type="button" 
                                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    <span wire:loading.remove wire:target="procesarDespacho">
                                        <i class="fas fa-check-double me-1"></i> Confirmar y Procesar Despacho
                                    </span>
                                    <span wire:loading wire:target="procesarDespacho">
                                        <i class="fas fa-spinner fa-spin me-1"></i> Procesando entrega...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 1: CREAR RECETA MÉDICA DESDE FARMACIA -->
    <!-- ========================================================================= -->
    @if ($modalCrearRecetaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-file-prescription text-teal-600"></i>
                                Prescribir y Crear Receta Médica
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                                Seleccione una proforma en curso para prescribir medicamentos que ingresarán directamente a la bandeja de farmacia.
                            </p>
                        </div>
                        <button wire:click="cerrarModalCrearReceta" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="guardarNuevaReceta" class="p-6 space-y-4">
                        <!-- Paso 1: Selección de Paciente / Proforma -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-user-injured text-teal-600"></i>
                                    Paciente / Proforma (En Curso) <span class="text-rose-500">*</span>
                                </span>
                                <span class="text-[10px] text-teal-600 font-semibold">Proformas activas</span>
                            </label>

                            @if ($receta_proforma_id && $proformaSeleccionadaReceta)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-teal-50/80 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-teal-950 dark:text-teal-100 flex items-center gap-2">
                                                <span>{{ $proformaSeleccionadaReceta->paciente?->nombre_completo ?? 'Paciente' }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-teal-200/80 dark:bg-teal-900 text-teal-800 dark:text-teal-200 font-mono font-bold">
                                                    Proforma #{{ $proformaSeleccionadaReceta->id }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-teal-700 dark:text-teal-300 flex flex-wrap items-center gap-2 mt-0.5">
                                                <span>CI: {{ $proformaSeleccionadaReceta->paciente?->cedula ?? 'S/C' }}</span>
                                                <span>&bull; Sede: {{ $proformaSeleccionadaReceta->sucursal?->nombre ?? 'Principal' }}</span>
                                                @if ($proformaSeleccionadaReceta->medicos->isNotEmpty())
                                                    <span>&bull; Médico: Dr(a). {{ $proformaSeleccionadaReceta->medicos->first()->nombre_completo }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        wire:click="limpiarProformaSeleccionadaReceta" 
                                        class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer shrink-0"
                                    >
                                        <i class="fas fa-times me-1"></i> Cambiar
                                    </button>
                                </div>
                            @else
                                <div class="relative">
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.250ms="buscarProformaReceta" 
                                            placeholder="Buscar por paciente, CI o N° de proforma..." 
                                            class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                        />
                                        @if ($buscarProformaReceta !== '')
                                            <button type="button" wire:click="$set('buscarProformaReceta', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if ($proformasSugeridasReceta->isNotEmpty())
                                        <div class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl divide-y divide-slate-100 dark:divide-slate-700/60 z-30">
                                            @foreach ($proformasSugeridasReceta as $prof)
                                                <button 
                                                    type="button" 
                                                    wire:click="seleccionarProformaReceta({{ $prof->id }})" 
                                                    class="w-full text-left p-2.5 hover:bg-teal-50/80 dark:hover:bg-teal-950/50 transition-colors flex items-center justify-between cursor-pointer"
                                                >
                                                    <div>
                                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                            <span>{{ $prof->paciente?->nombre_completo ?? 'Paciente sin nombre' }}</span>
                                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">
                                                                CI: {{ $prof->paciente?->cedula ?? 'S/C' }}
                                                            </span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                            Proforma #{{ $prof->id }} &bull; Sede: {{ $prof->sucursal?->nombre ?? 'N/D' }} &bull; Ingreso: {{ $prof->created_at?->format('d/m/Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300">
                                                            Seleccionar <i class="fas fa-chevron-right ms-1 text-[9px]"></i>
                                                        </span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (strlen(trim($buscarProformaReceta)) >= 2)
                                        <div class="absolute left-0 right-0 mt-1 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl text-center text-xs text-slate-500 z-30">
                                            No se encontraron proformas en curso que coincidan con la búsqueda.
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @error('receta_proforma_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if ($receta_proforma_id)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="receta_medico_id_farm" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Médico Prescriptor <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="receta_medico_id_farm" wire:model="receta_medico_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                                        @foreach ($medicos as $m)
                                            <option value="{{ $m->id }}">Dr(a). {{ $m->nombre_completo }} ({{ $m->especialidad?->nombre ?? 'General' }})</option>
                                        @endforeach
                                    </select>
                                    @error('receta_medico_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="receta_observaciones_farm" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Indicaciones / Tratamiento General
                                    </label>
                                    <input type="text" id="receta_observaciones_farm" wire:model="receta_observaciones" placeholder="Ej. Dieta blanda, reposo, control de signos..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs" />
                                </div>
                            </div>

                            <!-- Búsqueda y Selección de Medicamento con Control de Stock -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-teal-700 dark:text-teal-400 block flex items-center justify-between">
                                    <span><i class="fas fa-pills me-1"></i> Adjuntar Medicamento a la Receta</span>
                                    <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">Existencias en sede</span>
                                </span>

                                @if ($receta_nuevo_producto_id)
                                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                <i class="fas fa-capsules"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-teal-950 dark:text-teal-100">
                                                    {{ $receta_nuevo_producto_nombre }}
                                                </div>
                                                <div class="text-[11px] text-teal-700 dark:text-teal-300 flex items-center gap-2">
                                                    <span>{{ $receta_nuevo_unidad_medida }}</span>
                                                    @if ($receta_nuevo_marca_nombre)
                                                        <span>&bull; {{ $receta_nuevo_marca_nombre }}</span>
                                                    @endif
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                        Stock disponible: {{ $receta_nuevo_stock_disponible }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click="limpiarMedicamentoSeleccionadoReceta" 
                                            class="px-2.5 py-1 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-md transition-colors cursor-pointer"
                                        >
                                            <i class="fas fa-times me-1"></i> Cambiar
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                                Cantidad <span class="text-rose-500">*</span>
                                            </label>
                                            <input 
                                                type="number" 
                                                min="1" 
                                                wire:model="receta_nuevo_cantidad" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2 shadow-2xs font-bold"
                                            />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                                Posología e Indicaciones <span class="text-rose-500">*</span>
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="receta_nuevo_indicaciones" 
                                                placeholder="Ej. 1 comp cada 8 hrs por 5 días" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2 shadow-2xs"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex justify-end pt-1">
                                        <button 
                                            type="button" 
                                            wire:click="agregarMedicamentoAReceta" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>Agregar a la Receta</span>
                                        </button>
                                    </div>
                                @else
                                    <div class="relative">
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                            Buscar Fármaco / Medicamento <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.250ms="buscarMedicamentoReceta" 
                                                placeholder="Escriba el nombre comercial, principio activo o marca del fármaco..." 
                                                class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                            />
                                        </div>

                                        @if ($sugerenciasMedicamentosReceta->isNotEmpty())
                                            <div class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl divide-y divide-slate-100 dark:divide-slate-700/60 z-30">
                                                @foreach ($sugerenciasMedicamentosReceta as $sug)
                                                    @php
                                                        $stockTotal = (int) $sug->lotes->sum('cantidad_actual');
                                                    @endphp
                                                    <button 
                                                        type="button" 
                                                        wire:click="seleccionarMedicamentoReceta({{ $sug->id }})" 
                                                        class="w-full text-left p-2.5 hover:bg-teal-50/80 dark:hover:bg-teal-950/50 transition-colors flex items-center justify-between cursor-pointer"
                                                    >
                                                        <div>
                                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $sug->nombre }}</div>
                                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                                {{ $sug->unidad_medida }} @if ($sug->marca) &bull; {{ $sug->marca->nombre }} @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            @if ($stockTotal > 0)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-mono">
                                                                    Stock: {{ $stockTotal }}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 font-mono">
                                                                    Agotado (0)
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @elseif (strlen(trim($buscarMedicamentoReceta)) >= 2)
                                            <div class="absolute left-0 right-0 mt-1 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl text-center text-xs text-slate-500 z-30">
                                                No se encontraron medicamentos coincidentes en farmacia.
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Tabla de Medicamentos Agregados a la Receta -->
                            @if (! empty($receta_medicamentos))
                                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                    <table class="w-full text-xs text-left">
                                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                            <tr>
                                                <th class="py-2.5 px-3">Medicamento</th>
                                                <th class="py-2.5 px-3 text-center">Cant.</th>
                                                <th class="py-2.5 px-3">Indicaciones / Posología</th>
                                                <th class="py-2.5 px-3 text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                            @foreach ($receta_medicamentos as $idx => $med)
                                                <tr>
                                                    <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                                        {{ $med['producto_nombre'] }}
                                                        <span class="block text-[10px] text-slate-400 font-normal">
                                                            {{ $med['unidad_medida'] }} @if (! empty($med['marca_nombre'])) &bull; {{ $med['marca_nombre'] }} @endif
                                                        </span>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center font-bold text-teal-700 dark:text-teal-300">
                                                        {{ $med['cantidad'] }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                                        {{ $med['indicaciones'] }}
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        <button 
                                                            type="button" 
                                                            wire:click="removerMedicamentoReceta({{ $idx }})" 
                                                            class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors cursor-pointer"
                                                            title="Quitar de la receta"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif

                        <!-- Footer del Modal -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <button 
                                type="button" 
                                wire:click="cerrarModalCrearReceta" 
                                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                            >
                                Cancelar
                            </button>

                            <button 
                                type="submit" 
                                wire:loading.attr="disabled" 
                                @if (! $receta_proforma_id || empty($receta_medicamentos)) disabled @endif
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarNuevaReceta">
                                    <i class="fas fa-check-circle me-1"></i> Prescribir y Crear Receta
                                </span>
                                <span wire:loading wire:target="guardarNuevaReceta">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL 2: DESPACHAR INSUMOS EXTRAS DIRECTO -->
    <!-- ========================================================================= -->
    @if ($modalDespachoExtraDirectoOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-boxes-packing text-indigo-600"></i>
                                Despachar Insumos y Medicamentos Extras
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                                Despacho directo con descuento de stock por área y cargo al estado de cuenta del paciente.
                            </p>
                        </div>
                        <button wire:click="cerrarModalDespachoExtraDirecto" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="procesarDespachoExtraDirecto" class="p-6 space-y-4">
                        <!-- Paso 1: Selección de Paciente / Proforma -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-user-injured text-indigo-600"></i>
                                    Paciente / Proforma Destino (En Curso) <span class="text-rose-500">*</span>
                                </span>
                                <span class="text-[10px] text-indigo-600 font-semibold">Proformas activas</span>
                            </label>

                            @if ($extra_directo_proforma_id && $proformaSeleccionadaExtraDirecto)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-indigo-950 dark:text-indigo-100 flex items-center gap-2">
                                                <span>{{ $proformaSeleccionadaExtraDirecto->paciente?->nombre_completo ?? 'Paciente' }}</span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-200/80 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 font-mono font-bold">
                                                    Proforma #{{ $proformaSeleccionadaExtraDirecto->id }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-indigo-700 dark:text-indigo-300 flex flex-wrap items-center gap-2 mt-0.5">
                                                <span>CI: {{ $proformaSeleccionadaExtraDirecto->paciente?->cedula ?? 'S/C' }}</span>
                                                <span>&bull; Sede: {{ $proformaSeleccionadaExtraDirecto->sucursal?->nombre ?? 'Principal' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        wire:click="limpiarProformaSeleccionadaExtraDirecto" 
                                        class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer shrink-0"
                                    >
                                        <i class="fas fa-times me-1"></i> Cambiar
                                    </button>
                                </div>
                            @else
                                <div class="relative">
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.250ms="buscarProformaExtraDirecto" 
                                            placeholder="Buscar por paciente, CI o N° de proforma..." 
                                            class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                                        />
                                        @if ($buscarProformaExtraDirecto !== '')
                                            <button type="button" wire:click="$set('buscarProformaExtraDirecto', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if ($proformasSugeridasExtraDirecto->isNotEmpty())
                                        <div class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl divide-y divide-slate-100 dark:divide-slate-700/60 z-30">
                                            @foreach ($proformasSugeridasExtraDirecto as $prof)
                                                <button 
                                                    type="button" 
                                                    wire:click="seleccionarProformaExtraDirecto({{ $prof->id }})" 
                                                    class="w-full text-left p-2.5 hover:bg-indigo-50/80 dark:hover:bg-indigo-950/50 transition-colors flex items-center justify-between cursor-pointer"
                                                >
                                                    <div>
                                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                                            <span>{{ $prof->paciente?->nombre_completo ?? 'Paciente sin nombre' }}</span>
                                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">
                                                                CI: {{ $prof->paciente?->cedula ?? 'S/C' }}
                                                            </span>
                                                        </div>
                                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                            Proforma #{{ $prof->id }} &bull; Sede: {{ $prof->sucursal?->nombre ?? 'N/D' }} &bull; Ingreso: {{ $prof->created_at?->format('d/m/Y') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                                                            Seleccionar <i class="fas fa-chevron-right ms-1 text-[9px]"></i>
                                                        </span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (strlen(trim($buscarProformaExtraDirecto)) >= 2)
                                        <div class="absolute left-0 right-0 mt-1 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl text-center text-xs text-slate-500 z-30">
                                            No se encontraron proformas en curso que coincidan con la búsqueda.
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @error('extra_directo_proforma_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if ($extra_directo_proforma_id)
                            <!-- Paso 2: Selección de Sección / Almacén de Origen -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fas fa-layer-group text-indigo-500"></i>
                                        Sección / Depósito de Origen (Descargo de Stock) <span class="text-rose-500">*</span>
                                    </span>
                                    <span class="text-[10px] text-indigo-600 font-semibold">existencias por sección</span>
                                </label>
                                <select 
                                    wire:model.live="extra_directo_seccion_id" 
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-indigo-500 font-medium"
                                >
                                    @foreach ($seccionesSucursalExtraDirecto as $sec)
                                        <option value="{{ $sec->id }}">
                                            {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Almacén Principal)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Paso 3: Buscador de Insumos / Medicamentos Extras -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 block flex items-center justify-between">
                                    <span><i class="fas fa-box-open me-1"></i> Adjuntar Insumo o Medicamento Extra</span>
                                    <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">Stock disponible en la sección</span>
                                </span>

                                @if ($extra_directo_producto_id)
                                    <div class="p-3 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-indigo-950 dark:text-indigo-200">
                                                    {{ $extra_directo_producto_nombre }}
                                                </div>
                                                <div class="text-[11px] text-indigo-700 dark:text-indigo-400 flex flex-wrap items-center gap-2 mt-0.5">
                                                    <span>Unidad: {{ $extra_directo_unidad_medida }}</span>
                                                    <span>&bull; P. Sugerido: Bs. {{ $extra_directo_precio_unitario }}</span>
                                                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 text-[10px]">
                                                        Stock en área: {{ $extra_directo_stock_disponible }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click="limpiarInsumoExtraDirecto" 
                                            class="px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer shrink-0"
                                        >
                                            <i class="fas fa-times me-1"></i> Cambiar
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                                Cantidad <span class="text-rose-500">*</span>
                                            </label>
                                            <input 
                                                type="number" 
                                                min="1" 
                                                max="{{ $extra_directo_stock_disponible }}" 
                                                wire:model.live.debounce.250ms="extra_directo_cantidad" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2 shadow-2xs font-bold"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                                Precio Unitario (Bs.) <span class="text-rose-500">*</span>
                                            </label>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                wire:model.live.debounce.250ms="extra_directo_precio_unitario" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2 shadow-2xs font-mono font-bold"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                                Observación / Motivo
                                            </label>
                                            <input 
                                                type="text" 
                                                wire:model="extra_directo_observaciones" 
                                                placeholder="Ej. Solicitado en quirófano / piso..." 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2 shadow-2xs"
                                            />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <div class="text-xs text-slate-600 dark:text-slate-400">
                                            @php
                                                $subtotalItemDirecto = ((int) ($extra_directo_cantidad ?: 0)) * ((float) ($extra_directo_precio_unitario ?: 0));
                                            @endphp
                                            <span>Subtotal estimado: <strong class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">Bs. {{ number_format($subtotalItemDirecto, 2) }}</strong></span>
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click="agregarItemExtraDirecto" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>Agregar a la Lista</span>
                                        </button>
                                    </div>
                                @else
                                    <div class="space-y-1.5">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.250ms="buscarInsumoExtraDirecto" 
                                                placeholder="Buscar por nombre, código o descripción de producto..." 
                                                class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                                            />
                                            @if ($buscarInsumoExtraDirecto !== '')
                                                <button type="button" wire:click="$set('buscarInsumoExtraDirecto', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="max-h-44 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg divide-y divide-slate-100 dark:divide-slate-700/60">
                                            @forelse ($productosParaExtraDirecto as $pDir)
                                                @php
                                                    $stkEnSec = $pDir->lotes->sum(fn ($l) => $l->stockEnSeccion($extra_directo_seccion_id));
                                                @endphp
                                                <button 
                                                    type="button" 
                                                    wire:click="seleccionarInsumoExtraDirecto({{ $pDir->id }})" 
                                                    class="w-full text-left p-2.5 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 transition-colors flex items-center justify-between group cursor-pointer"
                                                >
                                                    <div>
                                                        <div class="font-bold text-xs text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                                            {{ $pDir->nombre }}
                                                        </div>
                                                        <div class="text-[11px] text-slate-400">
                                                            {{ $pDir->unidad_medida }} &bull; {{ $pDir->marca?->nombre ?? 'Genérico' }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                            Bs. {{ number_format($pDir->ultimo_precio_venta, 2) }}
                                                        </span>
                                                        <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                            <i class="fas fa-boxes text-[9px] me-0.5"></i> Stock en área: {{ $stkEnSec }}
                                                        </span>
                                                    </div>
                                                </button>
                                            @empty
                                                <div class="p-3 text-center text-xs text-slate-400">
                                                    <i class="fas fa-box-open me-1"></i> No se encontraron productos con existencias disponibles en esta área.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Tabla de Insumos Extras Encolados para Despacho -->
                            @if (! empty($cola_despachos_extras_directos))
                                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                    <table class="w-full text-xs text-left">
                                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 uppercase tracking-wider text-[11px]">
                                            <tr>
                                                <th class="py-2 px-3">Insumo / Medicamento</th>
                                                <th class="py-2 px-3">Área Origen</th>
                                                <th class="py-2 px-3 text-center">Cant.</th>
                                                <th class="py-2 px-3 text-right">P. Unit</th>
                                                <th class="py-2 px-3 text-right">Subtotal</th>
                                                <th class="py-2 px-3">Observaciones</th>
                                                <th class="py-2 px-3 text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                            @foreach ($cola_despachos_extras_directos as $idx => $item)
                                                <tr>
                                                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                                        {{ $item['producto_nombre'] }}
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-500">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                            {{ $item['seccion_nombre'] }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3 text-center font-bold text-indigo-700 dark:text-indigo-300">
                                                        {{ $item['cantidad'] }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono text-slate-600 dark:text-slate-300">
                                                        Bs. {{ number_format($item['precio_unitario'], 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800 dark:text-white">
                                                        Bs. {{ number_format($item['subtotal'], 2) }}
                                                    </td>
                                                    <td class="py-2 px-3 text-slate-500 text-[11px] truncate max-w-xs">
                                                        {{ $item['observaciones'] ?: '—' }}
                                                    </td>
                                                    <td class="py-2 px-3 text-center">
                                                        <button 
                                                            type="button" 
                                                            wire:click="removerItemExtraDirecto({{ $idx }})" 
                                                            class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors cursor-pointer"
                                                            title="Quitar este insumo"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-slate-50 dark:bg-slate-850 font-bold border-t border-slate-200 dark:border-slate-800">
                                            <tr>
                                                <td colspan="4" class="py-2 px-3 text-right text-slate-600 dark:text-slate-300">Total Despacho Extra:</td>
                                                <td class="py-2 px-3 text-right font-mono text-indigo-600 dark:text-indigo-400">
                                                    Bs. {{ number_format(array_sum(array_column($cola_despachos_extras_directos, 'subtotal')), 2) }}
                                                </td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        @endif

                        <!-- Footer del Modal -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <button 
                                    type="button" 
                                    wire:click="cerrarModalDespachoExtraDirecto" 
                                    class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                                >
                                    Cancelar
                                </button>
                                @if (!empty($cola_despachos_extras_directos))
                                    <div class="px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/60 flex items-center gap-1.5 shadow-2xs">
                                        <span class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold">Costo Total Extra:</span>
                                        <strong class="font-mono text-indigo-600 dark:text-indigo-400 font-bold text-sm">Bs. {{ number_format($this->totalExtraDirectoDespachar, 2) }}</strong>
                                    </div>
                                @endif
                            </div>

                            <button 
                                type="submit" 
                                wire:loading.attr="disabled" 
                                @if (! $extra_directo_proforma_id || empty($cola_despachos_extras_directos)) disabled @endif
                                class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="procesarDespachoExtraDirecto">
                                    <i class="fas fa-check-double me-1"></i> Confirmar Despacho Extra
                                </span>
                                <span wire:loading wire:target="procesarDespachoExtraDirecto">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Despachando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
