<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PICONS — PICT Consumables')</title>

    <link rel="icon" type="image/png" href="{{ asset('logo/pict.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/pict.png') }}">

    {{-- ═══ Tailwind via Vite (bukan CDN) ═══ --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js: gunakan versi spesifik, tetap defer --}}
    <script defer src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js"></script>

    {{-- Lucide: versi spesifik + defer agar tidak render-blocking --}}
    <script defer src="https://unpkg.com/lucide@0.400.0/dist/umd/lucide.min.js"></script>

    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        @include('layouts.partials.sidebar')

        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-black/40 lg:hidden"
             aria-hidden="true"></div>

        <div class="lg:ml-64 flex flex-col min-h-screen">
            @include('layouts.partials.navbar')

            <main class="flex-1 p-4 sm:p-6">
                @yield('content')
            </main>

            @hasSection('footer')
                <footer class="border-t border-slate-200 bg-white px-6 py-4 text-xs text-slate-500">
                    @yield('footer')
                </footer>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>

    @include('components.flash-modal')
    @include('components.confirm-modal')
    @include('components.pr-number-modal')

    @stack('scripts')
</body>
</html>