<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Centro de Salud') }}</title>

        <!-- Dark Mode Initializer Script (Prevents FOUC) -->
        <script>
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Google / Bunny Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Font Awesome 6 Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        <!-- Tailwind CSS (Compilado sin Vite) -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles
    </head>
    <body 
        class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-slate-100 dark:bg-slate-950 h-full transition-colors duration-200"
        x-data="{ 
            sidebarOpen: window.innerWidth >= 1024,
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
            },
            init() {
                window.addEventListener('resize', () => {
                    if (window.innerWidth < 1024) {
                        this.sidebarOpen = false;
                    }
                });
            }
        }"
    >
        <!-- Mobile Sidebar Backdrop -->
        <div 
            x-show="sidebarOpen" 
            @click="sidebarOpen = false" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs lg:hidden"
            style="display: none;"
        ></div>

        <!-- Sidebar (Menu Lateral AdminLTE) -->
        @include('layouts.partials.sidebar')

        <!-- Main Wrapper (Responsive Content Area) -->
        <div 
            class="min-h-screen flex flex-col transition-all duration-300 ease-in-out"
            :class="{ 'lg:ps-64': sidebarOpen, 'lg:ps-0': !sidebarOpen }"
        >
            <!-- Header (Navbar Superior con Botón de Modo Claro / Oscuro) -->
            @include('layouts.partials.header')

            <!-- Optional Content Header / Breadcrumb -->
            @isset($header)
                <div class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 py-3.5 px-4 sm:px-6 transition-colors duration-200">
                    {{ $header }}
                </div>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <!-- Session Alerts -->
                @if (session('status'))
                    <div class="mb-4 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center shadow-xs">
                        <i class="fas fa-check-circle text-emerald-500 text-lg me-3"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm flex items-center shadow-xs">
                        <i class="fas fa-exclamation-triangle text-red-500 text-lg me-3"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <!-- Footer (Pie de página AdminLTE) -->
            @include('layouts.partials.footer')
        </div>

        @livewireScripts
    </body>
</html>
