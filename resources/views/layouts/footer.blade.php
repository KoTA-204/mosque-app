<footer class="bg-green-900 text-white">
    {{-- Main Footer --}}
    <div class="bg-green-900 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                <div class="flex items-center gap-3">
                    <div class="w-40 h-40 bg-white  flex items-center justify-center shadow-md overflow-hidden">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Masjid" class="w-30 h-30 object-contain">
                    </div>
                    <div>
                        <span class="font-bold text-white text-sm sm:text-base leading-tight block">
                            Masjid Luqmanul Hakim
                        </span>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="text-white font-semibold mb-4">Layanan Kami</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ url('/') }}" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-green-500 rounded-full"></span>Kegiatan Islam</a></li>
                        <li><a href="{{ url('/donasi') }}" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-green-500 rounded-full"></span>Donasi</a></li>
                        <li><a href="{{ url('/organisasi') }}" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-green-500 rounded-full"></span>Organisasi</a></li>
                        <li><a href="{{ url('/tentang-kami') }}" class="text-green-300 hover:text-white text-sm transition-colors flex items-center gap-2"><span class="w-1 h-1 bg-green-500 rounded-full"></span>Tentang Kami</a></li>
                    </ul>
                </div>

                {{-- Map Embed --}}
                <div>
                    <h4 class="text-white font-semibold mb-3">Lokasi Masjid</h4>
                    <a href="https://maps.google.com/?q=Politeknik+Negeri+Bandung,+Ciwaruga,+Bandung+Barat" 
                       target="_blank" rel="noopener noreferrer"
                       class="block rounded-xl overflow-hidden border-2 border-green-700 hover:border-green-500 transition-colors group relative">
                        <img src="https://maps.googleapis.com/maps/api/staticmap?center=Politeknik+Negeri+Bandung,+Ciwaruga,+Kabupaten+Bandung+Barat&zoom=15&size=400x150&maptype=roadmap&markers=color:green%7CPoliteknik+Negeri+Bandung,+Ciwaruga&style=feature:all%7Csaturation:-20" 
                             alt="Lokasi Masjid"
                             class="w-full h-28 object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <div class="hidden w-full h-28 bg-green-800/50 items-center justify-center flex-col gap-2">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-green-300 text-xs text-center">Politeknik Negeri Bandung<br>Ciwaruga, Bandung Barat</span>
                        </div>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all flex items-center justify-center">
                            <span class="bg-white/90 text-gray-800 text-xs font-semibold px-3 py-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Buka di Google Maps</span>
                        </div>
                    </a>
                    <p class="text-green-400 text-xs mt-2">Politeknik Negeri Bandung, Jl. Kampus Polban, Desa Ciwaruga, Kabupaten Bandung Barat, 40012</p>
                </div>
            </div>

            <div class="border-t border-green-800 mt-8 pt-6 text-center">
                <p class="text-green-400 text-sm">&copy; {{ date('Y') }} Masjid Luqmanul Hakim. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</footer>