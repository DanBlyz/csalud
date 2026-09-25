<div>
    <!-- Breadcrumb & Header Title -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('proformas.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Proformas</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">Admisión y Apertura (Fase 1)</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/20">
                    <i class="fas fa-clipboard-user text-base"></i>
                </span>
                Apertura de Proforma Clínica
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Admisión del paciente, asignación de médico tratante, modalidad de atención y motivo clínico.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="{{ route('proformas.index') }}" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                <i class="fas fa-arrow-left"></i>
                <span>Volver al Listado</span>
            </a>
        </div>
    </div>

    <form wire:submit="abrirProforma" class="space-y-6">
        <!-- SECCIÓN 1: SELECCIÓN DEL PACIENTE -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-xs font-bold">1</span>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        Identificación del Paciente
                    </h2>
                </div>

                @if (! $pacienteSeleccionado)
                    <button 
                        type="button" 
                        wire:click="abrirModalNuevoPaciente" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 dark:bg-teal-950/60 dark:text-teal-300 text-xs font-semibold border border-teal-200 dark:border-teal-800/60 transition-colors cursor-pointer"
                    >
                        <i class="fas fa-user-plus text-xs"></i>
                        <span>+ Registrar Nuevo Paciente</span>
                    </button>
                @endif
            </div>

            @if ($pacienteSeleccionado)
                <!-- Tarjeta del Paciente Seleccionado -->
                <div class="p-4 rounded-xl border border-teal-200 dark:border-teal-800/60 bg-gradient-to-r from-teal-50/70 to-emerald-50/50 dark:from-teal-950/30 dark:to-emerald-950/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start sm:items-center gap-3.5">
                        <div class="h-12 w-12 rounded-full bg-teal-600 text-white flex items-center justify-center text-lg font-bold shrink-0 shadow-sm">
                            {{ substr($pacienteSeleccionado->nombres, 0, 1) }}{{ substr($pacienteSeleccionado->apellido_paterno, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                    {{ $pacienteSeleccionado->nombre_completo }}
                                </h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $pacienteSeleccionado->genero === 'Femenino' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $pacienteSeleccionado->genero }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <span><span class="font-medium text-slate-700 dark:text-slate-300">Documento:</span> {{ $pacienteSeleccionado->cedula ?: 'Sin CI' }}</span>
                                <span>•</span>
                                <span><span class="font-medium text-slate-700 dark:text-slate-300">Edad:</span> {{ $pacienteSeleccionado->edad !== null ? "{$pacienteSeleccionado->edad} años" : 'N/E' }}</span>
                                <span>•</span>
                                <span><span class="font-medium text-slate-700 dark:text-slate-300">Celular:</span> {{ $pacienteSeleccionado->celular ?: 'Sin celular' }}</span>
                                @if ($pacienteSeleccionado->contacto_emergencia_nombre)
                                    <span>•</span>
                                    <span><span class="font-medium text-amber-600 dark:text-amber-400">Emerg:</span> {{ $pacienteSeleccionado->contacto_emergencia_nombre }} ({{ $pacienteSeleccionado->contacto_emergencia_telefono }})</span>
                                @endif
                            </div>

                            @if ($pacienteSeleccionado->antecedentes_alergias)
                                <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-100/80 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 text-xs font-semibold">
                                    <i class="fas fa-exclamation-triangle text-rose-600 dark:text-rose-400"></i>
                                    <span>Alergias / Antecedentes: {{ $pacienteSeleccionado->antecedentes_alergias }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <button 
                        type="button" 
                        wire:click="deseleccionarPaciente" 
                        class="self-start md:self-center px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 text-xs font-semibold transition-colors cursor-pointer"
                    >
                        <i class="fas fa-exchange-alt me-1"></i> Cambiar Paciente
                    </button>
                </div>
            @else
                <!-- Buscador de Pacientes en Tiempo Real -->
                <div class="relative">
                    <label for="pacienteSearch" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Buscar Paciente por Nombre o Documento <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input 
                            type="text" 
                            id="pacienteSearch"
                            wire:model.live.debounce.250ms="pacienteSearch" 
                            placeholder="Escriba nombres, apellidos o cédula de identidad..." 
                            class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 shadow-2xs @error('paciente_id') border-rose-500 @enderror"
                        />
                    </div>
                    @error('paciente_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror

                    <!-- Dropdown de Sugerencias -->
                    @if (strlen(trim($pacienteSearch)) >= 2)
                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($this->resultadosPacientes as $res)
                                <button 
                                    type="button" 
                                    wire:click="seleccionarPaciente({{ $res->id }})" 
                                    class="w-full px-4 py-3 text-left hover:bg-teal-50/70 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between cursor-pointer"
                                >
                                    <div>
                                        <div class="font-bold text-xs text-slate-900 dark:text-white">
                                            {{ $res->nombre_completo }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                            <span>CI: {{ $res->cedula ?: 'Sin CI' }}</span>
                                            <span>•</span>
                                            <span>{{ $res->edad !== null ? "{$res->edad} años" : 'N/E' }}</span>
                                            <span>•</span>
                                            <span>{{ $res->celular ?: 'Sin celular' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-teal-600 dark:text-teal-400 flex items-center gap-1">
                                        <span>Seleccionar</span>
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                    </span>
                                </button>
                            @empty
                                <div class="px-4 py-4 text-center text-xs text-slate-400">
                                    <p class="font-medium text-slate-600 dark:text-slate-300">No se encontraron pacientes con "{{ $pacienteSearch }}"</p>
                                    <button 
                                        type="button" 
                                        wire:click="abrirModalNuevoPaciente" 
                                        class="mt-2 text-teal-600 hover:text-teal-800 dark:text-teal-400 font-bold underline"
                                    >
                                        ¿Desea registrarlo como nuevo paciente ahora?
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- SECCIÓN 2: PARÁMETROS OPERATIVOS Y ATENCIÓN CLÍNICA -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 space-y-6">
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold">2</span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                    Modalidad y Responsables de la Atención
                </h2>
            </div>

            <!-- Tipo de Atención (Cards Seleccionables) -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Tipo de Modalidad Clínica <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ $tipo_atencion === 'Ambulatoria' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                        <input type="radio" wire:model.live="tipo_atencion" value="Ambulatoria" class="sr-only">
                        <div class="flex items-center gap-3.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white text-base shadow-sm">
                                <i class="fas fa-walking"></i>
                            </span>
                            <div>
                                <span class="block text-xs font-bold text-slate-900 dark:text-white">Atención Ambulatoria</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">Consultas generales, revisiones o curaciones del día sin internación prolongada.</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-4 rounded-xl border cursor-pointer transition-all {{ $tipo_atencion === 'Internacion' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30 ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                        <input type="radio" wire:model.live="tipo_atencion" value="Internacion" class="sr-only">
                        <div class="flex items-center gap-3.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600 text-white text-base shadow-sm">
                                <i class="fas fa-bed"></i>
                            </span>
                            <div>
                                <span class="block text-xs font-bold text-slate-900 dark:text-white">Hospitalización / Internación</span>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">Paciente con cama asignada, cuidados continuos de enfermería y estadía médica.</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Asignación de Médicos Tratantes con Buscador -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Médicos Tratantes Responsables <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                        {{ count($medicos_seleccionados) }} médico(s) asignado(s)
                    </span>
                </div>

                <!-- Buscador de Médicos -->
                <div class="relative">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-user-md text-xs"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.200ms="medicoSearch" 
                            placeholder="Buscar médico por nombre o especialidad para añadir..." 
                            class="w-full pl-10 pr-4 py-2 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-blue-500 shadow-2xs"
                        />
                    </div>

                    <!-- Dropdown de Resultados de Médicos -->
                    @if (strlen(trim($medicoSearch)) >= 1)
                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800 max-h-56 overflow-y-auto">
                            @forelse ($this->resultadosMedicos as $doc)
                                <button 
                                    type="button" 
                                    wire:click="agregarMedico({{ $doc->id }})" 
                                    class="w-full px-4 py-2.5 text-left hover:bg-blue-50/70 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between cursor-pointer text-xs"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs">
                                            {{ substr($doc->nombres, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 dark:text-slate-100">Dr(a). {{ $doc->nombre_completo }}</span>
                                            <span class="text-[11px] text-slate-400 block">{{ $doc->especialidad?->nombre ?? 'Medicina General' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                        <i class="fas fa-plus text-[10px]"></i> Asignar
                                    </span>
                                </button>
                            @empty
                                <div class="px-4 py-3 text-center text-xs text-slate-400">
                                    No se encontraron médicos con "{{ $medicoSearch }}"
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

                <!-- Lista de Médicos Seleccionados en Chips/Tarjetas Compactas -->
                <div class="flex flex-wrap gap-2 pt-1">
                    @forelse ($this->medicosSeleccionadosModelos as $med)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-blue-200 dark:border-blue-900/60 bg-blue-50/80 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 text-xs shadow-2xs">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">
                                {{ substr($med->nombres, 0, 1) }}
                            </span>
                            <span class="font-semibold">Dr(a). {{ $med->nombre_completo }}</span>
                            @if ($med->especialidad)
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-blue-200/70 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300">
                                    {{ $med->especialidad->nombre }}
                                </span>
                            @endif
                            @if ($med->id === Auth::id())
                                <span class="text-[9px] uppercase tracking-wider font-bold text-amber-600 dark:text-amber-400" title="Usuario en sesión">(Tú)</span>
                            @endif
                            <button 
                                type="button" 
                                wire:click="eliminarMedico({{ $med->id }})" 
                                class="text-blue-400 hover:text-rose-600 dark:hover:text-rose-400 p-0.5 rounded transition-colors"
                                title="Quitar médico"
                            >
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    @empty
                        <p class="text-xs text-rose-500 font-medium">Debe seleccionar al menos un médico tratante.</p>
                    @endforelse
                </div>
                @error('medicos_seleccionados') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Pieza / Sala y Fechas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Pieza / Cama / Consultorio -->
                <div>
                    <label for="pieza" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ $tipo_atencion === 'Internacion' ? 'Sala / Pieza / Cama' : 'Consultorio / Sala de Procedimientos' }}
                    </label>
                    <input type="text" id="pieza" wire:model="pieza" placeholder="{{ $tipo_atencion === 'Internacion' ? 'Ej. Sala 2 - Cama 3' : 'Ej. Consultorio 1' }}" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs">
                </div>

                <!-- Fecha y Hora de Ingreso -->
                <div>
                    <label for="fecha_ingreso" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Fecha y Hora de Ingreso <span class="text-rose-500">*</span>
                    </label>
                    <input type="datetime-local" id="fecha_ingreso" wire:model="fecha_ingreso" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs @error('fecha_ingreso') border-rose-500 @enderror">
                    @error('fecha_ingreso') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Fecha Estimada de Salida (Opcional / Internación) -->
                <div>
                    <label for="fecha_salida" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Fecha Salida Estimada {{ $tipo_atencion === 'Internacion' ? '(Planificada)' : '(Opcional)' }}
                    </label>
                    <input type="datetime-local" id="fecha_salida" wire:model="fecha_salida" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs @error('fecha_salida') border-rose-500 @enderror">
                    @error('fecha_salida') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Motivo de Consulta y Diagnóstico Preliminar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="motivo_consulta" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Motivo de Consulta y Síntomas Iniciales
                    </label>
                    <textarea id="motivo_consulta" wire:model="motivo_consulta" rows="2" placeholder="Descripción detallada de la queja principal del paciente..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs"></textarea>
                </div>

                <div>
                    <label for="diagnostico" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Diagnóstico Preliminar / Impresión Clínica
                    </label>
                    <textarea id="diagnostico" wire:model="diagnostico" rows="2" placeholder="Hipótesis diagnóstica o diagnóstico presuntivo..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 p-2.5 shadow-2xs"></textarea>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: SERVICIOS Y PROCEDIMIENTOS MÉDICOS (EN LOTE) -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold">3</span>
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Servicios y Procedimientos Médicos
                        </h2>
                        <p class="text-[11px] text-slate-400">Busque y agregue múltiples prestaciones clínicas de una sola vez.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 rounded-full">
                    {{ count($servicios_agregados) }} servicio(s)
                </span>
            </div>

            <!-- Buscador de Servicios -->
            <div class="relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-stethoscope text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.200ms="servicioSearch" 
                        placeholder="Buscar procedimiento por nombre o categoría (ej. Consulta, Sutura, Curación)..." 
                        class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-2xs"
                    />
                </div>

                @if (strlen(trim($servicioSearch)) >= 1)
                    <div class="absolute z-20 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800 max-h-56 overflow-y-auto">
                        @forelse ($this->resultadosServicios as $serv)
                            <button 
                                type="button" 
                                wire:click="agregarServicioALista({{ $serv->id }})" 
                                class="w-full px-4 py-2.5 text-left hover:bg-indigo-50/70 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between cursor-pointer text-xs"
                            >
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $serv->nombre }}</span>
                                    <span class="text-[11px] text-slate-400 block">{{ $serv->categoria->nombre ?? 'General' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 font-mono">Bs. {{ number_format($serv->precio_tentativo, 2) }}</span>
                                    <span class="text-[10px] text-slate-400 block">+ Añadir</span>
                                </div>
                            </button>
                        @empty
                            <div class="px-4 py-3 text-center text-xs text-slate-400">
                                No se encontraron servicios con "{{ $servicioSearch }}"
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- Tabla de Servicios Agregados -->
            @if (! empty($servicios_agregados))
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-2.5 px-3">Servicio / Procedimiento</th>
                                <th class="py-2.5 px-3">Observaciones Clínicas</th>
                                <th class="py-2.5 px-3 w-32 text-right">Costo Acordado (Bs.)</th>
                                <th class="py-2.5 px-3 text-center w-12">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            @foreach ($servicios_agregados as $idx => $s)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/50">
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ $s['nombre'] }}</div>
                                        <span class="text-[10px] text-slate-400">{{ $s['categoria'] }}</span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input 
                                            type="text" 
                                            wire:model="servicios_agregados.{{ $idx }}.observaciones" 
                                            placeholder="Indicación específica..." 
                                            class="w-full text-xs rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-1.5 shadow-2xs"
                                        />
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            min="0" 
                                            wire:model.live="servicios_agregados.{{ $idx }}.costo_final" 
                                            class="w-24 text-right font-mono font-bold text-xs rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-1.5 text-indigo-600 dark:text-indigo-400"
                                        />
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <button 
                                            type="button" 
                                            wire:click="eliminarServicioDeLista({{ $idx }})" 
                                            class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors"
                                            title="Eliminar de la lista"
                                        >
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-3 text-center text-xs text-slate-400 italic bg-slate-50 dark:bg-slate-850 rounded-lg">
                    Aún no ha agregado servicios o procedimientos para esta proforma.
                </div>
            @endif
        </div>

        <!-- SECCIÓN 4: SOLICITUDES Y EXÁMENES (EN LOTE) -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-teal-100 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 text-xs font-bold">4</span>
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Solicitudes de Exámenes y Estudios Clínicos
                        </h2>
                        <p class="text-[11px] text-slate-400">Adjunte órdenes de laboratorio, radiografías o ecografías de inmediato.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/60 px-2.5 py-1 rounded-full">
                    {{ count($solicitudes_agregadas) }} examen(es)
                </span>
            </div>

            <!-- Mini Formulario Inline para Encolar Solicitud -->
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Estudio / Examen
                    </label>
                    <select wire:model="temp_solicitud_tipo_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                        <option value="">-- Seleccionar Estudio --</option>
                        @foreach ($tiposSolicitudes as $ts)
                            <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Indicaciones / Motivo del Estudio
                    </label>
                    <input type="text" wire:model="temp_solicitud_observaciones" placeholder="Ej. Control preoperatorio urgente..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Adjunto Digital (PDF/JPG)
                    </label>
                    <input type="file" wire:model="temp_solicitud_archivo" class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-teal-50 file:text-teal-700">
                </div>

                <div class="sm:col-span-1">
                    <button 
                        type="button" 
                        wire:click="agregarSolicitudALista" 
                        class="w-full py-2 px-3 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs flex items-center justify-center gap-1 cursor-pointer"
                        title="Añadir a la lista de solicitudes"
                    >
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            @error('temp_solicitud_tipo_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror

            <!-- Lista de Solicitudes Encoladas -->
            @if (! empty($solicitudes_agregadas))
                <div class="space-y-2">
                    @foreach ($solicitudes_agregadas as $idx => $req)
                        <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded bg-teal-100 text-teal-700 dark:bg-teal-950 dark:text-teal-300">
                                    <i class="fas fa-flask text-xs"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-100">{{ $req['tipo_nombre'] }}</span>
                                    <span class="text-slate-500 dark:text-slate-400 block text-[11px]">
                                        {{ $req['observaciones'] ?: 'Sin observaciones' }}
                                        @if ($req['archivo'])
                                            • <span class="text-teal-600 font-semibold"><i class="fas fa-paperclip"></i> Con archivo adjunto</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <button type="button" wire:click="eliminarSolicitudDeLista({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- SECCIÓN 5: CRONOGRAMA Y CALENDARIO (EN LOTE) -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 text-xs font-bold">5</span>
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Cronograma y Actividades del Paciente
                        </h2>
                        <p class="text-[11px] text-slate-400">Programe controles médicos, rondas de enfermería o cirugías.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-1 rounded-full">
                    {{ count($calendario_agregados) }} actividad(es)
                </span>
            </div>

            <!-- Mini Formulario Inline para Encolar Calendario -->
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850/60 border border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Fecha
                    </label>
                    <input type="date" wire:model="temp_evento_fecha" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Hora
                    </label>
                    <input type="time" wire:model="temp_evento_hora" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                </div>

                <div class="sm:col-span-6">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Descripción de la Actividad
                    </label>
                    <input type="text" wire:model="temp_evento_descripcion" placeholder="Ej. Control de presión arterial y temperatura..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                </div>

                <div class="sm:col-span-1">
                    <button 
                        type="button" 
                        wire:click="agregarEventoALista" 
                        class="w-full py-2 px-3 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-xs flex items-center justify-center gap-1 cursor-pointer"
                        title="Programar actividad"
                    >
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            @error('temp_evento_descripcion') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror

            <!-- Lista de Eventos Encolados -->
            @if (! empty($calendario_agregados))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($calendario_agregados as $idx => $ev)
                        <div class="p-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono font-bold text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950 px-2 py-0.5 rounded text-[11px]">
                                    {{ $ev['fecha'] }} {{ $ev['hora'] }}
                                </span>
                                <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $ev['descripcion'] }}</span>
                            </div>
                            <button type="button" wire:click="eliminarEventoDeLista({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- SECCIÓN 6: PRESCRIPCIÓN Y RECETA MÉDICA INICIAL (EN LOTE) -->
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs font-bold">6</span>
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Prescripción de Receta Médica Inicial (Opcional)
                        </h2>
                        <p class="text-[11px] text-slate-400">Prescriba los fármacos desde la admisión para despacho inmediato en Farmacia.</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="incluir_receta" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Habilitar Receta</span>
                </label>
            </div>

            @if ($incluir_receta)
                <!-- Cabecera de la Receta -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Médico Prescriptor Responsable
                        </label>
                        <select wire:model="receta_medico_id" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                            @foreach ($medicosParaReceta as $mDoc)
                                <option value="{{ $mDoc->id }}">Dr(a). {{ $mDoc->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Observaciones / Indicaciones Generales de la Receta
                        </label>
                        <input type="text" wire:model="receta_observaciones" placeholder="Ej. Tomar con abundante agua, dieta blanda..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-2 shadow-2xs">
                    </div>
                </div>

                <!-- Buscador de Fármacos -->
                <div class="relative">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-pills text-xs"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.200ms="medicamentoSearch" 
                            placeholder="Buscar fármaco en catálogo (ej. Paracetamol, Amoxicilina, Ibuprofeno)..." 
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 shadow-2xs"
                        />
                    </div>

                    @if (strlen(trim($medicamentoSearch)) >= 1)
                        <div class="absolute z-20 left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800 max-h-56 overflow-y-auto">
                            @forelse ($this->resultadosMedicamentos as $prod)
                                <button 
                                    type="button" 
                                    wire:click="agregarMedicamentoALista({{ $prod->id }})" 
                                    class="w-full px-4 py-2 text-left hover:bg-emerald-50/70 dark:hover:bg-slate-800/80 transition-colors flex items-center justify-between cursor-pointer text-xs"
                                >
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $prod->nombre }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ $prod->descripcion }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-emerald-600 font-mono">Bs. {{ number_format($prod->ultimo_precio_venta, 2) }}</span>
                                        <span class="text-[10px] text-slate-400 block">+ Prescribir</span>
                                    </div>
                                </button>
                            @empty
                                <div class="px-4 py-3 text-center text-xs text-slate-400">
                                    No se encontraron fármacos con "{{ $medicamentoSearch }}"
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

                <!-- Tabla de Fármacos Prescritos -->
                @if (! empty($receta_medicamentos))
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2 px-3">Medicamento</th>
                                    <th class="py-2 px-3 w-20 text-center">Cant.</th>
                                    <th class="py-2 px-3">Posología / Indicaciones (Dosis y Horario)</th>
                                    <th class="py-2 px-3 w-24 text-right">Precio Ref.</th>
                                    <th class="py-2 px-3 text-center w-12">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                                @foreach ($receta_medicamentos as $idx => $med)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/50">
                                        <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-100">
                                            {{ $med['nombre'] }}
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                wire:model.live="receta_medicamentos.{{ $idx }}.cantidad" 
                                                class="w-16 text-center text-xs rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-1 font-mono font-bold"
                                            />
                                        </td>
                                        <td class="py-2 px-3">
                                            <input 
                                                type="text" 
                                                wire:model="receta_medicamentos.{{ $idx }}.indicaciones" 
                                                placeholder="Ej. 1 comprimido cada 8 hrs por 5 días" 
                                                class="w-full text-xs rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 p-1 shadow-2xs"
                                            />
                                        </td>
                                        <td class="py-2 px-3 text-right font-mono font-semibold text-emerald-600">
                                            Bs. {{ number_format($med['precio'] * $med['cantidad'], 2) }}
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <button type="button" wire:click="eliminarMedicamentoDeLista({{ $idx }})" class="text-slate-400 hover:text-rose-600 p-1">
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
        </div>

        <!-- RESUMEN CONSOLIDADO Y BOTÓN DE APERTURA -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-900 text-white p-5 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="space-y-1">
                <span class="text-xs uppercase font-bold tracking-wider text-slate-400 block">Consolidado Estimado Inicial</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold font-mono text-emerald-400">
                        Bs. {{ number_format($this->totalEstimado, 2) }}
                    </span>
                    <span class="text-xs text-slate-400">
                        ({{ count($servicios_agregados) }} servicios, {{ count($solicitudes_agregadas) }} exámenes, {{ count($calendario_agregados) }} actividades, {{ count($receta_medicamentos) }} fármacos)
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a 
                    href="{{ route('proformas.index') }}" 
                    class="px-4 py-2.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition-colors"
                >
                    Cancelar
                </a>

                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    wire:target="abrirProforma"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-500/25 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <span wire:loading.remove wire:target="abrirProforma">
                        <i class="fas fa-save me-1.5"></i> Aperturar Proforma y Registrar Todo en Lote
                    </span>
                    <span wire:loading wire:target="abrirProforma" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i> Registrando expediente completo...
                    </span>
                </button>
            </div>
        </div>
    </form>

    <!-- MODAL REGISTRO RÁPIDO DE PACIENTE NUEVO -->
    @if ($modalNuevoPacienteOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-quick-pac" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                    <div class="px-6 py-4 bg-gradient-to-r from-teal-50 to-emerald-50 dark:from-slate-800/80 dark:to-slate-800/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white text-sm">
                                <i class="fas fa-user-plus"></i>
                            </span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" id="modal-quick-pac">
                                Registro Inmediato de Paciente
                            </h3>
                        </div>
                        <button wire:click="cerrarModalNuevoPaciente" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    <form wire:submit="guardarNuevoPaciente" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="nuevo_nombres" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Nombres <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="nuevo_nombres" wire:model="nuevo_nombres" placeholder="Ej. Ana" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('nuevo_nombres') border-rose-500 @enderror">
                                @error('nuevo_nombres') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nuevo_apellido_paterno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Apellido Paterno <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="nuevo_apellido_paterno" wire:model="nuevo_apellido_paterno" placeholder="Ej. Quispe" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('nuevo_apellido_paterno') border-rose-500 @enderror">
                                @error('nuevo_apellido_paterno') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nuevo_apellido_materno" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Apellido Materno
                                </label>
                                <input type="text" id="nuevo_apellido_materno" wire:model="nuevo_apellido_materno" placeholder="Ej. Mamani" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="nuevo_cedula" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Documento / CI <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" id="nuevo_cedula" wire:model="nuevo_cedula" placeholder="Ej. 7891234" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs @error('nuevo_cedula') border-rose-500 @enderror">
                                @error('nuevo_cedula') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nuevo_fecha_nacimiento" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Fecha Nacimiento
                                </label>
                                <input type="date" id="nuevo_fecha_nacimiento" wire:model="nuevo_fecha_nacimiento" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>

                            <div>
                                <label for="nuevo_genero" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Género <span class="text-rose-500">*</span>
                                </label>
                                <select id="nuevo_genero" wire:model="nuevo_genero" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="nuevo_celular" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Celular
                                </label>
                                <input type="text" id="nuevo_celular" wire:model="nuevo_celular" placeholder="Ej. 76543210" class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>
                            <div>
                                <label for="nuevo_direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Dirección
                                </label>
                                <input type="text" id="nuevo_direccion" wire:model="nuevo_direccion" placeholder="Calle, Zona..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs">
                            </div>
                        </div>

                        <div>
                            <label for="nuevo_antecedentes_alergias" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Alergias o Antecedentes Críticos
                            </label>
                            <textarea id="nuevo_antecedentes_alergias" wire:model="nuevo_antecedentes_alergias" rows="2" placeholder="Ej. Alérgico a penicilinas, AINEs..." class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-teal-500 p-2.5 shadow-2xs"></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
                            <button wire:click="cerrarModalNuevoPaciente" type="button" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancelar
                            </button>
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardarNuevoPaciente"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-500/20 hover:shadow-lg transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            >
                                <span wire:loading.remove wire:target="guardarNuevoPaciente">
                                    <i class="fas fa-save me-1"></i> Registrar y Seleccionar
                                </span>
                                <span wire:loading wire:target="guardarNuevoPaciente">
                                    <i class="fas fa-spinner fa-spin"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
