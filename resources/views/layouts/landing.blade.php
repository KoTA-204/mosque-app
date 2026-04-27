<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masjid Luqmanul Hakim')</title>
    <meta name="description" content="@yield('description', 'Sistem Pengelolaan Keuangan Masjid Luqmanul Hakim - Politeknik Negeri Bandung')">
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
   <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        arabic: ['Amiri', 'serif'],
                    },
                    colors: {
                        green: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Hero Slider */
        .hero-slide { display: none; }
        .hero-slide.active { display: block; }
        
        /* Smooth scroll */
        html { scroll-behavior: smooth; }
        
        /* Custom animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease forwards; }
        .animate-fade-in { animation: fadeIn 0.5s ease forwards; }
        
        /* Jadwal Shalat pulse */
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
        }
        .pulse-green { animation: pulse-green 2s infinite; }
        
        /* Program Kegiatan card hover */
        .kegiatan-card:hover .kegiatan-overlay { opacity: 1; }
        .kegiatan-card:hover .kegiatan-img { transform: scale(1.05); }
        
        /* Section underline */
        .section-title::after {
            content: '';
            display: block;
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #16a34a, #22c55e);
            margin: 10px auto 0;
            border-radius: 2px;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    {{-- Header --}}
    @include('layouts.header')

    {{-- Main Content --}}
    <main class="pt-16">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('layouts.footer')

    {{-- Global Scripts --}}
    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    entry.target.style.opacity = '1';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('[data-animate]').forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    </script>
    
    @stack('scripts')
</body>
</html>