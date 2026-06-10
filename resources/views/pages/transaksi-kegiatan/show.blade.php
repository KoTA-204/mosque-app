@extends('layouts.app')

@section('title', 'Detail Kegiatan')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.kegiatan-panitia.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Detail Kegiatan
                </h1>
            </div>
        </div>
    </div>

    @php
        $bgColor = match($kegiatan->jenis_kegiatan) {
            'QURBAN' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
            'ZAKAT'  => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
            'KAJIAN' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
            'SOSIAL' => 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400',
            default  => 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        };

        $statusColor = match($kegiatan->status) {
            'BERJALAN'   => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
            'SELESAI'    => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
            'DRAFT'      => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
            'DIBATALKAN' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
            default      => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        };
    @endphp

    {{-- Detail Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Top Section --}}
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-start justify-between gap-4 flex-wrap">

                <div class="flex items-start gap-4">
                    <div class="h-14 w-14 rounded-2xl {{ $bgColor }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $kegiatan->nama_kegiatan }}
                        </h2>

                        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full font-medium {{ $bgColor }}">
                                {{ ucfirst(strtolower($kegiatan->jenis_kegiatan)) }}
                            </span>

                            <span class="text-gray-300 dark:text-gray-600">•</span>

                            <span class="text-gray-500 dark:text-gray-400">
                                {{ $kegiatan->tanggal_mulai->format('d M Y') }}
                                —
                                {{ $kegiatan->tanggal_selesai?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                    {{ ucfirst(strtolower($kegiatan->status)) }}
                </span>

            </div>
        </div>

        {{-- Detail Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 px-6 py-5">

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Anggaran
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Tanggal Mulai
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $kegiatan->tanggal_mulai->format('d F Y') }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Tanggal Selesai
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $kegiatan->tanggal_selesai?->format('d F Y') ?? '-' }}
                </p>
            </div>

            <div class="space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Jenis Kegiatan
                </p>
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ ucfirst(strtolower($kegiatan->jenis_kegiatan)) }}
                </p>
            </div>

        </div>

        @if($kegiatan->deskripsi)
        <div class="px-6 pb-6">
            <div class="rounded-2xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">
                    Deskripsi
                </p>

                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ $kegiatan->deskripsi }}
                </p>
            </div>
        </div>
        @endif

    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Daftar Transaksi
                </h3>
            </div>

            <div class="flex items-center gap-2">

                {{-- Search --}}
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>

                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search..."
                               class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                    </div>
                </form>

                @if($kegiatan->status === 'AKTIF')
                    @if(auth()->user()->hasPermission('CREATE_TRANSAKSI_KEGIATAN'))
                        <a href="{{ route('dashboard.kegiatan-panitia.transaksi.create', $kegiatan) }}"
                           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                            Catat Transaksi
                        </a>
                    @endif
                @endif

            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">
                            No
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Kode
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Tanggal
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Jenis
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Jumlah
                        </th>

                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Dompet
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Status
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($transaksi as $index => $item)

                    @php
                        $jenis = $item->jenis_transaksi;

                        $statusBadge = match($item->status_approval) {
                            'PENDING'  => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                            'APPROVED' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                            'REJECTED' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                            'REVISION' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            default    => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                        };
                    @endphp

                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">

                        <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                            {{ $transaksi->firstItem() + $index }}
                        </td>

                        <td class="px-4 py-3.5 font-mono text-gray-700 dark:text-gray-300">
                            TRX-{{ now()->year }}-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                            {{ $item->tanggal_transaksi->format('d M Y') }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $jenis === 'PEMASUKAN'
                                    ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                                    : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' }}">
                                {{ ucfirst(strtolower($jenis)) }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 font-semibold
                            {{ $jenis === 'PEMASUKAN'
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-500 dark:text-red-400' }}">
                            {{ $jenis === 'PEMASUKAN' ? '+' : '-' }}
                            Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                        </td>

                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                            {{ $item->dompet->nama_dompet }}
                        </td>

                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                {{ ucfirst(strtolower($item->status_approval)) }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1">

                                {{-- Detail --}}
                                <a href="{{ route('dashboard.kegiatan-panitia.transaksi.show', [$kegiatan, $item]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                {{-- Edit --}}
                                @if(in_array($item->status_approval, ['PENDING', 'REVISION']))
                                <a href="{{ route('dashboard.kegiatan-panitia.transaksi.edit', [$kegiatan, $item]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif

                                {{-- Delete --}}
                                @if($item->status_approval === 'PENDING')
                                <form action="{{ route('dashboard.kegiatan-panitia.transaksi.destroy', [$kegiatan, $item]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin hapus transaksi ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>

                    </tr>

                    @empty
                    <tr>
                        <td colspan="8"
                            class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada transaksi kegiatan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        @if($transaksi->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">

            <div class="flex items-center gap-1">

                {{-- Previous --}}
                @if($transaksi->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">
                    Previous
                </span>
                @else
                <a href="{{ $transaksi->previousPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Previous
                </a>
                @endif

                {{-- Page Number --}}
                @foreach($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                    {{ $page === $transaksi->currentPage()
                        ? 'bg-green-600 text-white font-medium'
                        : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                {{-- Next --}}
                @if($transaksi->hasMorePages())
                <a href="{{ $transaksi->nextPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Next
                </a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">
                    Next
                </span>
                @endif

            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $transaksi->firstItem() }} to {{ $transaksi->lastItem() }} of {{ $transaksi->total() }} entries
            </span>

        </div>
        @endif

    </div>

</div>
@endsection