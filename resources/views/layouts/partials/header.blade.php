<header class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 sticky top-0 z-30 transition-colors duration-200">
    <div class="px-4 sm:px-6 flex items-center justify-between h-14">
        <!-- Left: Toggle & Quick Links -->
        <div class="flex items-center space-x-3">
            <!-- Sidebar Toggle Button (Mobile & Desktop) -->
            <button 
                @click="sidebarOpen = !sidebarOpen" 
                type="button" 
                class="p-2 rounded-md text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                title="Alternar Menú"
            >
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Quick Links -->
            <div class="hidden md:flex items-center space-x-4 text-sm text-gray-600 dark:text-slate-300">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors">
                    <i class="fas fa-home me-1"></i> Inicio
                </a>
                <span class="text-gray-300 dark:text-slate-700">|</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-semibold border border-blue-200 dark:border-blue-800">
                    <i class="fas fa-hospital-alt me-1"></i> Centro de Salud
                </span>
            </div>
        </div>

        <!-- Right: Theme Toggle, Search, Notifications & User Dropdown -->
        <div class="flex items-center space-x-2 sm:space-x-3">
            <!-- Clock / Date indicator -->
            <div class="hidden lg:flex items-center text-xs text-gray-500 dark:text-slate-400 bg-gray-50 dark:bg-slate-800/80 border border-gray-200 dark:border-slate-700 rounded-md px-3 py-1.5">
                <i class="far fa-calendar-alt text-blue-600 dark:text-blue-400 me-2"></i>
                <span>{{ \Carbon\Carbon::now()->isoFormat('D [de] MMMM, YYYY') }}</span>
            </div>

            <!-- Dark / Light Mode Switcher Button -->
            <button 
                @click="toggleTheme()" 
                type="button" 
                class="p-2 rounded-lg text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                :title="darkMode ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'"
            >
                <!-- Sun Icon (shown when dark) -->
                <i x-show="darkMode" class="fas fa-sun text-amber-400 text-lg hover:rotate-45 transition-transform"></i>
                <!-- Moon Icon (shown when light) -->
                <i x-show="!darkMode" class="fas fa-moon text-slate-600 text-lg hover:-rotate-12 transition-transform"></i>
            </button>

            <!-- Notifications Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    @click.away="open = false" 
                    type="button" 
                    class="relative p-2 rounded-md text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-800 focus:outline-none"
                    title="Notificaciones"
                >
                    <i class="far fa-bell text-lg"></i>
                    <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] font-bold text-white">
                        3
                    </span>
                </button>

                <!-- Notifications Panel -->
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-80 rounded-lg bg-white dark:bg-slate-800 shadow-xl border border-gray-200 dark:border-slate-700 py-1 z-50 divide-y divide-gray-100 dark:divide-slate-700"
                    style="display: none;"
                >
                    <div class="px-4 py-2 font-semibold text-xs text-gray-700 dark:text-slate-200 bg-gray-50 dark:bg-slate-900/60 flex justify-between items-center">
                        <span>Notificaciones del Sistema</span>
                        <span class="text-[11px] text-blue-600 dark:text-blue-400 font-normal">3 nuevas</span>
                    </div>
                    <div class="max-h-60 overflow-y-auto divide-y divide-gray-50 dark:divide-slate-750 text-xs">
                        <a href="#" class="flex px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 me-3">
                                <i class="fas fa-calendar-check text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 dark:text-slate-200">Nueva cita agendada</p>
                                <p class="text-gray-500 dark:text-slate-400 text-[11px]">Paciente Juan Pérez - Medicina General</p>
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">Hace 10 min</span>
                            </div>
                        </a>
                        <a href="#" class="flex px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            <div class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 me-3">
                                <i class="fas fa-user-clock text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 dark:text-slate-200">Paciente en triaje</p>
                                <p class="text-gray-500 dark:text-slate-400 text-[11px]">Sala de espera 2 - Pendiente atención</p>
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">Hace 25 min</span>
                            </div>
                        </a>
                        <a href="#" class="flex px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                            <div class="h-8 w-8 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 me-3">
                                <i class="fas fa-pills text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800 dark:text-slate-200">Stock de farmacia bajo</p>
                                <p class="text-gray-500 dark:text-slate-400 text-[11px]">Paracetamol 500mg por agotarse</p>
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">Hace 1 hora</span>
                            </div>
                        </a>
                    </div>
                    <div class="px-4 py-2 text-center bg-gray-50 dark:bg-slate-900/60">
                        <a href="#" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 font-medium">Ver todas las notificaciones</a>
                    </div>
                </div>
            </div>

            <!-- User Menu Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    @click.away="open = false" 
                    type="button" 
                    class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                    <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-600 to-teal-500 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-gray-800 dark:text-slate-200 leading-tight">{{ Auth::user()->name ?? 'Usuario' }}</div>
                        <div class="text-[10px] text-gray-500 dark:text-slate-400 leading-tight">Personal Médico</div>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 dark:text-slate-500 text-xs hidden sm:block"></i>
                </button>

                <!-- User Dropdown Menu -->
                <div 
                    x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-slate-800 shadow-xl border border-gray-200 dark:border-slate-700 py-1.5 z-50 divide-y divide-gray-100 dark:divide-slate-700"
                    style="display: none;"
                >
                    <!-- Header -->
                    <div class="px-4 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-900/80 dark:to-slate-850">
                        <p class="text-xs text-gray-500 dark:text-slate-400">Sesión iniciada como</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                        <p class="text-[11px] text-gray-500 dark:text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                    </div>

                    <!-- Items -->
                    <div class="py-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <i class="fas fa-user-circle w-5 text-gray-400 dark:text-slate-400 me-2"></i> Mi Perfil
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-xs text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <i class="fas fa-cog w-5 text-gray-400 dark:text-slate-400 me-2"></i> Configuración
                        </a>
                    </div>

                    <!-- Logout -->
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button 
                                type="submit" 
                                class="w-full text-left flex items-center px-4 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 transition-colors font-medium"
                            >
                                <i class="fas fa-sign-out-alt w-5 text-red-500 me-2"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
