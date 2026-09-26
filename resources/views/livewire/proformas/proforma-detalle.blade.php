<div>
    <!-- Breadcrumb & Top Bar -->
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('proformas.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Proformas</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">Expediente #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-md shadow-indigo-500/20">
                    <i class="fas fa-hospital-user text-base"></i>
                </span>
                Gestión Clínica Integral (Fase 2)
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <button 
                wire:click="abrirModalEditarCabecera" 
                type="button" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            >
                <i class="fas fa-edit text-xs text-blue-500"></i>
                <span>Editar Cabecera</span>
            </button>

            <a 
                href="{{ route('proformas.index') }}" 
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- FICHA CLÍNICA DEL PACIENTE Y CABECERA PROFORMA -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden mb-6">
        <!-- Patient Identity Banner -->
        <div class="p-5 sm:p-6 bg-gradient-to-r from-slate-50 via-blue-50/30 to-indigo-50/20 dark:from-slate-850 dark:via-slate-900 dark:to-slate-850 border-b border-slate-200 dark:border-slate-800">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <!-- Patient Avatar and Bio -->
                <div class="flex items-start sm:items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-blue-500/25 shrink-0">
                        {{ substr($proforma->paciente->nombres ?? 'P', 0, 1) }}{{ substr($proforma->paciente->apellido_paterno ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                                {{ $proforma->paciente->nombre_completo ?? 'Paciente no registrado' }}
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $proforma->paciente->genero === 'Femenino' ? 'bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                {{ $proforma->paciente->genero ?? 'N/E' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $proforma->estado === 'En Curso' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : ($proforma->estado === 'Pagada' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $proforma->estado === 'En Curso' ? 'bg-amber-500 animate-pulse' : ($proforma->estado === 'Pagada' ? 'bg-emerald-500' : 'bg-rose-500') }}"></span>
                                {{ $proforma->estado }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400 mt-1">
                            <span><strong class="text-slate-700 dark:text-slate-300">CI:</strong> {{ $proforma->paciente->cedula ?: 'Sin documento' }}</span>
                            <span>•</span>
                            <span><strong class="text-slate-700 dark:text-slate-300">Edad:</strong> {{ $proforma->paciente->edad !== null ? "{$proforma->paciente->edad} años" : 'N/E' }}</span>
                            <span>•</span>
                            <span><strong class="text-slate-700 dark:text-slate-300">Teléfono:</strong> {{ $proforma->paciente->celular ?: 'Sin celular' }}</span>
                            @if ($proforma->paciente->contacto_emergencia_nombre)
                                <span>•</span>
                                <span><strong class="text-amber-600 dark:text-amber-400">Emergencia:</strong> {{ $proforma->paciente->contacto_emergencia_nombre }} ({{ $proforma->paciente->contacto_emergencia_telefono }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Proforma Operational Meta Info -->
                <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-slate-800/80 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-2xs">
                    <div class="px-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Modalidad</span>
                        <span class="inline-flex items-center gap-1 text-xs font-bold {{ $proforma->tipo_atencion === 'Internacion' ? 'text-purple-600 dark:text-purple-400' : 'text-blue-600 dark:text-blue-400' }}">
                            <i class="fas {{ $proforma->tipo_atencion === 'Internacion' ? 'fa-bed' : 'fa-walking' }}"></i>
                            {{ $proforma->tipo_atencion }}
                        </span>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

                    <div class="px-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Pieza / Ubicación</span>
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                            {{ $proforma->pieza ?: 'No asignada' }}
                        </span>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

                    <div class="px-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Médicos Tratantes</span>
                        <div class="flex flex-wrap items-center gap-1.5 max-w-xs">
                            @forelse ($proforma->medicos as $med)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-[11px] font-medium border border-blue-200 dark:border-blue-900/50">
                                    <i class="fas fa-user-md text-[10px]"></i>
                                    Dr(a). {{ $med->nombre_completo }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-400 italic">Sin médicos asignados</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

                    <div class="px-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Fecha Ingreso</span>
                        <span class="text-xs font-mono font-medium text-slate-700 dark:text-slate-300">
                            {{ $proforma->fecha_ingreso?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Antecedentes de Alergias Alert Banner -->
            @if ($proforma->paciente->antecedentes_alergias)
                <div class="mt-4 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex items-start gap-2.5 text-rose-800 dark:text-rose-300">
                    <i class="fas fa-exclamation-triangle text-base text-rose-600 dark:text-rose-400 mt-0.5 shrink-0"></i>
                    <div class="text-xs">
                        <span class="font-bold">¡ALERTA MÉDICA / ANTECEDENTES Y ALERGIAS!</span>
                        <p class="mt-0.5 font-medium">{{ $proforma->paciente->antecedentes_alergias }}</p>
                    </div>
                </div>
            @endif

            <!-- Diagnóstico / Motivo de Consulta -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400 block mb-1">Motivo de Consulta</span>
                    <p class="text-slate-700 dark:text-slate-300">{{ $proforma->motivo_consulta ?: 'Sin motivo registrado' }}</p>
                </div>
                <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
                    <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400 block mb-1">Diagnóstico / Observación</span>
                    <p class="text-slate-700 dark:text-slate-300">{{ $proforma->diagnostico ?: 'Sin diagnóstico preliminar' }}</p>
                </div>
            </div>
        </div>

        <!-- CONSOLIDACIÓN DE COSTOS EN TIEMPO REAL -->
        <div class="p-5 sm:p-6 bg-slate-900 text-white">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 flex-1">
                    <!-- Subtotal Servicios -->
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>Servicios Clínicos</span>
                            <i class="fas fa-briefcase-medical text-indigo-400"></i>
                        </div>
                        <span class="text-base sm:text-lg font-bold font-mono text-white">
                            Bs. {{ number_format($subtotalServicios, 2) }}
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $proforma->servicios->count() }} procedimiento(s)</span>
                    </div>

                    <!-- Subtotal Consumos Extras -->
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/60">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>Insumos de Piso</span>
                            <i class="fas fa-syringe text-amber-400"></i>
                        </div>
                        <span class="text-base sm:text-lg font-bold font-mono text-white">
                            Bs. {{ number_format($subtotalConsumos, 2) }}
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $proforma->consumosExtras->count() }} insumo(s) cargado(s)</span>
                    </div>

                    <!-- Subtotal Despachos de Farmacia (Efectivos y Cobrables) -->
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-emerald-500/30">
                        <div class="flex items-center justify-between text-xs text-emerald-400 mb-1">
                            <span>Salidas Farmacia</span>
                            <i class="fas fa-check-double text-emerald-400"></i>
                        </div>
                        <span class="text-base sm:text-lg font-bold font-mono text-emerald-400">
                            Bs. {{ number_format($subtotalFarmacia, 2) }}
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Despachos reales entregados</span>
                    </div>

                    <!-- Prescripción Médica (Referencial / Pauta Clínica) -->
                    <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700/40">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span>Receta (Referencial)</span>
                            <i class="fas fa-pills text-teal-400"></i>
                        </div>
                        <span class="text-base sm:text-lg font-bold font-mono text-slate-300">
                            Bs. {{ number_format($totalPrescripcionReferencial, 2) }}
                        </span>
                        <span class="text-[10px] text-teal-400/90 block mt-0.5">Pauta médica (Sujeta a despacho)</span>
                    </div>
                </div>

                <!-- TOTAL PROFORMA Y ACCIÓN CAJA -->
                <div class="flex flex-col sm:flex-row lg:flex-col items-start sm:items-center lg:items-end justify-between gap-3 border-t lg:border-t-0 lg:border-s border-slate-800 pt-4 lg:pt-0 lg:ps-6">
                    <div class="text-left lg:text-right">
                        <span class="text-xs uppercase tracking-wider font-bold text-slate-400 block">Total Proforma</span>
                        <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-400">
                            Bs. {{ number_format($proforma->costo_total, 2) }}
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            Saldo Pendiente: <strong class="text-white font-mono">Bs. {{ number_format($proforma->saldoPendiente(), 2) }}</strong>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        @click="$dispatch('swal', {
                            icon: 'info',
                            title: 'Módulo de Caja y Pagos',
                            text: 'El total de la proforma es Bs. {{ number_format($proforma->costo_total, 2) }}. Será liquidado y cobrado en el Módulo de Caja (próximo paso).'
                        })"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-all transform active:scale-95 cursor-pointer"
                    >
                        <i class="fas fa-cash-register text-sm"></i>
                        <span>Enviar a Caja / Cobrar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS MODULARES DE GESTIÓN CLÍNICA -->
    <div class="space-y-6">
        <!-- Tab Navigation Bar -->
        <div class="flex items-center space-x-1 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-px">
            <!-- 1. Servicios -->
            <button 
                wire:click="cambiarTab('servicios')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'servicios' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-briefcase-medical text-xs"></i>
                <span>Servicios y Procedimientos</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    {{ $proforma->servicios->count() }}
                </span>
            </button>

            <!-- 2. Solicitudes / Exámenes -->
            <button 
                wire:click="cambiarTab('solicitudes')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'solicitudes' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-flask text-xs"></i>
                <span>Solicitudes y Exámenes</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    {{ $proforma->solicitudes->count() }}
                </span>
            </button>

            <!-- 3. Calendario -->
            <button 
                wire:click="cambiarTab('calendario')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'calendario' ? 'border-purple-600 text-purple-600 dark:text-purple-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-calendar-alt text-xs"></i>
                <span>Calendario y Agenda</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    {{ $proforma->calendarios->count() }}
                </span>
            </button>

            <!-- 4. Recetas Médicas -->
            <button 
                wire:click="cambiarTab('recetas')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'recetas' ? 'border-teal-600 text-teal-600 dark:text-teal-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-prescription text-xs"></i>
                <span>Prescripción y Recetas</span>
                @if ($proforma->recetaActiva)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold">
                        1 Activa
                    </span>
                @else
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-400 font-mono">0</span>
                @endif
            </button>

            <!-- 5. Consumos Extras -->
            <button 
                wire:click="cambiarTab('consumos')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'consumos' ? 'border-amber-600 text-amber-600 dark:text-amber-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-syringe text-xs"></i>
                <span>Consumos Extras e Insumos</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    {{ $proforma->consumosExtras->count() }}
                </span>
            </button>

            <!-- 6. Despachos de Farmacia -->
            <button 
                wire:click="cambiarTab('despachos')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'despachos' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-dolly-flatbed text-xs"></i>
                <span>Despachos de Farmacia</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                    {{ $movimientosDespacho->count() }}
                </span>
            </button>
        </div>

        <!-- CONTENIDO DEL TAB SELECCIONADO -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 transition-colors">
            <!-- =================================================================== -->
            <!-- TAB 1: SERVICIOS Y PROCEDIMIENTOS -->
            <!-- =================================================================== -->
            @if ($tab === 'servicios')
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-briefcase-medical text-indigo-600 dark:text-indigo-400"></i>
                                Catálogo de Servicios y Procedimientos Aplicados
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Asigne consultas, procedimientos, quirófano o curaciones al paciente.</p>
                        </div>
                        <button 
                            wire:click="abrirModalServicio" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                        >
                            <i class="fas fa-plus"></i>
                            <span>Agregar Procedimiento / Servicio</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Servicio Médico</th>
                                    <th class="py-3 px-4">Categoría</th>
                                    <th class="py-3 px-4">Observaciones Clínicas</th>
                                    <th class="py-3 px-4 text-right">Precio Catálogo</th>
                                    <th class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">Costo Final Acordado</th>
                                    <th class="py-3 px-4 text-center w-20">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse ($proforma->servicios as $idx => $ps)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm">
                                                {{ $ps->servicio->nombre ?? 'Servicio eliminado' }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                                {{ $ps->servicio?->categoria?->nombre ?? 'General' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                            {{ $ps->observaciones ?: 'Sin observaciones particulares' }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-400">
                                            Bs. {{ number_format($ps->servicio?->precio_tentativo ?? 0, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-emerald-400 text-sm">
                                            Bs. {{ number_format($ps->costo_final, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <button 
                                                type="button" 
                                                @click="$dispatch('swal:confirm', {
                                                    title: '¿Remover \'{{ addslashes($ps->servicio?->nombre ?? 'Servicio') }}\'?',
                                                    text: 'Se descontará del costo total de la proforma.',
                                                    icon: 'warning',
                                                    confirmButtonText: 'Sí, remover',
                                                    cancelButtonText: 'Cancelar',
                                                    event: 'eliminarServicioProforma',
                                                    params: {{ $ps->id }}
                                                })"
                                                class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                title="Eliminar Servicio de la Proforma"
                                            >
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 px-4 text-center text-slate-400">
                                            <i class="fas fa-briefcase-medical text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-600 dark:text-slate-300">No hay servicios clínicos agregados a esta proforma.</p>
                                            <button 
                                                wire:click="abrirModalServicio" 
                                                type="button" 
                                                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 cursor-pointer"
                                            >
                                                <i class="fas fa-plus"></i> Agregar Primer Servicio
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- =================================================================== -->
            <!-- TAB 2: SOLICITUDES Y EXÁMENES (FORMATO TABLA) -->
            <!-- =================================================================== -->
            @if ($tab === 'solicitudes')
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-flask text-blue-600 dark:text-blue-400"></i>
                                Solicitudes de Laboratorio, Imagenología y Exámenes
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Órdenes de laboratorio, ecografías, rayos X y recepción de archivos digitales adjuntos.</p>
                        </div>
                        <button 
                            wire:click="abrirModalSolicitud" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                        >
                            <i class="fas fa-plus"></i>
                            <span>Nueva Solicitud / Examen</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Estudio / Tipo de Examen</th>
                                    <th class="py-3 px-4">Indicaciones / Observaciones</th>
                                    <th class="py-3 px-4">Fecha de Solicitud</th>
                                    <th class="py-3 px-4">Documento / Informe</th>
                                    <th class="py-3 px-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse ($proforma->solicitudes as $index => $sol)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-100">
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs shrink-0">
                                                    <i class="fas fa-vial"></i>
                                                </span>
                                                <span class="text-sm font-semibold">{{ $sol->tipoSolicitud->nombre ?? 'Estudio General' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300 max-w-xs">
                                            {{ $sol->observaciones ?: 'Sin indicaciones específicas' }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                                            <i class="far fa-clock me-1 text-slate-400"></i>
                                            {{ $sol->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if ($sol->archivo)
                                                <a 
                                                    href="{{ asset('storage/' . $sol->archivo) }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 font-semibold text-xs transition-colors"
                                                >
                                                    <i class="fas fa-file-arrow-down"></i>
                                                    <span>Ver / Descargar</span>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-[11px] font-medium border border-slate-200 dark:border-slate-700">
                                                    <i class="fas fa-file-circle-xmark"></i> Sin archivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button 
                                                    type="button" 
                                                    wire:click="abrirModalSubirArchivo({{ $sol->id }})" 
                                                    wire:loading.attr="disabled"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-xs font-semibold transition-colors cursor-pointer"
                                                    title="{{ $sol->archivo ? 'Reemplazar / Actualizar archivo' : 'Adjuntar informe digital' }}"
                                                >
                                                    <i class="fas {{ $sol->archivo ? 'fa-arrow-up-from-bracket' : 'fa-paperclip' }}"></i>
                                                    <span>{{ $sol->archivo ? 'Actualizar' : 'Subir archivo' }}</span>
                                                </button>

                                                <button 
                                                    type="button" 
                                                    @click="$dispatch('swal:confirm', {
                                                        title: '¿Remover orden de \'{{ addslashes($sol->tipoSolicitud->nombre ?? 'Examen') }}\'?',
                                                        text: 'Se eliminará la solicitud médica y cualquier archivo adjunto.',
                                                        icon: 'warning',
                                                        confirmButtonText: 'Sí, remover',
                                                        cancelButtonText: 'Cancelar',
                                                        event: 'eliminarSolicitudProforma',
                                                        params: {{ $sol->id }}
                                                    })"
                                                    class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                    title="Eliminar Solicitud"
                                                >
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 px-4 text-center text-slate-400">
                                            <i class="fas fa-flask text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-600 dark:text-slate-300">No se han emitido órdenes de laboratorio o estudios complementarios.</p>
                                            <button 
                                                wire:click="abrirModalSolicitud" 
                                                type="button" 
                                                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 cursor-pointer"
                                            >
                                                <i class="fas fa-plus"></i> Solicitar Primer Examen
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- =================================================================== -->
            <!-- TAB 3: CALENDARIO Y AGENDA (FORMATO TABLA) -->
            <!-- =================================================================== -->
            @if ($tab === 'calendario')
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400"></i>
                                Cronograma de Actividades, Controles y Procedimientos
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Agenda cronológica de rondas de enfermería, cambio de sueros, curaciones o cirugías.</p>
                        </div>
                        <button 
                            wire:click="abrirModalCalendario" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                        >
                            <i class="fas fa-plus"></i>
                            <span>Programar Actividad</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Fecha Programada</th>
                                    <th class="py-3 px-4">Hora</th>
                                    <th class="py-3 px-4">Actividad / Control / Procedimiento</th>
                                    <th class="py-3 px-4 text-center">Estado</th>
                                    <th class="py-3 px-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse ($proforma->calendarios as $index => $cal)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors {{ $cal->estado === 'Realizado' ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}">
                                        <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 font-mono font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                                <i class="far fa-calendar text-purple-500"></i>
                                                {{ $cal->fecha?->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-mono font-semibold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                            <i class="far fa-clock text-slate-400 me-1"></i>
                                            {{ substr($cal->hora, 0, 5) }} hrs
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="font-medium {{ $cal->estado === 'Realizado' ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-900 dark:text-slate-100' }}">
                                                {{ $cal->descripcion }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <button 
                                                type="button" 
                                                wire:click="toggleEstadoEvento({{ $cal->id }})" 
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition-all transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $cal->estado === 'Realizado' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-purple-100 text-purple-800 hover:bg-purple-200 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-300 dark:border-purple-800' }}"
                                                title="Haz clic para alternar entre Programado y Realizado"
                                            >
                                                <i class="fas {{ $cal->estado === 'Realizado' ? 'fa-circle-check text-emerald-600' : 'fa-clock text-purple-600' }}"></i>
                                                <span>{{ $cal->estado }}</span>
                                            </button>
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <button 
                                                type="button" 
                                                @click="$dispatch('swal:confirm', {
                                                    title: '¿Remover actividad del calendario?',
                                                    text: 'Se eliminará \'{{ addslashes($cal->descripcion) }}\' de la agenda del paciente.',
                                                    icon: 'warning',
                                                    confirmButtonText: 'Sí, remover',
                                                    cancelButtonText: 'Cancelar',
                                                    event: 'eliminarEventoCalendario',
                                                    params: {{ $cal->id }}
                                                })"
                                                class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                title="Eliminar Actividad"
                                            >
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 px-4 text-center text-slate-400">
                                            <i class="fas fa-calendar-times text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-600 dark:text-slate-300">No hay citas, rondas o curaciones programadas en la agenda.</p>
                                            <button 
                                                wire:click="abrirModalCalendario" 
                                                type="button" 
                                                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 cursor-pointer"
                                            >
                                                <i class="fas fa-plus"></i> Programar Primera Actividad
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- =================================================================== -->
            <!-- TAB 4: PRESCRIPCIÓN Y RECETAS (REGLA: SOLO 1 RECETA ACTIVA) -->
            <!-- =================================================================== -->
            @if ($tab === 'recetas')
                <div class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-prescription text-teal-600 dark:text-teal-400"></i>
                                    Prescripciones y Recetas Médicas
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                    Regla: 1 Receta Activa
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Farmacia despacha únicamente los ítems de la prescripción marcada como Activa.</p>
                        </div>
                        <button 
                            wire:click="abrirModalReceta" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                        >
                            <i class="fas fa-plus"></i>
                            <span>Prescribir Nueva Receta</span>
                        </button>
                    </div>

                    <!-- BANNER EXPLICATIVO: REGLA DE COBRO POR SALIDA DE FARMACIA -->
                    <div class="p-3.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/50 flex items-start gap-3 text-xs text-blue-900 dark:text-blue-200">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5 text-sm shrink-0"></i>
                        <div class="space-y-1">
                            <span class="font-bold">Pauta Médica y Despachos de Farmacia:</span>
                            <p class="text-blue-800/90 dark:text-blue-300 leading-relaxed">
                                Las recetas constituyen la guía de tratamiento clínico indicada por el médico. El importe total de la receta es un <strong class="font-semibold underline">estimado referencial</strong>; a la cuenta de la proforma <strong class="font-semibold">solo se cargan los medicamentos efectivamente entregados por Farmacia</strong> día a día o según horario, previniendo cobros indebidos si el médico suspende o modifica la dosis del tratamiento.
                            </p>
                        </div>
                    </div>

                    <!-- RECETA ACTIVA VIGENTE -->
                    @if ($proforma->recetaActiva)
                        <div class="p-5 rounded-2xl border-2 border-emerald-500/80 bg-gradient-to-br from-emerald-50/40 via-white to-teal-50/30 dark:from-slate-850 dark:via-slate-900 dark:to-emerald-950/20 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white text-base shadow-sm">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                                Receta Médica Activa #{{ $proforma->recetaActiva->id }}
                                            </h4>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 animate-pulse">
                                                VIGENTE EN FARMACIA
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">
                                            Emitida por: <strong class="text-slate-700 dark:text-slate-300">Dr(a). {{ $proforma->recetaActiva->doctor->nombre_completo ?? 'Médico Tratante' }}</strong> • {{ $proforma->recetaActiva->created_at?->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:items-end gap-1">
                                    <div class="text-xs font-mono font-medium text-slate-500 dark:text-slate-400">
                                        Pauta Prescrita (Estimado Referencial): <span class="font-bold text-slate-700 dark:text-slate-200">Bs. {{ number_format($totalPrescripcionReferencial, 2) }}</span>
                                    </div>
                                    <div class="text-xs font-mono font-bold text-teal-600 dark:text-teal-400">
                                        Despachado en Farmacia (Cobrable): Bs. {{ number_format($subtotalFarmacia, 2) }}
                                    </div>
                                </div>
                            </div>

                            @if ($proforma->recetaActiva->observaciones)
                                <div class="mb-4 p-2.5 rounded-lg bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300">
                                    <strong class="font-bold text-slate-800 dark:text-slate-200">Indicaciones Generales:</strong> {{ $proforma->recetaActiva->observaciones }}
                                </div>
                            @endif

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-emerald-100/50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold border-b border-emerald-200 dark:border-slate-700 uppercase tracking-wider text-[10px]">
                                        <tr>
                                            <th class="py-2.5 px-3">Medicamento / Fármaco</th>
                                            <th class="py-2.5 px-3 text-center">Cantidad</th>
                                            <th class="py-2.5 px-3">Posología e Indicaciones (Dosis / Horas / Días)</th>
                                            <th class="py-2.5 px-3 text-right">Precio Unitario</th>
                                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                                            <th class="py-2.5 px-3 text-center">Estado Farmacia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @foreach ($proforma->recetaActiva->detalles as $det)
                                            <tr class="hover:bg-white/60 dark:hover:bg-slate-800/60">
                                                <td class="py-2.5 px-3">
                                                    <div class="font-bold text-slate-800 dark:text-white">
                                                        {{ $det->producto->nombre ?? 'Producto no encontrado' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400">
                                                        {{ $det->producto?->unidad_medida ?? 'Presentación estándar' }}
                                                    </div>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-bold font-mono text-xs">
                                                    {{ $det->cantidad }}
                                                </td>
                                                <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">
                                                    {{ $det->indicaciones }}
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono text-slate-500">
                                                    Bs. {{ number_format($det->producto?->ultimo_precio_venta ?? 0, 2) }}
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800 dark:text-emerald-400">
                                                    Bs. {{ number_format($det->cantidad * ($det->producto?->ultimo_precio_venta ?? 0), 2) }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    @if ($det->despachado)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                            <i class="fas fa-check me-1"></i> Despachado
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                                                            <i class="fas fa-clock me-1"></i> Pendiente
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="p-6 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 text-center text-slate-400">
                            <i class="fas fa-prescription-bottle text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p class="font-medium text-slate-700 dark:text-slate-200">No hay receta activa vigente para este paciente.</p>
                            <p class="text-xs text-slate-400 mt-1">Prescriba una receta para que farmacia proceda al despacho de medicamentos.</p>
                            <button 
                                wire:click="abrirModalReceta" 
                                type="button" 
                                class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 cursor-pointer"
                            >
                                <i class="fas fa-plus"></i> Prescribir Receta Ahora
                            </button>
                        </div>
                    @endif

                    <!-- HISTORIAL DE RECETAS ANTERIORES (INACTIVAS) -->
                    @php
                        $recetasHistoricas = $proforma->recetas->where('activo', false);
                    @endphp

                    @if ($recetasHistoricas->isNotEmpty())
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800" x-data="{ verHistorico: false }">
                            <button 
                                @click="verHistorico = !verHistorico" 
                                type="button" 
                                class="flex items-center justify-between w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            >
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-history text-slate-400"></i>
                                    <span>Historial de Recetas Anteriores ({{ $recetasHistoricas->count() }} archivadas)</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': verHistorico }"></i>
                            </button>

                            <div x-show="verHistorico" x-collapse class="mt-3 space-y-3" style="display: none;">
                                @foreach ($recetasHistoricas as $recOld)
                                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-700 dark:text-slate-300">Receta #{{ $recOld->id }} (Histórica)</span>
                                                <span class="px-2 py-0.2 rounded text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 font-semibold">Inactiva</span>
                                            </div>
                                            <span class="text-slate-400 font-mono text-[11px]">{{ $recOld->created_at?->format('d/m/Y H:i') }}</span>
                                        </div>

                                        <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                                            @foreach ($recOld->detalles as $dOld)
                                                <li>
                                                    <strong>{{ $dOld->producto->nombre ?? 'Medicamento' }}</strong> ({{ $dOld->cantidad }} un.): {{ $dOld->indicaciones }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- =================================================================== -->
            <!-- TAB 5: CONSUMOS EXTRAS E INSUMOS DIRECTOS -->
            <!-- =================================================================== -->
            @if ($tab === 'consumos')
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-syringe text-amber-600 dark:text-amber-400"></i>
                                Consumos Extras, Descartables y Material Hospitalario
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Jeringas, soluciones fisiológicas, apósitos o catéteres reportados directamente por enfermería y médicos.</p>
                        </div>
                        <button 
                            wire:click="abrirModalConsumo" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-md shadow-amber-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                        >
                            <i class="fas fa-plus"></i>
                            <span>Registrar Consumo de Insumo</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Insumo / Material</th>
                                    <th class="py-3 px-4 text-center">Cantidad</th>
                                    <th class="py-3 px-4 text-right">Precio Unitario</th>
                                    <th class="py-3 px-4 text-right font-bold text-slate-900 dark:text-white">Subtotal</th>
                                    <th class="py-3 px-4">Reportado Por</th>
                                    <th class="py-3 px-4">Fecha y Hora</th>
                                    <th class="py-3 px-4 text-center w-20">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse ($proforma->consumosExtras as $idx => $ce)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm">
                                                {{ $ce->producto->nombre ?? 'Insumo eliminado' }}
                                            </div>
                                            @if ($ce->observaciones)
                                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $ce->observaciones }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono font-bold">
                                            {{ $ce->cantidad }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-500">
                                            Bs. {{ number_format($ce->precio_unitario, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-amber-400 text-sm">
                                            Bs. {{ number_format($ce->cantidad * $ce->precio_unitario, 2) }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                            {{ $ce->user->nombre_completo ?? 'Personal de guardia' }}
                                        </td>
                                        <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">
                                            {{ $ce->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <button 
                                                type="button" 
                                                @click="$dispatch('swal:confirm', {
                                                    title: '¿Remover insumo extra?',
                                                    text: 'Se descontará del costo total de la proforma.',
                                                    icon: 'warning',
                                                    confirmButtonText: 'Sí, remover',
                                                    cancelButtonText: 'Cancelar',
                                                    event: 'eliminarConsumoExtra',
                                                    params: {{ $ce->id }}
                                                })"
                                                class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                title="Eliminar Insumo"
                                            >
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 px-4 text-center text-slate-400">
                                            <i class="fas fa-syringe text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-600 dark:text-slate-300">No se han registrado consumos extras de insumos o materiales hospitalarios.</p>
                                            <button 
                                                wire:click="abrirModalConsumo" 
                                                type="button" 
                                                class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 cursor-pointer"
                                            >
                                                <i class="fas fa-plus"></i> Registrar Primer Insumo
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- =================================================================== -->
            <!-- TAB 6: DESPACHOS Y ENTREGAS DE FARMACIA -->
            <!-- =================================================================== -->
            @if ($tab === 'despachos')
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-dolly-flatbed text-emerald-600 dark:text-emerald-400"></i>
                                Historial de Despachos y Entregas de Farmacia
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Control de medicamentos e insumos dispensados físicamente con descuento en tiempo real del stock de inventario.</p>
                        </div>
                        <button 
                            wire:click="abrirModalDespacho" 
                            wire:loading.attr="disabled"
                            type="button" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                        >
                            <i class="fas fa-plus-circle"></i>
                            <span>Realizar Despacho</span>
                        </button>
                    </div>

                    <!-- Resumen rápido de despachos -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Total Entregas Realizadas</span>
                            <div class="flex items-center justify-between">
                                <span class="text-base font-mono font-bold text-slate-800 dark:text-slate-100">
                                    {{ $movimientosDespacho->count() }} ítems
                                </span>
                                <i class="fas fa-dolly text-emerald-500/80"></i>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Valor Total Dispensado</span>
                            <div class="flex items-center justify-between">
                                <span class="text-base font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    Bs. {{ number_format($subtotalFarmacia, 2) }}
                                </span>
                                <i class="fas fa-receipt text-emerald-500/80"></i>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-0.5">Estado de Prescripción</span>
                            <div class="flex items-center justify-between">
                                @if ($proforma->recetaActiva)
                                    <span class="text-xs font-bold text-teal-600 dark:text-teal-400">
                                        Receta #{{ $proforma->recetaActiva->id }} ({{ $proforma->recetaActiva->detalles->count() }} fármacos)
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Sin receta activa</span>
                                @endif
                                <i class="fas fa-prescription text-teal-500/80"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Despachos -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">#</th>
                                    <th class="py-3 px-4">Fecha y Hora</th>
                                    <th class="py-3 px-4 text-center">Tipo de Salida</th>
                                    <th class="py-3 px-4">Medicamento / Insumo</th>
                                    <th class="py-3 px-4">Lote & Vencimiento</th>
                                    <th class="py-3 px-4 text-center">Cantidad</th>
                                    <th class="py-3 px-4 text-right">Subtotal Estimado</th>
                                    <th class="py-3 px-4 text-center">Receta</th>
                                    <th class="py-3 px-4">Dispensado Por</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @forelse ($movimientosDespacho as $idx => $mov)
                                    @php
                                        $precioUnit = (float) ($mov->lote?->precio_venta ?? $mov->producto?->ultimo_precio_venta ?? 0);
                                        $subtotalMov = $mov->cantidad * $precioUnit;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                            {{ $mov->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($mov->tipo_movimiento === 'Salida Receta')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-200 dark:border-teal-900/50">
                                                    <i class="fas fa-prescription text-[9px]"></i> Receta Médica
                                                </span>
                                            @elseif ($mov->tipo_movimiento === 'Consumo Extra')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50">
                                                    <i class="fas fa-box-open text-[9px]"></i> Insumo Extra
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/50">
                                                    <i class="fas fa-pills text-[9px]"></i> {{ $mov->tipo_movimiento }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white text-xs">
                                                {{ $mov->producto->nombre ?? 'Producto no encontrado' }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 mt-0.5">
                                                {{ $mov->producto->unidad_medida ?? 'Unidad' }}
                                                @if ($mov->producto?->marca)
                                                    • {{ $mov->producto->marca->nombre }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-mono font-bold text-slate-700 dark:text-slate-300 text-xs">
                                                {{ $mov->lote?->codigo_lote ?? 'S/L' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                Vto: {{ $mov->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs">
                                            {{ $mov->cantidad }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                            <div class="font-bold">Bs. {{ number_format($subtotalMov, 2) }}</div>
                                            <div class="text-[10px] text-slate-400">@ Bs. {{ number_format($precioUnit, 2) }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($mov->receta_id)
                                                <span class="font-mono text-xs font-bold text-teal-600 dark:text-teal-400">
                                                    #{{ $mov->receta_id }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                                            {{ $mov->usuario?->nombre_completo ?? $mov->user?->nombre_completo ?? 'Personal de Farmacia' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-12 px-4 text-center text-slate-400">
                                            <i class="fas fa-dolly-flatbed text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-600 dark:text-slate-300">Aún no se han registrado despachos de farmacia ni consumos para esta proforma.</p>
                                            <p class="text-xs text-slate-400 mt-0.5">Puede dispensar los medicamentos de la receta activa o cargar insumos extras directamente.</p>
                                            <button 
                                                wire:click="abrirModalDespacho" 
                                                type="button" 
                                                class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 cursor-pointer shadow-md shadow-emerald-500/20"
                                            >
                                                <i class="fas fa-plus-circle"></i> Realizar Primer Despacho
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODALES DE OPERACIÓN CLÍNICA -->
    <!-- ======================================================================= -->

    <!-- MODAL 1: EDITAR CABECERA -->
    @if ($modalEditarCabeceraOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-edit text-blue-500"></i>
                            Actualizar Datos Generales del Expediente
                        </h3>
                        <button wire:click="cerrarModalEditarCabecera" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarCabecera" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="edit_pieza" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Sala / Cama / Pieza
                                </label>
                                <input type="text" id="edit_pieza" wire:model="edit_pieza" placeholder="Ej. Cama 3 - Sala 2" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs">
                            </div>

                            <div>
                                <label for="edit_estado" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Estado de la Proforma <span class="text-rose-500">*</span>
                                </label>
                                <select id="edit_estado" wire:model="edit_estado" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs">
                                    <option value="En Curso">En Curso</option>
                                    <option value="Pagada">Pagada</option>
                                    <option value="Anulada">Anulada</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="edit_fecha_salida" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Fecha y Hora Estimada de Alta / Salida
                            </label>
                            <input type="datetime-local" id="edit_fecha_salida" wire:model="edit_fecha_salida" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs">
                        </div>

                        <div>
                            <label for="edit_motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Motivo de Consulta
                            </label>
                            <textarea id="edit_motivo" wire:model="edit_motivo" rows="2" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <div>
                            <label for="edit_diagnostico" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Diagnóstico / Evolución
                            </label>
                            <textarea id="edit_diagnostico" wire:model="edit_diagnostico" rows="2" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Médicos Asignados / Tratantes
                            </label>
                            <div class="max-h-44 overflow-y-auto space-y-1.5 p-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                                @forelse ($medicos as $med)
                                    <label class="flex items-center gap-2 p-1.5 rounded-md hover:bg-white dark:hover:bg-slate-700/60 cursor-pointer text-xs transition-colors">
                                        <input type="checkbox" wire:model="edit_medicos" value="{{ $med->id }}" class="rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600">
                                        <span class="font-medium text-slate-800 dark:text-slate-200">Dr(a). {{ $med->nombre_completo }}</span>
                                        @if ($med->especialidad)
                                            <span class="text-[10px] text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-1.5 py-0.5 rounded">({{ $med->especialidad->nombre }})</span>
                                        @endif
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-400 italic p-1">No hay médicos registrados.</p>
                                @endforelse
                            </div>
                            @error('edit_medicos') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalEditarCabecera" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarCabecera"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarCabecera">
                                    <i class="fas fa-save me-1"></i> Actualizar Cabecera
                                </span>
                                <span wire:loading wire:target="guardarCabecera">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: AGREGAR SERVICIOS CLÍNICOS (EN LOTE) -->
    @if ($modalServicioOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-briefcase-medical text-indigo-600"></i>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                Agregar Servicios / Procedimientos
                            </h3>
                        </div>
                        <button wire:click="cerrarModalServicio" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="agregarServicio" class="p-6 space-y-4">
                        <!-- Selector y Datos del Servicio -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <!-- Buscador reactivo de Servicio o Ficha de Selección -->
                            @if ($nuevo_servicio_id && $servicioSeleccionado)
                                <div class="p-3 rounded-xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-indigo-900 dark:text-indigo-200">
                                                {{ $servicioSeleccionado->nombre }}
                                            </div>
                                            <div class="text-[11px] text-indigo-600 dark:text-indigo-400">
                                                Categoría: {{ $servicioSeleccionado->categoria->nombre ?? 'General' }} • Ref: Bs. {{ number_format($servicioSeleccionado->precio_tentativo, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        wire:click="limpiarServicioSeleccionado" 
                                        class="px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer shrink-0"
                                    >
                                        <i class="fas fa-times me-1"></i> Cambiar
                                    </button>
                                </div>
                            @else
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                        Buscar Procedimiento / Servicio
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.250ms="buscarServicio" 
                                            placeholder="Escriba para buscar por nombre o categoría..." 
                                            class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                                        >
                                        @if ($buscarServicio)
                                            <button type="button" wire:click="$set('buscarServicio', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Lista reactiva de coincidencias -->
                                    <div class="max-h-44 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg divide-y divide-slate-100 dark:divide-slate-700/60">
                                        @forelse ($serviciosFiltrados as $serv)
                                            <button 
                                                type="button" 
                                                wire:click="seleccionarServicio({{ $serv->id }})" 
                                                class="w-full text-left p-2.5 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 transition-colors flex items-center justify-between group cursor-pointer"
                                            >
                                                <div>
                                                    <div class="font-bold text-xs text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                                        {{ $serv->nombre }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        {{ $serv->categoria->nombre ?? 'General' }}
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                        Bs. {{ number_format($serv->precio_tentativo, 2) }}
                                                    </span>
                                                    <span class="block text-[10px] text-slate-400">sugerido</span>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="p-3 text-center text-xs text-slate-400">
                                                <i class="fas fa-search me-1"></i> No se encontraron procedimientos coincidentes.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="nuevo_servicio_costo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Costo Acordado (Bs.)
                                    </label>
                                    <input type="number" step="0.01" id="nuevo_servicio_costo" wire:model="nuevo_servicio_costo" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2 shadow-2xs font-mono font-bold text-indigo-600">
                                </div>

                                <div>
                                    <label for="nuevo_servicio_observaciones" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Observaciones
                                    </label>
                                    <input type="text" id="nuevo_servicio_observaciones" wire:model="nuevo_servicio_observaciones" placeholder="Detalle médico..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2 shadow-2xs">
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button 
                                    type="button" 
                                    wire:click="agregarServicioACola" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 text-xs font-bold border border-indigo-200 dark:border-indigo-800 transition-colors cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i>
                                    <span>Añadir a la cola</span>
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Servicios en Cola Temporal -->
                        @if (! empty($cola_servicios))
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                                    Servicios en cola para registrar ({{ count($cola_servicios) }}):
                                </span>
                                <div class="max-h-44 overflow-y-auto space-y-1.5 border border-slate-200 dark:border-slate-800 rounded-lg p-2 bg-slate-50/50 dark:bg-slate-850/50">
                                    @foreach ($cola_servicios as $idx => $cs)
                                        <div class="flex items-center justify-between p-2 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                            <div>
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $cs['nombre'] }}</span>
                                                <span class="text-slate-400 block text-[11px]">{{ $cs['observaciones'] ?: $cs['categoria'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="font-mono font-bold text-indigo-600">Bs. {{ number_format($cs['costo_final'], 2) }}</span>
                                                <button type="button" wire:click="eliminarServicioDeCola({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalServicio" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="agregarServicio"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="agregarServicio">
                                    <i class="fas fa-save me-1"></i> Guardar {{ count($cola_servicios) > 1 ? count($cola_servicios).' Servicios' : 'Servicio' }}
                                </span>
                                <span wire:loading wire:target="agregarServicio">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: AGREGAR SOLICITUD / EXAMEN (EN LOTE) -->
    @if ($modalSolicitudOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-flask text-blue-600"></i>
                            Solicitud de Examen o Estudio Clínico
                        </h3>
                        <button wire:click="cerrarModalSolicitud" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="agregarSolicitud" class="p-6 space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <div>
                                <label for="nuevo_tipo_solicitud_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Tipo de Examen / Estudio
                                </label>
                                <select id="nuevo_tipo_solicitud_id" wire:model="nuevo_tipo_solicitud_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs">
                                    <option value="">-- Seleccione Estudio Requerido --</option>
                                    @foreach ($tiposSolicitudes as $ts)
                                        <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="nuevo_solicitud_observaciones" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Indicaciones o Motivo del Examen
                                </label>
                                <input type="text" id="nuevo_solicitud_observaciones" wire:model="nuevo_solicitud_observaciones" placeholder="Detalles de la orden o informe..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                            </div>

                            <div>
                                <label for="nuevo_solicitud_archivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Adjuntar Archivo Digital (PDF / JPG - Opcional)
                                </label>
                                <input type="file" id="nuevo_solicitud_archivo" wire:model="nuevo_solicitud_archivo" class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>

                            <div class="flex justify-end pt-1">
                                <button 
                                    type="button" 
                                    wire:click="agregarSolicitudACola" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-300 text-xs font-bold border border-blue-200 dark:border-blue-800 transition-colors cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i>
                                    <span>Añadir a la cola</span>
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Solicitudes en Cola Temporal -->
                        @if (! empty($cola_solicitudes))
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                                    Exámenes en cola para registrar ({{ count($cola_solicitudes) }}):
                                </span>
                                <div class="max-h-44 overflow-y-auto space-y-1.5 border border-slate-200 dark:border-slate-800 rounded-lg p-2 bg-slate-50/50 dark:bg-slate-850/50">
                                    @foreach ($cola_solicitudes as $idx => $csol)
                                        <div class="flex items-center justify-between p-2 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                            <div>
                                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $csol['tipo_nombre'] }}</span>
                                                <span class="text-slate-400 block text-[11px]">
                                                    {{ $csol['observaciones'] ?: 'Sin observaciones' }}
                                                    @if ($csol['archivo']) • <span class="text-blue-500">Con adjunto</span> @endif
                                                </span>
                                            </div>
                                            <button type="button" wire:click="eliminarSolicitudDeCola({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalSolicitud" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="agregarSolicitud"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="agregarSolicitud">
                                    <i class="fas fa-save me-1"></i> Guardar {{ count($cola_solicitudes) > 1 ? count($cola_solicitudes).' Solicitudes' : 'Solicitud' }}
                                </span>
                                <span wire:loading wire:target="agregarSolicitud">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: SUBIR / ACTUALIZAR ARCHIVO ADJUNTO DE SOLICITUD -->
    @if ($modalArchivoSolicitudOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-file-arrow-up text-blue-600"></i>
                            Adjuntar Resultado / Documento
                        </h3>
                        <button wire:click="cerrarModalSubirArchivo" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarArchivoSolicitud" class="p-6 space-y-4">
                        <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-slate-800 border border-blue-100 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300">
                            <span class="font-bold text-blue-700 dark:text-blue-400 block mb-0.5">Estudio Médico:</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $solicitud_estudio_nombre }}</span>
                        </div>

                        <div>
                            <label for="solicitud_nuevo_archivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Seleccionar Archivo Digital <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="file" 
                                id="solicitud_nuevo_archivo" 
                                wire:model="solicitud_nuevo_archivo" 
                                accept="application/pdf,image/*" 
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-300 p-2 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer"
                            >
                            <p class="text-[11px] text-slate-400 mt-1">
                                <i class="fas fa-info-circle me-1"></i> Formatos aceptados: <strong>PDF, JPG, PNG, WEBP</strong> (Máx. 10MB).
                            </p>
                            @error('solicitud_nuevo_archivo') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror

                            <div wire:loading wire:target="solicitud_nuevo_archivo" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                <i class="fas fa-spinner fa-spin me-1"></i> Procesando archivo...
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalSubirArchivo" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarArchivoSolicitud"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarArchivoSolicitud">
                                    <i class="fas fa-cloud-arrow-up me-1"></i> Guardar Archivo
                                </span>
                                <span wire:loading wire:target="guardarArchivoSolicitud">
                                    <i class="fas fa-spinner fa-spin"></i> Subiendo...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 4: AGREGAR EVENTOS CALENDARIO (EN LOTE) -->
    @if ($modalCalendarioOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-calendar-plus text-purple-600"></i>
                            Programar Actividades / Controles
                        </h3>
                        <button wire:click="cerrarModalCalendario" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="agregarEventoCalendario" class="p-6 space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="nuevo_evento_fecha" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Fecha
                                    </label>
                                    <input type="date" id="nuevo_evento_fecha" wire:model="nuevo_evento_fecha" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>

                                <div>
                                    <label for="nuevo_evento_hora" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Hora
                                    </label>
                                    <input type="time" id="nuevo_evento_hora" wire:model="nuevo_evento_hora" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <label for="nuevo_evento_descripcion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Descripción de la Actividad
                                    </label>
                                    <input type="text" id="nuevo_evento_descripcion" wire:model="nuevo_evento_descripcion" placeholder="Ej. Pase de visita, curación, suero..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>

                                <div>
                                    <label for="nuevo_evento_estado" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Estado
                                    </label>
                                    <select id="nuevo_evento_estado" wire:model="nuevo_evento_estado" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                        <option value="Programado">Programado</option>
                                        <option value="Realizado">Realizado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <button 
                                    type="button" 
                                    wire:click="agregarEventoACola" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-950 dark:text-purple-300 text-xs font-bold border border-purple-200 dark:border-purple-800 transition-colors cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i>
                                    <span>Añadir al cronograma</span>
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Eventos en Cola Temporal -->
                        @if (! empty($cola_eventos))
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                                    Actividades en cola para registrar ({{ count($cola_eventos) }}):
                                </span>
                                <div class="max-h-44 overflow-y-auto space-y-1.5 border border-slate-200 dark:border-slate-800 rounded-lg p-2 bg-slate-50/50 dark:bg-slate-850/50">
                                    @foreach ($cola_eventos as $idx => $cev)
                                        <div class="flex items-center justify-between p-2 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-purple-600 bg-purple-50 dark:bg-purple-950 px-1.5 py-0.5 rounded text-[10px]">
                                                    {{ $cev['fecha'] }} {{ $cev['hora'] }}
                                                </span>
                                                <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $cev['descripcion'] }}</span>
                                            </div>
                                            <button type="button" wire:click="eliminarEventoDeCola({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalCalendario" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="agregarEventoCalendario"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="agregarEventoCalendario">
                                    <i class="fas fa-calendar-check me-1"></i> Guardar {{ count($cola_eventos) > 1 ? count($cola_eventos).' Actividades' : 'Actividad' }}
                                </span>
                                <span wire:loading wire:target="agregarEventoCalendario">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 5: PRESCRIBIR NUEVA RECETA (REGLA: SOLO 1 ACTIVA) -->
    @if ($modalRecetaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-prescription text-teal-600"></i>
                                Elaborar Nueva Receta Médica
                            </h3>
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium mt-0.5">
                                <i class="fas fa-info-circle me-1"></i> Esta receta pasará a estado ACTIVO. Las recetas anteriores serán archivadas.
                            </p>
                        </div>
                        <button wire:click="cerrarModalReceta" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="prescribirReceta" class="p-6 space-y-4">
                        <div>
                            <label for="receta_medico_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Médico Prescriptor <span class="text-rose-500">*</span>
                            </label>
                            <select id="receta_medico_id" wire:model="receta_medico_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('receta_medico_id') border-rose-500 @enderror">
                                @foreach ($medicos as $m)
                                    <option value="{{ $m->id }}">Dr(a). {{ $m->nombre_completo }} ({{ $m->especialidad?->nombre ?? 'General' }})</option>
                                @endforeach
                            </select>
                            @error('receta_medico_id') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="receta_observaciones" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Indicaciones / Recomendaciones Generales de Tratamiento
                            </label>
                            <textarea id="receta_observaciones" wire:model="receta_observaciones" rows="2" placeholder="Dieta blanda, reposo relativo, abundante hidratación..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <!-- Lista Dinámica de Medicamentos -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    Medicamentos a Prescribir <span class="text-rose-500">*</span>
                                </label>
                                <button 
                                    type="button" 
                                    wire:click="agregarFilaMedicamento" 
                                    class="text-xs font-bold text-teal-600 hover:text-teal-800 dark:text-teal-400 flex items-center gap-1 cursor-pointer"
                                >
                                    <i class="fas fa-plus-circle"></i>
                                    <span>+ Añadir Fármaco</span>
                                </button>
                            </div>

                            <div class="space-y-3 max-h-60 overflow-y-auto pe-1">
                                @foreach ($receta_medicamentos as $index => $item)
                                    <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-850 space-y-2 relative">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-teal-700 dark:text-teal-300">Fármaco #{{ $index + 1 }}</span>
                                            @if (count($receta_medicamentos) > 1)
                                                <button 
                                                    type="button" 
                                                    wire:click="eliminarFilaMedicamento({{ $index }})" 
                                                    class="text-rose-500 hover:text-rose-700 text-xs cursor-pointer p-0.5"
                                                    title="Quitar fármaco"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                            @if (! empty($item['producto_id']))
                                                <div class="sm:col-span-2">
                                                    <div class="flex items-center justify-between p-2 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60">
                                                        <div class="flex items-center gap-2 truncate">
                                                            <i class="fas fa-pills text-teal-600 text-xs shrink-0"></i>
                                                            <span class="text-xs font-bold text-teal-900 dark:text-teal-200 truncate">
                                                                {{ $item['producto_nombre'] ?? 'Medicamento' }}
                                                            </span>
                                                            <span class="text-[11px] text-teal-600 dark:text-teal-400 font-medium shrink-0">
                                                                ({{ $item['unidad_medida'] ?? 'Unidad' }})
                                                            </span>
                                                        </div>
                                                        <button 
                                                            type="button" 
                                                            wire:click="limpiarMedicamentoReceta({{ $index }})" 
                                                            class="text-rose-500 hover:text-rose-700 text-[11px] font-bold cursor-pointer shrink-0 ms-2"
                                                        >
                                                            <i class="fas fa-times me-0.5"></i> Cambiar
                                                        </button>
                                                    </div>
                                                    @error("receta_medicamentos.{$index}.producto_id") <p class="text-[10px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                                </div>
                                            @else
                                                <div class="sm:col-span-2 relative">
                                                    <div class="relative">
                                                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                                        <input 
                                                            type="text" 
                                                            wire:model.live.debounce.250ms="receta_medicamentos.{{ $index }}.busqueda" 
                                                            placeholder="Escriba para buscar medicamento..." 
                                                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500"
                                                        >
                                                    </div>

                                                    @if (! empty($sugerenciasMedicamentosReceta[$index]) && count($sugerenciasMedicamentosReceta[$index]) > 0)
                                                        <div class="absolute left-0 right-0 mt-1 max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl divide-y divide-slate-100 dark:divide-slate-700/60 z-20">
                                                            @foreach ($sugerenciasMedicamentosReceta[$index] as $sug)
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="seleccionarMedicamentoReceta({{ $index }}, {{ $sug->id }})" 
                                                                    class="w-full text-left p-2 hover:bg-teal-50/70 dark:hover:bg-teal-950/40 text-xs flex items-center justify-between cursor-pointer"
                                                                >
                                                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sug->nombre }}</span>
                                                                    <span class="text-[10px] text-slate-400">{{ $sug->unidad_medida }}</span>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @elseif(! empty($item['busqueda']) && strlen(trim($item['busqueda'])) >= 2)
                                                        <div class="absolute left-0 right-0 mt-1 p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xl text-center text-xs text-slate-400 z-20">
                                                            Sin resultados para "{{ $item['busqueda'] }}"
                                                        </div>
                                                    @endif
                                                    @error("receta_medicamentos.{$index}.producto_id") <p class="text-[10px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                                </div>
                                            @endif

                                            <div>
                                                <input type="number" min="1" wire:model="receta_medicamentos.{{ $index }}.cantidad" placeholder="Cant." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 text-center">
                                                @error("receta_medicamentos.{$index}.cantidad") <p class="text-[10px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                            </div>
                                        </div>

                                        <div>
                                            <input type="text" wire:model="receta_medicamentos.{{ $index }}.indicaciones" placeholder="Dosis y horario (ej. 1 comp cada 8 hrs por 5 días vía oral)" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2">
                                            @error("receta_medicamentos.{$index}.indicaciones") <p class="text-[10px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalReceta" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="prescribirReceta"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="prescribirReceta">
                                    <i class="fas fa-check-circle me-1"></i> Emitir Receta Activa
                                </span>
                                <span wire:loading wire:target="prescribirReceta">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 6: REGISTRAR CONSUMOS EXTRAS (EN LOTE) -->
    @if ($modalConsumoOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-boxes-stacked text-amber-600"></i>
                            Cargar Consumos de Insumos Hospitalarios
                        </h3>
                        <button wire:click="cerrarModalConsumo" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="registrarConsumoExtra" class="p-6 space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <!-- Buscador reactivo de Insumo / Material o Ficha de Selección -->
                            @if ($consumo_producto_id && $productoConsumoSeleccionado)
                                <div class="p-3 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-amber-900 dark:text-amber-200">
                                                {{ $productoConsumoSeleccionado->nombre }}
                                            </div>
                                            <div class="text-[11px] text-amber-600 dark:text-amber-400">
                                                Unidad: {{ $productoConsumoSeleccionado->unidad_medida ?? 'Unidad' }} • Precio Sugerido: Bs. {{ number_format($productoConsumoSeleccionado->ultimo_precio_venta, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        wire:click="limpiarInsumoConsumo" 
                                        class="px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer shrink-0"
                                    >
                                        <i class="fas fa-times me-1"></i> Cambiar
                                    </button>
                                </div>
                            @else
                                <div class="space-y-1.5">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                        Buscar Insumo / Material Descartable
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.250ms="buscarInsumoConsumo" 
                                            placeholder="Escriba para buscar por nombre o descripción..." 
                                            class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 shadow-2xs"
                                        >
                                        @if ($buscarInsumoConsumo)
                                            <button type="button" wire:click="$set('buscarInsumoConsumo', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Resultados reactivos de insumos -->
                                    <div class="max-h-44 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg divide-y divide-slate-100 dark:divide-slate-700/60">
                                        @forelse ($productosParaConsumo as $pCons)
                                            <button 
                                                type="button" 
                                                wire:click="seleccionarInsumoConsumo({{ $pCons->id }})" 
                                                class="w-full text-left p-2.5 hover:bg-amber-50/70 dark:hover:bg-amber-950/40 transition-colors flex items-center justify-between group cursor-pointer"
                                            >
                                                <div>
                                                    <div class="font-bold text-xs text-slate-800 dark:text-slate-200 group-hover:text-amber-600 dark:group-hover:text-amber-400">
                                                        {{ $pCons->nombre }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        {{ $pCons->unidad_medida }} • {{ $pCons->marca?->nombre ?? 'Genérico' }}
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400">
                                                        Bs. {{ number_format($pCons->ultimo_precio_venta, 2) }}
                                                    </span>
                                                    <span class="block text-[10px] text-slate-400">precio venta</span>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="p-3 text-center text-xs text-slate-400">
                                                <i class="fas fa-search me-1"></i> No se encontraron insumos hospitalarios.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="consumo_cantidad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Cantidad
                                    </label>
                                    <input type="number" min="1" id="consumo_cantidad" wire:model.live="consumo_cantidad" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 p-2 shadow-2xs @error('consumo_cantidad') border-rose-500 @enderror">
                                    @error('consumo_cantidad') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="consumo_precio_unitario" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Precio (Bs.)
                                    </label>
                                    <input type="number" step="0.01" id="consumo_precio_unitario" wire:model="consumo_precio_unitario" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 p-2 shadow-2xs @error('consumo_precio_unitario') border-rose-500 @enderror">
                                    @error('consumo_precio_unitario') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-xs px-1 text-slate-500">
                                <span>Subtotal línea:</span>
                                <span class="font-mono font-bold text-amber-600 dark:text-amber-400">
                                    Bs. {{ number_format(((int) $consumo_cantidad) * ((float) $consumo_precio_unitario), 2) }}
                                </span>
                            </div>

                            <div>
                                <label for="consumo_observaciones" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Observaciones de Uso
                                </label>
                                <input type="text" id="consumo_observaciones" wire:model="consumo_observaciones" placeholder="Ej. Curación de herida, colocación de vía..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 p-2 shadow-2xs">
                            </div>

                            <div class="flex justify-end pt-1">
                                <button 
                                    type="button" 
                                    wire:click="agregarConsumoACola" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-300 text-xs font-bold border border-amber-200 dark:border-amber-800 transition-colors cursor-pointer"
                                >
                                    <i class="fas fa-plus"></i>
                                    <span>Añadir a la lista</span>
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Consumos en Cola -->
                        @if (! empty($cola_consumos))
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                                    Insumos listos para cargar ({{ count($cola_consumos) }}):
                                </span>
                                <div class="max-h-40 overflow-y-auto space-y-1.5 border border-slate-200 dark:border-slate-800 rounded-lg p-2 bg-slate-50/50 dark:bg-slate-850/50">
                                    @foreach ($cola_consumos as $idx => $cc)
                                        <div class="flex items-center justify-between p-2 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                            <div class="space-y-0.5">
                                                <div class="font-medium text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                    <span class="font-bold text-amber-600">{{ $cc['cantidad'] }}x</span>
                                                    <span>{{ $cc['nombre'] }}</span>
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-mono">
                                                    Bs. {{ number_format($cc['precio_unitario'] * $cc['cantidad'], 2) }}
                                                    @if (!empty($cc['observaciones']))
                                                        • <span class="text-slate-500">{{ $cc['observaciones'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button type="button" wire:click="eliminarConsumoDeCola({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalConsumo" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="registrarConsumoExtra"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-md shadow-amber-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="registrarConsumoExtra">
                                    <i class="fas fa-plus me-1"></i> Cargar {{ count($cola_consumos) > 1 ? count($cola_consumos).' Insumos' : 'Insumo' }} a Proforma
                                </span>
                                <span wire:loading wire:target="registrarConsumoExtra">
                                    <i class="fas fa-spinner fa-spin"></i> Procesando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL 7: DISPENSACIÓN Y DESPACHO DE FARMACIA DIRECTO EN PROFORMA -->
    <!-- ======================================================================= -->
    @if ($modalDespachoProformaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl max-h-[90vh] flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-dolly-flatbed text-emerald-600 dark:text-emerald-400"></i>
                                Dispensación y Despacho de Farmacia
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Paciente: <strong class="text-slate-700 dark:text-slate-200">{{ $proforma->paciente->nombre_completo }}</strong> • Proforma #{{ $proforma->id }}
                            </p>
                        </div>
                        <button wire:click="cerrarModalDespacho" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body con scroll -->
                    <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
                        <!-- SECCIÓN 1: FÁRMACOS DE LA RECETA ACTIVA -->
                        <div>
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-950/80 text-teal-700 dark:text-teal-300 font-bold flex items-center justify-center text-xs">1</span>
                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                                        Medicamentos Prescritos
                                        @if ($proforma->recetaActiva)
                                            <span class="text-teal-600 dark:text-teal-400 font-mono font-normal text-xs">(Receta #{{ $proforma->recetaActiva->id }})</span>
                                        @endif
                                    </h4>
                                </div>
                                @if ($proforma->recetaActiva)
                                    <span class="text-[11px] text-slate-400">
                                        Prescrita por: Dr(a). {{ $proforma->recetaActiva->doctor?->nombre_completo ?? 'Médico Tratante' }}
                                    </span>
                                @endif
                            </div>

                            @if (! $proforma->recetaActiva)
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-center">
                                    <i class="fas fa-info-circle text-base text-slate-400 mb-1 block"></i>
                                    Esta proforma no cuenta actualmente con una receta médica activa. Puede dispensar insumos y medicamentos extras en la siguiente sección.
                                </div>
                            @elseif (empty($despachosItems))
                                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 text-center font-medium">
                                    <i class="fas fa-check-circle text-base text-emerald-600 mb-1 block"></i>
                                    Todos los fármacos prescritos en esta receta ya han sido despachados en su totalidad.
                                </div>
                            @else
                                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                            <tr>
                                                <th class="py-2.5 px-3">Medicamento</th>
                                                <th class="py-2.5 px-3 text-center">Prescrito</th>
                                                <th class="py-2.5 px-3 text-center">Entregado</th>
                                                <th class="py-2.5 px-3 text-center">Saldo</th>
                                                <th class="py-2.5 px-3">Lote a Descargar</th>
                                                <th class="py-2.5 px-3 text-center w-28">A Despachar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                            @foreach ($despachosItems as $detId => $item)
                                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30">
                                                    <td class="py-2.5 px-3">
                                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item['producto_nombre'] }}</div>
                                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $item['indicaciones'] }}</div>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center font-mono font-medium">{{ $item['cantidad_prescrita'] }}</td>
                                                    <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $item['despachadas_previas'] }}</td>
                                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-teal-600 dark:text-teal-400">
                                                        {{ $item['saldo_pendiente'] }}
                                                    </td>
                                                    <td class="py-2.5 px-3">
                                                        @php
                                                            $lotesDisp = $lotesDisponiblesPorItem[$detId] ?? collect();
                                                        @endphp
                                                        @if ($lotesDisp->isEmpty())
                                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-500 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded border border-rose-200 dark:border-rose-900/50">
                                                                <i class="fas fa-times-circle"></i> Sin stock disponible
                                                            </span>
                                                        @else
                                                            <select 
                                                                wire:model.live="despachosItems.{{ $detId }}.lote_id"
                                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 focus:ring-2 focus:ring-teal-500"
                                                            >
                                                                @foreach ($lotesDisp as $lt)
                                                                    <option value="{{ $lt->id }}">
                                                                        Lote: {{ $lt->codigo_lote }} (Stock: {{ $lt->cantidad_actual }} {{ $item['unidad_medida'] }}) - Vto: {{ $lt->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @endif
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center">
                                                        <input 
                                                            type="number" 
                                                            min="0" 
                                                            max="{{ $item['saldo_pendiente'] }}"
                                                            wire:model.live="despachosItems.{{ $detId }}.cantidad_despachar"
                                                            class="w-20 text-xs font-mono font-bold text-center rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 focus:ring-2 focus:ring-teal-500"
                                                            {{ empty($lotesDisp) || $lotesDisp->isEmpty() || $item['saldo_pendiente'] <= 0 ? 'disabled' : '' }}
                                                        >
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- SECCIÓN 2: INSUMOS Y MEDICAMENTOS ADICIONALES / EXTRAS -->
                        <div>
                            <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-bold flex items-center justify-center text-xs">2</span>
                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                                        Insumos y Medicamentos Adicionales / Extras
                                    </h4>
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    Salida directa de piso o botiquín de farmacia
                                </span>
                            </div>

                            <!-- Selector reactivo de insumo extra -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                                @if ($despacho_extra_producto_id && $productoDespachoExtraSeleccionado)
                                    <!-- Insumo seleccionado -->
                                    <div class="p-2.5 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-800 dark:text-slate-100 text-xs">
                                                    {{ $productoDespachoExtraSeleccionado->nombre }}
                                                </span>
                                                <span class="text-[11px] text-amber-700 dark:text-amber-300 block">
                                                    {{ $productoDespachoExtraSeleccionado->unidad_medida }} • {{ $productoDespachoExtraSeleccionado->marca?->nombre ?? 'Genérico' }}
                                                </span>
                                            </div>
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click="limpiarDespachoExtraProducto"
                                            class="px-2 py-1 text-[11px] font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer shrink-0"
                                        >
                                            <i class="fas fa-times me-0.5"></i> Cambiar
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                        <!-- Selector de Lote del Extra -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                                Lote con Stock Disponible <span class="text-rose-500">*</span>
                                            </label>
                                            <select 
                                                wire:model.live="despacho_extra_lote_id" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-amber-500"
                                            >
                                                @forelse ($lotesParaDespachoExtra as $ltExtra)
                                                    <option value="{{ $ltExtra->id }}">
                                                        Lote: {{ $ltExtra->codigo_lote }} (Disp: {{ $ltExtra->cantidad_actual }}) - Vto: {{ $ltExtra->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }} - Bs. {{ number_format($ltExtra->precio_venta ?? $productoDespachoExtraSeleccionado->ultimo_precio_venta ?? 0, 2) }}
                                                    </option>
                                                @empty
                                                    <option value="">Sin lotes con stock en esta sucursal</option>
                                                @endforelse
                                            </select>
                                        </div>

                                        <!-- Cantidad Extra -->
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                                Cantidad <span class="text-rose-500">*</span>
                                            </label>
                                            <input 
                                                type="number" 
                                                min="1" 
                                                wire:model.live="despacho_extra_cantidad" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 text-center font-bold focus:ring-2 focus:ring-amber-500"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                            Observaciones de Uso
                                        </label>
                                        <input 
                                            type="text" 
                                            wire:model="despacho_extra_observaciones" 
                                            placeholder="Ej. Curación adicional de herida quirúrgica..." 
                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-amber-500"
                                        >
                                    </div>

                                    <div class="flex justify-end">
                                        <button 
                                            type="button" 
                                            wire:click="agregarDespachoExtraItem"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>Añadir a Lista de Despacho</span>
                                        </button>
                                    </div>
                                @else
                                    <!-- Buscador reactivo de insumos con stock -->
                                    <div class="space-y-1.5">
                                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                            Buscar Insumo o Medicamento Extra (con stock en sucursal)
                                        </label>
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.250ms="buscarDespachoExtraProducto" 
                                                placeholder="Escriba para buscar medicamento o insumo con existencias..." 
                                                class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 shadow-2xs"
                                            >
                                            @if ($buscarDespachoExtraProducto)
                                                <button type="button" wire:click="$set('buscarDespachoExtraProducto', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-md divide-y divide-slate-100 dark:divide-slate-700/60">
                                            @forelse ($productosParaDespachoExtras as $pExt)
                                                <button 
                                                    type="button" 
                                                    wire:click="seleccionarDespachoExtraProducto({{ $pExt->id }})" 
                                                    class="w-full text-left p-2.5 hover:bg-amber-50/70 dark:hover:bg-amber-950/40 transition-colors flex items-center justify-between group cursor-pointer"
                                                >
                                                    <div>
                                                        <span class="font-bold text-xs text-slate-800 dark:text-slate-200 group-hover:text-amber-600 dark:group-hover:text-amber-400 block">
                                                            {{ $pExt->nombre }}
                                                        </span>
                                                        <span class="text-[11px] text-slate-400">
                                                            {{ $pExt->unidad_medida }} • {{ $pExt->marca?->nombre ?? 'Genérico' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                            {{ $pExt->lotes->sum('cantidad_actual') }} en stock
                                                        </span>
                                                    </div>
                                                </button>
                                            @empty
                                                <div class="p-3 text-center text-xs text-slate-400">
                                                    No se encontraron productos con existencias disponibles en esta sucursal.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Lista de Extras en Cola para Despacho -->
                            @if (! empty($despachosExtrasItems))
                                <div class="mt-3 space-y-1.5">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">
                                        Insumos extras a incluir en este despacho ({{ count($despachosExtrasItems) }}):
                                    </span>
                                    <div class="max-h-36 overflow-y-auto space-y-1 rounded-xl border border-slate-200 dark:border-slate-800 p-2 bg-slate-50/50 dark:bg-slate-850/50">
                                        @foreach ($despachosExtrasItems as $idx => $ex)
                                            <div class="flex items-center justify-between p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                                <div>
                                                    <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                        <span class="text-amber-600 font-mono">{{ $ex['cantidad'] }}x</span>
                                                        <span>{{ $ex['producto_nombre'] }}</span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">
                                                        Lote: {{ $ex['lote_codigo'] }} • Vto: {{ $ex['lote_vencimiento'] }}
                                                        @if ($ex['observaciones'])
                                                            • <em>{{ $ex['observaciones'] }}</em>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="font-mono font-bold text-amber-600">Bs. {{ number_format($ex['subtotal'], 2) }}</span>
                                                    <button type="button" wire:click="eliminarDespachoExtraItem({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                        <button 
                            type="button" 
                            wire:click="cerrarModalDespacho" 
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                        >
                            Cancelar
                        </button>

                        <button 
                            type="button" 
                            wire:click="procesarDespacho" 
                            wire:loading.attr="disabled"
                            wire:target="procesarDespacho"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="procesarDespacho">
                                <i class="fas fa-check-circle me-1"></i> Confirmar y Despachar
                            </span>
                            <span wire:loading wire:target="procesarDespacho">
                                <i class="fas fa-spinner fa-spin me-1"></i> Descontando Kardex...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
