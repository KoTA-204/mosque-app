@extends('layouts.landing')
@section('title', 'Masjid Luqmanul Hakim - Politeknik Negeri Bandung')
@section('description', 'Masjid Luqmanul Hakim - Pusat ibadah dan kegiatan Islam di Politeknik Negeri Bandung')
@section('content')

<!-- Hero Banner -->
<section class="relative h-[88vh] min-h-[500px] max-h-[780px] overflow-hidden" id="hero">
    {{-- Slides --}}
    @foreach($banners as $i => $banner)
    <div class="hero-slide absolute inset-0 {{ $i === 0 ? 'active' : '' }}" data-slide="{{ $i }}">
        <div class="absolute inset-0">
            @if($banner['type'] === 'image')
                <img src="{{ $banner['image'] }}" alt="{{ $banner['title'] }}" 
                     class="w-full h-full object-cover transition-transform duration-[8000ms] scale-105" 
                     style="{{ $i === 0 ? 'transform: scale(1)' : '' }}">
            @elseif($banner['type'] === 'gradient')
                <div class="w-full h-full {{ $banner['gradient'] }}"></div>
            @endif
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 h-full flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-xl {{ $banner['align'] === 'right' ? 'ml-auto text-right' : '' }}">
                    @if(isset($banner['badge']))
                    <span class="inline-flex items-center gap-1.5 bg-green-600/90 text-white text-sm font-semibold px-3 py-1.5 rounded-full mb-4 backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                        {{ $banner['badge'] }}
                    </span>
                    @endif
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-4" style="text-shadow: 0 2px 20px rgba(0,0,0,0.5)">
                        {!! $banner['title'] !!}
                    </h1>
                    
                    <p class="text-white/90 text-base sm:text-lg leading-relaxed mb-8" style="text-shadow: 0 1px 4px rgba(0,0,0,0.5)">
                        {{ $banner['subtitle'] }}
                    </p>
                    
                    @if(isset($banner['cta']))
                    <div class="flex flex-wrap gap-3 {{ $banner['align'] === 'right' ? 'justify-end' : '' }}">
                        @foreach($banner['cta'] as $cta)
                        <a href="{{ $cta['url'] }}" 
                           class="{{ $cta['style'] === 'primary' ? 'bg-green-600 hover:bg-green-500 text-white' : 'bg-white/20 hover:bg-white/30 text-white border border-white/40 backdrop-blur-sm' }} font-semibold px-6 py-3 rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg text-base">
                            {{ $cta['label'] }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Navigation Arrows --}}
    <button class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all duration-200 border border-white/20" id="prev-slide">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all duration-200 border border-white/20" id="next-slide">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Dots Indicator --}}
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2" id="slide-dots">
        @foreach($banners as $i => $banner)
        <button class="hero-dot w-2 h-2 rounded-full transition-all duration-300 {{ $i === 0 ? 'w-8 bg-white' : 'bg-white/50' }}" data-dot="{{ $i }}"></button>
        @endforeach
    </div>

    {{-- Slide Counter --}}
    <div class="absolute bottom-6 right-6 z-20 text-white/80 text-sm font-medium">
        <span id="current-slide-num">1</span><span class="text-white/40">/{{ count($banners) }}</span>
    </div>
</section>


