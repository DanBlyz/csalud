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

        <div class="flex items-center gap-2.5">
            <span class="px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/60 border border-teal-200 dark:border-teal-900 text-teal-800 dark:text-teal-300 text-xs font-semibold flex items-center gap-2">
                <i class="fas fa-clock text-teal-600"></i>
                <span>Pendientes por Atender: <strong>{{ $totalPendientes }}</strong></span>
            </span>
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
                                            <th class="py-2.5 px-3 w-44">Área / Sección de Origen</th>
                                            <th class="py-2.5 px-3">Lote a Descargar</th>
                                            <th class="py-2.5 px-3 text-center w-28">Cant. a Despachar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @foreach ($despachosItems as $detId => $item)
                                            @php
                                                $lotesDisponibles = $lotesDisponiblesPorItem[$detId] ?? collect();
                                                $saldo = $item['saldo_pendiente'];
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
                                                                    {{ $l->codigo_lote }} (Stock en área: {{ $stkSec }} un. - Vence: {{ $l->fecha_vencimiento?->format('d/m/y') ?? 'S/F' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <span class="text-xs text-rose-500 font-bold flex items-center gap-1">
                                                            <i class="fas fa-exclamation-circle"></i> Sin stock en esta área
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    @if ($saldo > 0 && $lotesDisponibles->isNotEmpty())
                                                        <input 
                                                            type="number" 
                                                            min="0" 
                                                            max="{{ $saldo }}"
                                                            wire:model="despachosItems.{{ $detId }}.cantidad_despachar"
                                                            class="w-20 text-xs text-center rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 shadow-2xs font-mono font-bold"
                                                        />
                                                    @else
                                                        <span class="font-mono text-slate-400 font-bold">0</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
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
                                                        {{ $le->codigo_lote }} (Stock: {{ $stkLoteSec }} - Vto: {{ $le->fecha_vencimiento?->format('d/m/y') ?? 'S/F' }})
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
                                                            type="button" 
                                                            class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors"
                                                            title="Quitar este insumo extra"
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
                        </div>

                        <!-- Modal Footer -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                @php
                                    $totalExtrasMonto = array_sum(array_column($extrasItems, 'subtotal'));
                                @endphp
                                @if ($totalExtrasMonto > 0)
                                    <span>Monto adicional en extras: <strong class="text-indigo-600 font-mono">Bs. {{ number_format($totalExtrasMonto, 2) }}</strong></span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2.5">
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
</div>
