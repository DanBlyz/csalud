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
        <script src="{{ asset('js/sweetalert2/sweetalert2.all.min.js') }}"></script>

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

        <!-- SweetAlert2 Global Event Listeners & Dark Mode Adapter -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const getSwalTheme = () => {
                    const isDark = document.documentElement.classList.contains('dark');
                    return {
                        background: isDark ? '#0f172a' : '#ffffff',
                        color: isDark ? '#f8fafc' : '#1e293b',
                        confirmButtonColor: '#0284c7', // Primary Sky Blue
                        cancelButtonColor: '#64748b',  // Slate Gray
                    };
                };

                // Normal / Toast Alerts
                window.addEventListener('swal', (event) => {
                    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    if (!data) return;
                    const theme = getSwalTheme();
                    const isToast = data.toast !== undefined ? data.toast : true;

                    if (isToast) {
                        Swal.fire({
                            toast: true,
                            position: data.position || 'top-end',
                            showConfirmButton: false,
                            timer: data.timer || 3500,
                            timerProgressBar: true,
                            icon: data.icon || 'success',
                            title: data.title || '',
                            text: data.text || '',
                            background: theme.background,
                            color: theme.color,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: data.icon || 'info',
                            title: data.title || '',
                            text: data.text || '',
                            confirmButtonColor: theme.confirmButtonColor,
                            cancelButtonColor: theme.cancelButtonColor,
                            background: theme.background,
                            color: theme.color,
                        });
                    }
                });

                // Confirmation Dialogs (Destructive actions)
                window.addEventListener('swal:confirm', (event) => {
                    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    if (!data) return;
                    const theme = getSwalTheme();

                    Swal.fire({
                        title: data.title || '¿Está seguro?',
                        text: data.text || 'Esta acción no se puede deshacer.',
                        icon: data.icon || 'warning',
                        showCancelButton: true,
                        confirmButtonColor: data.confirmButtonColor || '#e11d48', // Rose Red
                        cancelButtonColor: theme.cancelButtonColor,
                        confirmButtonText: data.confirmButtonText || 'Sí, continuar',
                        cancelButtonText: data.cancelButtonText || 'Cancelar',
                        background: theme.background,
                        color: theme.color,
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (data.componentId && window.Livewire) {
                                const component = window.Livewire.find(data.componentId);
                                if (component && typeof component.call === 'function') {
                                    component.call(data.method, ...(data.params || []));
                                    return;
                                }
                            }
                            if (window.Livewire) {
                                window.Livewire.dispatch(data.event, data.params || {});
                            }
                        }
                    });
                });

                // Auto-trigger on Blade session flashes
                @if (session('success') || session('status'))
                    window.dispatchEvent(new CustomEvent('swal', {
                        detail: [{
                            icon: 'success',
                            title: '¡Éxito!',
                            text: "{{ session('success') ?? session('status') }}",
                            toast: true
                        }]
                    }));
                @endif

                @if (session('error'))
                    window.dispatchEvent(new CustomEvent('swal', {
                        detail: [{
                            icon: 'error',
                            title: 'Error',
                            text: "{{ session('error') }}",
                            toast: false
                        }]
                    }));
                @endif

                @if (session('warning'))
                    window.dispatchEvent(new CustomEvent('swal', {
                        detail: [{
                            icon: 'warning',
                            title: 'Atención',
                            text: "{{ session('warning') }}",
                            toast: true
                        }]
                    }));
                @endif
            });
        </script>
    </body>
</html>