<!-- Jadwal Shalat -->
<section id="jadwal-shalat" class="py-10 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
 
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
                <div>
                    <span class="block text-xs font-semibold tracking-widest text-gray-400 uppercase">Jadwal Shalat 5 Waktu</span>
                    <span class="block text-sm font-medium text-gray-800 leading-tight" id="jadwal-tanggal">Memuat...</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="font-mono text-base font-semibold text-gray-800 tabular-nums" id="live-clock">--:--:--</span>
                        <span class="text-xs font-medium text-gray-400">WIB</span>
                    </div>
                    <div class="w-px h-5 bg-gray-200"></div>
                    <div class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <select id="kota-select" class="text-sm font-medium text-gray-600 bg-transparent border-none outline-none cursor-pointer text-right w-40 sm:w-56 truncate">
                            <option value="">Memuat...</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="jadwal-loading" class="flex items-center justify-center gap-3 py-16 text-gray-400">
                <svg class="animate-spin w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-sm">Memuat jadwal shalat...</span>
            </div>
            
            <!-- Error -->
            <div id="jadwal-error" class="hidden text-center py-12 text-sm text-red-400">
                Gagal memuat jadwal.
                <button onclick="loadJadwalShalat()" class="underline hover:text-red-600 ml-1">Coba lagi</button>
            </div>
 
            <div id="jadwal-content" class="hidden">
 
                <div class="grid grid-cols-1 sm:grid-cols-[220px_1fr]">
 
                    {{-- SPOTLIGHT (kiri) --}}
                    <div class="bg-green-700 p-6 flex flex-col justify-between gap-6 border-b sm:border-b-0 sm:border-r border-green-600">
                        <div>
                            <span class="block text-xs font-semibold tracking-widest text-green-300 uppercase mb-3">Berikutnya</span>
                            <p class="text-green-200 text-sm font-medium mb-1" id="spot-name">—</p>
                            <p class="font-mono text-5xl font-semibold text-white leading-none tabular-nums" id="spot-time">--:--</p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-green-400" id="spot-prev-name">—</span>
                                <span class="text-xs text-green-400" id="spot-next-name-small">—</span>
                            </div>
                            <div class="w-full h-px bg-green-600 rounded-full overflow-hidden">
                                <div id="spot-bar" class="h-full bg-green-300 rounded-full transition-all duration-700" style="width: 0%"></div>
                            </div>
                            <p class="text-green-400 text-xs mt-3" id="spot-countdown">—</p>
                        </div>
                    </div>
 
                    <div class="divide-y divide-gray-100">
 
                        @php
                        $shalatList = [
                            ['key' => 'imsak',   'label' => 'Imsak',
                             'icon' => '<path d="M10 2C7.5 2.5 5.5 4.7 5.5 7.5C5.5 10.5 8 13 11 13C11.8 13 12.5 12.8 13.2 12.4C12.1 14.1 10.2 15 8 15C4.7 15 2 12.3 2 9C2 5.7 4.5 3 7.7 2.3C8.4 2.1 9.2 2 10 2Z" stroke-linejoin="round"/>'],
                            ['key' => 'subuh',   'label' => 'Subuh',
                             'icon' => '<circle cx="8" cy="8" r="2.8"/><path d="M8 2v1.3M8 12.7V14M2 8h1.3M12.7 8H14M3.8 3.8l.9.9M11.3 11.3l.9.9M3.8 12.2l.9-.9M11.3 4.7l.9-.9" opacity=".45"/>'],
                            ['key' => 'dzuhur',  'label' => 'Dzuhur',
                             'icon' => '<circle cx="8" cy="8" r="3.2"/><path d="M8 1.5V3M8 13v1.5M1.5 8H3M13 8h1.5M3.2 3.2l1.1 1.1M11.7 11.7l1.1 1.1M3.2 12.8l1.1-1.1M11.7 4.3l1.1-1.1"/>'],
                            ['key' => 'ashar',   'label' => 'Ashar',
                             'icon' => '<circle cx="8" cy="5.5" r="2.5"/><path d="M1.5 11.5C3.2 9.5 5.2 8.5 8 8.5s4.8 1 6.5 3"/><path d="M.5 14h15" opacity=".35"/>'],
                            ['key' => 'maghrib', 'label' => 'Maghrib',
                             'icon' => '<circle cx="8" cy="4.8" r="2.3"/><path d="M2 11C3.8 8.5 5.7 7.2 8 7.2s4.2 1.3 6 3.8"/><path d="M.5 14h15"/>'],
                            ['key' => 'isya',    'label' => 'Isya',
                             'icon' => '<path d="M10 1.5C7.5 2 5.5 4.2 5.5 7C5.5 10 8 12.5 11 12.5C11.8 12.5 12.5 12.3 13.2 12C12.1 13.7 10.2 15 8 15C4.7 15 2 12.3 2 9C2 5.7 4.5 3 7.7 2.3"/>'],
                        ];
                        @endphp
 
                        @foreach($shalatList as $s)
                        <div class="shalat-row flex items-center gap-4 px-5 py-3.5 transition-colors duration-150 hover:bg-gray-50"
                             data-shalat="{{ $s['key'] }}">
 
                            <div class="shalat-icon w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 transition-colors duration-150">
                                <svg class="w-4 h-4 text-gray-400 transition-colors duration-150"
                                     fill="none" stroke="currentColor" stroke-width="1.5"
                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 16 16">
                                    {!! $s['icon'] !!}
                                </svg>
                            </div>
 
                            <span class="shalat-label flex-1 text-sm font-medium text-gray-500 transition-colors duration-150">
                                {{ $s['label'] }}
                            </span>
 
                            <span class="jadwal-time font-mono text-base font-semibold text-gray-800 tabular-nums"
                                  data-field="{{ $s['key'] }}">--:--</span>
 
                            <span class="active-pip w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse opacity-0 shrink-0 transition-opacity duration-150"></span>
                        </div>
                        @endforeach
 
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Program Kegiatan -->
<section class="py-16 bg-gray-50" id="program-kegiatan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 sm:mb-12" data-animate>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 section-title">Program Kegiatan</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($programKegiatan as $kegiatan)
                @php($cfg = $kegiatan->jenisConfig())
                @php($st = $kegiatan->statusConfig())
                @php($deskripsi = $kegiatan->deskripsi ?: 'Belum ada deskripsi untuk kegiatan ini.')
                <article class="kegiatan-card group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col overflow-hidden">
                    {{-- Aksen warna sesuai jenis --}}
                    <span class="absolute inset-x-0 top-0 h-1 {{ $cfg['accent_class'] }}"></span>

                    <div class="p-5 flex flex-col h-full">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl {{ $cfg['bg_class'] }} flex items-center justify-center text-lg shadow-sm shrink-0">{{ $cfg['icon'] }}</div>
                                <span class="text-sm font-semibold {{ $cfg['tag_class'] }} px-2.5 py-1 rounded-full truncate">{{ $cfg['label'] }}</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-medium {{ $st['class'] }} px-2 py-0.5 rounded-full shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>
                                {{ $st['label'] }}
                            </span>
                        </div>

                        {{-- Judul + deskripsi (info utama) --}}
                        <h3 class="text-base font-bold text-gray-900 group-hover:text-green-700 transition-colors line-clamp-1">{{ $kegiatan->nama_kegiatan }}</h3>
                        <p class="mt-1.5 text-sm text-gray-500 leading-relaxed line-clamp-2 min-h-[2.5rem]">{{ $deskripsi }}</p>

                        {{-- Meta --}}
                        <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate">{{ $kegiatan->rentangTanggal() }}</span>
                            </div>
                        </div>

                        {{-- Aksi --}}
                        <div class="mt-4 flex items-center justify-end">
                            <button type="button"
                                onclick="openKegiatanModal(this)"
                                data-nama="{{ $kegiatan->nama_kegiatan }}"
                                data-deskripsi="{{ $kegiatan->deskripsi }}"
                                data-jenis="{{ $cfg['label'] }}"
                                data-icon="{{ $cfg['icon'] }}"
                                data-status="{{ $st['label'] }}"
                                data-tanggal="{{ $kegiatan->rentangTanggal() }}"
                                data-anggaran="{{ $kegiatan->anggaranFormatted() }}"
                                data-bg="{{ $cfg['bg_class'] }}"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-800 transition-colors">
                                Detail
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 text-center py-16 px-6 bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="text-3xl mb-3">🗓️</div>
                    <p class="text-gray-500 font-medium">Belum ada kegiatan yang sedang berjalan saat ini.</p>
                    <p class="text-gray-400 text-sm mt-1">Silakan cek kembali secara berkala.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Detail Kegiatan --}}
    <div id="kegiatan-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-close-modal></div>
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto transform transition-all">
            <div id="km-header" class="p-6 text-white relative bg-green-700">
                <button type="button" data-close-modal aria-label="Tutup"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-2xl shrink-0" id="km-icon"></div>
                    <span id="km-status" class="inline-block text-sm font-semibold px-2.5 py-1 rounded-full bg-white/20"></span>
                </div>
                <h3 id="km-title" class="text-xl font-bold leading-snug pr-8"></h3>
            </div>
            <div class="p-6 space-y-5">
                <p id="km-deskripsi" class="text-sm text-gray-600 leading-relaxed"></p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a2 2 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-medium">Jenis Kegiatan</p>
                            <p class="text-sm font-semibold text-gray-800" id="km-jenis"></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-medium">Jadwal Pelaksanaan</p>
                            <p class="text-sm font-semibold text-gray-800" id="km-tanggal"></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-medium">Anggaran</p>
                            <p class="text-sm font-semibold text-gray-800" id="km-anggaran"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kilas Laporan Keuangan -->
