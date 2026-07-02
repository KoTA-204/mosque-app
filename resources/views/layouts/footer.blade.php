<footer class="bg-green-900 text-white">
    {{-- Main Footer --}}
    <div class="bg-green-900 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                {{-- Brand --}}
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 bg-white flex items-center justify-center shadow-md overflow-hidden rounded-xl">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo Masjid" class="w-11 h-11 object-contain">
                        </div>
                        <span class="font-bold text-white text-base sm:text-lg leading-tight">
                            Masjid Luqmanul Hakim
                        </span>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="text-white font-semibold mb-4 text-base">Tautan Cepat</h4>
                    <ul class="space-y-2.5">
                        <li><a href="{{ url('/') }}#program-kegiatan" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Program Kegiatan</a></li>
                        <li><a href="{{ route('laporan-keuangan.index') }}" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Transparansi Keuangan</a></li>
                        <li><a href="{{ route('laporan.posisi-keuangan') }}" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Laporan Keuangan (ISAK 335)</a></li>
                    </ul>
                </div>

                {{-- Map Embed --}}
                <div>
                    <h4 class="text-white font-semibold mb-3 text-base">Lokasi Masjid</h4>
                    <div class="rounded-xl overflow-hidden border-2 border-green-700">
                        <iframe src="https://www.google.com/maps?q=Masjid+Luqmanul+Hakim+Politeknik+Negeri+Bandung&output=embed"
                                width="100%" height="150" style="border:0;"
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                title="Lokasi Masjid Luqmanul Hakim" class="w-full h-36"></iframe>
                    </div>
                    <a href="https://maps.app.goo.gl/NJUZSjfSkD6q9XPR6" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-green-300 hover:text-white text-sm mt-3 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                    <p class="text-green-300 text-sm mt-2 leading-relaxed">Politeknik Negeri Bandung, Jl. Kampus Polban, Desa Ciwaruga, Kabupaten Bandung Barat 40012</p>
                </div>
            </div>

            <div class="border-t border-green-800 mt-8 pt-6 text-center">
                <p class="text-green-400 text-sm">&copy; {{ date('Y') }} Masjid Luqmanul Hakim. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</footer>