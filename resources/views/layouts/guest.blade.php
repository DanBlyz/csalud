<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Centro de Salud') }} - Acceso</title>

        <!-- Dark Mode Initializer Script (Prevents FOUC) -->
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Font Awesome 6 Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        <!-- Tailwind CSS (Compilado sin Vite) -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles
    </head>
    <body 
        class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-100 dark:bg-slate-950 min-h-screen flex flex-col justify-center items-center p-4 selection:bg-blue-500 selection:text-white transition-colors duration-200"
        x-data="{ 
            darkMode: document.documentElement.classList.contains('dark'),
            toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    this.darkMode = false;
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    this.darkMode = true;
                }
            }
        }"
    >
        <!-- Top right theme toggle for guest view -->
        <div class="fixed top-4 right-4 z-50">
            <button 
                @click="toggleTheme()" 
                type="button" 
                class="p-2.5 rounded-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-xs text-gray-600 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-slate-700 shadow-sm focus:outline-none transition-colors"
                :title="darkMode ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'"
            >
                <i x-show="darkMode" class="fas fa-sun text-amber-400 text-base"></i>
                <i x-show="!darkMode" class="fas fa-moon text-slate-600 text-base"></i>
            </button>
        </div>

        <!-- Background subtle decoration -->
        <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden opacity-30 dark:opacity-20">
            <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-blue-400 dark:bg-blue-600 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-teal-300 dark:bg-teal-700 blur-3xl"></div>
        </div>

        <div class="w-full sm:max-w-md">
            {{ $slot }}
        </div>

        <!-- Footer note -->
        <div class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400">
            <p><b class="text-slate-700 dark:text-slate-300">Centro de Salud</b> &copy; {{ date('Y') }} - Sistema de Gestión Hospitalaria</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Acceso restringido únicamente para personal autorizado.</p>
        </div>

        @livewireScripts
    </body>
</html>
