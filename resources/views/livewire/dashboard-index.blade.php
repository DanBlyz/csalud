<div class="space-y-6">

    <!-- Header & Control Bar: Saludo, Fecha y Sucursal -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-gray-200 dark:border-slate-800 p-5 transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Panel Principal de Control
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Resumen operativo clínico y monitoreo de existencias hospitalarias en tiempo real
                        </p>
                    </div>
                </div>
            </div>

            <!-- Controles Reactivos: Selector de Fecha y Sucursal -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Selector de Sucursal -->
                @if(auth()->user()->esAdmin() || count($sucursales) > 1)
                    <div class="flex items-center bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 shadow-xs">
                        <i class="fas fa-hospital text-slate-400 dark:text-slate-500 text-xs me-2"></i>
                        <select 
                            wire:model.live="filtroSucursal" 
                            class="bg-transparent text-xs font-semibold text-slate-700 dark:text-slate-200 border-none focus:ring-0 p-0 cursor-pointer"
                        >
                            @if(auth()->user()->esAdmin())
                                <option value="" class="dark:bg-slate-800">Todas las Sucursales</option>
                            @endif
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" class="dark:bg-slate-800">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    @if(auth()->user()->sucursal)
                        <div class="flex items-center bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 rounded-xl border border-blue-200 dark:border-blue-900/50 px-3 py-1.5 text-xs font-semibold">
                            <i class="fas fa-hospital me-2"></i>
                            <span>{{ auth()->user()->sucursal->nombre }}</span>
                        </div>
                    @endif
                @endif

                <!-- Selector de Fecha de Atención -->
                <div class="flex items-center bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-1.5 shadow-xs">
                    <i class="fas fa-calendar-day text-slate-400 dark:text-slate-500 text-xs me-2"></i>
                    <input 
                        type="date" 
                        wire:model.live="fecha" 
                        class="bg-transparent text-xs font-semibold text-slate-700 dark:text-slate-200 border-none focus:ring-0 p-0 cursor-pointer"
                        title="Seleccionar fecha para auditar métricas"
                    />
                </div>

                <!-- Botón de acceso rápido a "Hoy" -->
                @if($fecha !== today()->format('Y-m-d'))
                    <button 
                        wire:click="setHoy" 
                        type="button"
                        class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition flex items-center"
                        title="Regresar a la fecha de hoy"
                    >
                        <i class="fas fa-rotate-left me-1.5 text-[10px]"></i> Ver Hoy
                    </button>
                @endif

                <!-- Badge de Fecha Activa -->
                <div class="hidden sm:flex items-center px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-xl text-[11px] font-medium text-slate-600 dark:text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 me-2 animate-pulse"></span>
                    <span>{{ $fechaTexto }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas de Pacientes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Tarjeta 1: Total Pacientes Registrados -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-md p-5 flex flex-col justify-between group transition-all duration-200 hover:shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs mb-2">
                        Padrón General
                    </span>
                    <p class="text-xs font-medium text-blue-100">Total Pacientes</p>
                    <h3 class="text-3xl font-black tracking-tight mt-1">
                        {{ number_format($totalPacientes) }}
                    </h3>
                    <p class="text-[11px] text-blue-100/90 mt-1.5 flex items-center">
                        <i class="fas fa-database text-[10px] me-1.5 opacity-80"></i> Registrados en el sistema
                    </p>
                </div>
                <div class="p-3.5 bg-white/15 rounded-2xl group-hover:scale-110 transition-transform shadow-inner">
                    <i class="fas fa-hospital-user text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between">
                @permiso('pacientes.ver')
                <a href="{{ route('pacientes.index') }}" class="text-xs font-semibold text-blue-100 hover:text-white flex items-center justify-between w-full group/link">
                    <span>Directorio de Pacientes</span>
                    <i class="fas fa-arrow-right text-xs transition-transform group-hover/link:translate-x-1"></i>
                </a>
                @else
                <span class="text-[11px] text-blue-200">Total histórico acumulado</span>
                @endpermiso
            </div>
        </div>

        <!-- Tarjeta 2: Pacientes Internados ese Día -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-600 to-violet-800 text-white shadow-md p-5 flex flex-col justify-between group transition-all duration-200 hover:shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs mb-2">
                        Hospitalización
                    </span>
                    <p class="text-xs font-medium text-purple-100">Internados del Día</p>
                    <h3 class="text-3xl font-black tracking-tight mt-1">
                        {{ number_format($pacientesInternadosHoy) }}
                    </h3>
                    <p class="text-[11px] text-purple-100/90 mt-1.5 flex items-center">
                        <i class="fas fa-procedures text-[10px] me-1.5 opacity-80"></i> Ingresos a cama / sala
                    </p>
                </div>
                <div class="p-3.5 bg-white/15 rounded-2xl group-hover:scale-110 transition-transform shadow-inner">
                    <i class="fas fa-bed-pulse text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between">
                @permiso('proformas.ver')
                <a href="{{ route('proformas.index') }}" class="text-xs font-semibold text-purple-100 hover:text-white flex items-center justify-between w-full group/link">
                    <span>Ver ingresos clínicos</span>
                    <i class="fas fa-arrow-right text-xs transition-transform group-hover/link:translate-x-1"></i>
                </a>
                @else
                <span class="text-[11px] text-purple-200">Atención hospitalaria continua</span>
                @endpermiso
            </div>
        </div>

        <!-- Tarjeta 3: Pacientes Atendidos Ambulatorios (No Internados) ese Día -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-md p-5 flex flex-col justify-between group transition-all duration-200 hover:shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs mb-2">
                        Consulta Externa
                    </span>
                    <p class="text-xs font-medium text-emerald-100">Atención Ambulatoria</p>
                    <h3 class="text-3xl font-black tracking-tight mt-1">
                        {{ number_format($pacientesAmbulatoriosHoy) }}
                    </h3>
                    <p class="text-[11px] text-emerald-100/90 mt-1.5 flex items-center">
                        <i class="fas fa-stethoscope text-[10px] me-1.5 opacity-80"></i> Atendidos sin internación
                    </p>
                </div>
                <div class="p-3.5 bg-white/15 rounded-2xl group-hover:scale-110 transition-transform shadow-inner">
                    <i class="fas fa-user-nurse text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between">
                @permiso('proformas.ver')
                <a href="{{ route('proformas.index') }}" class="text-xs font-semibold text-emerald-100 hover:text-white flex items-center justify-between w-full group/link">
                    <span>Ver consultas ambulatorias</span>
                    <i class="fas fa-arrow-right text-xs transition-transform group-hover/link:translate-x-1"></i>
                </a>
                @else
                <span class="text-[11px] text-emerald-200">Consultorios y triajes</span>
                @endpermiso
            </div>
        </div>

        <!-- Tarjeta 4: Pacientes Dados de Alta ese Día -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-md p-5 flex flex-col justify-between group transition-all duration-200 hover:shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white/20 text-white backdrop-blur-xs mb-2">
                        Egresos Médicos
                    </span>
                    <p class="text-xs font-medium text-amber-100">Altas Médicas Hoy</p>
                    <h3 class="text-3xl font-black tracking-tight mt-1">
                        {{ number_format($pacientesAltasHoy) }}
                    </h3>
                    <p class="text-[11px] text-amber-100/90 mt-1.5 flex items-center">
                        <i class="fas fa-circle-check text-[10px] me-1.5 opacity-80"></i> Altas efectivas registradas
                    </p>
                </div>
                <div class="p-3.5 bg-white/15 rounded-2xl group-hover:scale-110 transition-transform shadow-inner">
                    <i class="fas fa-house-medical-circle-check text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between">
                @permiso('proformas.ver')
                <a href="{{ route('proformas.index') }}" class="text-xs font-semibold text-amber-100 hover:text-white flex items-center justify-between w-full group/link">
                    <span>Ver historial de atenciones</span>
                    <i class="fas fa-arrow-right text-xs transition-transform group-hover/link:translate-x-1"></i>
                </a>
                @else
                <span class="text-[11px] text-amber-200">Pacientes dados de alta</span>
                @endpermiso
            </div>
        </div>

    </div>

    <!-- Barra de Accesos Rápidos Operativos -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-gray-200 dark:border-slate-800 p-4 transition-colors">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider flex items-center">
                <i class="fas fa-bolt text-amber-500 me-2 text-xs"></i> Acciones Rápidas del Turno
            </h3>
        </div>
        <div class="flex flex-wrap gap-2.5">
            @permiso('proformas.crear')
            <a href="{{ route('proformas.crear') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-file-medical me-2 text-xs"></i> Nueva Proforma / Atención
            </a>
            @endpermiso

            @permiso('pacientes.crear')
            <a href="{{ route('pacientes.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-user-plus me-2 text-xs"></i> Registrar Paciente
            </a>
            @endpermiso

            @permiso('farmacia.lotes')
            <a href="{{ route('farmacia.lotes') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-boxes-stacked me-2 text-xs"></i> Gestión de Lotes y Stock
            </a>
            @endpermiso

            @permiso('farmacia.despachos')
            <a href="{{ route('farmacia.despachos') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-prescription-bottle-medical me-2 text-xs"></i> Despacho de Recetas
            </a>
            @endpermiso

            @permiso('caja.ver')
            <a href="{{ route('caja.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-cash-register me-2 text-xs text-emerald-400"></i> Caja y Cobros
            </a>
            @endpermiso
        </div>
    </div>

    <!-- SECCIÓN INFERIOR: MEDICAMENTOS POR AGOTARSE SEGÚN SU SECCIÓN -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xs border border-gray-200 dark:border-slate-800 overflow-hidden transition-colors">
        
        <!-- Header de la sección de inventario crítico -->
        <div class="p-5 border-b border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-900/80">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="flex items-center justify-center w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-sm">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Medicamentos Próximos a Agotarse
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Monitoreo de existencias mínimas y stock crítico clasificado por sección hospitalaria
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Alertas Rápidas -->
                <div class="flex items-center gap-2">
                    @if($totalAgotados > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50">
                            <i class="fas fa-ban me-1.5 text-[10px]"></i> {{ $totalAgotados }} Agotados (0)
                        </span>
                    @endif

                    @if($totalCriticos > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                            <i class="fas fa-fire me-1.5 text-[10px]"></i> {{ $totalCriticos }} Críticos (≤ 5)
                        </span>
                    @endif

                    @permiso('farmacia.lotes')
                    <a href="{{ route('farmacia.lotes') }}" class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-semibold transition">
                        <i class="fas fa-arrow-up-right-from-square me-1 text-[10px]"></i> Ir a Farmacia
                    </a>
                    @endpermiso
                </div>
            </div>

            <!-- Filtros de Secciones Hospitalarias (Tabs) y Umbral -->
            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                <!-- Tabs de Secciones -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full custom-scrollbar">
                    <button 
                        type="button"
                        wire:click="seleccionarSeccion(null)"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 {{ is_null($filtroSeccion) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                    >
                        <i class="fas fa-layer-group me-1.5 text-[10px]"></i> Todas las Secciones
                    </button>

                    @foreach($seccionesDisponibles as $sec)
                        <button 
                            type="button"
                            wire:click="seleccionarSeccion({{ $sec->id }})"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0 {{ $filtroSeccion === $sec->id ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}"
                        >
                            @if($sec->es_almacen_principal)
                                <i class="fas fa-warehouse me-1.5 text-[10px] text-amber-500"></i>
                            @else
                                <i class="fas fa-hospital-user me-1.5 text-[10px] text-teal-500"></i>
                            @endif
                            {{ $sec->nombre }}
                        </button>
                    @endforeach
                </div>

                <!-- Buscador de Medicamento y Filtro de Umbral -->
                <div class="flex items-center gap-2">
                    <!-- Buscador -->
                    <div class="relative min-w-[200px]">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="buscarMedicamento" 
                            placeholder="Buscar medicamento o lote..." 
                            class="w-full text-xs pl-8 pr-3 py-1.5 rounded-xl border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent placeholder-gray-400 dark:placeholder-slate-500 shadow-2xs"
                        />
                        <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 dark:text-slate-500 text-[11px]"></i>
                    </div>

                    <!-- Umbral de Stock -->
                    <select 
                        wire:model.live="umbralStock" 
                        class="text-xs py-1.5 px-3 rounded-xl border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-2xs"
                        title="Filtrar por nivel máximo de existencias"
                    >
                        <option value="5">Stock ≤ 5 (Crítico)</option>
                        <option value="10">Stock ≤ 10 (Estándar)</option>
                        <option value="20">Stock ≤ 20 (Amplio)</option>
                        <option value="0">Solo Agotados (0)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabla de Medicamentos con Stock Bajo -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 dark:text-slate-300">
                <thead class="bg-gray-50/80 dark:bg-slate-800/80 text-gray-500 dark:text-slate-400 uppercase tracking-wider text-[11px] border-b border-gray-200 dark:border-slate-800 font-bold">
                    <tr>
                        <th class="px-5 py-3.5">Medicamento / Presentación</th>
                        <th class="px-4 py-3.5">Laboratorio / Marca</th>
                        <th class="px-4 py-3.5">Lote & Vencimiento</th>
                        <th class="px-4 py-3.5">Sección Hospitalaria</th>
                        <th class="px-4 py-3.5 text-center">Stock en Sección</th>
                        <th class="px-4 py-3.5 text-center">Stock Consolidado</th>
                        <th class="px-4 py-3.5 text-center">Nivel de Alerta</th>
                        <th class="px-5 py-3.5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60">
                    @forelse($medicamentosPorAgotarse as $item)
                        @php
                            $lote = $item->lote;
                            $producto = $lote?->producto;
                            $stockSeccion = (int) $item->cantidad_actual;
                            $stockTotalLote = (int) ($lote?->cantidad_actual ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            
                            <!-- Medicamento -->
                            <td class="px-5 py-3.5 font-medium text-slate-900 dark:text-white">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i class="fas fa-pills"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 dark:text-white text-xs">
                                            {{ $producto?->nombre ?? 'Producto no identificado' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $producto?->unidad_medida ?? 'Unidades' }} 
                                            @if($producto?->stock_minimo)
                                                · <span class="text-slate-400">Mín. sugerido: {{ $producto->stock_minimo }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Marca -->
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $producto?->marca?->nombre ?? 'Genérico / Sin marca' }}
                                </span>
                            </td>

                            <!-- Lote & Vencimiento -->
                            <td class="px-4 py-3.5">
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center font-mono text-[11px] font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">
                                        {{ $lote?->codigo_lote ?? 'N/A' }}
                                    </span>
                                    @if($lote && $lote->fecha_vencimiento)
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                            Vence: {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                            @if($lote->isVencido())
                                                <span class="text-rose-500 font-bold ml-1">(Vencido)</span>
                                            @elseif($lote->isPorVencer(60))
                                                <span class="text-amber-500 font-bold ml-1">({{ $lote->textoVencimiento() }})</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <!-- Sección Hospitalaria -->
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $item->seccion?->es_almacen_principal ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/50' : 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-900/50' }}">
                                    @if($item->seccion?->es_almacen_principal)
                                        <i class="fas fa-warehouse me-1.5 text-[10px]"></i>
                                    @else
                                        <i class="fas fa-hospital-user me-1.5 text-[10px]"></i>
                                    @endif
                                    {{ $item->seccion?->nombre ?? 'Sección no asignada' }}
                                </span>
                            </td>

                            <!-- Stock en Sección -->
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm font-extrabold {{ $stockSeccion === 0 ? 'text-rose-600 dark:text-rose-400' : ($stockSeccion <= 5 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-200') }}">
                                    {{ $stockSeccion }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">unidades</span>
                            </td>

                            <!-- Stock Consolidado -->
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                    {{ $stockTotalLote }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">total sede</span>
                            </td>

                            <!-- Nivel de Alerta -->
                            <td class="px-4 py-3.5 text-center">
                                @if($stockSeccion <= 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        <i class="fas fa-circle-exclamation text-[10px] me-1"></i> Agotado
                                    </span>
                                @elseif($stockSeccion <= 5)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i class="fas fa-triangle-exclamation text-[10px] me-1"></i> Crítico
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-yellow-100 dark:bg-yellow-950/70 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">
                                        <i class="fas fa-arrow-trend-down text-[10px] me-1"></i> Stock Bajo
                                    </span>
                                @endif
                            </td>

                            <!-- Acción -->
                            <td class="px-5 py-3.5 text-right">
                                @permiso('farmacia.lotes')
                                <a 
                                    href="{{ route('farmacia.lotes') }}" 
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 font-semibold text-xs transition"
                                    title="Gestionar lote, transferencias o reabastecimiento"
                                >
                                    <span>Gestionar</span>
                                    <i class="fas fa-arrow-right text-[10px] ms-1"></i>
                                </a>
                                @endpermiso
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 mx-auto flex items-center justify-center text-2xl shadow-inner">
                                        <i class="fas fa-shield-heart"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">
                                        Inventario en Niveles Óptimos
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        @if($filtroSeccion)
                                            No se detectaron medicamentos con existencias menores o iguales a {{ $umbralStock }} unidades en la sección seleccionada.
                                        @else
                                            No se detectaron medicamentos con existencias críticas en las secciones evaluadas.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer con Paginación -->
        @if($medicamentosPorAgotarse->hasPages())
            <div class="px-5 py-3.5 bg-gray-50/50 dark:bg-slate-900/80 border-t border-gray-100 dark:border-slate-800">
                {{ $medicamentosPorAgotarse->links() }}
            </div>
        @endif

    </div>

</div>
