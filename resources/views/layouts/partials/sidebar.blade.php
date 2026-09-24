<aside 
    class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-200 flex flex-col transition-transform duration-300 ease-in-out shadow-xl"
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
>
    <!-- Brand / Logo -->
    <div class="h-14 flex items-center justify-between px-4 bg-slate-950 border-b border-slate-800 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 text-white font-bold text-base tracking-wide">
            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-teal-400 text-white shadow-md">
                <i class="fas fa-heartbeat text-sm"></i>
            </span>
            <span class="truncate">
                <span class="text-blue-400 font-extrabold">CENTRO</span><span class="text-white font-light">SALUD</span>
            </span>
        </a>
        <!-- Close button on mobile -->
        <button 
            @click="sidebarOpen = false" 
            type="button" 
            class="lg:hidden text-slate-400 hover:text-white p-1 rounded-md"
        >
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- User Info Box (AdminLTE style) -->
    <div class="px-4 py-3 bg-slate-900/60 border-b border-slate-800 flex items-center space-x-3 shrink-0">
        <div class="relative">
            <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-teal-500 to-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-inner">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
            </div>
            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-slate-900" title="En línea"></span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
            <p class="text-[11px] text-emerald-400 flex items-center">
                <i class="fas fa-circle text-[7px] me-1.5"></i> En línea
            </p>
        </div>
    </div>

    <!-- Navigation Menu (Scrollable) -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-xs select-none custom-scrollbar">
        <!-- Section: PRINCIPAL -->
        <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Principal
        </div>

        <!-- Dashboard Link -->
        <a 
            href="{{ route('dashboard') }}" 
            class="flex items-center px-3 py-2.5 rounded-md font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-tachometer-alt w-5 text-sm {{ request()->routeIs('dashboard') ? 'text-white' : 'text-blue-400' }}"></i>
            <span class="ms-2">Dashboard</span>
        </a>

        <!-- Section: ATENCIÓN MÉDICA -->
        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Atención Médica
        </div>

        <!-- Citas Médicas (Dropdown) -->
        <div x-data="{ open: false }">
            <button 
                @click="open = !open" 
                type="button" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
            >
                <div class="flex items-center">
                    <i class="fas fa-calendar-check w-5 text-sm text-teal-400"></i>
                    <span class="ms-2">Citas Médicas</span>
                </div>
                <i class="fas fa-chevron-right text-[10px] transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open" x-collapse class="space-y-1 ps-8 pe-1 pt-1" style="display: none;">
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-teal-400"></i> Agendar Cita
                </a>
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-teal-400"></i> Listado de Citas
                </a>
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-teal-400"></i> Calendario
                </a>
            </div>
        </div>

        <!-- Pacientes (Dropdown) -->
        <div x-data="{ open: false }">
            <button 
                @click="open = !open" 
                type="button" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
            >
                <div class="flex items-center">
                    <i class="fas fa-user-injured w-5 text-sm text-sky-400"></i>
                    <span class="ms-2">Pacientes</span>
                </div>
                <i class="fas fa-chevron-right text-[10px] transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open" x-collapse class="space-y-1 ps-8 pe-1 pt-1" style="display: none;">
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-sky-400"></i> Directorio Pacientes
                </a>
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-sky-400"></i> Registrar Paciente
                </a>
            </div>
        </div>

        <!-- Triaje / Signos Vitales -->
        <a href="#" class="flex items-center px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
            <i class="fas fa-stethoscope w-5 text-sm text-amber-400"></i>
            <span class="ms-2">Triaje & Signos</span>
        </a>

        <!-- Historias Clínicas -->
        <a href="#" class="flex items-center px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
            <i class="fas fa-notes-medical w-5 text-sm text-rose-400"></i>
            <span class="ms-2">Historias Clínicas</span>
        </a>

        <!-- Section: GESTIÓN CLÍNICA -->
        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Gestión Clínica
        </div>

        <!-- Médicos & Especialidades -->
        <div x-data="{ open: false }">
            <button 
                @click="open = !open" 
                type="button" 
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
            >
                <div class="flex items-center">
                    <i class="fas fa-user-md w-5 text-sm text-indigo-400"></i>
                    <span class="ms-2">Médicos & Esp.</span>
                </div>
                <i class="fas fa-chevron-right text-[10px] transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </button>
            <div x-show="open" x-collapse class="space-y-1 ps-8 pe-1 pt-1" style="display: none;">
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-indigo-400"></i> Directorio Médico
                </a>
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-indigo-400"></i> Especialidades
                </a>
                <a href="#" class="block px-2.5 py-1.5 rounded text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
                    <i class="far fa-circle text-[8px] me-2 text-indigo-400"></i> Turnos y Horarios
                </a>
            </div>
        </div>

        <!-- Farmacia & Recetas -->
        <a href="#" class="flex items-center px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
            <i class="fas fa-pills w-5 text-sm text-emerald-400"></i>
            <span class="ms-2">Farmacia / Medicinas</span>
            <span class="ms-auto text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-semibold">Stock</span>
        </a>

        <!-- Section: SISTEMA -->
        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Administración
        </div>

        <!-- Reportes -->
        <a href="#" class="flex items-center px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
            <i class="fas fa-chart-line w-5 text-sm text-yellow-400"></i>
            <span class="ms-2">Reportes & Estadísticas</span>
        </a>

        <!-- Usuarios & Roles -->
        <a href="#" class="flex items-center px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
            <i class="fas fa-users-cog w-5 text-sm text-purple-400"></i>
            <span class="ms-2">Usuarios y Roles</span>
        </a>

        <!-- Configuración -->
        <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2.5 rounded-md font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
            <i class="fas fa-cog w-5 text-sm text-slate-400"></i>
            <span class="ms-2">Configuración</span>
        </a>
    </nav>

    <!-- Sidebar Footer / Status -->
    <div class="p-3 bg-slate-950 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between shrink-0">
        <span class="flex items-center">
            <i class="fas fa-shield-alt text-blue-500 me-1.5"></i> v1.0.0
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt text-xs"></i>
            </button>
        </form>
    </div>
</aside>
