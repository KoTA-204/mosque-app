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
                    <span class="inline-flex items-center gap-1.5 bg-green-600/90 text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-4 backdrop-blur-sm">
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
                           class="{{ $cta['style'] === 'primary' ? 'bg-green-600 hover:bg-green-500 text-white' : 'bg-white/20 hover:bg-white/30 text-white border border-white/40 backdrop-blur-sm' }} font-semibold px-6 py-3 rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg text-sm">
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
                    <span class="block text-[10px] font-semibold tracking-widest text-gray-400 uppercase">Jadwal Shalat</span>
                    <span class="block text-sm font-medium text-gray-800 leading-tight" id="jadwal-tanggal">Memuat...</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="font-mono text-base font-semibold text-gray-800 tabular-nums" id="live-clock">--:--:--</span>
                    </div>
                    <div class="w-px h-5 bg-gray-200"></div>
                    <div class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <select id="kota-select" class="text-sm font-medium text-gray-600 bg-transparent border-none outline-none cursor-pointer">
                            <option value="1301">Bandung</option>
                            <option value="1101">Jakarta</option>
                            <option value="1501">Surabaya</option>
                            <option value="1601">Yogyakarta</option>
                            <option value="1201">Medan</option>
                            <option value="2001">Makassar</option>
                            <option value="1701">Semarang</option>
                            <option value="1401">Palembang</option>
                            <option value="1801">Denpasar</option>
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
                            <span class="block text-[10px] font-semibold tracking-widest text-green-300 uppercase mb-3">Berikutnya</span>
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

