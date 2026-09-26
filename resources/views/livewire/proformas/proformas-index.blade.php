<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white shadow-md shadow-blue-500/20">
                    <i class="fas fa-file-medical-alt text-base"></i>
                </span>
                Proformas y Expedientes Clínicos
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Gestión centralizada de atenciones médicas, procedimientos, internaciones y estados de cuenta.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="{{ route('proformas.crear') }}" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer"
            >
                <i class="fas fa-plus-circle"></i>
                <span>Nueva Admisión / Proforma</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-lg">
                <i class="fas fa-procedures"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Proformas En Curso</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalEnCurso) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 text-lg">
                <i class="fas fa-bed"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Internados Activos</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalInternados) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-lg">
                <i class="fas fa-walking"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Ambulatorias Hoy</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalAmbulatoriasHoy) }}</h3>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs flex items-center gap-3.5">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Proformas Liquidadas</span>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ number_format($totalPagadas) }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden transition-colors duration-200">
        <!-- Card Header: Controles y Filtros -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="perPageProf" class="text-xs font-medium text-slate-600 dark:text-slate-400">Mostrar:</label>
                        <select 
                            id="perPageProf" 
                            wire:model.live="perPage" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-xs text-slate-500 dark:text-slate-400">registros</span>
                    </div>

                    <!-- Filtro Sucursal (si aplica) -->
                    @if (auth()->user()->rol?->nombre === 'Admin')
                        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>
                        <div class="flex items-center gap-2">
                            <label for="filtroSuc" class="text-xs font-medium text-slate-600 dark:text-slate-400">Sucursal:</label>
                            <select 
                                id="filtroSuc" 
                                wire:model.live="filtroSucursal" 
                                class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 py-1.5 px-2.5 shadow-2xs"
                            >
                                <option value="">Todas las Sedes</option>
                                @foreach ($sucursales as $suc)
                                    <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro Estado -->
                    <div class="flex items-center gap-2">
                        <label for="filtroEstProf" class="text-xs font-medium text-slate-600 dark:text-slate-400">Estado:</label>
                        <select 
                            id="filtroEstProf" 
                            wire:model.live="filtroEstado" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todos los Estados</option>
                            <option value="En Curso">En Curso</option>
                            <option value="Pagada">Pagada</option>
                            <option value="Anulada">Anulada</option>
                        </select>
                    </div>

                    <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <!-- Filtro Tipo de Atención -->
                    <div class="flex items-center gap-2">
                        <label for="filtroTipAt" class="text-xs font-medium text-slate-600 dark:text-slate-400">Atención:</label>
                        <select 
                            id="filtroTipAt" 
                            wire:model.live="filtroTipo" 
                            class="text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 py-1.5 px-2.5 shadow-2xs"
                        >
                            <option value="">Todas</option>
                            <option value="Ambulatoria">Ambulatoria</option>
                            <option value="Internacion">Internación</option>
                        </select>
                    </div>
                </div>

                <!-- Buscador Debounce -->
                <div class="relative w-full lg:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por código, paciente, CI o médico..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 shadow-2xs"
                    />
                    @if ($search !== '')
                        <button wire:click="$set('search', '')" type="button" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de Proformas -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-14 text-center"># Código</th>
                        <th class="py-3 px-4">Paciente</th>
                        <th class="py-3 px-4">Tipo & Pieza</th>
                        <th class="py-3 px-4">Médico(s)</th>
                        <th class="py-3 px-4">Fecha Ingreso</th>
                        <th class="py-3 px-4 text-center">Detalle Clínico</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Total (Bs.)</th>
                        <th class="py-3 px-4 text-center w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse ($proformas as $proforma)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Código -->
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-xs text-blue-600 dark:text-blue-400">
                                #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Paciente -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ $proforma->paciente->nombre_completo ?? 'Paciente no encontrado' }}
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <span>CI: {{ $proforma->paciente->cedula ?: 'S/D' }}</span>
                                    <span>•</span>
                                    <span>{{ $proforma->paciente->edad !== null ? "{$proforma->paciente->edad} años" : 'S/E' }}</span>
                                    @if ($proforma->paciente?->antecedentes_alergias)
                                        <span class="text-rose-500 font-bold" title="Alergias: {{ $proforma->paciente->antecedentes_alergias }}">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tipo & Pieza -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $proforma->tipo_atencion === 'Internacion' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                    <i class="fas {{ $proforma->tipo_atencion === 'Internacion' ? 'fa-bed' : 'fa-walking' }} text-[10px]"></i>
                                    {{ $proforma->tipo_atencion }}
                                </span>
                                @if ($proforma->pieza)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1">
                                        {{ $proforma->pieza }}
                                    </div>
                                @endif
                            </td>

                            <!-- Médico(s) Tratante(s) -->
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-1 max-w-xs">
                                    @forelse ($proforma->medicos as $med)
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-800 dark:text-slate-200">
                                                <i class="fas fa-user-md text-[10px] text-blue-500"></i>
                                                Dr(a). {{ $med->nombre_completo }}
                                            </span>
                                            @if ($med->especialidad)
                                                <span class="text-[10px] text-slate-400">({{ $med->especialidad->nombre }})</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Sin asignar</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Fecha Ingreso -->
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                                <div>{{ $proforma->fecha_ingreso?->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $proforma->fecha_ingreso?->format('H:i') }} hrs</div>
                            </td>

                            <!-- Detalle Clínico Resumido -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 text-[10px]">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" title="{{ $proforma->servicios_count }} servicios médicos">
                                        <i class="fas fa-stethoscope text-indigo-500 me-0.5"></i> {{ $proforma->servicios_count }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" title="{{ $proforma->solicitudes_count }} exámenes/solicitudes">
                                        <i class="fas fa-flask text-teal-500 me-0.5"></i> {{ $proforma->solicitudes_count }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" title="{{ $proforma->recetas_count }} recetas emitidas">
                                        <i class="fas fa-pills text-purple-500 me-0.5"></i> {{ $proforma->recetas_count }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" title="{{ $proforma->consumos_extras_count }} consumos extras">
                                        <i class="fas fa-syringe text-amber-500 me-0.5"></i> {{ $proforma->consumos_extras_count }}
                                    </span>
                                </div>
                            </td>

                            <!-- Estado -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($proforma->estado === 'En Curso')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        En Curso
                                    </span>
                                @elseif ($proforma->estado === 'Pagada')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Pagada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Anulada
                                    </span>
                                @endif
                            </td>

                            <!-- Total -->
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-sm text-slate-900 dark:text-white">
                                Bs. {{ number_format($proforma->costo_total, 2) }}
                            </td>

                            <!-- Acciones -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Ver / Gestionar Detalle -->
                                    <a 
                                        href="{{ route('proformas.show', $proforma->id) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300 dark:hover:bg-blue-900/60 text-xs font-semibold transition-colors"
                                        title="Gestionar Detalle de la Proforma"
                                    >
                                        <i class="fas fa-stethoscope text-xs"></i>
                                        <span>Abrir</span>
                                    </a>

                                    <!-- Imprimir Detalle Clínico PDF -->
                                    <a 
                                        href="{{ route('proformas.pdf.detalle', $proforma->id) }}" 
                                        target="_blank"
                                        class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                        title="Imprimir Detalle Clínico PDF"
                                    >
                                        <i class="fas fa-file-pdf text-xs"></i>
                                    </a>

                                    @if ($proforma->estado === 'En Curso')
                                        <!-- Anular -->
                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Anular Proforma #{{ $proforma->id }}?',
                                                text: 'Pasará a estado Anulada y no permitirá más cargos.',
                                                icon: 'warning',
                                                confirmButtonText: 'Sí, anular',
                                                cancelButtonText: 'Cancelar',
                                                event: 'anularProforma',
                                                params: {{ $proforma->id }}
                                            })"
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 hover:bg-amber-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Anular Proforma"
                                        >
                                            <i class="fas fa-ban text-xs"></i>
                                        </button>

                                        <!-- Eliminar -->
                                        <button 
                                            type="button" 
                                            @click="$dispatch('swal:confirm', {
                                                title: '¿Eliminar Proforma #{{ $proforma->id }}?',
                                                text: 'Esta acción mandará la proforma a la papelera (SoftDelete).',
                                                icon: 'error',
                                                confirmButtonText: 'Sí, eliminar',
                                                cancelButtonText: 'Cancelar',
                                                event: 'eliminarProforma',
                                                params: {{ $proforma->id }}
                                            })"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                            title="Eliminar Proforma"
                                        >
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 text-2xl mb-3">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <p class="text-base font-semibold text-slate-700 dark:text-slate-200">No se encontraron proformas</p>
                                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                                        No hay registros de atenciones que coincidan con los filtros aplicados.
                                    </p>
                                    <a 
                                        href="{{ route('proformas.crear') }}" 
                                        class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs"
                                    >
                                        <i class="fas fa-plus"></i> Abrir Nueva Proforma
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($proformas->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $proformas->links() }}
            </div>
        @endif
    </div>
</div>
