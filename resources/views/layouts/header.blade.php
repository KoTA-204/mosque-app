<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="main-header">
    <div class="bg-white/95 backdrop-blur-md shadow-sm border-b border-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <div class="w-30 h-30 bg-gradient-to-br  flex items-center justify-center shadow-md overflow-hidden">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Masjid" class="w-10 h-10 object-contain">
                    </div>
                    <div>
                        <span class="font-bold text-gray-900 text-sm sm:text-base leading-tight block">
                            Masjid Luqmanul Hakim
                        </span>
                    </div>
                </div>

                {{-- Navigation --}}
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ url('/') }}" class="text-sm font-semibold text-green-700 border-b-2 border-green-600 pb-0.5 transition-colors">Utama</a>
                    <a href="{{ url('/donasi') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition-colors">Donasi</a>
                    <a href="{{ route('laporan-keuangan.index') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition-colors">Laporan Keuangan</a>
                    <a href="{{ url('/tentang-kami') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition-colors">Tentang Kami</a>
                </nav>

                {{-- Login Button --}}
                <div class="flex items-center gap-3">
                    <a href="{{ url('/login') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5">
                        Login
                    </a>
                    {{-- Mobile Menu Button --}}
                    <button class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors" id="mobile-menu-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div class="md:hidden hidden bg-white border-t border-gray-100 px-4 pb-4" id="mobile-menu">
            <nav class="flex flex-col gap-3 pt-3">
                <a href="{{ url('/') }}" class="text-sm font-semibold text-green-700 py-2">Utama</a>
                <a href="{{ url('/donasi') }}" class="text-sm text-gray-600 py-2">Donasi</a>
                <a href="{{ url('/organisasi') }}" class="text-sm text-gray-600 py-2">Organisasi</a>
                <a href="{{ url('/tentang-kami') }}" class="text-sm text-gray-600 py-2">Tentang Kami</a>
            </nav>
        </div>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>