<!-- Program Infaq & Shadaqah -->
<section class="py-16 bg-gray-50" id="program-infaq">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-animate>
            <h2 class="text-3xl font-bold text-gray-900 section-title">Program Infaq & Shadaqah</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" data-animate>
            @foreach($programInfaq as $program)
            <a href="{{ $program['url'] }}" class="group block bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
                {{-- Image --}}
                <div class="relative h-52 overflow-hidden bg-gray-200">
                    <img src="{{ $program['image'] }}" alt="{{ $program['title'] }}" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="lazy">
                    
                    {{-- Category Badge --}}
                    @if(isset($program['category']))
                    <span class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm text-green-700 text-xs font-bold px-2.5 py-1 rounded-lg shadow-sm">
                        {{ $program['category'] }}
                    </span>
                    @endif
                    
                    {{-- Progress overlay --}}
                    @if(isset($program['progress']))
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                        <div class="flex justify-between text-white text-xs mb-1.5">
                            <span>Terkumpul</span>
                            <span class="font-bold">{{ $program['progress'] }}%</span>
                        </div>
                        <div class="w-full bg-white/30 rounded-full h-1.5">
                            <div class="bg-green-400 rounded-full h-1.5 transition-all duration-500" style="width: {{ $program['progress'] }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="p-5">
                    <h3 class="font-bold text-gray-900 mb-2 group-hover:text-green-700 transition-colors">{{ $program['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">{{ $program['description'] }}</p>
                    
                    @if(isset($program['target']))
                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-400">Target</p>
                            <p class="text-sm font-bold text-gray-800">{{ $program['target'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 text-green-600 text-sm font-semibold group-hover:gap-2 transition-all">
                            Donasi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        <div class="text-center mt-10" data-animate>
            <a href="{{ url('/donasi') }}" class="inline-flex items-center gap-2 border-2 border-green-600 text-green-700 hover:bg-green-600 hover:text-white font-semibold px-8 py-3 rounded-xl transition-all duration-200">
                Lihat Program Lainnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>


<!-- Program Kegiatan -->
<section class="py-16 bg-white" id="program-kegiatan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-animate>
            <h2 class="text-3xl font-bold text-gray-900 section-title">Program Kegiatan</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($programKegiatan as $kegiatan)
            <div class="kegiatan-card group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-400 border border-gray-100 cursor-pointer">
                <div class="flex flex-col sm:flex-row">
                    {{-- Icon/Color Block --}}
                    <div class="relative sm:w-52 h-44 sm:h-auto flex-shrink-0 overflow-hidden {{ $kegiatan['bg_class'] }}">
                        {{-- Pattern --}}
                        <div class="absolute inset-0 opacity-10">
                            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="pattern-{{ $loop->index }}" width="30" height="30" patternUnits="userSpaceOnUse">
                                        <circle cx="15" cy="15" r="2" fill="white"/>
                                        <circle cx="0" cy="0" r="2" fill="white"/>
                                        <circle cx="30" cy="0" r="2" fill="white"/>
                                        <circle cx="0" cy="30" r="2" fill="white"/>
                                        <circle cx="30" cy="30" r="2" fill="white"/>
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#pattern-{{ $loop->index }})"/>
                            </svg>
                        </div>
                        
                        {{-- Icon --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-6xl transform group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                                {{ $kegiatan['icon'] }}
                            </div>
                        </div>

                        {{-- Thumbnail images if available --}}
                        @if(isset($kegiatan['images']) && count($kegiatan['images']) > 0)
                        <div class="absolute bottom-3 left-3 flex -space-x-2">
                            @foreach(array_slice($kegiatan['images'], 0, 3) as $img)
                            <div class="w-10 h-10 rounded-lg border-2 border-white overflow-hidden shadow-md bg-white/20">
                                <img src="{{ $img }}" class="w-full h-full object-cover" alt="" loading="lazy">
                            </div>
                            @endforeach
                            @if(count($kegiatan['images']) > 3)
                            <div class="w-10 h-10 rounded-lg border-2 border-white bg-black/30 flex items-center justify-center shadow-md">
                                <span class="text-white text-xs font-bold">+{{ count($kegiatan['images']) - 3 }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <span class="inline-block text-xs font-semibold {{ $kegiatan['tag_class'] }} px-2.5 py-1 rounded-full mb-2">
                                        {{ $kegiatan['tag'] }}
                                    </span>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-700 transition-colors">
                                        {{ $kegiatan['title'] }}
                                    </h3>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">{{ $kegiatan['description'] }}</p>
                        </div>
                        
                        <div class="mt-4 flex items-center justify-between">
                            {{-- Schedule Info --}}
                            <div class="flex flex-col gap-1">
                                @if(isset($kegiatan['schedule']))
                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $kegiatan['schedule'] }}
                                </div>
                                @endif
                                @if(isset($kegiatan['time']))
                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $kegiatan['time'] }}
                                </div>
                                @endif
                            </div>
                            
                            {{-- CTA --}}
                            <a href="{{ $kegiatan['url'] ?? '#' }}" 
                               class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-800 transition-colors group-hover:gap-2.5">
                                Lihat Detail
                                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Hover border accent --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 {{ $kegiatan['accent_class'] }} opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-l-2xl"></div>
            </div>
            @endforeach
        </div>
    </div>
</section>


<!-- Kilas Laporan Keuangan -->
<section class="py-16 bg-gray-50" id="laporan-keuangan">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-animate>
            <h2 class="text-3xl font-bold text-gray-900 section-title">Kilas Laporan Keuangan</h2>
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
                    <span class="text-xs font-medium text-gray-500">Saldo Awal</span>
                </div>
                <p class="text-xs text-gray-400 mb-2">{{ $laporan['periode_awal'] }}</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($laporan['saldo_awal'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Rp</p>
            </div>

            {{-- Pemasukan --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-green-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500">Pemasukan</span>
                </div>
                <p class="text-xs text-gray-400 mb-2">Yang masuk hingga hari ini</p>
                <p class="text-xl font-bold text-green-600">{{ number_format($laporan['pemasukan'], 0, ',', '.') }}</p>
                <p class="text-xs text-green-400 mt-0.5">Rp</p>
            </div>

            {{-- Pengeluaran --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-red-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-red-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500">Pengeluaran</span>
                </div>
                <p class="text-xs text-gray-400 mb-2">Yang keluar hingga hari ini</p>
                <p class="text-xl font-bold text-red-500">{{ number_format($laporan['pengeluaran'], 0, ',', '.') }}</p>
                <p class="text-xs text-red-400 mt-0.5">Rp</p>
            </div>

            {{-- Saldo Akhir --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-7 h-7 bg-amber-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500">Saldo Akhir</span>
                </div>
                <p class="text-xs text-gray-400 mb-2">{{ $laporan['periode_akhir'] }}</p>
                <p class="text-xl font-bold text-gray-900">{{ number_format($laporan['saldo_akhir'], 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Rp</p>
            </div>
        </div>

        <div class="text-center mt-8" data-animate>
            <a href="{{ url('/laporan') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-8 py-3 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat Detail Laporan
            </a>
        </div>
    </div>
</section>

<!-- Temukan Kami -->
<section class="py-16" id="temukan-kami">
    <div class="max-w-7xl mx-auto px-8 sm:px-20 lg:px-20 text-center">
        <div class="flex items-center justify-center gap-5">
            {{-- WhatsApp --}}
            <a href="https://wa.me/6281234567890" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-green-500 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </a>
            {{-- Instagram --}}
            <a href="https://instagram.com/masjidluqmanulhakim" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-pink-600 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
            {{-- YouTube --}}
            <a href="https://youtube.com/@masjidluqmanulhakim" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-red-600 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
            </a>
            {{-- Telegram --}}
            <a href="https://t.me/masjidluqmanulhakim" target="_blank" class="w-20 h-20 bg-gray-100 hover:bg-blue-500 hover:text-white text-gray-600 rounded-full flex items-center justify-center transition-all duration-200">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
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
 
    async function loadJadwalShalat(kotaId) {
        const kota = kotaId || document.getElementById('kota-select')?.value || '1301';
        document.getElementById('jadwal-loading')?.classList.remove('hidden');
        document.getElementById('jadwal-content')?.classList.add('hidden');
        document.getElementById('jadwal-error')?.classList.add('hidden');
 
        try {
            const res  = await fetch(`/api/jadwal-shalat?kota=${kota}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
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
 
    window.loadJadwalShalat = loadJadwalShalat;
 
    document.getElementById('kota-select')?.addEventListener('change', function () { loadJadwalShalat(this.value); });
    setInterval(tickClock, 1000);
    setInterval(updateActive, 30000);
    tickClock();
    loadJadwalShalat();
})();
</script>
@endpush