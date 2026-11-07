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

        <div class="flex items-center flex-wrap gap-2">
            <button 
                wire:click="abrirModalHistoriaClinica" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-teal-50 dark:bg-teal-950/40 border border-teal-300 dark:border-teal-700 text-teal-800 dark:text-teal-200 text-xs font-semibold hover:bg-teal-100 dark:hover:bg-teal-900/60 transition-colors cursor-pointer"
                title="Ver y editar la Historia Clínica del paciente"
            >
                <i class="fas fa-file-medical-alt text-teal-600 dark:text-teal-400"></i>
                <span>Historia Clínica</span>
            </button>

            @permiso('proformas.imprimir')
                <a 
                    href="{{ route('proformas.pdf.historia_clinica', $proforma->id) }}" 
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors"
                    title="Imprimir documento oficial de Historia Clínica en PDF"
                >
                    <i class="fas fa-print text-xs"></i>
                    <span>Imprimir H.C.</span>
                </a>
            @endpermiso

            <button 
                wire:click="abrirModalEpicrisis" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 text-xs font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-colors cursor-pointer"
                title="Ver y editar la Epicrisis médica de alta"
            >
                <i class="fas fa-file-signature text-emerald-600 dark:text-emerald-400"></i>
                <span>Epicrisis (Alta)</span>
            </button>

            @permiso('proformas.imprimir')
                <a 
                    href="{{ route('proformas.pdf.epicrisis', $proforma->id) }}" 
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors"
                    title="Imprimir documento oficial de Epicrisis en PDF"
                >
                    <i class="fas fa-print text-xs"></i>
                    <span>Imprimir Epicrisis</span>
                </a>
            @endpermiso

            @if ($proforma->estado !== 'Pagada')
                @permiso('proformas.editar')
                    <button 
                        wire:click="abrirModalEditarCabecera" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    >
                        <i class="fas fa-edit text-xs text-blue-500"></i>
                        <span>Editar Cabecera</span>
                    </button>
                @endpermiso
            @endif

            <a 
                href="{{ route('proformas.index') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                <i class="fas fa-arrow-left text-xs"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>

    @if ($proforma->estado === 'Pagada')
        <!-- AVISO DE PROFORMA CERRADA / ESTADO PAGADA -->
        <div class="mb-5 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shrink-0">
                    <i class="fas fa-lock text-base"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200 flex items-center gap-2">
                        <span>Expediente Cerrado (Proforma Pagada)</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">Solo Lectura</span>
                    </h3>
                    <p class="text-xs text-emerald-800/80 dark:text-emerald-300/80 mt-0.5">
                        Esta proforma se encuentra saldada y cerrada. Los servicios, exámenes, cronograma, recetas e insumos no admiten nuevas adiciones, ediciones ni eliminaciones. Únicamente está habilitada la gestión de liquidación de honorarios a médicos tratantes.
                    </p>
                </div>
            </div>
            <button 
                wire:click="cambiarTab('pagos_medicos')" 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-sm transition-colors shrink-0 cursor-pointer self-start md:self-auto"
            >
                <i class="fas fa-hand-holding-medical"></i>
                <span>Gestionar Honorarios Médicos</span>
            </button>
        </div>
    @endif

    <!-- FICHA CLÍNICA DEL PACIENTE Y CABECERA PROFORMA -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs overflow-hidden mb-6">
        <!-- Patient Identity Banner -->
        <div class="p-5 sm:p-6 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-800">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <!-- Patient Avatar and Bio -->
                <div class="flex items-start sm:items-center gap-4">
                    <div class="h-14 w-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl font-bold shadow-md shadow-blue-500/25 shrink-0">
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
                            @if ($proforma->paciente?->institucion)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40" title="Institución / Convenio Asegurador">
                                    <i class="fas fa-building text-[10px]"></i> {{ $proforma->paciente->institucion->nombre }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    Particular
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $proforma->estado === 'En Curso' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : ($proforma->estado === 'Pagada' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $proforma->estado === 'En Curso' ? 'bg-amber-500 animate-pulse' : ($proforma->estado === 'Pagada' ? 'bg-emerald-500' : 'bg-rose-500') }}"></span>
                                {{ $proforma->estado }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400 mt-1">
                            <span><strong class="text-slate-700 dark:text-slate-300">CI:</strong> {{ $proforma->paciente->cedula ?: 'Sin documento' }}</span>
                            <span>•</span>
                            <span><strong class="text-slate-700 dark:text-slate-300">Edad:</strong> {{ $proforma->edad_paciente !== null ? "{$proforma->edad_paciente} años" : 'N/E' }}</span>
                            <span>•</span>
                            <span><strong class="text-slate-700 dark:text-slate-300">Teléfono:</strong> {{ $proforma->paciente->celular ?: 'Sin celular' }}</span>
                            @if ($proforma->paciente->estado_civil)
                                <span>•</span>
                                <span><strong class="text-slate-700 dark:text-slate-300">Estado Civil:</strong> {{ $proforma->paciente->estado_civil }}</span>
                            @endif
                            @if ($proforma->paciente->ocupacion)
                                <span>•</span>
                                <span><strong class="text-slate-700 dark:text-slate-300">Ocupación:</strong> {{ $proforma->paciente->ocupacion }}</span>
                            @endif
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
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Proforma #</span>
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                            {{ $proforma->id }}
                        </span>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

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

            <!-- Diagnóstico / Motivo de Consulta y Resumen de Historia Clínica -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Motivo de Consulta</span>
                        @if ($proforma->historiaClinica)
                            <span class="text-[10px] text-teal-600 dark:text-teal-400 font-semibold">
                                <i class="fas fa-check-circle me-0.5"></i> H.C. Registrada
                            </span>
                        @endif
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 font-medium">{{ $proforma->motivo_consulta ?: 'Sin motivo registrado' }}</p>
                    @if ($proforma->historiaClinica?->enfermedad_actual)
                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-slate-600 dark:text-slate-300">Enfermedad Actual:</span> {{ \Illuminate\Support\Str::limit($proforma->historiaClinica->enfermedad_actual, 140) }}
                        </div>
                    @endif
                </div>
                <div class="p-3 rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400">Diagnóstico / Impresión Clínica</span>
                        <button wire:click="abrirModalHistoriaClinica" type="button" class="text-[10px] text-teal-600 hover:text-teal-800 dark:text-teal-400 font-bold flex items-center gap-1 cursor-pointer">
                            <i class="fas fa-edit"></i> Ver / Editar H.C.
                        </button>
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 font-medium">{{ $proforma->diagnostico ?: 'Sin diagnóstico preliminar' }}</p>
                    @if ($proforma->historiaClinica && ($proforma->historiaClinica->pa || $proforma->historiaClinica->fc || $proforma->historiaClinica->temperatura))
                        <div class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-center gap-2 text-[10px]">
                            @if ($proforma->historiaClinica->pa)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 font-mono"><strong>PA:</strong> {{ $proforma->historiaClinica->pa }}</span>
                            @endif
                            @if ($proforma->historiaClinica->fc)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 font-mono"><strong>FC:</strong> {{ $proforma->historiaClinica->fc }} lpm</span>
                            @endif
                            @if ($proforma->historiaClinica->temperatura)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 font-mono"><strong>T:</strong> {{ $proforma->historiaClinica->temperatura }} °C</span>
                            @endif
                            @if ($proforma->historiaClinica->sato2)
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 font-mono"><strong>Sat:</strong> {{ $proforma->historiaClinica->sato2 }}%</span>
                            @endif
                            @if ($proforma->historiaClinica->imc)
                                <span class="px-1.5 py-0.5 rounded bg-teal-100 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 font-mono font-bold"><strong>IMC:</strong> {{ $proforma->historiaClinica->imc }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resumen de Epicrisis Registrada -->
            @if ($proforma->epicrisis)
                <div class="mt-3 p-3.5 rounded-xl bg-cyan-50/70 dark:bg-cyan-950/20 border border-cyan-200/80 dark:border-cyan-800/50 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex items-start gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-600 text-white shadow-2xs mt-0.5">
                            <i class="fas fa-file-medical text-xs"></i>
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-cyan-900 dark:text-cyan-200 uppercase tracking-wider text-[11px]">Epicrisis Clínica de Egreso</span>
                                @if ($proforma->epicrisis->causa_egreso)
                                    <span class="px-2 py-0.5 rounded-full bg-cyan-100 dark:bg-cyan-900/60 text-cyan-800 dark:text-cyan-300 font-semibold text-[10px]">
                                        {{ $proforma->epicrisis->causa_egreso }}
                                    </span>
                                @endif
                                @if ($proforma->epicrisis->fecha_egreso)
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                        Egreso: {{ $proforma->epicrisis->fecha_egreso->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-slate-600 dark:text-slate-300 mt-0.5 text-xs">
                                <strong>Diagnóstico de Egreso:</strong> {{ $proforma->epicrisis->diagnostico_egreso ?: 'Sin diagnóstico de egreso especificado' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-end md:self-center">
                        <button wire:click="abrirModalEpicrisis" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-cyan-300 dark:border-cyan-700 text-cyan-700 dark:text-cyan-300 font-semibold hover:bg-cyan-50 dark:hover:bg-slate-750 transition-colors shadow-2xs text-xs">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <a href="{{ route('proformas.pdf.epicrisis', $proforma->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-semibold transition-colors shadow-2xs text-xs">
                            <i class="fas fa-print"></i> PDF
                        </a>
                    </div>
                </div>
            @endif
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

                    <div class="flex flex-wrap items-center gap-2">
                        @if($proforma->estado === 'Pagada' || $proforma->saldoPendiente() <= 0)
                            @permiso('proformas.imprimir')
                                <a 
                                    href="{{ route('proformas.pdf.recibo', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 hover:shadow-lg transition-all transform active:scale-95"
                                    title="Imprimir Recibo Oficial de Caja"
                                >
                                    <i class="fas fa-receipt text-xs"></i>
                                    <span>Recibo Caja</span>
                                </a>
                                <a 
                                    href="{{ route('proformas.pdf.resumen', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 hover:shadow-lg transition-all transform active:scale-95"
                                    title="Imprimir Resumen Clínico PDF (Costos Finales)"
                                >
                                    <i class="fas fa-file-invoice text-xs"></i>
                                    <span>Resumen PDF</span>
                                </a>
                                <a 
                                    href="{{ route('proformas.pdf.detalle', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 hover:shadow-lg transition-all transform active:scale-95"
                                    title="Imprimir Detalle Clínico Completo PDF"
                                >
                                    <i class="fas fa-file-pdf text-xs"></i>
                                    <span>Detalle PDF</span>
                                </a>
                            @endpermiso
                        @else
                            @permiso('cobro-caja')
                                <a 
                                    href="{{ route('caja.index', ['search' => $proforma->id]) }}" 
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-lg shadow-emerald-500/20 hover:shadow-xl transition-all transform active:scale-95 cursor-pointer"
                                >
                                    <i class="fas fa-cash-register text-sm"></i>
                                    <span>Liquidar en Caja</span>
                                </a>
                            @endpermiso
                            @permiso('proformas.imprimir')
                                <a 
                                    href="{{ route('proformas.pdf.resumen', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 hover:shadow-lg transition-all transform active:scale-95"
                                    title="Imprimir Resumen Clínico PDF (Costos Finales)"
                                >
                                    <i class="fas fa-file-invoice text-xs"></i>
                                    <span>Resumen PDF</span>
                                </a>
                                <a 
                                    href="{{ route('proformas.pdf.detalle', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition"
                                    title="Imprimir Detalle Clínico Completo PDF"
                                >
                                    <i class="fas fa-file-pdf text-xs text-rose-400"></i>
                                    <span>Detalle PDF</span>
                                </a>
                            @endpermiso
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS MODULARES DE GESTIÓN CLÍNICA -->
    <div class="space-y-6">
        <!-- Tab Navigation Bar -->
        <div class="flex items-center space-x-1 border-b border-slate-200 dark:border-slate-800 overflow-x-auto pb-px">
            <!-- 1. Servicios -->
            @permiso('proformas.servicios.ver')
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
            @endpermiso

            <!-- 2. Solicitudes / Exámenes -->
            @permiso('proformas.solicitudes.ver')
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
            @endpermiso

            <!-- 2.A. Hoja de Evolución -->
            @permiso('proformas.evolucion.ver')
                <button 
                    wire:click="cambiarTab('evoluciones')" 
                    type="button" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'evoluciones' ? 'border-sky-600 text-sky-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                >
                    <i class="fas fa-notes-medical text-xs"></i>
                    <span>Hoja de Evolución</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $proforma->evoluciones->count() }}
                    </span>
                </button>
            @endpermiso

            <!-- 2.B. Hoja de Indicaciones -->
            @permiso('proformas.indicacion.ver')
                <button 
                    wire:click="cambiarTab('indicaciones')" 
                    type="button" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'indicaciones' ? 'border-cyan-600 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                >
                    <i class="fas fa-clipboard-list text-xs"></i>
                    <span>Hoja de Indicaciones</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $proforma->indicaciones->count() }}
                    </span>
                </button>
            @endpermiso

            <!-- 2.C. Hoja de Internación -->
            @permiso('proformas.internacion.ver')
                <button 
                    wire:click="cambiarTab('internaciones')" 
                    type="button" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'internaciones' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                >
                    <i class="fas fa-bed text-xs"></i>
                    <span>Hoja de Internación</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $proforma->internaciones->count() }}
                    </span>
                </button>
            @endpermiso

            <!-- 3. Calendario -->
            @permiso('proformas.cronograma.ver')
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
            @endpermiso

            <!-- 3.A. Cirugías / Descargo de Quirófano -->
            @permiso('proformas.cirugia.ver')
                <button 
                    wire:click="cambiarTab('cirugias')" 
                    type="button" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'cirugias' ? 'border-rose-600 text-rose-600 dark:text-rose-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                >
                    <i class="fas fa-procedures text-xs"></i>
                    <span>Descargo de Quirófano</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $proforma->cirugias->count() }}
                    </span>
                </button>
            @endpermiso

            <!-- 4. Recetas Médicas -->
            @permiso('proformas.recetas.ver')
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
            @endpermiso

            <!-- 5. Consumos Extras -->
            @permiso('proformas.consumos.ver')
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
            @endpermiso

            <!-- 6. Despachos de Farmacia -->
            @permiso('despachar-farmacia')
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
            @endpermiso

            <!-- 7. Honorarios Médicos -->
            @permiso('proformas.honorarios.ver')
                <button 
                    wire:click="cambiarTab('pagos_medicos')" 
                    type="button" 
                    class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'pagos_medicos' ? 'border-violet-600 text-violet-600 dark:text-violet-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
                >
                    <i class="fas fa-hand-holding-medical text-xs"></i>
                    <span>Honorarios Médicos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono">
                        {{ $proforma->pagosMedicos->count() }}
                    </span>
                </button>
            @endpermiso

            <!-- 8. Impresiones Clínicas y Formularios (Libre acceso sin restricciones de permisos) -->
            <button 
                wire:click="cambiarTab('impresiones')" 
                type="button" 
                class="px-4 py-2.5 text-xs sm:text-sm font-bold border-b-2 transition-colors flex items-center gap-2 shrink-0 cursor-pointer {{ $tab === 'impresiones' ? 'border-sky-600 text-sky-600 dark:text-sky-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}"
            >
                <i class="fas fa-print text-xs"></i>
                <span>Impresiones</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300 font-bold">
                    PDF
                </span>
            </button>
        </div>

        <!-- CONTENIDO DEL TAB SELECCIONADO -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 transition-colors">
            <!-- =================================================================== -->
            <!-- TAB 1: SERVICIOS Y PROCEDIMIENTOS -->
            <!-- =================================================================== -->
            @if ($tab === 'servicios')
                @permiso('proformas.servicios.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-briefcase-medical text-indigo-600 dark:text-indigo-400"></i>
                                    Catálogo de Servicios y Procedimientos Aplicados
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Asigne consultas, procedimientos, quirófano o curaciones al paciente.</p>
                            </div>
                            @if ($proforma->estado !== 'Pagada')
                                @permiso('proformas.servicios.agregar')
                                    <button 
                                        wire:click="abrirModalServicio" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Agregar Procedimiento / Servicio</span>
                                    </button>
                                @endpermiso
                            @endif
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
                                        @if ($proforma->estado !== 'Pagada')
                                            @permiso('proformas.servicios.eliminar')
                                                <th class="py-3 px-4 text-center w-20">Acción</th>
                                            @endpermiso
                                        @endif
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
                                            @if ($proforma->estado !== 'Pagada')
                                                @permiso('proformas.servicios.eliminar')
                                                    <td class="py-3 px-4 text-center">
                                                        <button 
                                                            type="button" 
                                                            @click="$dispatch('swal:confirm', {
                                                                title: '¿Remover \'{{ addslashes($ps->servicio?->nombre ?? 'Servicio') }}\'?',
                                                                text: 'Se descontará del costo total de la proforma.',
                                                                icon: 'warning',
                                                                confirmButtonText: 'Sí, remover',
                                                                cancelButtonText: 'Cancelar',
                                                                componentId: '{{ $this->getId() }}',
                                                                method: 'eliminarServicioProforma',
                                                                params: [{{ $ps->id }}],
                                                                event: 'eliminarServicioProforma'
                                                            })"
                                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                            title="Eliminar Servicio de la Proforma"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </td>
                                                @endpermiso
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 7 : 6 }}" class="py-10 px-4 text-center text-slate-400">
                                                <i class="fas fa-briefcase-medical text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-300">No hay servicios clínicos agregados a esta proforma.</p>
                                                @if ($proforma->estado !== 'Pagada')
                                                    @permiso('proformas.servicios.agregar')
                                                        <button 
                                                            wire:click="abrirModalServicio" 
                                                            type="button" 
                                                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 cursor-pointer"
                                                        >
                                                            <i class="fas fa-plus"></i> Agregar Primer Servicio
                                                        </button>
                                                    @endpermiso
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar los servicios y procedimientos clínicos.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 2: SOLICITUDES Y EXÁMENES (FORMATO TABLA) -->
            <!-- =================================================================== -->
            @if ($tab === 'solicitudes')
                @permiso('proformas.solicitudes.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-flask text-blue-600 dark:text-blue-400"></i>
                                    Solicitudes de Laboratorio, Imagenología y Exámenes
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Órdenes de laboratorio, ecografías, rayos X y recepción de archivos digitales adjuntos.</p>
                            </div>
                            @if ($proforma->estado !== 'Pagada')
                                @permiso('proformas.solicitudes.agregar')
                                    <button 
                                        wire:click="abrirModalSolicitud" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Nueva Solicitud / Examen</span>
                                    </button>
                                @endpermiso
                            @endif
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
                                        @if ($proforma->estado !== 'Pagada')
                                            @permiso('proformas.solicitudes.eliminar')
                                                <th class="py-3 px-4 text-center">Acciones</th>
                                            @endpermiso
                                        @endif
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
                                            @if ($proforma->estado !== 'Pagada')
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <div class="inline-flex items-center gap-1.5">
                                                        @permiso('proformas.solicitudes.agregar')
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
                                                        @endpermiso

                                                        @permiso('proformas.solicitudes.eliminar')
                                                            <button 
                                                                type="button" 
                                                                @click="$dispatch('swal:confirm', {
                                                                    title: '¿Remover orden de \'{{ addslashes($sol->tipoSolicitud->nombre ?? 'Examen') }}\'?',
                                                                    text: 'Se eliminará la solicitud médica y cualquier archivo adjunto.',
                                                                    icon: 'warning',
                                                                    confirmButtonText: 'Sí, remover',
                                                                    cancelButtonText: 'Cancelar',
                                                                    componentId: '{{ $this->getId() }}',
                                                                    method: 'eliminarSolicitudProforma',
                                                                    params: [{{ $sol->id }}],
                                                                    event: 'eliminarSolicitudProforma'
                                                                })"
                                                                class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                                title="Eliminar Solicitud"
                                                            >
                                                                <i class="fas fa-trash-alt text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 6 : 5 }}" class="py-10 px-4 text-center text-slate-400">
                                                <i class="fas fa-flask text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-300">No se han emitido órdenes de laboratorio o estudios complementarios.</p>
                                                @if ($proforma->estado !== 'Pagada')
                                                    @permiso('proformas.solicitudes.agregar')
                                                        <button 
                                                            wire:click="abrirModalSolicitud" 
                                                            type="button" 
                                                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 cursor-pointer"
                                                        >
                                                            <i class="fas fa-plus"></i> Solicitar Primer Examen
                                                        </button>
                                                    @endpermiso
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar solicitudes y exámenes clínicos.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB: HOJA DE EVOLUCIÓN -->
            <!-- =================================================================== -->
            @if ($tab === 'evoluciones')
                @permiso('proformas.evolucion.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-notes-medical text-sky-600 dark:text-sky-400"></i>
                                    Hoja de Evolución Clínica
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Registro cronológico del estado de salud, evolución y observaciones clínicas del paciente.</p>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-center">
                                <a 
                                    href="{{ route('proformas.pdf.evolucion', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                                >
                                    <i class="fas fa-file-pdf text-red-400"></i>
                                    <span>Imprimir Hoja PDF</span>
                                </a>
                                @if ($proforma->estado !== 'Pagada')
                                    @permiso('proformas.evolucion.crear')
                                        <button 
                                            wire:click="abrirModalEvolucion" 
                                            wire:loading.attr="disabled"
                                            type="button" 
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-md shadow-sky-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>Nueva Nota de Evolución</span>
                                        </button>
                                    @endpermiso
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4 w-44">Fecha y Hora</th>
                                        <th class="py-3 px-4">Evolución / Descripción Clínica</th>
                                        <th class="py-3 px-4 w-48">Registrado Por</th>
                                        @if ($proforma->estado !== 'Pagada')
                                            <th class="py-3 px-4 text-center w-28">Acciones</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse ($evoluciones as $index => $item)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 whitespace-nowrap">
                                                <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                    <i class="far fa-calendar-alt text-sky-500 text-[11px]"></i>
                                                    {{ $item->fecha_hora ? $item->fecha_hora->format('d/m/Y') : '-' }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                    <i class="far fa-clock text-[10px]"></i>
                                                    {{ $item->fecha_hora ? $item->fecha_hora->format('H:i') : '-' }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed font-sans prose prose-sm dark:prose-invert max-w-none clinical-notes-content">
                                                    {!! $item->descripcion !!}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    <i class="fas fa-user-md text-sky-600 text-[10px]"></i>
                                                    {{ $item->usuarioCreador->name ?? 'Usuario del Sistema' }}
                                                </span>
                                            </td>
                                            @if ($proforma->estado !== 'Pagada')
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-2">
                                                        @permiso('proformas.evolucion.editar')
                                                            <button 
                                                                wire:click="abrirModalEvolucion({{ $item->id }})" 
                                                                type="button" 
                                                                class="p-1.5 text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded transition-colors cursor-pointer"
                                                                title="Editar Evolución"
                                                            >
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                        @permiso('proformas.evolucion.eliminar')
                                                            <button 
                                                                wire:click="eliminarEvolucion({{ $item->id }})" 
                                                                wire:confirm="¿Está seguro de eliminar esta nota de evolución?"
                                                                type="button" 
                                                                class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded transition-colors cursor-pointer"
                                                                title="Eliminar Evolución"
                                                            >
                                                                <i class="fas fa-trash-alt text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 5 : 4 }}" class="py-12 text-center text-slate-400">
                                                <i class="fas fa-notes-medical text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-400">No hay notas de evolución registradas</p>
                                                <p class="text-xs text-slate-400 mt-0.5">Presione "Nueva Nota de Evolución" para redactar el primer informe de seguimiento.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar la hoja de evolución clínica.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB: HOJA DE INDICACIONES -->
            <!-- =================================================================== -->
            @if ($tab === 'indicaciones')
                @permiso('proformas.indicacion.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-clipboard-list text-cyan-600 dark:text-cyan-400"></i>
                                    Hoja de Indicaciones Médicas
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Instrucciones médicas, esquema terapéutico, administración de fármacos y cuidados de enfermería.</p>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-center">
                                <a 
                                    href="{{ route('proformas.pdf.indicacion', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                                >
                                    <i class="fas fa-file-pdf text-red-400"></i>
                                    <span>Imprimir Hoja PDF</span>
                                </a>
                                @if ($proforma->estado !== 'Pagada')
                                    @permiso('proformas.indicacion.crear')
                                        <button 
                                            wire:click="abrirModalIndicacion" 
                                            wire:loading.attr="disabled"
                                            type="button" 
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-md shadow-cyan-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>Nueva Indicación Médica</span>
                                        </button>
                                    @endpermiso
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4 w-44">Fecha y Hora</th>
                                        <th class="py-3 px-4">Indicaciones Médicas</th>
                                        <th class="py-3 px-4 w-48">Registrado Por</th>
                                        @if ($proforma->estado !== 'Pagada')
                                            <th class="py-3 px-4 text-center w-28">Acciones</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse ($indicaciones as $index => $item)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 whitespace-nowrap">
                                                <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                    <i class="far fa-calendar-alt text-cyan-500 text-[11px]"></i>
                                                    {{ $item->fecha_hora ? $item->fecha_hora->format('d/m/Y') : '-' }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                    <i class="far fa-clock text-[10px]"></i>
                                                    {{ $item->fecha_hora ? $item->fecha_hora->format('H:i') : '-' }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed font-sans prose prose-sm dark:prose-invert max-w-none clinical-notes-content">
                                                    {!! $item->descripcion !!}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    <i class="fas fa-user-md text-cyan-600 text-[10px]"></i>
                                                    {{ $item->usuarioCreador->name ?? 'Médico / Personal' }}
                                                </span>
                                            </td>
                                            @if ($proforma->estado !== 'Pagada')
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-2">
                                                        @permiso('proformas.indicacion.editar')
                                                            <button 
                                                                wire:click="abrirModalIndicacion({{ $item->id }})" 
                                                                type="button" 
                                                                class="p-1.5 text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-950/40 rounded transition-colors cursor-pointer"
                                                                title="Editar Indicación"
                                                            >
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                        @permiso('proformas.indicacion.eliminar')
                                                            <button 
                                                                wire:click="eliminarIndicacion({{ $item->id }})" 
                                                                wire:confirm="¿Está seguro de eliminar esta indicación médica?"
                                                                type="button" 
                                                                class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded transition-colors cursor-pointer"
                                                                title="Eliminar Indicación"
                                                            >
                                                                <i class="fas fa-trash-alt text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 5 : 4 }}" class="py-12 text-center text-slate-400">
                                                <i class="fas fa-clipboard-list text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-400">No hay indicaciones médicas registradas</p>
                                                <p class="text-xs text-slate-400 mt-0.5">Presione "Nueva Indicación Médica" para ingresar el plan terapéutico.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar la hoja de indicaciones médicas.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB: HOJA DE INTERNACIÓN -->
            <!-- =================================================================== -->
            @if ($tab === 'internaciones')
                @permiso('proformas.internacion.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-bed text-indigo-600 dark:text-indigo-400"></i>
                                    Hoja de Internación
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Registro de hospitalización, ingresos a sala, altas transitorias y notas de internación.</p>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-center">
                                <a 
                                    href="{{ route('proformas.pdf.internacion', $proforma->id) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                                >
                                    <i class="fas fa-file-pdf text-red-400"></i>
                                    <span>Imprimir Hoja PDF</span>
                                </a>
                                @if ($proforma->estado !== 'Pagada')
                                    @permiso('proformas.internacion.crear')
                                        <button 
                                            wire:click="abrirModalInternacion" 
                                            wire:loading.attr="disabled"
                                            type="button" 
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>Nuevo Registro de Internación</span>
                                        </button>
                                    @endpermiso
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4 w-44">Fecha y Hora</th>
                                        <th class="py-3 px-4">Detalle / Nota de Internación</th>
                                        <th class="py-3 px-4 w-48">Registrado Por</th>
                                        @if ($proforma->estado !== 'Pagada')
                                            <th class="py-3 px-4 text-center w-28">Acciones</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse ($internaciones as $index => $item)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                            <td class="py-3 px-4 whitespace-nowrap">
                                                <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                    <i class="far fa-calendar-alt text-indigo-500 text-[11px]"></i>
                                                    {{ $item->fecha_hora ? $item->fecha_hora->format('d/m/Y') : '-' }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                    <i class="far fa-clock text-[10px]"></i>
                                                    {{ $item->fecha_hora ? $item->fecha_hora->format('H:i') : '-' }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="text-xs text-slate-800 dark:text-slate-200 leading-relaxed font-sans prose prose-sm dark:prose-invert max-w-none clinical-notes-content">
                                                    {!! $item->descripcion !!}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    <i class="fas fa-user-md text-indigo-600 text-[10px]"></i>
                                                    {{ $item->usuarioCreador->name ?? 'Personal Clínico' }}
                                                </span>
                                            </td>
                                            @if ($proforma->estado !== 'Pagada')
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-2">
                                                        @permiso('proformas.internacion.editar')
                                                            <button 
                                                                wire:click="abrirModalInternacion({{ $item->id }})" 
                                                                type="button" 
                                                                class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 rounded transition-colors cursor-pointer"
                                                                title="Editar Internación"
                                                            >
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                        @permiso('proformas.internacion.eliminar')
                                                            <button 
                                                                wire:click="eliminarInternacion({{ $item->id }})" 
                                                                wire:confirm="¿Está seguro de eliminar este registro de internación?"
                                                                type="button" 
                                                                class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded transition-colors cursor-pointer"
                                                                title="Eliminar Internación"
                                                            >
                                                                <i class="fas fa-trash-alt text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 5 : 4 }}" class="py-12 text-center text-slate-400">
                                                <i class="fas fa-bed text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-400">No hay registros de internación</p>
                                                <p class="text-xs text-slate-400 mt-0.5">Presione "Nuevo Registro de Internación" para documentar la estancia hospitalaria.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar la hoja de internación hospitalaria.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 6: CALENDARIO Y AGENDA (FORMATO TABLA) -->
            <!-- =================================================================== -->
            @if ($tab === 'calendario')
                @permiso('proformas.cronograma.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400"></i>
                                    Cronograma de Actividades, Controles y Procedimientos
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Agenda cronológica de rondas de enfermería, cambio de sueros, curaciones o cirugías.</p>
                            </div>
                            @if ($proforma->estado !== 'Pagada')
                                @permiso('proformas.cronograma.agregar')
                                    <button 
                                        wire:click="abrirModalCalendario" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-md shadow-purple-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Programar Actividad</span>
                                    </button>
                                @endpermiso
                            @endif
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
                                        @if ($proforma->estado !== 'Pagada')
                                            @permiso('proformas.cronograma.gestionar')
                                                <th class="py-3 px-4 text-center">Acciones</th>
                                            @endpermiso
                                        @endif
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
                                                @if ($proforma->estado !== 'Pagada')
                                                    @permiso('proformas.cronograma.gestionar')
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
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $cal->estado === 'Realizado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-300 dark:border-purple-800' }}">
                                                            <i class="fas {{ $cal->estado === 'Realizado' ? 'fa-circle-check text-emerald-600' : 'fa-clock text-purple-600' }}"></i>
                                                            <span>{{ $cal->estado }}</span>
                                                        </span>
                                                    @endpermiso
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $cal->estado === 'Realizado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-300 dark:border-purple-800' }}">
                                                        <i class="fas {{ $cal->estado === 'Realizado' ? 'fa-circle-check text-emerald-600' : 'fa-clock text-purple-600' }}"></i>
                                                        <span>{{ $cal->estado }}</span>
                                                    </span>
                                                @endif
                                            </td>
                                            @if ($proforma->estado !== 'Pagada')
                                                @permiso('proformas.cronograma.gestionar')
                                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                                        <button 
                                                            type="button" 
                                                            @click="$dispatch('swal:confirm', {
                                                                title: '¿Remover actividad del calendario?',
                                                                text: 'Se eliminará \'{{ addslashes($cal->descripcion) }}\' de la agenda del paciente.',
                                                                icon: 'warning',
                                                                confirmButtonText: 'Sí, remover',
                                                                cancelButtonText: 'Cancelar',
                                                                componentId: '{{ $this->getId() }}',
                                                                method: 'eliminarEventoCalendario',
                                                                params: [{{ $cal->id }}],
                                                                event: 'eliminarEventoCalendario'
                                                            })"
                                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                            title="Eliminar Actividad"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </td>
                                                @endpermiso
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 6 : 5 }}" class="py-10 px-4 text-center text-slate-400">
                                                <i class="fas fa-calendar-times text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-300">No hay citas, rondas o curaciones programadas en la agenda.</p>
                                                @if ($proforma->estado !== 'Pagada')
                                                    @permiso('proformas.cronograma.agregar')
                                                        <button 
                                                            wire:click="abrirModalCalendario" 
                                                            type="button" 
                                                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-600 text-white text-xs font-semibold hover:bg-purple-700 cursor-pointer"
                                                        >
                                                            <i class="fas fa-plus"></i> Programar Primera Actividad
                                                        </button>
                                                    @endpermiso
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar el cronograma y agenda médica.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 3.A: DESCARGO DE QUIRÓFANO Y CIRUGÍAS -->
            <!-- =================================================================== -->
            @if ($tab === 'cirugias')
                @permiso('proformas.cirugia.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                        <i class="fas fa-procedures text-xs"></i>
                                    </span>
                                    <span>Descargos de Quirófano e Intervenciones Quirúrgicas</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Registro de cirugías, personal interviniente, insumos/medicamentos utilizados (Anestesiología y Enfermería) y equipos especiales.
                                </p>
                            </div>

                            @if ($proforma->estado !== 'Pagada')
                                @permiso('proformas.cirugia.crear')
                                    <button 
                                        wire:click="abrirModalNuevaCirugia" 
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 hover:shadow-lg transition-all transform active:scale-95 cursor-pointer shrink-0"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Registrar Cirugía / Descargo</span>
                                    </button>
                                @endpermiso
                            @endif
                        </div>

                        <!-- LISTA DE CIRUGÍAS -->
                        <div class="space-y-4">
                            @forelse ($cirugias as $cir)
                                <div class="p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-rose-300 dark:hover:border-rose-900/60 transition-all">
                                    <!-- Cabecera de la cirugía -->
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex flex-wrap items-center gap-2.5">
                                            <span class="px-2.5 py-1 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 font-mono font-bold text-xs">
                                                N° {{ $cir->numero ?: str_pad($cir->id, 4, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $cir->es_convenio ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/50 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300' }}">
                                                {{ $cir->es_convenio ? 'Convenio: '.($cir->convenio_nombre ?: 'Aseguradora') : 'Institucional' }}
                                            </span>
                                            @if ($cir->tipo_cirugia)
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                    Cirugía {{ $cir->tipo_cirugia }}
                                                </span>
                                            @endif
                                            <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                                <i class="far fa-calendar-alt text-rose-500"></i>
                                                {{ $cir->fecha?->format('d/m/Y') ?: 'Sin fecha' }}
                                            </span>
                                        </div>

                                        <!-- Acciones de la cirugía -->
                                        <div class="flex flex-wrap items-center gap-2">
                                            <!-- Menú desplegable o botones de impresión PDF -->
                                            <a 
                                                href="{{ route('proformas.pdf.cirugia_anestesiologia', [$proforma->id, $cir->id]) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs"
                                                title="Imprimir Hoja de Anestesiología PDF"
                                            >
                                                <i class="fas fa-print text-rose-600"></i>
                                                <span>Anestesiología PDF</span>
                                            </a>
                                            <a 
                                                href="{{ route('proformas.pdf.cirugia_enfermeria', [$proforma->id, $cir->id]) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs"
                                                title="Imprimir Hoja de Enfermería PDF"
                                            >
                                                <i class="fas fa-print text-teal-600"></i>
                                                <span>Enfermería PDF</span>
                                            </a>
                                            <a 
                                                href="{{ route('proformas.pdf.cirugia', [$proforma->id, $cir->id]) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-2xs"
                                                title="Imprimir Descargo Quirófano Completo PDF (Ambas Hojas)"
                                            >
                                                <i class="fas fa-file-pdf"></i>
                                                <span>Descargo Completo</span>
                                            </a>

                                            @if ($proforma->estado !== 'Pagada')
                                                @permiso('proformas.cirugia.editar')
                                                    <button 
                                                        wire:click="abrirModalEditarCirugia({{ $cir->id }})" 
                                                        type="button" 
                                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors cursor-pointer"
                                                        title="Editar Cirugía / Descargo"
                                                    >
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                @endpermiso
                                                @permiso('proformas.cirugia.eliminar')
                                                    <button 
                                                        wire:click="eliminarCirugia({{ $cir->id }})" 
                                                        wire:confirm="¿Está seguro de eliminar este descargo de quirófano? Esta acción cancelará también la receta quirúrgica vinculada."
                                                        type="button" 
                                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer"
                                                        title="Eliminar Cirugía"
                                                    >
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                @endpermiso
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Datos de la intervención -->
                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Cirugía Efectuada</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-100 mt-0.5">
                                                {{ $cir->cirugia_efectuada ?: 'Sin especificar' }}
                                            </p>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                                                <strong>Diag. Operación:</strong> {{ $cir->diagnostico_operacion ?: 'N/E' }}
                                            </span>
                                        </div>

                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Especialidad y Sala</span>
                                            <p class="font-medium text-slate-700 dark:text-slate-200 mt-0.5">
                                                <i class="fas fa-stethoscope text-rose-500 me-1"></i> {{ $cir->especialidad ?: 'Cirugía General' }}
                                            </p>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">
                                                <strong>Sala:</strong> {{ $cir->sala ?: 'S/N' }} • <strong>Cama:</strong> {{ $cir->cama ?: 'S/N' }}
                                            </span>
                                        </div>

                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Tiempos y Anestesia</span>
                                            <span class="text-[11px] text-slate-600 dark:text-slate-300 block mt-0.5">
                                                <strong>Quirófano:</strong> {{ $cir->hora_ingreso_quirofano ?: '--' }} a {{ $cir->hora_salida_quirofano ?: '--' }}
                                            </span>
                                            <span class="text-[11px] text-slate-600 dark:text-slate-300 block">
                                                <strong>Cirugía:</strong> {{ $cir->hora_inicio_cirugia ?: '--' }} a {{ $cir->hora_fin_cirugia ?: '--' }}
                                            </span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">
                                                <strong>Anestesia:</strong> {{ $cir->tipo_anestesia ?: 'N/E' }}
                                            </span>
                                        </div>

                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Equipo Responsable</span>
                                            <span class="text-[11px] text-slate-700 dark:text-slate-200 block mt-0.5 font-medium">
                                                <strong>Cirujano:</strong> {{ $cir->cirujano?->nombre_completo ?? 'No asignado' }}
                                            </span>
                                            <span class="text-[11px] text-slate-600 dark:text-slate-300 block">
                                                <strong>Anestesiólogo:</strong> {{ $cir->anestesiologo?->nombre_completo ?? 'No asignado' }}
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1 flex flex-wrap gap-1">
                                                @if ($cir->primerAyudante)
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">1er Ayud: {{ $cir->primerAyudante->nombre_completo }}</span>
                                                @endif
                                                @if ($cir->instrumentador)
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Inst: {{ $cir->instrumentador->nombre_completo }}</span>
                                                @endif
                                                @if ($cir->circulante)
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Circ: {{ $cir->circulante->nombre_completo }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Medicamentos e Insumos utilizados en la cirugía -->
                                    @if ($cir->receta && $cir->receta->detalles->count() > 0)
                                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                                    <i class="fas fa-pills text-teal-600"></i>
                                                    <span>Medicamentos e Insumos Utilizados en Quirófano ({{ $cir->receta->detalles->count() }} ítems)</span>
                                                </span>
                                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                                                    <i class="fas fa-info-circle me-1"></i> Entregar reporte a Farmacia para el descargo y salida formal
                                                </span>
                                            </div>
                                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                                                <table class="w-full text-left text-xs">
                                                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 text-[11px] uppercase">
                                                        <tr>
                                                            <th class="py-2 px-3">Área</th>
                                                            <th class="py-2 px-3">Producto / Medicamento / Insumo</th>
                                                            <th class="py-2 px-3">Sección Origen</th>
                                                            <th class="py-2 px-3 text-center">Cantidad</th>
                                                            <th class="py-2 px-3 text-right">Costo Ref.</th>
                                                            <th class="py-2 px-3 text-center">Estado Farmacia</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono text-[11px]">
                                                        @foreach ($cir->receta->detalles as $det)
                                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                                                <td class="py-2 px-3 font-sans">
                                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $det->area_quirurgica === 'Anestesiología' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300' }}">
                                                                        {{ $det->area_quirurgica ?: 'Anestesiología' }}
                                                                    </span>
                                                                </td>
                                                                <td class="py-2 px-3 font-sans font-medium text-slate-800 dark:text-slate-200">
                                                                    {{ $det->producto->nombre ?? 'Producto no encontrado' }}
                                                                    @if ($det->indicaciones)
                                                                        <span class="text-[10px] text-slate-400 font-normal block">{{ $det->indicaciones }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="py-2 px-3 font-sans text-slate-600 dark:text-slate-400">
                                                                    {{ $det->seccion?->nombre ?? 'Quirófano' }}
                                                                </td>
                                                                <td class="py-2 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                                                    {{ $det->cantidad }}
                                                                </td>
                                                                <td class="py-2 px-3 text-right text-slate-600 dark:text-slate-400">
                                                                    Bs. {{ number_format($det->costo_unitario ?? 0, 2) }}
                                                                </td>
                                                                <td class="py-2 px-3 text-center font-sans">
                                                                    @if ($det->despachado)
                                                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold">
                                                                            <i class="fas fa-check-double me-0.5"></i> Despachado
                                                                        </span>
                                                                    @else
                                                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 font-semibold">
                                                                            <i class="fas fa-clock me-0.5"></i> Pendiente Farmacia
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Equipos especiales y solicitudes -->
                                    @php
                                        $equiposActivos = [];
                                        if ($cir->torre_laparoscopia) $equiposActivos[] = 'Torre de Laparoscopía';
                                        if ($cir->maquina_anestesiologia) $equiposActivos[] = 'Máquina de Anestesiología';
                                        if ($cir->oxigeno_central) $equiposActivos[] = 'Oxígeno Central';
                                        if ($cir->bomba_infusion) $equiposActivos[] = 'Bomba de Infusión';
                                        if ($cir->monitor_multiparametrico) $equiposActivos[] = 'Monitor Multiparamétrico';
                                        if ($cir->electrobisturi) $equiposActivos[] = 'Electrobisturí';
                                        if ($cir->maquina_aspiracion) $equiposActivos[] = 'Máquina de Aspiración';
                                        if ($cir->ecografo) $equiposActivos[] = 'Ecógrafo';
                                        if ($cir->rayos_x) $equiposActivos[] = 'Rayos X';
                                        if ($cir->intensificador_imagenes) $equiposActivos[] = 'Intensificador de Imágenes';
                                        if ($cir->material_importadora) $equiposActivos[] = 'Importadora: '.$cir->material_importadora;
                                        if ($cir->patologia) $equiposActivos[] = 'Patología: '.$cir->patologia;
                                        if ($cir->otros_equipos) $equiposActivos[] = 'Otros: '.$cir->otros_equipos;
                                    @endphp

                                    @if (count($equiposActivos) > 0)
                                        <div class="mt-3 pt-2 flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                                            <span class="font-bold text-slate-700 dark:text-slate-300"><i class="fas fa-microchip me-1 text-rose-500"></i> Equipos Especiales:</span>
                                            @foreach ($equiposActivos as $eq)
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-[10px]">
                                                    {{ $eq }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="py-12 px-4 text-center rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800">
                                    <i class="fas fa-procedures text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">No hay cirugías registradas</h4>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                        No se han registrado descargos de quirófano ni intervenciones quirúrgicas para esta proforma.
                                    </p>
                                    @if ($proforma->estado !== 'Pagada')
                                        @permiso('proformas.cirugia.crear')
                                            <button 
                                                wire:click="abrirModalNuevaCirugia" 
                                                type="button" 
                                                class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 cursor-pointer shadow-md transition"
                                            >
                                                <i class="fas fa-plus"></i> Registrar Primer Descargo de Quirófano
                                            </button>
                                        @endpermiso
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar los descargos de quirófano.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 4: PRESCRIPCIÓN Y RECETAS (REGLA: SOLO 1 RECETA ACTIVA) -->
            <!-- =================================================================== -->
            @if ($tab === 'recetas')
                @permiso('proformas.recetas.ver')
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
                            @if ($proforma->estado !== 'Pagada')
                                @permiso('emitir-receta')
                                    <button 
                                        wire:click="abrirModalReceta" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Prescribir Nueva Receta</span>
                                    </button>
                                @endpermiso
                            @endif
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
                            <div class="p-5 rounded-2xl border-2 border-emerald-500/80 bg-emerald-50/40 dark:bg-slate-850 shadow-xs">
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

                                    <div class="flex flex-col sm:items-end gap-2">
                                        <div class="flex items-center gap-2">
                                            <a 
                                                href="{{ route('proformas.pdf.receta', [$proforma->id, $proforma->recetaActiva->id]) }}" 
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
                                                title="Imprimir Receta Vigente en PDF"
                                            >
                                                <i class="fas fa-file-pdf text-red-300"></i>
                                                <span>Imprimir Receta PDF</span>
                                            </a>
                                        </div>
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
                                @if ($proforma->estado !== 'Pagada')
                                    @permiso('emitir-receta')
                                        <button 
                                            wire:click="abrirModalReceta" 
                                            type="button" 
                                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 cursor-pointer"
                                        >
                                            <i class="fas fa-plus"></i> Prescribir Receta Ahora
                                        </button>
                                    @endpermiso
                                @endif
                            </div>
                        @endif

                        <!-- TABLA GENERAL DE RECETAS (ACTUAL Y PREVIAS) -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                        <i class="fas fa-history text-teal-600 dark:text-teal-400"></i>
                                        <span>Historial y Registro de Recetas Médicas</span>
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-0.5">Listado cronológico de todas las prescripciones emitidas para esta proforma (receta actual e históricas).</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 self-start sm:self-center">
                                    Total: {{ $proforma->recetas->count() }} {{ $proforma->recetas->count() === 1 ? 'receta' : 'recetas' }}
                                </span>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xs">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200 dark:border-slate-800">
                                        <tr>
                                            <th class="py-3 px-4 w-12 text-center">#</th>
                                            <th class="py-3 px-4 w-44">Fecha y Hora</th>
                                            <th class="py-3 px-4 w-32">Número Receta</th>
                                            <th class="py-3 px-4">Prescrito Por</th>
                                            <th class="py-3 px-4 text-center">Medicamentos</th>
                                            <th class="py-3 px-4 text-center w-36">Estado</th>
                                            <th class="py-3 px-4 text-center w-36">Opción Imprimir</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                        @forelse ($proforma->recetas as $index => $rec)
                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors {{ $rec->activo ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}">
                                                <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap">
                                                    <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                                        <i class="far fa-calendar-alt text-teal-500 text-[11px]"></i>
                                                        {{ $rec->created_at ? $rec->created_at->format('d/m/Y') : '-' }}
                                                    </div>
                                                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                                        <i class="far fa-clock text-[10px]"></i>
                                                        {{ $rec->created_at ? $rec->created_at->format('H:i') : '-' }}
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-800 dark:text-slate-200">
                                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                        #{{ str_pad($rec->id, 4, '0', STR_PAD_LEFT) }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                        <i class="fas fa-user-md text-teal-600 text-[10px]"></i>
                                                        {{ $rec->doctor->nombre_completo ?? $rec->doctor->name ?? 'Médico Tratante' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-center font-mono">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300">
                                                        <i class="fas fa-pills text-[10px]"></i>
                                                        {{ $rec->detalles->count() }} {{ $rec->detalles->count() === 1 ? 'fármaco' : 'fármacos' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    @if ($rec->activo)
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                            <i class="fas fa-check-circle text-[10px]"></i> Vigente / Activa
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                            <i class="fas fa-archive text-[10px]"></i> Archivada / Previa
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <a 
                                                        href="{{ route('proformas.pdf.receta', [$proforma->id, $rec->id]) }}" 
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold shadow-2xs hover:shadow-sm transition-all transform active:scale-95 cursor-pointer"
                                                        title="Imprimir Receta #{{ $rec->id }} en PDF"
                                                    >
                                                        <i class="fas fa-file-pdf text-red-400 text-xs"></i>
                                                        <span>Imprimir PDF</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-12 text-center text-slate-400">
                                                    <i class="fas fa-prescription-bottle text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                    <p class="font-medium text-slate-600 dark:text-slate-400">No se han emitido recetas médicas todavía</p>
                                                    <p class="text-xs text-slate-400 mt-0.5">Utilice el botón "Prescribir Nueva Receta" para emitir la primera pauta médica.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar las prescripciones y recetas médicas.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 5: CONSUMOS EXTRAS E INSUMOS DIRECTOS -->
            <!-- =================================================================== -->
            @if ($tab === 'consumos')
                @permiso('proformas.consumos.ver')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-syringe text-amber-600 dark:text-amber-400"></i>
                                    Consumos Extras, Descartables y Material Hospitalario
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Jeringas, soluciones fisiológicas, apósitos o catéteres reportados directamente por enfermería y médicos.</p>
                            </div>
                            @if ($proforma->estado !== 'Pagada')
                                @permiso('proformas.consumos.agregar')
                                    <button 
                                        wire:click="abrirModalConsumo" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-md shadow-amber-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Registrar Consumo de Insumo</span>
                                    </button>
                                @endpermiso
                            @endif
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
                                        @if ($proforma->estado !== 'Pagada')
                                            @permiso('proformas.consumos.eliminar')
                                                <th class="py-3 px-4 text-center w-20">Acción</th>
                                            @endpermiso
                                        @endif
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
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-1.5">
                                                        @if (str_starts_with($ce->observaciones, '['))
                                                            @php
                                                                $finTag = strpos($ce->observaciones, ']');
                                                                $tagArea = $finTag !== false ? substr($ce->observaciones, 1, $finTag - 1) : null;
                                                                $restoObs = $finTag !== false ? trim(substr($ce->observaciones, $finTag + 1)) : $ce->observaciones;
                                                            @endphp
                                                            @if ($tagArea)
                                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                                    <i class="fas fa-layer-group text-[8px]"></i> {{ $tagArea }}
                                                                </span>
                                                            @endif
                                                            @if ($restoObs)
                                                                <span>{{ $restoObs }}</span>
                                                            @endif
                                                        @else
                                                            <span>{{ $ce->observaciones }}</span>
                                                        @endif
                                                    </div>
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
                                            @if ($proforma->estado !== 'Pagada')
                                                @permiso('proformas.consumos.eliminar')
                                                    <td class="py-3 px-4 text-center">
                                                        <button 
                                                            type="button" 
                                                            @click="$dispatch('swal:confirm', {
                                                                title: '¿Remover insumo extra?',
                                                                text: 'Se descontará del costo total de la proforma y las existencias se reintegrarán al inventario.',
                                                                icon: 'warning',
                                                                confirmButtonText: 'Sí, remover',
                                                                cancelButtonText: 'Cancelar',
                                                                componentId: '{{ $this->getId() }}',
                                                                method: 'eliminarConsumoExtra',
                                                                params: [{{ $ce->id }}],
                                                                event: 'eliminarConsumoExtra'
                                                            })"
                                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800 rounded-md transition-colors cursor-pointer"
                                                            title="Eliminar Insumo"
                                                        >
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </td>
                                                @endpermiso
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $proforma->estado !== 'Pagada' ? 8 : 7 }}" class="py-10 px-4 text-center text-slate-400">
                                                <i class="fas fa-syringe text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-300">No se han registrado consumos extras de insumos o materiales hospitalarios.</p>
                                                @if ($proforma->estado !== 'Pagada')
                                                    @permiso('proformas.consumos.agregar')
                                                        <button 
                                                            wire:click="abrirModalConsumo" 
                                                            type="button" 
                                                            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 cursor-pointer"
                                                        >
                                                            <i class="fas fa-plus"></i> Registrar Primer Insumo
                                                        </button>
                                                    @endpermiso
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar consumos e insumos extras.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 6: DESPACHOS Y ENTREGAS DE FARMACIA -->
            <!-- =================================================================== -->
            @if ($tab === 'despachos')
                @permiso('despachar-farmacia')
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-dolly-flatbed text-emerald-600 dark:text-emerald-400"></i>
                                    Historial de Despachos y Entregas de Farmacia
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Control de medicamentos e insumos dispensados físicamente con descuento en tiempo real del stock de inventario.</p>
                            </div>
                            @if ($proforma->estado !== 'Pagada')
                                @permiso('despachar-farmacia')
                                    <button 
                                        wire:click="abrirModalDespacho" 
                                        wire:loading.attr="disabled"
                                        type="button" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                    >
                                        <i class="fas fa-plus-circle"></i>
                                        <span>Realizar Despacho</span>
                                    </button>
                                @endpermiso
                            @endif
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
                                    <th class="py-3 px-4">Área / Sección</th>
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
                                        $precioUnit = (float) ($mov->producto?->ultimo_precio_venta ?? 0);
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
                                            @elseif (in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion']))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50">
                                                    <i class="fas fa-undo-alt text-[9px]"></i> Devolución
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
                                            @if ($mov->observaciones)
                                                <div class="text-[10px] text-purple-600 dark:text-purple-400 italic mt-0.5 flex items-center gap-1">
                                                    <i class="fas fa-comment-dots text-[9px]"></i> {{ $mov->observaciones }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if (in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion']))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                    <i class="fas fa-arrow-down text-[9px] text-purple-500"></i>
                                                    Reingreso a: {{ $mov->seccionDestino?->nombre ?? ($mov->seccionOrigen?->nombre ?? 'Almacén Central') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    <i class="fas fa-layer-group text-[9px] text-teal-500"></i>
                                                    {{ $mov->seccionOrigen?->nombre ?? 'Almacén Central' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-mono font-bold text-slate-700 dark:text-slate-300 text-xs">
                                                {{ $mov->lote?->codigo_lote ?? 'S/L' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                Vto: {{ $mov->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono font-bold text-xs {{ in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion']) ? 'text-purple-600 dark:text-purple-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            @if (in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion']))
                                                +{{ $mov->cantidad }}
                                            @else
                                                {{ $mov->cantidad }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                            @if (in_array($mov->tipo_movimiento, ['Devolución', 'Devolucion']))
                                                <div class="font-bold text-purple-600 dark:text-purple-400">-Bs. {{ number_format($subtotalMov, 2) }}</div>
                                            @else
                                                <div class="font-bold">Bs. {{ number_format($subtotalMov, 2) }}</div>
                                            @endif
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
                                        <td colspan="10" class="py-12 px-4 text-center text-slate-400">
                                            <i class="fas fa-dolly-flatbed text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <p class="font-medium text-slate-600 dark:text-slate-300">Aún no se han registrado despachos de farmacia ni consumos para esta proforma.</p>
                                            <p class="text-xs text-slate-400 mt-0.5">Puede dispensar los medicamentos de la receta activa o cargar insumos extras directamente.</p>
                                            @if ($proforma->estado !== 'Pagada')
                                                @permiso('despachar-farmacia')
                                                    <button 
                                                        wire:click="abrirModalDespacho" 
                                                        type="button" 
                                                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 cursor-pointer shadow-md shadow-emerald-500/20"
                                                    >
                                                        <i class="fas fa-plus-circle"></i> Realizar Primer Despacho
                                                    </button>
                                                @endpermiso
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar ni gestionar despachos de farmacia.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 7: HONORARIOS Y PAGOS MÉDICOS -->
            <!-- =================================================================== -->
            @if ($tab === 'pagos_medicos')
                @permiso('proformas.honorarios.ver')
                    <div>
                        <!-- Header del Tab -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fas fa-hand-holding-medical text-violet-600 dark:text-violet-400"></i>
                                    Liquidación y Honorarios de Médicos Tratantes
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Pondere los servicios clínicos realizados y asigne los montos correspondientes a los médicos que participaron en este caso.</p>
                            </div>
                            @permiso('proformas.honorarios.gestionar')
                                <button 
                                    wire:click="abrirModalPagoMedico" 
                                    wire:loading.attr="disabled"
                                    type="button" 
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-md shadow-violet-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer self-start sm:self-center"
                                >
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Asignar Honorario</span>
                                </button>
                            @endpermiso
                        </div>

                    <!-- Banner de Estado de la Proforma -->
                    @if ($proforma->estado === 'Pagada')
                        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-start gap-3 text-xs">
                            <i class="fas fa-check-circle text-base text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0"></i>
                            <div>
                                <span class="font-bold uppercase tracking-wider text-[11px]">¡Proforma Pagada / Cancelada!</span>
                                <p class="mt-0.5 text-slate-700 dark:text-slate-300">
                                    La cuenta del paciente ha sido saldada en caja. Proceda a la distribución y liquidación formal de honorarios para cada médico participante según la ponderación de servicios brindados.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="mb-5 p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 flex items-start gap-3 text-xs">
                            <i class="fas fa-info-circle text-base text-blue-600 dark:text-blue-400 mt-0.5 shrink-0"></i>
                            <div>
                                <span class="font-bold uppercase tracking-wider text-[11px]">Proforma en Estado: {{ $proforma->estado }}</span>
                                <p class="mt-0.5 text-slate-700 dark:text-slate-300">
                                    El personal puede registrar o pre-asignar los honorarios médicos como cálculo previo. La liquidación contable definitiva se consolida una vez que la proforma sea pagada en caja.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Tarjetas de Métricas de Honorarios (Colores Sólidos) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                        <!-- Card 1: Total Facturado -->
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>Total Proforma</span>
                                <i class="fas fa-file-invoice-dollar text-blue-500"></i>
                            </div>
                            <span class="text-lg font-bold font-mono text-slate-900 dark:text-white">
                                Bs. {{ number_format($proforma->costo_total, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Saldo: Bs. {{ number_format($proforma->saldoPendiente(), 2) }}</span>
                        </div>

                        <!-- Card 2: Total Servicios Clínicos -->
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>Servicios Clínicos</span>
                                <i class="fas fa-briefcase-medical text-indigo-500"></i>
                            </div>
                            <span class="text-lg font-bold font-mono text-slate-900 dark:text-white">
                                Bs. {{ number_format($subtotalServicios, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $proforma->servicios->count() }} procedimiento(s)</span>
                        </div>

                        <!-- Card 3: Total Honorarios Asignados -->
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>Honorarios Asignados</span>
                                <i class="fas fa-hand-holding-medical text-violet-500"></i>
                            </div>
                            <span class="text-lg font-bold font-mono text-violet-600 dark:text-violet-400">
                                Bs. {{ number_format($proforma->totalPagosMedicos(), 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $proforma->pagosMedicos->count() }} registro(s)</span>
                        </div>

                        <!-- Card 4: Margen Institucional -->
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>Margen Institucional</span>
                                <i class="fas fa-hospital text-emerald-500"></i>
                            </div>
                            <span class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                Bs. {{ number_format(max(0, $proforma->costo_total - $proforma->totalPagosMedicos()), 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Remanente clínico</span>
                        </div>
                    </div>

                    <!-- Panel de Médicos Asignados al Caso Clínico -->
                    @if ($proforma->medicos->isNotEmpty())
                        <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                    <i class="fas fa-user-md text-blue-500"></i>
                                    Médicos Tratantes Asignados a la Proforma
                                </h4>
                                <span class="text-[11px] text-slate-400">Haga clic en un médico para asignarle honorario directamente</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach ($proforma->medicos as $med)
                                    @php
                                        $pagosEsteMedico = $proforma->pagosMedicos->where('medico_id', $med->id);
                                        $totalEsteMedico = (float) $pagosEsteMedico->sum('monto');
                                    @endphp
                                    <div class="p-3 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-xs text-slate-800 dark:text-slate-100 truncate">
                                                Dr(a). {{ $med->nombre_completo }}
                                            </div>
                                            <div class="text-[10px] text-slate-400 truncate">
                                                {{ $med->especialidad->nombre ?? 'Medicina General' }}
                                            </div>
                                            <div class="mt-1 text-[11px] font-mono font-semibold {{ $totalEsteMedico > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400' }}">
                                                Asignado: Bs. {{ number_format($totalEsteMedico, 2) }}
                                            </div>
                                        </div>

                                        @permiso('proformas.honorarios.gestionar')
                                            <button 
                                                type="button" 
                                                wire:click="abrirModalPagoMedico({{ $med->id }})" 
                                                class="shrink-0 px-2.5 py-1.5 rounded-lg bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-300 hover:bg-violet-100 dark:hover:bg-violet-900 border border-violet-200 dark:border-violet-800 text-[11px] font-semibold transition"
                                                title="Asignar honorario a este médico"
                                            >
                                                <i class="fas fa-plus text-[10px] me-1"></i> Asignar
                                            </button>
                                        @endpermiso
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Base de Ponderación: Servicios Clínicos Realizados -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                <i class="fas fa-list-check text-indigo-500"></i>
                                Servicios Clínicos Realizados (Base para Ponderar Honorarios)
                            </h4>
                            <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 font-semibold">
                                Total: Bs. {{ number_format($subtotalServicios, 2) }}
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="py-2 px-3 w-10 text-center">#</th>
                                        <th class="py-2 px-3">Procedimiento / Servicio</th>
                                        <th class="py-2 px-3">Categoría</th>
                                        <th class="py-2 px-3">Observaciones de Ejecución</th>
                                        <th class="py-2 px-3 text-right">Costo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-slate-700 dark:text-slate-300 text-xs">
                                    @forelse ($proforma->servicios as $sIdx => $ps)
                                        <tr class="hover:bg-white/60 dark:hover:bg-slate-800/60">
                                            <td class="py-2 px-3 text-center font-mono text-slate-400 text-[11px]">{{ $sIdx + 1 }}</td>
                                            <td class="py-2 px-3 font-semibold text-slate-900 dark:text-white">
                                                {{ $ps->servicio->nombre ?? 'Servicio no especificado' }}
                                            </td>
                                            <td class="py-2 px-3 text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ $ps->servicio->categoria->nombre ?? 'General' }}
                                            </td>
                                            <td class="py-2 px-3 text-[11px] text-slate-500 dark:text-slate-400 italic">
                                                {{ $ps->observaciones ?: 'Sin observaciones' }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                                Bs. {{ number_format($ps->costo_final, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-4 px-3 text-center text-slate-400 italic">
                                                No se han registrado procedimientos ni servicios clínicos en esta proforma.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabla de Honorarios Médicos Registrados -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                <i class="fas fa-file-invoice text-violet-500"></i>
                                Historial de Honorarios Médicos Asignados
                            </h4>
                            <span class="text-xs font-mono font-bold text-violet-600 dark:text-violet-400">
                                Total Honorarios: Bs. {{ number_format($proforma->totalPagosMedicos(), 2) }}
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">Fecha Pago / Liquidación</th>
                                        <th class="py-3 px-4">Médico Beneficiario</th>
                                        <th class="py-3 px-4">Caja / Salida</th>
                                        <th class="py-3 px-4">Detalle / Ponderación / Observación</th>
                                        <th class="py-3 px-4 text-right">Monto Honorario</th>
                                        <th class="py-3 px-4">Registrado Por</th>
                                        @anypermiso(['proformas.honorarios.gestionar', 'proformas.honorarios.eliminar'])
                                            <th class="py-3 px-4 text-center w-24">Acciones</th>
                                        @endanypermiso
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    @forelse ($proforma->pagosMedicos as $idx => $pm)
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                            <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]">{{ $idx + 1 }}</td>
                                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                                {{ $pm->fecha_pago?->format('d/m/Y') ?? $pm->created_at?->format('d/m/Y') }}
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-slate-900 dark:text-white text-xs flex items-center gap-1.5">
                                                    <i class="fas fa-user-md text-blue-500 text-[10px]"></i>
                                                    Dr(a). {{ $pm->medico->nombre_completo ?? 'Médico no encontrado' }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">
                                                    {{ $pm->medico?->especialidad?->nombre ?? 'Medicina General' }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-1.5">
                                                    @if ($pm->caja_id)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                            <i class="fas fa-cash-register me-1 text-emerald-500"></i>Caja #{{ $pm->caja_id }}
                                                        </span>
                                                    @else
                                                        <span class="text-slate-400 text-[11px] italic">Sin caja</span>
                                                    @endif

                                                    @php
                                                        $tipoBadge = match($pm->tipo_pago) {
                                                            'Efectivo' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
                                                            'Transferencia' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800',
                                                            'QR' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-400 dark:border-purple-800',
                                                            default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $tipoBadge }}">
                                                        {{ $pm->tipo_pago ?? 'Efectivo' }}
                                                    </span>
                                                </div>
                                                @if ($pm->numero_referencia)
                                                    <div class="text-[10px] font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                                        Ref: {{ $pm->numero_referencia }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="text-xs text-slate-800 dark:text-slate-200">
                                                    {{ $pm->observaciones ?: 'Sin detalle u observación registrada' }}
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-right font-mono font-bold text-sm text-violet-600 dark:text-violet-400">
                                                Bs. {{ number_format((float) $pm->monto, 2) }}
                                            </td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 text-[11px]">
                                                {{ $pm->user?->nombre_completo ?? 'Personal Administrativo' }}
                                            </td>
                                            @anypermiso(['proformas.honorarios.gestionar', 'proformas.honorarios.eliminar'])
                                                <td class="py-3 px-4 text-center">
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        @permiso('proformas.honorarios.gestionar')
                                                            <button 
                                                                wire:click="editarPagoMedico({{ $pm->id }})" 
                                                                type="button" 
                                                                class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/50 transition cursor-pointer"
                                                                title="Editar Honorario"
                                                            >
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                        @endpermiso
                                                        @permiso('proformas.honorarios.eliminar')
                                                            <button 
                                                                wire:click="eliminarPagoMedico({{ $pm->id }})" 
                                                                wire:confirm="¿Está seguro de revertir este honorario médico y su salida de caja asociada?"
                                                                wire:loading.attr="disabled"
                                                                type="button" 
                                                                class="p-1.5 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                                                title="Revertir Honorario y Egreso de Caja"
                                                            >
                                                                <i class="fas fa-trash-alt text-xs" wire:loading.remove wire:target="eliminarPagoMedico({{ $pm->id }})"></i>
                                                                <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarPagoMedico({{ $pm->id }})"></i>
                                                            </button>
                                                        @endpermiso
                                                    </div>
                                                </td>
                                            @endanypermiso
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-12 px-4 text-center text-slate-400">
                                                <i class="fas fa-hand-holding-medical text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium text-slate-600 dark:text-slate-300">Aún no se han registrado honorarios médicos para esta proforma.</p>
                                                <p class="text-xs text-slate-400 mt-0.5">Revise los servicios realizados arriba y pondere el monto a pagar para cada médico participante.</p>
                                                @permiso('proformas.honorarios.gestionar')
                                                    <button 
                                                        wire:click="abrirModalPagoMedico" 
                                                        type="button" 
                                                        class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-violet-600 text-white text-xs font-semibold hover:bg-violet-700 cursor-pointer shadow-md shadow-violet-500/20"
                                                    >
                                                        <i class="fas fa-plus-circle"></i> Asignar Primer Honorario
                                                    </button>
                                                @endpermiso
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                    <div class="py-12 px-4 text-center text-slate-400">
                        <i class="fas fa-lock text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-slate-700 dark:text-slate-300">Acceso Restringido</p>
                        <p class="text-xs text-slate-400 mt-1">No cuenta con autorización para visualizar la liquidación y honorarios médicos.</p>
                    </div>
                @endpermiso
            @endif

            <!-- =================================================================== -->
            <!-- TAB 8: IMPRESIONES Y FORMULARIOS CLÍNICOS (LIBRE ACCESO) -->
            <!-- =================================================================== -->
            @if ($tab === 'impresiones')
                <div>
                    <!-- Header del Tab -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-print text-sky-600 dark:text-sky-400"></i>
                                Centro de Impresiones y Documentación Clínica
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Emisión y descarga en formato PDF de formularios de enfermería, hospitalización, expediente clínico y reportes administrativos con datos del paciente pre-cargados.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40 text-xs font-bold">
                                <i class="fas fa-check-circle text-xs"></i> Acceso Habilitado
                            </span>
                        </div>
                    </div>

                    <!-- SECCIÓN 1: FORMULARIOS DE ENFERMERÍA Y HOSPITALIZACIÓN -->
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 text-xs font-bold">
                                <i class="fas fa-user-nurse"></i>
                            </span>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[12px]">
                                Formularios de Enfermería y Hospitalización
                            </h4>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">Documento / Formulario</th>
                                        <th class="py-3 px-4">Categoría</th>
                                        <th class="py-3 px-4">Descripción y Contenido</th>
                                        <th class="py-3 px-4 text-center">Datos Pre-cargados</th>
                                        <th class="py-3 px-4 text-center w-36">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    <!-- 1. Alta Solicitada -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">1</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-file-signature text-rose-500 text-sm"></i>
                                                <span>Alta Solicitada (Alta Voluntaria)</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                Legal / Egreso
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Documento formal de exoneración legal y retiro voluntario con membrete institucional, diagnósticos del caso y cláusulas de descargo de responsabilidad con firmas.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Diagnóstico, Cama
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.alta_solicitada', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 2. Registro de Enfermería en Emergencia -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">2</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-notes-medical text-amber-500 text-sm"></i>
                                                <span>Registro de Enfermería en Emergencia</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                Emergencias
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Ficha de triaje y valoración inicial de enfermería, dolor, alergias, necesidades básicas, signos vitales horarios y Escala de Coma de Glasgow estructurada.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Teléfono, HC, Vitals
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.registro_enfermeria', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 3. Control de Signos Vitales -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">3</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-heartbeat text-emerald-500 text-sm"></i>
                                                <span>Control de Signos Vitales</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                Monitoreo
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Planilla limpia para registro manual y continuo de signos vitales (PA, FC, FR, Temperatura, Saturación O2) durante la estadía hospitalaria.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Ficha Paciente y Sala/Cama
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.signos_vitales', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 4. Kardex de Enfermería -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">4</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-id-card-alt text-sky-500 text-sm"></i>
                                                <span>Kardex de Enfermería (Doble Ficha)</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
                                                Kardex / Dietas
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Ficha duplicada por hoja para control de exámenes complementarios, cuidados específicos de enfermería y control de dieta por turno.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Médico, Diagnóstico
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.kardex_enfermeria', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 5. Hoja de Control de Líquidos -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">5</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-tint text-blue-500 text-sm"></i>
                                                <span>Hoja de Control de Líquidos (Balance Hídrico)</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                                Balance Hídrico
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Planilla apaisada (horizontal) de ingresos (sueros, plasma, sangre, VO) y egresos (orina, heces, vómitos, drenajes) en 3 turnos (08-14, 14-20, 20-08) y control ponderal.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, H.CL, Sala, Cama
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.control_liquidos', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 6. Tarjeta Única de Tratamiento -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">6</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-pills text-teal-500 text-sm"></i>
                                                <span>Tarjeta Única de Tratamiento</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                                Tratamiento / Soluciones
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Ficha duplicada con línea de corte (2 páginas). Anverso: horarios de medicación por turno (TM, TT, TN), dosis y vía. Reverso: control de Soluciones Parenterales con estados y sello/firma.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Sala/Cama, HC, Pauta
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.tarjeta_tratamiento', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 7. Registro Tratamiento de Enfermería -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">7</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-clipboard-check text-cyan-600 text-sm"></i>
                                                <span>Registro Tratamiento de Enfermería</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300">
                                                Enfermería / Venoclisis
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Formato institucional de 2 páginas con filiación completa de enfermería, grilla horaria extendida de fármacos por fecha/firma, tabla de Venoclisis, observaciones y firma/sello.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Nombres, Apellidos, Sexo, Sala/Cama
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.tratamiento_enfermeria', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 8. Gráfica de Signos Vitales -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">8</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-chart-line text-rose-500 text-sm"></i>
                                                <span>Gráfica de Signos Vitales (Curva Térmica y Pulso)</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                Gráfica / Turnos
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Hoja gráfica institucional de curva térmica (35°-42°C), pulso (60-160 lpm), respiración (10-70 rpm), frecuencia por turnos (M, T, N en 7 días) y control de PA, heces, orina, dieta, peso y satO2.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Sala/Cama, Ingreso, HC
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.grafica_signos_vitales', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 9. Registro de Glucemias -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">9</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-tint text-amber-500 text-sm"></i>
                                                <span>Registro de Glucemias</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                Glucemias / Rescate
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Planilla de monitoreo de glicemia capilar con datos del paciente pre-cargados en la cabecera, fecha, hora, valor en mg/dL, rescate de insulina/fármaco, costo y observaciones.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Edad, Sala/Cama, Ingreso
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.registro_glucemias', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- 10. Descargo de Insumos y Medicamentos (Piso / Sala) -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">10</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-boxes text-emerald-600 text-sm"></i>
                                                <span>Descargo de Insumos y Medicamentos (Piso)</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                Insumos / Descargos
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Hoja oficial de control de materiales e insumos gastados en sala/habitación con médico tratante, fecha, hora, medicamento/insumo, cantidad, precio, solicitante y firma.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Médico, Sala/Cama, HC
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.descargo_medicamentos_insumos', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: CONSENTIMIENTOS INFORMADOS Y DOCUMENTOS MÉDICO-LEGALES -->
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 text-xs font-bold">
                                <i class="fas fa-file-contract"></i>
                            </span>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[12px]">
                                Consentimientos Informados y Formularios Médico-Legales
                            </h4>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">Documento / Consentimiento</th>
                                        <th class="py-3 px-4">Categoría</th>
                                        <th class="py-3 px-4">Descripción y Contenido</th>
                                        <th class="py-3 px-4 text-center">Datos Pre-cargados</th>
                                        <th class="py-3 px-4 text-center w-36">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    <!-- Consentimiento Anestesia Raquídea -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">1</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-syringe text-indigo-500 text-sm"></i>
                                                <span>Consentimiento Informado - Anestesia Raquídea</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                Anestesiología / Legal
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Documento médico-legal oficial (2 páginas) con consideraciones clínicas, riesgos y complicaciones, aceptación formal del procedimiento anestésico, constancia de firmas y hojas de revocación / minoría de edad.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Procedimiento, Servicio, Cédula
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.consentimiento_anestesia', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Consentimiento Quirúrgico -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">2</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-procedures text-purple-500 text-sm"></i>
                                                <span>Consentimiento y Aceptación de Procedimiento Quirúrgico</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                                Quirúrgico / Legal
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Documento médico-legal oficial (2 páginas) de información, declaración del paciente (alergias/antecedentes), consentimiento y aceptación del tratamiento quirúrgico programado con firmas de paciente, familiar y cirujano.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double text-[10px]"></i> Paciente, Cirugía, Médico Cirujano
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.consentimiento_quirurgico', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: EXPEDIENTE CLÍNICO DE LA ATENCIÓN -->
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 text-xs font-bold">
                                <i class="fas fa-folder-open"></i>
                            </span>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[12px]">
                                Expediente Clínico de la Atención
                            </h4>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">Documento Clínico</th>
                                        <th class="py-3 px-4">Tipo</th>
                                        <th class="py-3 px-4">Estado / Registros Disponibles</th>
                                        <th class="py-3 px-4 text-center w-36">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    <!-- Historia Clínica -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">1</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-file-medical-alt text-teal-600 text-sm"></i>
                                                <span>Historia Clínica Completa</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                                Admisión / Consulta
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Filiación, antecedentes personales patológicos/no patológicos, examen físico y diagnóstico.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.historia_clinica', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Epicrisis -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">2</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-file-invoice text-indigo-600 text-sm"></i>
                                                <span>Epicrisis Médica de Egreso</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                Alta Médica
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Resumen clínico al retiro del paciente, diagnóstico de ingreso y egreso, tratamiento realizado e indicaciones.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.epicrisis', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Evoluciones -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">3</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-stethoscope text-cyan-600 text-sm"></i>
                                                <span>Notas de Evolución Clínica</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300">
                                                Seguimiento
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Historial cronológico de notas de progreso y evolución médica registrada ({{ $proforma->evoluciones->count() }} registradas).
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.evolucion', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Indicaciones Médicas -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">4</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-clipboard-list text-emerald-600 text-sm"></i>
                                                <span>Indicaciones Médicas</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                Tratamiento
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Órdenes e indicaciones médicas para enfermería y piso ({{ $proforma->indicaciones->count() }} registradas).
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.indicacion', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Internaciones -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">5</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-bed text-blue-600 text-sm"></i>
                                                <span>Hoja de Internación</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                                Hospitalización
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Historial de internación, cama, sala y altas de servicio asignadas al paciente.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.internacion', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <!-- Receta Médica -->
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">6</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-prescription text-teal-600 text-sm"></i>
                                                <span>Receta Médica Oficial</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                                Farmacia
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            @if ($proforma->recetaActiva)
                                                Receta activa con {{ $proforma->recetaActiva->detalles->count() }} ítems prescritos e indicaciones.
                                            @else
                                                Formato de prescripción médica con membrete y datos del paciente.
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.receta', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: REPORTES DE QUIRÓFANO (SI EXISTEN CIRUGÍAS) -->
                    @if ($proforma->cirugias->isNotEmpty())
                        <div class="mb-8">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 text-xs font-bold">
                                    <i class="fas fa-procedures"></i>
                                </span>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[12px]">
                                    Cirugías y Descargo de Quirófano ({{ $proforma->cirugias->count() }})
                                </h4>
                            </div>

                            <div class="space-y-3">
                                @foreach ($proforma->cirugias as $cirugia)
                                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-900 dark:text-white text-xs">
                                                    Cirugía {{ $cirugia->numero }}: {{ $cirugia->cirugia_efectuada ?? $cirugia->diagnostico_operacion }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                    {{ $cirugia->tipo_cirugia ?? 'Procedimiento' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                                Cirujano: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $cirugia->cirujano->name ?? '---' }}</span> | Fecha: {{ $cirugia->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a 
                                                href="{{ route('proformas.pdf.cirugia', [$proforma->id, $cirugia->id]) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-semibold transition-colors"
                                                title="Descargo Completo"
                                            >
                                                <i class="fas fa-file-medical"></i> Descargo Completo
                                            </a>
                                            <a 
                                                href="{{ route('proformas.pdf.cirugia_anestesiologia', [$proforma->id, $cirugia->id]) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-[11px] font-semibold transition-colors"
                                                title="Hoja de Anestesiología"
                                            >
                                                <i class="fas fa-syringe"></i> Anestesiología
                                            </a>
                                            <a 
                                                href="{{ route('proformas.pdf.cirugia_enfermeria', [$proforma->id, $cirugia->id]) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-semibold transition-colors"
                                                title="Hoja de Enfermería"
                                            >
                                                <i class="fas fa-user-nurse"></i> Enfermería Quirófano
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- SECCIÓN 4: INFORMES ADMINISTRATIVOS Y FINANCIEROS -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 text-xs font-bold">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </span>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[12px]">
                                Informes Administrativos y Financieros
                            </h4>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">Informe / Comprobante</th>
                                        <th class="py-3 px-4">Tipo</th>
                                        <th class="py-3 px-4">Descripción</th>
                                        <th class="py-3 px-4 text-center w-36">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">1</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-list-alt text-slate-600 dark:text-slate-300 text-sm"></i>
                                                <span>Detalle Clínico-Administrativo de Proforma</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                                Auditoría / Detallado
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Desglose completo y minucioso de cada servicio, consumo extra, despacho de farmacia y pagos asentados.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.detalle', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>

                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-bold">2</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-receipt text-indigo-600 text-sm"></i>
                                                <span>Resumen Financiero Consolidado de Proforma</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                Finanzas / Facturación
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                            Consolidado de costos finales acordados para caja, aseguradoras o convenios institucionales.
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('proformas.pdf.resumen', $proforma->id) }}" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-700 hover:bg-indigo-800 text-white font-semibold text-xs shadow-xs hover:shadow transition-colors"
                                            >
                                                <i class="fas fa-print"></i>
                                                <span>Imprimir PDF</span>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- MODALES DE OPERACIÓN CLÍNICA -->
    <!-- ======================================================================= -->

    <!-- MODAL HISTORIA CLÍNICA -->
    @if ($modalHistoriaClinicaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl">
                    <div class="px-6 py-4 bg-teal-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white shadow-xs">
                                <i class="fas fa-file-medical-alt text-sm"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                    Historia Clínica del Paciente
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Expediente #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }} • {{ $proforma->paciente->nombre_completo }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('proformas.pdf.historia_clinica', $proforma->id) }}" 
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs"
                                title="Imprimir Historia Clínica Oficial en PDF"
                            >
                                <i class="fas fa-print text-xs text-teal-600"></i>
                                <span>PDF</span>
                            </a>
                            <button wire:click="cerrarModalHistoriaClinica" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    <form wire:submit="guardarHistoriaClinica" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                        <!-- Referencia rápida del paciente -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex flex-wrap items-center gap-3 text-slate-600 dark:text-slate-300">
                                <span><strong class="text-slate-900 dark:text-white">Paciente:</strong> {{ $proforma->paciente->nombre_completo }}</span>
                                <span>•</span>
                                <span><strong>CI:</strong> {{ $proforma->paciente->cedula ?: 'Sin CI' }}</span>
                                <span>•</span>
                                <span><strong>Edad:</strong> {{ $proforma->edad_paciente !== null ? "{$proforma->edad_paciente} años" : 'N/E' }}</span>
                                <span>•</span>
                                <span><strong>Sexo:</strong> {{ $proforma->paciente->genero ?? 'N/E' }}</span>
                                <span>•</span>
                                <span><strong>Estado Civil:</strong> {{ $proforma->paciente->estado_civil ?: 'No especificado' }}</span>
                                <span>•</span>
                                <span><strong>Ocupación:</strong> {{ $proforma->paciente->ocupacion ?: 'No especificada' }}</span>
                            </div>
                            <span class="text-[11px] font-mono text-slate-400">N° H.C.: {{ $proforma->paciente->numero_historia_clinica }}</span>
                        </div>

                        <!-- 1. Filiación Complementaria -->
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block mb-3">
                                <i class="fas fa-id-card text-teal-600 dark:text-teal-400 me-1"></i> Filiación Complementaria
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Procedencia
                                    </label>
                                    <input type="text" wire:model="hc_procedencia" placeholder="Ej. La Paz, Santa Cruz..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Residencia (Domicilio)
                                    </label>
                                    <input type="text" wire:model="hc_residencia" placeholder="Ej. Calle ..., Zona ..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Fuente de Información
                                    </label>
                                    <input type="text" wire:model="hc_fuente_informacion" placeholder="Ej. Paciente, Tutor..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Motivo de Consulta y Enfermedad Actual -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Motivo de Consulta
                                </label>
                                <textarea wire:model="hc_motivo_consulta" rows="3" placeholder="Motivo o queja principal..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Historia de la Enfermedad Actual
                                </label>
                                <textarea wire:model="hc_enfermedad_actual" rows="3" placeholder="Cronología y sintomatología..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                        </div>

                        <!-- 3. Antecedentes Personales -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Antecedentes Personales Patológicos
                                </label>
                                <textarea wire:model="hc_antecedentes_patologicos" rows="2.5" placeholder="Enfermedades crónicas, quirúrgicos, alergias..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Antecedentes Personales No Patológicos
                                </label>
                                <textarea wire:model="hc_antecedentes_no_patologicos" rows="2.5" placeholder="Hábitos, inmunizaciones, alimentación..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                        </div>

                        <!-- 4. Antecedentes Ginecológicos -->
                        <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200/70 dark:border-purple-800/40">
                            <span class="text-xs font-bold uppercase tracking-wider text-purple-900 dark:text-purple-300 block mb-2.5">
                                <i class="fas fa-venus text-purple-600 dark:text-purple-400 me-1"></i> Antecedentes Ginecológicos (Si aplica)
                            </span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">MENARCA</label>
                                    <input type="text" wire:model="hc_menarca" placeholder="Ej. 13 a." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">FUM</label>
                                    <input type="text" wire:model="hc_fum" placeholder="Ej. 12/04/24" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">G</label>
                                    <input type="text" wire:model="hc_gestas" placeholder="0" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">P</label>
                                    <input type="text" wire:model="hc_partos" placeholder="0" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">C</label>
                                    <input type="text" wire:model="hc_cesareas" placeholder="0" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">A</label>
                                    <input type="text" wire:model="hc_abortos" placeholder="0" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">PAP</label>
                                    <input type="text" wire:model="hc_pap" placeholder="Negativo" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Otros</label>
                                    <input type="text" wire:model="hc_gineco_otros" placeholder="Regular" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- 5. Examen Físico y Signos Vitales -->
                        <div class="space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block">
                                <i class="fas fa-heartbeat text-rose-500 me-1"></i> Examen Físico y Signos Vitales
                            </span>

                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">PA (mmHg)</label>
                                    <input type="text" wire:model="hc_pa" placeholder="120/80" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">FC (lpm)</label>
                                    <input type="text" wire:model="hc_fc" placeholder="75" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">FR (rpm)</label>
                                    <input type="text" wire:model="hc_fr" placeholder="18" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">T (°C)</label>
                                    <input type="text" wire:model="hc_temperatura" placeholder="36.5" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">SatO2 (%)</label>
                                    <input type="text" wire:model="hc_sato2" placeholder="98" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">P (kg)</label>
                                    <input type="text" wire:model.live.debounce.400ms="hc_peso" placeholder="70" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">T (cm)</label>
                                    <input type="text" wire:model.live.debounce.400ms="hc_talla" placeholder="170" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">IMC</label>
                                    <input type="text" wire:model="hc_imc" readonly placeholder="24.2" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-teal-700 dark:text-teal-300 font-bold p-2 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                    Descripción del Examen Físico (Cabeza, Cuello, Tórax, Abdomen, Extremidades...)
                                </label>
                                <textarea wire:model="hc_examen_fisico" rows="3" placeholder="Hallazgos del examen físico..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                        </div>

                        <!-- 6. Diagnóstico y Conducta -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Diagnóstico Clínico / Impresión Presuntiva
                                </label>
                                <textarea wire:model="hc_diagnostico" rows="2.5" placeholder="Diagnóstico definitivo o presuntivo..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Conducta a Seguir / Plan Terapéutico
                                </label>
                                <textarea wire:model="hc_conducta" rows="2.5" placeholder="Plan terapéutico y manejo..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                            <a 
                                href="{{ route('proformas.pdf.historia_clinica', $proforma->id) }}" 
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition-colors"
                            >
                                <i class="fas fa-print text-teal-600"></i>
                                <span>Imprimir Documento PDF</span>
                            </a>
                            <div class="flex items-center gap-2">
                                <button wire:click="cerrarModalHistoriaClinica" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                    Cancelar
                                </button>
                                <button 
                                    type="submit" 
                                    wire:loading.attr="disabled"
                                    wire:target="guardarHistoriaClinica"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    <span wire:loading.remove wire:target="guardarHistoriaClinica">
                                        <i class="fas fa-save me-1"></i> Guardar Historia Clínica
                                    </span>
                                    <span wire:loading wire:target="guardarHistoriaClinica">
                                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL EPICRISIS (SALIDA / ALTA MÉDICA) -->
    @if ($modalEpicrisisOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl">
                    <div class="px-6 py-4 bg-cyan-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-600 text-white shadow-xs">
                                <i class="fas fa-file-medical text-sm"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                    Epicrisis Médica (Documento de Salida)
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Expediente #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }} • {{ $proforma->paciente->nombre_completo }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a 
                                href="{{ route('proformas.pdf.epicrisis', $proforma->id) }}" 
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs"
                                title="Imprimir Epicrisis Oficial en PDF"
                            >
                                <i class="fas fa-print text-xs text-cyan-600"></i>
                                <span>PDF</span>
                            </a>
                            <button wire:click="cerrarModalEpicrisis" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    <form wire:submit="guardarEpicrisis" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                        <!-- Referencia rápida de Filiación del Paciente -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex flex-wrap items-center gap-3 text-slate-600 dark:text-slate-300">
                                <span><strong class="text-slate-900 dark:text-white">Paciente:</strong> {{ $proforma->paciente->nombre_completo }}</span>
                                <span>•</span>
                                <span><strong>Sexo:</strong> {{ $proforma->paciente->genero ?? 'N/E' }}</span>
                                <span>•</span>
                                <span><strong>Edad al Ingreso:</strong> {{ $proforma->edad_paciente !== null ? "{$proforma->edad_paciente} años" : 'N/E' }}</span>
                                <span>•</span>
                                <span><strong>Teléfono:</strong> {{ $proforma->paciente->telefono ?: 'S/N' }}</span>
                                <span>•</span>
                                <span><strong>Fecha Ingreso:</strong> {{ $proforma->fecha_ingreso?->format('d/m/Y H:i') ?: 'N/E' }}</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-cyan-700 dark:text-cyan-300 bg-cyan-100/70 dark:bg-cyan-950/60 px-2 py-0.5 rounded">
                                N° H.C.: {{ $proforma->paciente->numero_historia_clinica }}
                            </span>
                        </div>

                        <!-- 1. Parámetros de Internación / Ubicación y Salida -->
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block mb-3">
                                <i class="fas fa-hospital-user text-cyan-600 dark:text-cyan-400 me-1"></i> Parámetros de Estancia y Salida
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Cama / Pieza
                                    </label>
                                    <input type="text" wire:model="epi_cama" placeholder="Ej. Cama 3, Hab. 102..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Servicio
                                    </label>
                                    <input type="text" wire:model="epi_servicio" placeholder="Ej. Medicina Interna, Cirugía..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Fecha y Hora de Egreso
                                    </label>
                                    <input type="datetime-local" wire:model="epi_fecha_egreso" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Condiciones y Diagnósticos -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Condiciones de Ingreso
                                    </label>
                                    <input type="text" wire:model="epi_condiciones_ingreso" placeholder="Ej. Regular estado general, consciente, orientado..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Diagnóstico de Ingreso
                                    </label>
                                    <input type="text" wire:model="epi_diagnostico_ingreso" placeholder="Diagnóstico al momento de admisión..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Condiciones de Egreso
                                    </label>
                                    <input type="text" wire:model="epi_condiciones_egreso" placeholder="Ej. Estable, asintomático, afebril, hemodinámicamente estable..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Diagnóstico de Egreso
                                    </label>
                                    <input type="text" wire:model="epi_diagnostico_egreso" placeholder="Diagnóstico definitivo al alta..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Causa de Egreso
                                </label>
                                <input type="text" wire:model="epi_causa_egreso" list="lista_causas_egreso" placeholder="Ej. Alta médica, Mejoría clínica, Transferencia, Retiro voluntario, Fallecimiento..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                <datalist id="lista_causas_egreso">
                                    <option value="Alta médica por curación">
                                    <option value="Alta médica por mejoría">
                                    <option value="Alta solicitada / Retiro voluntario">
                                    <option value="Transferencia a otro centro">
                                    <option value="Fuga del paciente">
                                    <option value="Fallecimiento">
                                </datalist>
                            </div>
                        </div>

                        <!-- 3. Secciones Clínicas Detalladas -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Estudios Complementarios
                                </label>
                                <textarea wire:model="epi_estudios_complementarios" rows="2.5" placeholder="Laboratorios, ecografías, radiografías, tomografías realizadas..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Antecedentes Relevantes
                                </label>
                                <textarea wire:model="epi_antecedentes_relevantes" rows="2" placeholder="Antecedentes patológicos o quirúrgicos de interés..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Historia Médica Actual
                                </label>
                                <textarea wire:model="epi_historia_medica_actual" rows="2.5" placeholder="Resumen del cuadro clínico y evolución intrahospitalaria..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Procedimientos
                                    </label>
                                    <textarea wire:model="epi_procedimientos" rows="2.5" placeholder="Cirugías, punciones, curaciones, intubación..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Tratamiento Realizado
                                    </label>
                                    <textarea wire:model="epi_tratamiento_realizado" rows="2.5" placeholder="Esquema terapéutico administrado durante la estancia..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Indicaciones de Alta
                                </label>
                                <textarea wire:model="epi_indicaciones_alta" rows="2.5" placeholder="Medicación ambulatoria, reposo, dieta, signos de alarma..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                            </div>
                        </div>

                        <!-- 4. Cierre: Próxima Cita y Observaciones -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Próxima Cita / Control
                                </label>
                                <input type="text" wire:model="epi_proxima_cita" placeholder="Ej. En 7 días por consulta externa, 15/11/2026..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Observaciones
                                </label>
                                <input type="text" wire:model="epi_observaciones" placeholder="Notas adicionales al egreso..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                            <a 
                                href="{{ route('proformas.pdf.epicrisis', $proforma->id) }}" 
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition-colors"
                            >
                                <i class="fas fa-print text-cyan-600"></i>
                                <span>Imprimir Documento PDF</span>
                            </a>
                            <div class="flex items-center gap-2">
                                <button wire:click="cerrarModalEpicrisis" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                    Cancelar
                                </button>
                                <button 
                                    type="submit" 
                                    wire:loading.attr="disabled"
                                    wire:target="guardarEpicrisis"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md shadow-cyan-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    <span wire:loading.remove wire:target="guardarEpicrisis">
                                        <i class="fas fa-save me-1"></i> Guardar Epicrisis
                                    </span>
                                    <span wire:loading wire:target="guardarEpicrisis">
                                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL CIRUGÍAS / DESCARGO DE QUIRÓFANO -->
    @if ($modalCirugiaOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-5xl">
                    <div class="px-6 py-4 bg-rose-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white shadow-xs">
                                <i class="fas fa-procedures text-sm"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">
                                    {{ $cirugia_id_en_edicion ? 'Editar Descargo de Quirófano' : 'Registrar Descargo de Quirófano / Cirugía' }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Expediente #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }} • {{ $proforma->paciente->nombre_completo }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($cirugia_id_en_edicion)
                                <a 
                                    href="{{ route('proformas.pdf.cirugia', [$proforma->id, $cirugia_id_en_edicion]) }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 transition shadow-2xs"
                                    title="Imprimir PDF del Descargo"
                                >
                                    <i class="fas fa-print text-rose-600"></i>
                                    <span>PDF</span>
                                </a>
                            @endif
                            <button wire:click="cerrarModalCirugia" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                                <i class="fas fa-times text-base"></i>
                            </button>
                        </div>
                    </div>

                    <form wire:submit="guardarCirugia" class="p-6 space-y-5 max-h-[82vh] overflow-y-auto">
                        <!-- Referencia rápida del paciente -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex flex-wrap items-center gap-3 text-slate-600 dark:text-slate-300">
                                <span><strong class="text-slate-900 dark:text-white">Paciente:</strong> {{ $proforma->paciente->nombre_completo }}</span>
                                <span>•</span>
                                <span><strong>Sexo:</strong> {{ $proforma->paciente->genero ?? 'N/E' }}</span>
                                <span>•</span>
                                <span><strong>Edad al Ingreso:</strong> {{ $proforma->edad_paciente !== null ? "{$proforma->edad_paciente} años" : 'N/E' }}</span>
                                <span>•</span>
                                <span><strong>N° H.C.:</strong> {{ $proforma->paciente->numero_historia_clinica }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300">
                                    Folio: {{ $cir_numero ?: 'Auto-generado' }}
                                </span>
                            </div>
                        </div>

                        <!-- 1. IDENTIFICACIÓN Y COBERTURA DE LA CIRUGÍA -->
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block mb-3">
                                <i class="fas fa-id-card-alt text-rose-600 dark:text-rose-400 me-1"></i> Identificación y Régimen de la Intervención
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        N° Descargo de Quirófano
                                    </label>
                                    <input type="text" wire:model="cir_numero" placeholder="Ej. CQ-001..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Fecha de la Cirugía *
                                    </label>
                                    <input type="date" wire:model="cir_fecha" required class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Régimen de Cobertura
                                    </label>
                                    <div class="flex items-center gap-4 pt-2">
                                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                            <input type="checkbox" wire:model.live="cir_es_institucional" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                            <span>Institucional</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                            <input type="checkbox" wire:model.live="cir_es_convenio" class="rounded text-purple-600 focus:ring-purple-500 border-slate-300">
                                            <span>Convenio</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                            Nombre del Convenio
                                        </label>
                                        @if ($proforma->paciente->institucion)
                                            <span class="text-[10px] text-purple-600 dark:text-purple-400 font-semibold truncate max-w-[150px]" title="Institución del paciente: {{ $proforma->paciente->institucion->nombre }}">
                                                <i class="fas fa-building me-0.5"></i> {{ $proforma->paciente->institucion->nombre }}
                                            </span>
                                        @endif
                                    </div>
                                    <input 
                                        type="text" 
                                        wire:model="cir_convenio_nombre" 
                                        list="lista_convenios_instituciones"
                                        placeholder="Aseguradora / Empresa..." 
                                        {{ ! $cir_es_convenio ? 'disabled' : '' }} 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs disabled:opacity-50"
                                    >
                                    <datalist id="lista_convenios_instituciones">
                                        @if ($proforma->paciente->institucion)
                                            <option value="{{ $proforma->paciente->institucion->nombre }}">
                                        @endif
                                        @foreach (\App\Models\Institucion::where('estado', 'Activo')->orderBy('nombre')->get() as $inst)
                                            <option value="{{ $inst->nombre }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mt-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Especialidad Quirúrgica
                                    </label>
                                    <input type="text" wire:model="cir_especialidad" placeholder="Ej. Cirugía General, Traumatología..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Sala / Quirófano
                                    </label>
                                    <input type="text" wire:model="cir_sala" placeholder="Ej. Quirófano 1, Sala Operaciones..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Cama / Destino
                                    </label>
                                    <input type="text" wire:model="cir_cama" placeholder="Ej. Cama 4, Recuperación..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Tipo de Cirugía
                                    </label>
                                    <select wire:model="cir_tipo_cirugia" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="Ambulatoria">Ambulatoria</option>
                                        <option value="Menor">Menor</option>
                                        <option value="Media">Media</option>
                                        <option value="Mayor">Mayor</option>
                                        <option value="Especial">Especial</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 2. PROCEDIMIENTO, DIAGNÓSTICO Y HORARIOS -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Cirugía Efectuada (Procedimiento)
                                    </label>
                                    <textarea wire:model="cir_cirugia_efectuada" rows="2" placeholder="Detalle del procedimiento quirúrgico efectuado..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                        Diagnóstico de Operación (Pre y Post)
                                    </label>
                                    <textarea wire:model="cir_diagnostico_operacion" rows="2" placeholder="Diagnóstico operatorio..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                                </div>
                            </div>

                            <!-- Grilla de Tiempos de Quirófano, Anestesia y Cirugía -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 block mb-2">
                                    <i class="fas fa-clock text-rose-500 me-1"></i> Tiempos Quirúrgicos y Técnica Anestésica
                                </span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Ingreso Quirófano</label>
                                        <input type="time" wire:model="cir_hora_ingreso_quirofano" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Salida Quirófano</label>
                                        <input type="time" wire:model="cir_hora_salida_quirofano" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Inicio Anestesia</label>
                                        <input type="time" wire:model="cir_hora_inicio_anestesia" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Fin Anestesia</label>
                                        <input type="time" wire:model="cir_hora_fin_anestesia" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Inicio Cirugía</label>
                                        <input type="time" wire:model="cir_hora_inicio_cirugia" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Fin Cirugía</label>
                                        <input type="time" wire:model="cir_hora_fin_cirugia" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 pt-2 border-t border-slate-200 dark:border-slate-750">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipo de Anestesia</label>
                                        <input type="text" wire:model="cir_tipo_anestesia" placeholder="Ej. General Balanceada, Raquídea, Epidural, Local..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Analgesia Post-Operatoria</label>
                                        <input type="text" wire:model="cir_analgesia" placeholder="Ej. Ketorolaco 30mg + Tramadol 50mg..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 shadow-2xs">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. PERSONAL QUE INTERVIENE (ENCARGADOS) -->
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block mb-3">
                                <i class="fas fa-user-md text-rose-600 dark:text-rose-400 me-1"></i> Personal Quirúrgico Interviniente (Equipo Responsable)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Cirujano (Principal) *</label>
                                    <select wire:model="cir_cirujano_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="">Seleccione Cirujano...</option>
                                        @foreach ($personalClinica as $u)
                                            <option value="{{ $u->id }}">{{ $u->nombre_completo }} ({{ $u->rol?->nombre ?? 'Personal' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Anestesiólogo</label>
                                    <select wire:model="cir_anestesiologo_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="">Seleccione Anestesiólogo...</option>
                                        @foreach ($personalClinica as $u)
                                            <option value="{{ $u->id }}">{{ $u->nombre_completo }} ({{ $u->rol?->nombre ?? 'Personal' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">1er Ayudante</label>
                                    <select wire:model="cir_primer_ayudante_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="">Sin 1er ayudante...</option>
                                        @foreach ($personalClinica as $u)
                                            <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">2do Ayudante</label>
                                    <select wire:model="cir_segundo_ayudante_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="">Sin 2do ayudante...</option>
                                        @foreach ($personalClinica as $u)
                                            <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Instrumentador</label>
                                    <select wire:model="cir_instrumentador_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="">Sin instrumentador...</option>
                                        @foreach ($personalClinica as $u)
                                            <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Circulante</label>
                                    <select wire:model="cir_circulante_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs">
                                        <option value="">Sin circulante...</option>
                                        @foreach ($personalClinica as $u)
                                            <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 4. MEDICAMENTOS E INSUMOS UTILIZADOS (DESCARGO VINCULADO A RECETA) -->
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                        <i class="fas fa-pills text-teal-600 dark:text-teal-400"></i>
                                        <span>Medicamentos e Insumos Utilizados (Anestesiología y Enfermería)</span>
                                    </span>
                                    <p class="text-[11px] text-slate-400">
                                        Seleccione de qué sección se extrajo cada ítem como referencia para la encargada de farmacia.
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300">
                                    {{ count($cola_medicamentos_cirugia) }} ítem(s) en cola
                                </span>
                            </div>

                            <!-- Barra de Adición Rápida de Medicamentos -->
                            <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-750 space-y-2">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                    <!-- Buscador de Medicamento -->
                                    <div class="sm:col-span-4 relative">
                                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Buscar Fármaco / Insumo *</label>
                                        <div class="relative">
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.300ms="buscarMedicamentoCirugia" 
                                                placeholder="Escriba nombre del medicamento..." 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2 shadow-2xs pr-8"
                                            >
                                            <i class="fas fa-search absolute right-2.5 top-2.5 text-slate-400 text-xs"></i>
                                        </div>

                                        <!-- Desplegable predictivo de productos -->
                                        @if (! empty($buscarMedicamentoCirugia) && ! $cir_medicamento_producto_id && $sugerenciasMedicamentosCirugia->count() > 0)
                                            <div class="absolute z-30 left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl divide-y divide-slate-100 dark:divide-slate-800">
                                                @foreach ($sugerenciasMedicamentosCirugia as $p)
                                                    <button 
                                                        type="button" 
                                                        wire:click="seleccionarMedicamentoCirugia({{ $p->id }})" 
                                                        class="w-full text-left px-3 py-2 text-xs hover:bg-rose-50 dark:hover:bg-slate-800 flex items-center justify-between cursor-pointer"
                                                    >
                                                        <div>
                                                            <span class="font-bold text-slate-800 dark:text-slate-100 block">{{ $p->nombre }}</span>
                                                            <span class="text-[10px] text-slate-400">{{ $p->marca?->nombre ?? 'Sin marca' }}</span>
                                                        </div>
                                                        <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                                            Stock: {{ $p->lotes->sum('cantidad_actual') }}
                                                        </span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Hoja / Área -->
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Hoja / Área</label>
                                        <select wire:model="cir_medicamento_area" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2 shadow-2xs">
                                            <option value="Anestesiología">Anestesiología</option>
                                            <option value="Enfermería">Enfermería</option>
                                        </select>
                                    </div>

                                    <!-- Sección de Procedencia -->
                                    <div class="sm:col-span-3">
                                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Sección de Origen *</label>
                                        <select wire:model="cir_medicamento_seccion_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2 shadow-2xs">
                                            @foreach ($seccionesSucursal as $sec)
                                                <option value="{{ $sec->id }}">{{ $sec->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="sm:col-span-1">
                                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">Cant.</label>
                                        <input type="number" min="1" wire:model="cir_medicamento_cantidad" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2 shadow-2xs text-center font-bold">
                                    </div>

                                    <!-- Precio Unitario (Bs.) -->
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5">P. Unit. (Bs.)</label>
                                        <input type="number" step="0.01" min="0" wire:model="cir_medicamento_costo_unitario" placeholder="0.00" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2 shadow-2xs text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <input type="text" wire:model="cir_medicamento_indicaciones" placeholder="Indicaciones o notas posológicas opcionales..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-850 p-2 shadow-2xs">
                                    <button 
                                        type="button" 
                                        wire:click="agregarMedicamentoACirugia" 
                                        class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shrink-0 shadow-xs cursor-pointer flex items-center gap-1.5"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Agregar</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla Acumulativa de Medicamentos e Insumos en la Cirugía -->
                            @if (count($cola_medicamentos_cirugia) > 0)
                                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-750 max-h-56 overflow-y-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] uppercase sticky top-0">
                                            <tr>
                                                <th class="py-2 px-3">Área</th>
                                                <th class="py-2 px-3">Producto / Medicamento</th>
                                                <th class="py-2 px-3">Sección Origen</th>
                                                <th class="py-2 px-3 text-center">Cant.</th>
                                                <th class="py-2 px-3 text-right">Costo Ref.</th>
                                                <th class="py-2 px-3 text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 font-mono text-[11px]">
                                            @foreach ($cola_medicamentos_cirugia as $idx => $m)
                                                <tr>
                                                    <td class="py-1.5 px-3 font-sans">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $m['area'] === 'Anestesiología' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300' }}">
                                                            {{ $m['area'] }}
                                                        </span>
                                                    </td>
                                                    <td class="py-1.5 px-3 font-sans font-medium text-slate-800 dark:text-slate-200">
                                                        {{ $m['producto_nombre'] }}
                                                        @if (! empty($m['indicaciones']))
                                                            <span class="text-[10px] text-slate-400 block font-normal">{{ $m['indicaciones'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 px-3 font-sans text-slate-500 dark:text-slate-400">
                                                        {{ $m['seccion_nombre'] }}
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center font-bold text-slate-800 dark:text-slate-100">
                                                        {{ $m['cantidad'] }}
                                                    </td>
                                                    <td class="py-1.5 px-3 text-right text-slate-600 dark:text-slate-400">
                                                        Bs. {{ number_format($m['costo_unitario'], 2) }}
                                                    </td>
                                                    <td class="py-1.5 px-3 text-center font-sans">
                                                        <button 
                                                            type="button" 
                                                            wire:click="quitarMedicamentoDeCirugia({{ $idx }})" 
                                                            class="text-rose-500 hover:text-rose-700 p-1 cursor-pointer"
                                                            title="Quitar"
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

                        <!-- 5. EQUIPOS ESPECIALES Y SOLICITUDES (HOJA DE ENFERMERÍA) -->
                        <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block">
                                <i class="fas fa-microchip text-rose-600 dark:text-rose-400 me-1"></i> Equipos Especiales y Solicitudes de Quirófano (Hoja de Enfermería)
                            </span>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 text-xs">
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_torre_laparoscopia" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Torre Laparoscopía</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_bomba_infusion" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Bomba de Infusión</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_maquina_aspiracion" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Máquina de Aspiración</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_maquina_anestesiologia" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Máq. Anestesiología</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_monitor_multiparametrico" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Monitor Multiparamétrico</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_ecografo" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Ecógrafo</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_rayos_x" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Rayos X</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_oxigeno_central" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Oxígeno Central</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_electrobisturi" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Electrobisturí</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" wire:model="cir_intensificador_imagenes" class="rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                                    <span>Intensificador de Imágenes</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Uso Material - Importadora</label>
                                    <input type="text" wire:model="cir_material_importadora" placeholder="Detalle de importadora..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Patología</label>
                                    <input type="text" wire:model="cir_patologia" placeholder="Muestras a patología..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Otros Equipos / Observaciones</label>
                                    <input type="text" wire:model="cir_otros_equipos" placeholder="Equipamiento adicional..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                                </div>
                            </div>
                        </div>

                        <!-- 6. OBSERVACIONES -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Observaciones Generales
                            </label>
                            <textarea wire:model="cir_observaciones" rows="2" placeholder="Notas de la cirugía o descargo..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                            <button wire:click="cerrarModalCirugia" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarCirugia"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarCirugia">
                                    <i class="fas fa-save me-1"></i> Guardar Descargo de Quirófano
                                </span>
                                <span wire:loading wire:target="guardarCirugia">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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
                                            class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
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
                                                <button 
                                                    type="button" 
                                                    wire:click="eliminarServicioDeCola({{ $idx }})" 
                                                    wire:loading.attr="disabled"
                                                    class="text-slate-400 hover:text-rose-600 p-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                    title="Quitar de la lista"
                                                >
                                                    <i class="fas fa-times" wire:loading.remove wire:target="eliminarServicioDeCola({{ $idx }})"></i>
                                                    <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarServicioDeCola({{ $idx }})"></i>
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
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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

                            <div
                                x-data="{ uploading: false, progress: 0 }"
                                x-on:livewire-upload-start="uploading = true"
                                x-on:livewire-upload-finish="uploading = false"
                                x-on:livewire-upload-error="uploading = false"
                                x-on:livewire-upload-progress="progress = $event.detail.progress"
                            >
                                <label for="nuevo_solicitud_archivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Adjuntar Archivo Digital (PDF / JPG - Opcional)
                                </label>
                                <input 
                                    type="file" 
                                    id="nuevo_solicitud_archivo" 
                                    wire:model="nuevo_solicitud_archivo" 
                                    accept="application/pdf,image/*" 
                                    class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-300 p-1.5 border border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer"
                                >

                                <div x-show="uploading" class="mt-1" style="display: none;">
                                    <span class="text-[11px] text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1">
                                        <i class="fas fa-spinner fa-spin"></i> Subiendo archivo (<span x-text="progress + '%'"></span>)...
                                    </span>
                                </div>

                                @if ($nuevo_solicitud_archivo && ! $errors->has('nuevo_solicitud_archivo'))
                                    <div class="mt-1.5 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 font-medium">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Archivo listo: {{ method_exists($nuevo_solicitud_archivo, 'getClientOriginalName') ? $nuevo_solicitud_archivo->getClientOriginalName() : 'Adjunto cargado' }}</span>
                                    </div>
                                @endif
                                @error('nuevo_solicitud_archivo') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end pt-1">
                                <button 
                                    type="button" 
                                    wire:click="agregarSolicitudACola" 
                                    wire:loading.attr="disabled"
                                    wire:target="nuevo_solicitud_archivo, agregarSolicitudACola"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-300 text-xs font-bold border border-blue-200 dark:border-blue-800 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
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
                                            <button 
                                                type="button" 
                                                wire:click="eliminarSolicitudDeCola({{ $idx }})" 
                                                wire:loading.attr="disabled"
                                                class="text-slate-400 hover:text-rose-600 p-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                title="Quitar de la lista"
                                            >
                                                <i class="fas fa-times" wire:loading.remove wire:target="eliminarSolicitudDeCola({{ $idx }})"></i>
                                                <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarSolicitudDeCola({{ $idx }})"></i>
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
                                wire:target="nuevo_solicitud_archivo, agregarSolicitud"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="nuevo_solicitud_archivo, agregarSolicitud">
                                    <i class="fas fa-save me-1"></i> Guardar {{ count($cola_solicitudes) > 1 ? count($cola_solicitudes).' Solicitudes' : 'Solicitud' }}
                                </span>
                                <span wire:loading wire:target="nuevo_solicitud_archivo, agregarSolicitud">
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
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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

                        <div 
                            x-data="{ uploading: false, progress: 0 }"
                            x-on:livewire-upload-start="uploading = true"
                            x-on:livewire-upload-finish="uploading = false"
                            x-on:livewire-upload-error="uploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress"
                        >
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

                            <!-- Barra de progreso interactiva Alpine -->
                            <div x-show="uploading" class="mt-2" style="display: none;">
                                <div class="flex items-center justify-between text-xs text-blue-600 dark:text-blue-400 mb-1">
                                    <span class="flex items-center gap-1.5 font-medium">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando archivo...
                                    </span>
                                    <span x-text="progress + '%'" class="font-bold"></span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-150" :style="'width: ' + progress + '%'"></div>
                                </div>
                            </div>

                            @if ($solicitud_nuevo_archivo && ! $errors->has('solicitud_nuevo_archivo'))
                                <div class="mt-2 p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                                    <span class="truncate flex items-center gap-2 font-medium">
                                        <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                                        {{ method_exists($solicitud_nuevo_archivo, 'getClientOriginalName') ? $solicitud_nuevo_archivo->getClientOriginalName() : 'Archivo preparado' }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 shrink-0">
                                        {{ method_exists($solicitud_nuevo_archivo, 'getSize') ? round($solicitud_nuevo_archivo->getSize() / 1024, 1) . ' KB' : '' }}
                                    </span>
                                </div>
                            @endif

                            @error('solicitud_nuevo_archivo') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalSubirArchivo" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="solicitud_nuevo_archivo, guardarArchivoSolicitud"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="solicitud_nuevo_archivo, guardarArchivoSolicitud">
                                    <i class="fas fa-cloud-arrow-up me-1"></i> Guardar Archivo
                                </span>
                                <span wire:loading wire:target="solicitud_nuevo_archivo, guardarArchivoSolicitud">
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
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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
                                            <button 
                                                type="button" 
                                                wire:click="eliminarEventoDeCola({{ $idx }})" 
                                                wire:loading.attr="disabled"
                                                class="text-slate-400 hover:text-rose-600 p-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                title="Quitar de la lista"
                                            >
                                                <i class="fas fa-times" wire:loading.remove wire:target="eliminarEventoDeCola({{ $idx }})"></i>
                                                <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarEventoDeCola({{ $idx }})"></i>
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
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
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

                        <!-- Sección: Búsqueda y Selección de Medicamento con Control de Stock -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-teal-700 dark:text-teal-400 block flex items-center justify-between">
                                <span><i class="fas fa-pills me-1"></i> Agregar Medicamento a la Receta</span>
                                <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400">Control estricto de existencias</span>
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
                            @else
                                <div class="relative">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                        Buscar Medicamento en Farmacia <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.300ms="buscarMedicamentoReceta" 
                                            placeholder="Escriba el nombre comercial, principio activo o marca del fármaco..." 
                                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                        >
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

                            @error('receta_nuevo_producto_id')
                                <p class="text-[11px] text-rose-500">{{ $message }}</p>
                            @enderror

                            <!-- Entradas: Cantidad, Horarios y Botón Agregar -->
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1">
                                <div class="sm:col-span-3">
                                    <label for="receta_nuevo_cantidad" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                        Cantidad <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="receta_nuevo_cantidad" 
                                        min="1" 
                                        wire:model="receta_nuevo_cantidad" 
                                        placeholder="Cant." 
                                        class="w-full text-xs font-bold text-center rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2 shadow-2xs"
                                    >
                                    @error('receta_nuevo_cantidad')
                                        <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="receta_nuevo_indicaciones" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                                        Horarios / Posología / Indicación <span class="text-rose-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="receta_nuevo_indicaciones" 
                                        wire:model="receta_nuevo_indicaciones" 
                                        placeholder="Ej. 1 comp cada 8 hrs por 5 días vía oral..." 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2 shadow-2xs"
                                    >
                                    @error('receta_nuevo_indicaciones')
                                        <p class="text-[10px] text-rose-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3 flex items-end">
                                    <button 
                                        type="button" 
                                        wire:click="agregarMedicamentoAReceta" 
                                        wire:loading.attr="disabled"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm shadow-teal-500/20 hover:shadow transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Agregar</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Medicamentos Acumulados en la Receta -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    Medicamentos Agregados a la Receta
                                    <span class="font-mono text-teal-600 dark:text-teal-400">({{ count($receta_medicamentos) }})</span>
                                </label>
                                <span class="text-[11px] text-slate-400">Estos ítems se prescribirán para el paciente</span>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-800">
                                        <tr>
                                            <th class="py-2.5 px-3 w-8 text-center">#</th>
                                            <th class="py-2.5 px-3">Medicamento</th>
                                            <th class="py-2.5 px-3 w-20 text-center">Cant.</th>
                                            <th class="py-2.5 px-3">Indicaciones / Horario</th>
                                            <th class="py-2.5 px-3 w-14 text-center">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                        @forelse ($receta_medicamentos as $idx => $medItem)
                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="py-2.5 px-3 text-center text-slate-400 font-mono text-[11px]">{{ $idx + 1 }}</td>
                                                <td class="py-2.5 px-3">
                                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ $medItem['producto_nombre'] ?? 'Medicamento' }}</div>
                                                    <div class="text-[10px] text-slate-400 font-mono">{{ $medItem['unidad_medida'] ?? 'Unidad' }}</div>
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-mono font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                                        {{ $medItem['cantidad'] }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                                    {{ $medItem['indicaciones'] }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <button 
                                                        type="button" 
                                                        wire:click="removerMedicamentoReceta({{ $idx }})" 
                                                        class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded transition-colors cursor-pointer"
                                                        title="Quitar fármaco de la lista"
                                                    >
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="py-8 text-center text-slate-400">
                                                    <i class="fas fa-prescription-bottle-alt text-2xl mb-1 text-slate-300 dark:text-slate-600"></i>
                                                    <p class="font-medium text-slate-600 dark:text-slate-400">No se han agregado medicamentos a la receta todavía</p>
                                                    <p class="text-[11px] text-slate-400 mt-0.5">Busque un producto arriba, indique la cantidad y posología, y presione "Agregar".</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @error('receta_medicamentos')
                                <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
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
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-boxes-stacked text-amber-600"></i>
                            Cargar Consumos de Insumos Hospitalarios
                        </h3>
                        <button wire:click="cerrarModalConsumo" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="registrarConsumoExtra" class="p-6 space-y-4">
                        <!-- Selector de Área / Sección de Origen -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-layer-group text-amber-500"></i>
                                    Área / Sección de Origen (Descargo de Stock) <span class="text-rose-500">*</span>
                                </span>
                                <span class="text-[10px] text-amber-600 font-semibold lowercase">existencias por área</span>
                            </label>
                            <select 
                                wire:model.live="consumo_seccion_id" 
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-amber-500 font-medium"
                            >
                                @foreach ($seccionesSucursal as $sec)
                                    <option value="{{ $sec->id }}">
                                        {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Almacén Principal)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
                                            <div class="text-[11px] text-amber-700 dark:text-amber-400 flex flex-wrap items-center gap-2 mt-0.5">
                                                <span>Unidad: {{ $productoConsumoSeleccionado->unidad_medida ?? 'Unidad' }} • Precio Sugerido: Bs. {{ number_format($productoConsumoSeleccionado->ultimo_precio_venta, 2) }}</span>
                                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800 text-[10px]">
                                                    <i class="fas fa-boxes"></i> Stock en Área: {{ $consumo_stock_disponible }}
                                                </span>
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
                                        Buscar Insumo / Material Descartable (Con Stock en Área Seleccionada)
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.250ms="buscarInsumoConsumo" 
                                            placeholder="Escriba para buscar por nombre o descripción..." 
                                            class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                        >
                                        @if ($buscarInsumoConsumo)
                                            <button type="button" wire:click="$set('buscarInsumoConsumo', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Resultados reactivos de insumos con stock -->
                                    <div class="max-h-44 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg divide-y divide-slate-100 dark:divide-slate-700/60">
                                        @forelse ($productosParaConsumo as $pCons)
                                            @php
                                                $stkEnSec = $pCons->lotes->sum(fn ($l) => $l->stockEnSeccion($consumo_seccion_id));
                                            @endphp
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
                                                    <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                        <i class="fas fa-boxes text-[9px] me-0.5"></i> Stock en área: {{ $stkEnSec }} {{ $pCons->unidad_medida ?? 'u.' }}
                                                    </span>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="p-3 text-center text-xs text-slate-400">
                                                <i class="fas fa-box-open me-1"></i> No se encontraron insumos hospitalarios con stock disponible en esta área.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="consumo_cantidad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                            Cantidad
                                        </label>
                                        @if ($consumo_producto_id)
                                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                Máx: {{ $consumo_stock_disponible }}
                                            </span>
                                        @endif
                                    </div>
                                    <input type="number" min="1" max="{{ $consumo_stock_disponible > 0 ? $consumo_stock_disponible : '' }}" id="consumo_cantidad" wire:model.live="consumo_cantidad" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-amber-500 p-2 shadow-2xs @error('consumo_cantidad') border-rose-500 @enderror">
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
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                        <i class="fas fa-layer-group text-[8px]"></i> {{ $cc['seccion_nombre'] ?? 'Área' }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-mono">
                                                    Bs. {{ number_format($cc['precio_unitario'] * $cc['cantidad'], 2) }}
                                                    @if (!empty($cc['observaciones']))
                                                        • <span class="text-slate-500">{{ $cc['observaciones'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button 
                                                type="button" 
                                                wire:click="eliminarConsumoDeCola({{ $idx }})" 
                                                wire:loading.attr="disabled"
                                                class="text-slate-400 hover:text-rose-600 p-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                title="Quitar de la lista"
                                            >
                                                <i class="fas fa-times" wire:loading.remove wire:target="eliminarConsumoDeCola({{ $idx }})"></i>
                                                <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarConsumoDeCola({{ $idx }})"></i>
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
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
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
                        <!-- Selector Global de Sección por Defecto para Despacho -->
                        <div class="p-3 rounded-xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-teal-900 dark:text-teal-200">Área / Botiquín Sugerido por Defecto</div>
                                    <div class="text-[11px] text-teal-700 dark:text-teal-400">Aplica automáticamente esta sección a los fármacos de la receta</div>
                                </div>
                            </div>
                            <div class="sm:w-64">
                                <select 
                                    wire:model.live="despacho_seccion_defecto_id" 
                                    class="w-full text-xs rounded-lg border-teal-300 dark:border-teal-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-teal-500 font-medium"
                                >
                                    @foreach ($seccionesSucursal as $sec)
                                        <option value="{{ $sec->id }}">
                                            {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Principal)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

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
                                                <th class="py-2.5 px-3 w-40">Área Origen</th>
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
                                                        <select 
                                                            wire:change="cambiarSeccionItemDespacho({{ $detId }}, $event.target.value)"
                                                            class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 focus:ring-2 focus:ring-teal-500 font-medium"
                                                        >
                                                            @foreach ($seccionesSucursal as $sec)
                                                                <option value="{{ $sec->id }}" {{ ($item['seccion_id'] ?? null) == $sec->id ? 'selected' : '' }}>
                                                                    {{ $sec->nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="py-2.5 px-3">
                                                        @php
                                                            $lotesDisp = $lotesDisponiblesPorItem[$detId] ?? collect();
                                                        @endphp
                                                        @if ($lotesDisp->isEmpty())
                                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-500 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded border border-rose-200 dark:border-rose-900/50">
                                                                <i class="fas fa-times-circle"></i> Sin stock en área
                                                            </span>
                                                        @else
                                                            <select 
                                                                wire:model.live="despachosItems.{{ $detId }}.lote_id"
                                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-1.5 focus:ring-2 focus:ring-teal-500"
                                                            >
                                                                @foreach ($lotesDisp as $lt)
                                                                    <option value="{{ $lt->id }}">
                                                                        Lote: {{ $lt->codigo_lote }} (Disp: {{ $lt->stockEnSeccion($item['seccion_id'] ?? 0) }} {{ $item['unidad_medida'] }}) - Vto: {{ $lt->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }}
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
                                <!-- Selector de Área para Extras -->
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-layer-group text-amber-500"></i>
                                            Área / Botiquín de Salida <span class="text-rose-500">*</span>
                                        </span>
                                        <span class="text-[10px] text-amber-600 lowercase font-medium">stock en esta sección</span>
                                    </label>
                                    <select 
                                        wire:model.live="despacho_extra_seccion_id" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-amber-500 font-medium"
                                    >
                                        @foreach ($seccionesSucursal as $sec)
                                            <option value="{{ $sec->id }}">
                                                {{ $sec->nombre }} {{ $sec->es_almacen_principal ? '(Almacén Principal)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

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
                                                Lote con Stock en Área <span class="text-rose-500">*</span>
                                            </label>
                                            <select 
                                                wire:model.live="despacho_extra_lote_id" 
                                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2 focus:ring-2 focus:ring-amber-500"
                                            >
                                                @forelse ($lotesParaDespachoExtra as $ltExtra)
                                                    <option value="{{ $ltExtra->id }}">
                                                        Lote: {{ $ltExtra->codigo_lote }} (Disp: {{ $ltExtra->stockEnSeccion($despacho_extra_seccion_id) }}) - Vto: {{ $ltExtra->fecha_vencimiento?->format('d/m/Y') ?? 'S/F' }} - Bs. {{ number_format($productoDespachoExtraSeleccionado->ultimo_precio_venta ?? 0, 2) }}
                                                    </option>
                                                @empty
                                                    <option value="">Sin lotes con stock en esta área</option>
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
                                            Buscar Insumo o Medicamento Extra (con stock en área seleccionada)
                                        </label>
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input 
                                                type="text" 
                                                wire:model.live.debounce.250ms="buscarDespachoExtraProducto" 
                                                placeholder="Escriba para buscar medicamento o insumo con existencias..." 
                                                class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs"
                                            >
                                            @if ($buscarDespachoExtraProducto)
                                                <button type="button" wire:click="$set('buscarDespachoExtraProducto', '')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs p-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="max-h-40 overflow-y-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-md divide-y divide-slate-100 dark:divide-slate-700/60">
                                            @forelse ($productosParaDespachoExtras as $pExt)
                                                @php
                                                    $stkExtraSec = $pExt->lotes->sum(fn ($l) => $l->stockEnSeccion($despacho_extra_seccion_id));
                                                @endphp
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
                                                            {{ $stkExtraSec }} en área
                                                        </span>
                                                    </div>
                                                </button>
                                            @empty
                                                <div class="p-3 text-center text-xs text-slate-400">
                                                    No se encontraron productos con existencias disponibles en esta área.
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
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                            <i class="fas fa-layer-group text-[8px]"></i> {{ $ex['seccion_nombre'] ?? 'Área' }}
                                                        </span>
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
                                                    <button 
                                                        type="button" 
                                                        wire:click="eliminarDespachoExtraItem({{ $idx }})" 
                                                        wire:loading.attr="disabled"
                                                        class="text-slate-400 hover:text-rose-600 p-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                        title="Quitar ítem extra"
                                                    >
                                                        <i class="fas fa-times" wire:loading.remove wire:target="eliminarDespachoExtraItem({{ $idx }})"></i>
                                                        <i class="fas fa-spinner fa-spin text-xs" wire:loading wire:target="eliminarDespachoExtraItem({{ $idx }})"></i>
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

    <!-- ======================================================================= -->
    <!-- MODAL: ASIGNAR / MODIFICAR HONORARIO MÉDICO -->
    <!-- ======================================================================= -->
    @if ($modalPagoMedicoOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <!-- Header -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-hand-holding-medical text-violet-600 dark:text-violet-400"></i>
                            <span>{{ $editando_pago_medico_id ? 'Modificar Honorario Médico' : 'Asignar Honorario a Médico' }}</span>
                        </h3>
                        <button wire:click="cerrarModalPagoMedico" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <form wire:submit="guardarPagoMedico" class="p-6 space-y-4">
                        <!-- Selección Rápida de Médicos Asignados -->
                        @if ($proforma->medicos->isNotEmpty())
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Médicos de la Proforma (Acceso Rápido)
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($proforma->medicos as $m)
                                        <button 
                                            type="button" 
                                            wire:click="$set('pago_medico_id', {{ $m->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition cursor-pointer {{ $pago_medico_id === $m->id ? 'bg-violet-600 text-white border-violet-600 shadow-xs' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:border-violet-400' }}"
                                        >
                                            <i class="fas fa-user-md text-[11px]"></i>
                                            <span>Dr(a). {{ $m->nombre_completo }}</span>
                                            @if ($m->especialidad)
                                                <span class="text-[10px] opacity-75">({{ $m->especialidad->nombre }})</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Selector General de Médico -->
                        <div>
                            <label for="pago_medico_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Médico Destinatario <span class="text-rose-500">*</span>
                            </label>
                            <select 
                                id="pago_medico_id" 
                                wire:model="pago_medico_id" 
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                            >
                                <option value="">-- Seleccione el médico --</option>
                                @foreach ($medicos as $medOption)
                                    <option value="{{ $medOption->id }}">
                                        Dr(a). {{ $medOption->nombre_completo }} ({{ $medOption->especialidad->nombre ?? 'Medicina General' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('pago_medico_id')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Selección de Caja y Canal de Desembolso -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i class="fas fa-cash-register text-emerald-500"></i>
                                    Origen de Fondos (Caja y Canal de Desembolso)
                                </span>
                                @if ($this->saldoCajaSeleccionada > 0)
                                    <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                        Disp. {{ $pago_medico_tipo_pago }}: Bs. {{ number_format($this->saldoCajaSeleccionada, 2) }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Caja Abierta -->
                                <div>
                                    <label for="pago_medico_caja_id" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                        Caja de Desembolso <span class="text-rose-500">*</span>
                                    </label>
                                    <select 
                                        id="pago_medico_caja_id" 
                                        wire:model.live="pago_medico_caja_id" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2 shadow-2xs"
                                    >
                                        <option value="">-- Seleccionar Caja Abierta --</option>
                                        @foreach ($this->cajasAbiertas as $cAbierta)
                                            <option value="{{ $cAbierta->id }}">
                                                Caja #{{ $cAbierta->id }} - {{ $cAbierta->user->nombre_completo ?? 'Usuario' }} (Apertura: {{ $cAbierta->fecha_apertura?->format('d/m/Y H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pago_medico_caja_id')
                                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                    @if ($this->cajasAbiertas->isEmpty())
                                        <p class="text-amber-600 dark:text-amber-400 text-[11px] mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i> No hay cajas abiertas en el sistema para registrar el egreso.
                                        </p>
                                    @endif
                                </div>

                                <!-- Tipo de Pago / Canal -->
                                <div>
                                    <label for="pago_medico_tipo_pago" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                        Método de Pago / Canal <span class="text-rose-500">*</span>
                                    </label>
                                    <select 
                                        id="pago_medico_tipo_pago" 
                                        wire:model.live="pago_medico_tipo_pago" 
                                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2 shadow-2xs"
                                    >
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Transferencia">Transferencia Bancaria</option>
                                        <option value="QR">Pago QR</option>
                                    </select>
                                    @error('pago_medico_tipo_pago')
                                        <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- N° Referencia / Comprobante de Desembolso -->
                            <div>
                                <label for="pago_medico_numero_referencia" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                                    N° de Comprobante / Referencia Bancaria
                                    @if (in_array($pago_medico_tipo_pago, ['Transferencia', 'QR']))
                                        <span class="text-rose-500">*</span>
                                    @else
                                        <span class="text-slate-400 font-normal">(Opcional para efectivo)</span>
                                    @endif
                                </label>
                                <input 
                                    type="text" 
                                    id="pago_medico_numero_referencia" 
                                    wire:model="pago_medico_numero_referencia" 
                                    placeholder="{{ in_array($pago_medico_tipo_pago, ['Transferencia', 'QR']) ? 'Ej. REF-849204, Transf. Banco Unión...' : 'Ej. Recibo N° 00451' }}"
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2 shadow-2xs"
                                >
                                @error('pago_medico_numero_referencia')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Fila: Monto y Fecha -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="pago_medico_monto" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Monto de Honorario (Bs.) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-mono font-bold text-slate-400">
                                        Bs.
                                    </span>
                                    <input 
                                        type="number" 
                                        id="pago_medico_monto" 
                                        wire:model="pago_medico_monto" 
                                        step="0.01" 
                                        min="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-9 pr-3 text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                                    >
                                </div>
                                @error('pago_medico_monto')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="pago_medico_fecha" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha de Liquidación / Pago <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    id="pago_medico_fecha" 
                                    wire:model="pago_medico_fecha" 
                                    class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs"
                                >
                                @error('pago_medico_fecha')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Observaciones / Ponderación -->
                        <div>
                            <label for="pago_medico_observaciones" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Detalle / Ponderación de Servicios Realizados
                            </label>
                            <textarea 
                                id="pago_medico_observaciones" 
                                wire:model="pago_medico_observaciones" 
                                rows="3"
                                placeholder="Indique el criterio o los procedimientos considerados (ej. 50% de la apendicectomía realizada, atención médica integral, honorarios de interconsulta, etc.)..."
                                class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-violet-500 p-2.5 shadow-2xs resize-none"
                            ></textarea>
                            @error('pago_medico_observaciones')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Resumen de Servicios de la Proforma (Referencia en Modal) -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
                            <span class="font-bold text-[11px] uppercase tracking-wider text-slate-600 dark:text-slate-300 block mb-1.5 flex items-center justify-between">
                                <span><i class="fas fa-clipboard-list text-indigo-500 me-1"></i> Procedimientos Clínicos en este Expediente</span>
                                <span class="font-mono text-indigo-600 dark:text-indigo-400">Total: Bs. {{ number_format($subtotalServicios, 2) }}</span>
                            </span>
                            <div class="max-h-28 overflow-y-auto space-y-1 divide-y divide-slate-200 dark:divide-slate-700/60 text-[11px]">
                                @forelse ($proforma->servicios as $s)
                                    <div class="pt-1 first:pt-0 flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-300 truncate me-2">
                                            • {{ $s->servicio->nombre ?? 'Servicio' }}
                                            @if ($s->observaciones)
                                                <em class="text-slate-400">({{ $s->observaciones }})</em>
                                            @endif
                                        </span>
                                        <span class="font-mono font-semibold text-slate-800 dark:text-slate-200 shrink-0">
                                            Bs. {{ number_format($s->costo_final, 2) }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-slate-400 italic">Sin servicios registrados en la proforma</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="cerrarModalPagoMedico" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold shadow-md shadow-violet-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarPagoMedico">
                                    <i class="fas fa-check-circle me-1"></i> Guardar Honorario
                                </span>
                                <span wire:loading wire:target="guardarPagoMedico">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL: HOJA DE EVOLUCIÓN CLÍNICA -->
    <!-- ======================================================================= -->
    @if ($modalEvolucionOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-notes-medical text-sky-600"></i>
                            {{ $evolucion_id ? 'Editar Nota de Evolución' : 'Registrar Nota de Evolución' }}
                        </h3>
                        <button wire:click="cerrarModalEvolucion" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarEvolucion" class="p-6 space-y-4">
                        <div>
                            <label for="evolucion_fecha_hora" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Fecha y Hora del Registro <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="evolucion_fecha_hora" 
                                wire:model="evolucion_fecha_hora" 
                                class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-500 p-2.5 shadow-2xs"
                            >
                            @error('evolucion_fecha_hora')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nota Clínica / Evolución del Paciente <span class="text-rose-500">*</span>
                            </label>
                            <div 
                                wire:ignore
                                x-data="{
                                    init() {
                                        let editor = null;
                                        const startEditor = () => {
                                            if (typeof ClassicEditor === 'undefined') {
                                                setTimeout(startEditor, 60);
                                                return;
                                            }
                                            ClassicEditor
                                                .create(this.$refs.ckEditorEvolucion, {
                                                    toolbar: [
                                                        'heading', '|',
                                                        'bold', 'italic', '|',
                                                        'bulletedList', 'numberedList', '|',
                                                        'undo', 'redo'
                                                    ]
                                                })
                                                .then(newEditor => {
                                                    editor = newEditor;
                                                    this.$el._ckeditor = newEditor;
                                                    const initial = @this.get('evolucion_descripcion') || '';
                                                    if (initial) {
                                                        editor.setData(initial);
                                                    }
                                                    editor.model.document.on('change:data', () => {
                                                        @this.set('evolucion_descripcion', editor.getData(), false);
                                                    });
                                                })
                                                .catch(err => console.error('Error CKEditor Evolución:', err));
                                        };
                                        startEditor();

                                        $watch('$wire.evolucion_descripcion', (val) => {
                                            if (editor && editor.getData() !== (val || '')) {
                                                editor.setData(val || '');
                                            }
                                        });
                                    },
                                    destroy() {
                                        if (this.$el._ckeditor) {
                                            this.$el._ckeditor.destroy();
                                            this.$el._ckeditor = null;
                                        }
                                    }
                                }"
                                class="mt-1"
                            >
                                <textarea x-ref="ckEditorEvolucion" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 min-h-[160px]"></textarea>
                            </div>
                            @error('evolucion_descripcion')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="cerrarModalEvolucion" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-md shadow-sky-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarEvolucion">
                                    <i class="fas fa-check-circle me-1"></i> Guardar Evolución
                                </span>
                                <span wire:loading wire:target="guardarEvolucion">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL: HOJA DE INDICACIONES MÉDICAS -->
    <!-- ======================================================================= -->
    @if ($modalIndicacionOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-cyan-600"></i>
                            {{ $indicacion_id ? 'Editar Indicación Médica' : 'Registrar Indicación Médica' }}
                        </h3>
                        <button wire:click="cerrarModalIndicacion" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarIndicacion" class="p-6 space-y-4">
                        <div>
                            <label for="indicacion_fecha_hora" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Fecha y Hora de la Indicación <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="indicacion_fecha_hora" 
                                wire:model="indicacion_fecha_hora" 
                                class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 p-2.5 shadow-2xs"
                            >
                            @error('indicacion_fecha_hora')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Esquema Terapéutico / Indicaciones <span class="text-rose-500">*</span>
                            </label>
                            <div 
                                wire:ignore
                                x-data="{
                                    init() {
                                        let editor = null;
                                        const startEditor = () => {
                                            if (typeof ClassicEditor === 'undefined') {
                                                setTimeout(startEditor, 60);
                                                return;
                                            }
                                            ClassicEditor
                                                .create(this.$refs.ckEditorIndicacion, {
                                                    toolbar: [
                                                        'heading', '|',
                                                        'bold', 'italic', '|',
                                                        'bulletedList', 'numberedList', '|',
                                                        'undo', 'redo'
                                                    ]
                                                })
                                                .then(newEditor => {
                                                    editor = newEditor;
                                                    this.$el._ckeditor = newEditor;
                                                    const initial = @this.get('indicacion_descripcion') || '';
                                                    if (initial) {
                                                        editor.setData(initial);
                                                    }
                                                    editor.model.document.on('change:data', () => {
                                                        @this.set('indicacion_descripcion', editor.getData(), false);
                                                    });
                                                })
                                                .catch(err => console.error('Error CKEditor Indicación:', err));
                                        };
                                        startEditor();

                                        $watch('$wire.indicacion_descripcion', (val) => {
                                            if (editor && editor.getData() !== (val || '')) {
                                                editor.setData(val || '');
                                            }
                                        });
                                    },
                                    destroy() {
                                        if (this.$el._ckeditor) {
                                            this.$el._ckeditor.destroy();
                                            this.$el._ckeditor = null;
                                        }
                                    }
                                }"
                                class="mt-1"
                            >
                                <textarea x-ref="ckEditorIndicacion" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 min-h-[160px]"></textarea>
                            </div>
                            @error('indicacion_descripcion')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="cerrarModalIndicacion" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-md shadow-cyan-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarIndicacion">
                                    <i class="fas fa-check-circle me-1"></i> Guardar Indicación
                                </span>
                                <span wire:loading wire:target="guardarIndicacion">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================================= -->
    <!-- MODAL: HOJA DE INTERNACIÓN -->
    <!-- ======================================================================= -->
    @if ($modalInternacionOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="fas fa-bed text-indigo-600"></i>
                            {{ $internacion_id ? 'Editar Registro de Internación' : 'Nuevo Registro de Internación' }}
                        </h3>
                        <button wire:click="cerrarModalInternacion" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg cursor-pointer">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarInternacion" class="p-6 space-y-4">
                        <div>
                            <label for="internacion_fecha_hora" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Fecha y Hora del Registro <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="internacion_fecha_hora" 
                                wire:model="internacion_fecha_hora" 
                                class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 p-2.5 shadow-2xs"
                            >
                            @error('internacion_fecha_hora')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nota / Reporte de Internación Hospitalaria <span class="text-rose-500">*</span>
                            </label>
                            <div 
                                wire:ignore
                                x-data="{
                                    init() {
                                        let editor = null;
                                        const startEditor = () => {
                                            if (typeof ClassicEditor === 'undefined') {
                                                setTimeout(startEditor, 60);
                                                return;
                                            }
                                            ClassicEditor
                                                .create(this.$refs.ckEditorInternacion, {
                                                    toolbar: [
                                                        'heading', '|',
                                                        'bold', 'italic', '|',
                                                        'bulletedList', 'numberedList', '|',
                                                        'undo', 'redo'
                                                    ]
                                                })
                                                .then(newEditor => {
                                                    editor = newEditor;
                                                    this.$el._ckeditor = newEditor;
                                                    const initial = @this.get('internacion_descripcion') || '';
                                                    if (initial) {
                                                        editor.setData(initial);
                                                    }
                                                    editor.model.document.on('change:data', () => {
                                                        @this.set('internacion_descripcion', editor.getData(), false);
                                                    });
                                                })
                                                .catch(err => console.error('Error CKEditor Internación:', err));
                                        };
                                        startEditor();

                                        $watch('$wire.internacion_descripcion', (val) => {
                                            if (editor && editor.getData() !== (val || '')) {
                                                editor.setData(val || '');
                                            }
                                        });
                                    },
                                    destroy() {
                                        if (this.$el._ckeditor) {
                                            this.$el._ckeditor.destroy();
                                            this.$el._ckeditor = null;
                                        }
                                    }
                                }"
                                class="mt-1"
                            >
                                <textarea x-ref="ckEditorInternacion" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 p-2.5 min-h-[160px]"></textarea>
                            </div>
                            @error('internacion_descripcion')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="cerrarModalInternacion" 
                                class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                            >
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarInternacion">
                                    <i class="fas fa-check-circle me-1"></i> Guardar Internación
                                </span>
                                <span wire:loading wire:target="guardarInternacion">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script src="{{ asset('plugins/ckeditor/ckeditor-classic.bundle.js') }}"></script>
    <style>
        .dark .ck.ck-editor__main > .ck-editor__editable {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: #334155 !important;
        }
        .dark .ck.ck-toolbar {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        .dark .ck.ck-button {
            color: #cbd5e1 !important;
        }
        .dark .ck.ck-button:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }
        .dark .ck.ck-button.ck-on {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        .dark .ck.ck-dropdown__panel {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        .dark .ck.ck-list__item, .dark .ck.ck-list__button {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
        }
        .dark .ck.ck-list__button:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }
        .ck-editor__editable_inline {
            min-height: 180px;
            max-height: 380px;
        }
        /* List formatting override for CKEditor & Clinical Notes (counteracting Tailwind reset) */
        .ck-content ul,
        .ck.ck-content ul,
        .ck-editor__editable ul,
        .clinical-notes-content ul {
            list-style-type: disc !important;
            padding-left: 1.5rem !important;
            margin-top: 0.35rem !important;
            margin-bottom: 0.35rem !important;
        }
        .ck-content ol,
        .ck.ck-content ol,
        .ck-editor__editable ol,
        .clinical-notes-content ol {
            list-style-type: decimal !important;
            padding-left: 1.5rem !important;
            margin-top: 0.35rem !important;
            margin-bottom: 0.35rem !important;
        }
        .ck-content li,
        .ck.ck-content li,
        .ck-editor__editable li,
        .clinical-notes-content li {
            display: list-item !important;
            margin-bottom: 0.2rem !important;
        }
    </style>
@endpush
