<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Sistem Cuaca') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap"
        rel="stylesheet">

    <script>
        (function() {
            const t = localStorage.getItem("theme-dashboard");
            const dark = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>

<body
    x-data="{sidebarOpen: false}"
    class="min-h-screen font-ui bg-background text-foreground antialiased">

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-[199] bg-black/60 md:hidden"></div>

    <x-sidebar/>

    <div class="md:pl-64">
        <x-header :title="$title ?? null"/>

        <main class="relative">
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 3000)"
                    x-transition
                    class="fixed top-20 right-4 z-50 rounded-lg border border-border bg-card px-4 py-3 text-sm text-foreground shadow-lg"
                >
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-check-circle class="h-5 w-5 text-emerald-500" />
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    x-transition
                    class="fixed top-20 right-4 z-50 rounded-lg border border-border bg-card px-4 py-3 text-sm text-foreground shadow-lg"
                >
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-x-circle class="h-5 w-5 text-rose-500" />
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            <div class="{{ request()->routeIs('monitoring') ? 'p-3 md:p-4' : 'p-4 md:p-6' }}">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
    @stack('scripts')
</body>

</html>
