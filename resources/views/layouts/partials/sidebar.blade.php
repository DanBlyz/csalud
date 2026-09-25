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

        <!-- Section: ATENCIÓN CLÍNICA -->
        <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Atención Clínica
        </div>

        <!-- Pacientes -->
        <a 
            href="{{ route('pacientes.index') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('pacientes.*') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-user-injured w-5 text-sm {{ request()->routeIs('pacientes.*') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Pacientes</span>
        </a>

        <!-- Proformas / Admisión -->
        <a 
            href="{{ route('proformas.index') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('proformas.index') || request()->routeIs('proformas.show') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-file-invoice-dollar w-5 text-sm {{ request()->routeIs('proformas.index') || request()->routeIs('proformas.show') ? 'text-white' : 'text-blue-400' }}"></i>
            <span class="ms-2">Proformas Clínicas</span>
        </a>

        <!-- Nueva Proforma (Admisión) -->
        <a 
            href="{{ route('proformas.crear') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('proformas.crear') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-plus-circle w-5 text-sm {{ request()->routeIs('proformas.crear') ? 'text-white' : 'text-indigo-400' }}"></i>
            <span class="ms-2">Nueva Admisión</span>
        </a>

        <!-- Section: FARMACIA E INVENTARIO -->
        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Farmacia e Inventario
        </div>

        <!-- Despacho de Recetas -->
        <a 
            href="{{ route('farmacia.despachos') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('farmacia.despachos') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-hand-holding-medical w-5 text-sm {{ request()->routeIs('farmacia.despachos') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Despacho de Recetas</span>
        </a>

        <!-- Catálogo de Medicamentos e Insumos -->
        <a 
            href="{{ route('farmacia.productos') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('farmacia.productos') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-pills w-5 text-sm {{ request()->routeIs('farmacia.productos') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Catálogo de Fármacos</span>
        </a>

        <!-- Control de Lotes y Abastecimiento -->
        <a 
            href="{{ route('farmacia.lotes') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('farmacia.lotes') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-boxes w-5 text-sm {{ request()->routeIs('farmacia.lotes') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Lotes y Stock</span>
        </a>

        <!-- Kardex y Movimientos -->
        <a 
            href="{{ route('farmacia.movimientos') }}" 
            class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('farmacia.movimientos') ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
        >
            <i class="fas fa-clipboard-list w-5 text-sm {{ request()->routeIs('farmacia.movimientos') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Kardex de Movimientos</span>
        </a>

        <!-- Section: SISTEMA -->
        <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Administración
        </div>

        <!-- Sedes / Sucursales -->
        <a href="{{ route('administracion.sucursales') }}" class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('administracion.sucursales') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-hospital w-5 text-sm {{ request()->routeIs('administracion.sucursales') ? 'text-white' : 'text-blue-400' }}"></i>
            <span class="ms-2">Sedes y Sucursales</span>
        </a>

        <!-- Roles y Permisos -->
        <a href="{{ route('administracion.roles') }}" class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('administracion.roles') ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-user-tag w-5 text-sm {{ request()->routeIs('administracion.roles') ? 'text-white' : 'text-purple-400' }}"></i>
            <span class="ms-2">Roles de Acceso</span>
        </a>

        <!-- Usuarios y Personal -->
        <a href="{{ route('administracion.usuarios') }}" class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('administracion.usuarios') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-users-cog w-5 text-sm {{ request()->routeIs('administracion.usuarios') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Personal y Usuarios</span>
        </a>

        <!-- Especialidades -->
        <a href="{{ route('administracion.especialidades') }}" class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('administracion.especialidades') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-stethoscope w-5 text-sm {{ request()->routeIs('administracion.especialidades') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Especialidades</span>
        </a>

        <!-- Servicios -->
        <a href="{{ route('administracion.servicios') }}" class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('administracion.servicios') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-clinic-medical w-5 text-sm {{ request()->routeIs('administracion.servicios') ? 'text-white' : 'text-teal-400' }}"></i>
            <span class="ms-2">Servicios</span>
        </a>

        <!-- Configuración de Perfil -->
        {{-- <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2 rounded-md font-medium text-xs transition-colors {{ request()->routeIs('profile.edit') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-cog w-5 text-sm text-slate-400"></i>
            <span class="ms-2">Mi Perfil</span>
        </a> --}}
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