<section class="py-16 bg-gray-50" id="laporan-keuangan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-animate>
            <h2 class="text-3xl font-bold text-gray-900 section-title">Kilas Transparansi Keuangan</h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" data-animate>
            {{-- Saldo Awal --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Saldo Awal</span>
                </div>
                <p class="text-sm text-gray-400 mb-2">{{ $laporan['periode_awal'] }}</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($laporan['saldo_awal'], 0, ',', '.') }}</p>
                <p class="text-sm text-gray-400 mt-0.5">Rp</p>
            </div>

            {{-- Pemasukan --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Pemasukan</span>
                </div>
                <p class="text-sm text-gray-400 mb-2">Yang masuk hingga hari ini</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($laporan['pemasukan'], 0, ',', '.') }}</p>
                <p class="text-sm text-green-400 mt-0.5">Rp</p>
            </div>

            {{-- Pengeluaran --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-red-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-red-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Pengeluaran</span>
                </div>
                <p class="text-sm text-gray-400 mb-2">Yang keluar hingga hari ini</p>
                <p class="text-xl font-bold text-red-500">{{ number_format($laporan['pengeluaran'], 0, ',', '.') }}</p>
                <p class="text-sm text-red-400 mt-0.5">Rp</p>
            </div>

            {{-- Saldo Akhir --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500">Saldo Akhir</span>
                </div>
                <p class="text-sm text-gray-400 mb-2">{{ $laporan['periode_akhir'] }}</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($laporan['saldo_akhir'], 0, ',', '.') }}</p>
                <p class="text-sm text-gray-400 mt-0.5">Rp</p>
            </div>
        </div>

        <div class="text-center mt-8" data-animate>
            <a href="{{ route('laporan-keuangan.index') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat Detail
            </a>
        </div>
    </div>
</section>

<!-- Temukan Kami -->
<section class="py-16" id="temukan-kami">
    <div class="max-w-7xl mx-auto px-8 sm:px-20 lg:px-20 text-center">
        <div class="flex items-center justify-center gap-5">
            {{-- LINE --}}
            <a href="https://line.me/R/ti/p/%40qxh9919x" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-green-500 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                </svg>
            </a>
            {{-- Instagram --}}
            <a href="https://www.instagram.com/masjidlhpolban/" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-pink-600 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
            {{-- YouTube --}}
            <a href="https://www.youtube.com/@masjidlhpolban5893" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-red-600 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
            </a>
            {{-- Email --}}
            <a href="mailto:luqmanulhakimdkm@gmail.com" class="w-20 h-20 bg-gray-100 hover:bg-blue-500 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Hero Slider
(function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const counter = document.getElementById('current-slide-num');
    let current = 0;
    let autoplayInterval;
    
    function goTo(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('w-8', 'bg-white');
        dots[current].classList.add('bg-white/50');
        
        current = (idx + slides.length) % slides.length;
        
        slides[current].classList.add('active');
        dots[current].classList.add('w-8', 'bg-white');
        dots[current].classList.remove('bg-white/50');
        counter.textContent = current + 1;
    }
    
    function startAutoplay() {
        autoplayInterval = setInterval(() => goTo(current + 1), 5500);
    }
    
    document.getElementById('next-slide')?.addEventListener('click', () => { clearInterval(autoplayInterval); goTo(current + 1); startAutoplay(); });
    document.getElementById('prev-slide')?.addEventListener('click', () => { clearInterval(autoplayInterval); goTo(current - 1); startAutoplay(); });
    
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => { clearInterval(autoplayInterval); goTo(i); startAutoplay(); });
    });
    
    // Touch/Swipe support
    let startX = 0;
    document.getElementById('hero')?.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    document.getElementById('hero')?.addEventListener('touchend', e => {
        const diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) { clearInterval(autoplayInterval); goTo(diff > 0 ? current + 1 : current - 1); startAutoplay(); }
    });
    
    startAutoplay();
})();


