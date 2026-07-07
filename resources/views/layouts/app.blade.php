<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <title>{{ $title ?? 'Dashboard' }} | MosQue</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = savedTheme || 'light';
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

           Alpine.store('sidebar', {
                isExpanded: false,
                isMobileOpen: false,
                isHovered: false,
                isMobile: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    if (!this.isMobile && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                if (document.body) document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                if (document.body) document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>
    @stack('styles')
</head>

<body
    x-data="{ loaded: true }"
    x-init="
        $nextTick(() => {
            $store.sidebar.isMobile = window.innerWidth < 1280;
            $store.sidebar.isExpanded = window.innerWidth >= 1280;
            
            const checkMobile = () => {
                if (window.innerWidth < 1280) {
                    $store.sidebar.isMobile = true;
                    $store.sidebar.setMobileOpen(false);
                    $store.sidebar.isExpanded = false;
                } else {
                    $store.sidebar.isMobile = false;
                    $store.sidebar.isMobileOpen = false;
                    $store.sidebar.isExpanded = true;
                }
            };
            window.addEventListener('resize', checkMobile);
        });
    ">

    <x-common.preloader/>

    @auth
    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[280px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[72px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered
            }">
            @include('layouts.app-header')
            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @yield('content')
            </div>
        </div>
    </div>
    @else
    @include('layouts.header')
    <main class="pt-16 min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </div>
    </main>
    @include('layouts.footer')
    @endauth
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @include('layouts.confirm-global')
</body>

@stack('scripts')

</html>