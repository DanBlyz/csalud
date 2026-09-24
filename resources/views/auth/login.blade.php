<x-guest-layout>
    <!-- AdminLTE Style Login Box -->
    <div class="bg-white dark:bg-slate-900 shadow-xl rounded-2xl border border-gray-200/80 dark:border-slate-800 overflow-hidden transition-colors duration-200">
        <!-- Brand Header -->
        <div class="px-6 pt-8 pb-6 text-center bg-gradient-to-b from-blue-50/60 to-white dark:from-slate-900 dark:to-slate-900 border-b border-gray-100 dark:border-slate-800">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-teal-500 text-white shadow-lg shadow-blue-500/25 mb-3">
                <i class="fas fa-hospital-alt text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">
                <span class="text-blue-600 font-extrabold">CENTRO</span><span class="text-slate-800 dark:text-white font-light">SALUD</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 font-medium">Portal Institucional de Atención Médica</p>
        </div>

        <div class="p-6 sm:p-8">
            <!-- Session Status / Flash Notification -->
            @if (session('status'))
                <div class="mb-5 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center">
                    <i class="fas fa-check-circle text-emerald-500 text-base me-2"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Validation Errors Summary -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-xs">
                    <div class="font-semibold flex items-center mb-1">
                        <i class="fas fa-exclamation-circle text-red-500 text-sm me-1.5"></i>
                        <span>Error al intentar acceder:</span>
                    </div>
                    <ul class="list-disc list-inside ps-1 space-y-0.5 text-red-700 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Correo Electrónico
                    </label>
                    <div class="relative rounded-lg shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-slate-500">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            autocomplete="username" 
                            placeholder="ejemplo@csalud.com"
                            class="block w-full pl-10 pr-3.5 py-2.5 text-sm text-gray-900 dark:text-white bg-white dark:bg-slate-800 border @error('email') border-red-400 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-slate-700 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-lg placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-1 transition"
                        />
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-gray-700 dark:text-slate-300">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                                ¿Olvidaste tu clave?
                            </a>
                        @endif
                    </div>
                    <div class="relative rounded-lg shadow-xs" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-slate-500">
                            <i class="fas fa-lock text-sm"></i>
                        </div>
                        <input 
                            id="password" 
                            :type="show ? 'text' : 'password'" 
                            name="password" 
                            required 
                            autocomplete="current-password" 
                            placeholder="••••••••"
                            class="block w-full pl-10 pr-10 py-2.5 text-sm text-gray-900 dark:text-white bg-white dark:bg-slate-800 border @error('password') border-red-400 focus:ring-red-500 focus:border-red-500 @else border-gray-300 dark:border-slate-700 focus:ring-blue-500 focus:border-blue-500 @enderror rounded-lg placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-1 transition"
                        />
                        <button 
                            type="button" 
                            @click="show = !show" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 focus:outline-none"
                            tabindex="-1"
                        >
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordarme -->
                <div class="flex items-center pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input 
                            id="remember_me" 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 text-blue-600 bg-gray-100 dark:bg-slate-800 border-gray-300 dark:border-slate-700 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer"
                        />
                        <span class="ms-2 text-xs font-medium text-gray-600 dark:text-slate-300">Mantener sesión iniciada</span>
                    </label>
                </div>

                <!-- Botón de Ingreso -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full flex items-center justify-center py-2.5 px-4 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-md shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 cursor-pointer"
                    >
                        <span>Ingresar al Sistema</span>
                        <i class="fas fa-sign-in-alt ms-2 text-sm"></i>
                    </button>
                </div>
            </form>

            <!-- Credenciales de prueba informativas -->
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50/80 dark:bg-slate-850 -mx-6 -mb-6 sm:-mx-8 sm:-mb-8 p-4 rounded-b-2xl text-[11px] text-gray-600 dark:text-slate-400 flex items-start space-x-2.5">
                <i class="fas fa-info-circle text-blue-500 text-sm mt-0.5 shrink-0"></i>
                <div class="leading-relaxed">
                    <span class="font-semibold text-gray-800 dark:text-slate-200">Credenciales de acceso:</span>
                    <div class="mt-0.5 text-gray-500 dark:text-slate-400 font-mono text-[11px]">
                        admin@csalud.com | admin1234
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