// Jadwal Shalat
(function () {
    const KEYS  = ['imsak','subuh','dzuhur','ashar','maghrib','isya'];
    const NAMES = { imsak:'Imsak', subuh:'Subuh', dzuhur:'Dzuhur', ashar:'Ashar', maghrib:'Maghrib', isya:'Isya' };
    let jdwl = {};
 
    function tickClock() {
        const el = document.getElementById('live-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    }
 
    function toMin(t) {
        if (!t || t.startsWith('-')) return -1;
        const [h, m] = t.split(':').map(Number);
        return h * 60 + m;
    }
 
    function setEl(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }
 
    function updateActive() {
        if (!Object.keys(jdwl).length) return;
        const nm    = new Date().getHours() * 60 + new Date().getMinutes();
        const order = ['subuh','dzuhur','ashar','maghrib','isya'];
        let nextKey = null, nextMin = null, prevKey = null;
 
        for (let i = 0; i < order.length; i++) {
            const t = toMin(jdwl[order[i]]);
            if (t > nm) { nextKey = order[i]; nextMin = t; prevKey = i > 0 ? order[i-1] : 'subuh'; break; }
        }
 
        document.querySelectorAll('.shalat-row').forEach(row => {
            row.classList.remove('bg-green-50');
            row.querySelector('.shalat-icon').classList.replace('bg-green-100', 'bg-gray-100');
            row.querySelector('.shalat-icon svg').classList.replace('text-green-600', 'text-gray-400');
            row.querySelector('.shalat-label').classList.replace('text-green-800', 'text-gray-500');
            row.querySelector('.shalat-label').classList.remove('font-semibold');
            row.querySelector('.jadwal-time').classList.replace('text-green-800', 'text-gray-800');
            row.querySelector('.active-pip').classList.add('opacity-0');
        });
 
        if (nextKey) {
            const row = document.querySelector(`.shalat-row[data-shalat="${nextKey}"]`);
            if (row) {
                row.classList.add('bg-green-50');
                row.querySelector('.shalat-icon').classList.replace('bg-gray-100', 'bg-green-100');
                row.querySelector('.shalat-icon svg').classList.replace('text-gray-400', 'text-green-600');
                row.querySelector('.shalat-label').classList.replace('text-gray-500', 'text-green-800');
                row.querySelector('.shalat-label').classList.add('font-semibold');
                row.querySelector('.jadwal-time').classList.replace('text-gray-800', 'text-green-800');
                row.querySelector('.active-pip').classList.remove('opacity-0');
            }
 
            const diff  = nextMin - nm;
            const h     = Math.floor(diff / 60), m = diff % 60;
            const cdStr = h > 0 ? `${h} jam ${String(m).padStart(2,'0')} menit lagi` : `${m} menit lagi`;
 
            setEl('spot-name', NAMES[nextKey]);
            setEl('spot-time', (jdwl[nextKey] || '--:--').substring(0, 5));
            setEl('spot-countdown', cdStr);
            setEl('spot-prev-name', prevKey ? NAMES[prevKey] : '');
            setEl('spot-next-name-small', NAMES[nextKey]);
            setEl('footer-label', 'Menuju ' + NAMES[nextKey]);
            setEl('footer-cd', cdStr);
 
            const prevMin = toMin(jdwl[prevKey] || jdwl['subuh']);
            const span    = nextMin - prevMin;
            const pct     = span > 0 ? Math.min(100, Math.round(((nm - prevMin) / span) * 100)) : 0;
            const bar     = document.getElementById('spot-bar');
            if (bar) bar.style.width = pct + '%';
        } else {
            setEl('spot-name', 'Isya');
            setEl('spot-time', (jdwl.isya || '--:--').substring(0, 5));
            setEl('spot-countdown', 'Shalat hari ini telah selesai');
            setEl('footer-label', 'Semua shalat hari ini telah selesai');
            setEl('footer-cd', '—');
            const bar = document.getElementById('spot-bar');
            if (bar) bar.style.width = '100%';
        }
    }

    async function loadDaftarKota() {
        try {
            const res = await fetch('/api/jadwal-shalat/kota');
            const json = await res.json();
            const select = document.getElementById('kota-select');
            select.innerHTML = '';

            json.data.forEach(kota => {
                const opt = document.createElement('option');
                opt.value = JSON.stringify({ provinsi: kota.provinsi, kabkota: kota.kabkota });
                opt.textContent = kota.label;
                if (kota.kabkota === 'Kota Bandung') opt.selected = true; // default
                select.appendChild(opt);
            });

            loadJadwalShalat();
        } catch {
            // biarkan fallback opsi default kalau gagal load
        }
    }
 
    async function loadJadwalShalat(kotaValue) {
        const raw = kotaValue || document.getElementById('kota-select')?.value;
        if (!raw) return;

        const { provinsi, kabkota } = JSON.parse(raw);

        document.getElementById('jadwal-loading')?.classList.remove('hidden');
        document.getElementById('jadwal-content')?.classList.add('hidden');
        document.getElementById('jadwal-error')?.classList.add('hidden');

        try {
            const res = await fetch(`/api/jadwal-shalat?provinsi=${encodeURIComponent(provinsi)}&kabkota=${encodeURIComponent(kabkota)}`);
            if (!res.ok) throw 0;
            const data = await res.json();
            if (data.status !== 'ok') throw 0;
            jdwl = data.data;
        } catch {
            document.getElementById('jadwal-loading')?.classList.add('hidden');
            document.getElementById('jadwal-error')?.classList.remove('hidden');
            return;
        }
 
        KEYS.forEach(k => {
            const el = document.querySelector(`[data-field="${k}"]`);
            if (el && jdwl[k]) el.textContent = jdwl[k].substring(0, 5);
        });
 
        const tgl = document.getElementById('jadwal-tanggal');
        if (tgl) tgl.textContent = jdwl.tanggal || new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
 
        document.getElementById('jadwal-loading')?.classList.add('hidden');
        document.getElementById('jadwal-content')?.classList.remove('hidden');
        updateActive();
    }
 
    document.getElementById('kota-select')?.addEventListener('change', function () { loadJadwalShalat(this.value); });
    tickClock();
    setInterval(tickClock, 1000);
    setInterval(updateActive, 30000);
    loadDaftarKota();
    })();

// Modal Detail Kegiatan
(function () {
    const modal = document.getElementById('kegiatan-modal');
    if (!modal) return;
    const header = document.getElementById('km-header');

    window.openKegiatanModal = function (btn) {
        const d = btn.dataset;
        document.getElementById('km-icon').textContent     = d.icon || '';
        document.getElementById('km-title').textContent    = d.nama || '';
        document.getElementById('km-status').textContent   = d.status || '';
        document.getElementById('km-jenis').textContent    = d.jenis || '-';
        document.getElementById('km-tanggal').textContent  = d.tanggal || '-';
        document.getElementById('km-anggaran').textContent = d.anggaran || '-';

        const desc = document.getElementById('km-deskripsi');
        if (d.deskripsi && d.deskripsi.trim() !== '') {
            desc.textContent = d.deskripsi;
            desc.classList.remove('hidden');
        } else {
            desc.textContent = '';
            desc.classList.add('hidden');
        }

        header.className = 'p-6 text-white relative ' + (d.bg || 'bg-green-700');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
@endpush