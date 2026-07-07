@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">List Kegiatan</h1>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
            <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Total kegiatan</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
            <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Sedang aktif</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['aktif'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
            <p class="mb-1 text-sm text-gray-500 dark:text-gray-400">Transaksi pending</p>
            <p class="text-3xl font-bold text-yellow-500">{{ $summary['pending'] }}</p>
        </div>
    </div>

    {{-- Filter + Search --}}
    <div class="flex items-center justify-between gap-4">

        {{-- Filter Tab --}}
        <div class="flex items-center gap-2">
            @foreach(['', 'AKTIF', 'DITUTUP'] as $tab)
                @php
                    $label = match($tab) {
                        'AKTIF' => 'Aktif',
                        'DITUTUP'  => 'Selesai',
                        default    => 'Semua',
                    };
                    $isActive = ($status ?? '') == $tab;
                @endphp
                <a href="{{ route('dashboard.transaksi-kegiatan.index', ['status' => $tab, 'search' => $search]) }}"
                   class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200
                       {{ $isActive
                           ? 'bg-green-600 text-white shadow-sm'
                           : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('dashboard.transaksi-kegiatan.index') }}" class="relative w-64">
            <input type="hidden" name="status" value="{{ $status }}">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11 4C7.13401 4 4 7.13401 4 11C4 14.866 7.13401 18 11 18C14.866 18 18 14.866 18 11C18 7.13401 14.866 4 11 4ZM2 11C2 6.02944 6.02944 2 11 2C15.9706 2 20 6.02944 20 11C20 15.9706 15.9706 20 11 20C6.02944 20 2 15.9706 2 11Z" fill="currentColor"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9429 15.9429C16.3334 15.5524 16.9666 15.5524 17.3571 15.9429L21.7071 20.2929C22.0976 20.6834 22.0976 21.3166 21.7071 21.7071C21.3166 22.0976 20.6834 22.0976 20.2929 21.7071L15.9429 17.3571C15.5524 16.9666 15.5524 16.3334 15.9429 15.9429Z" fill="currentColor"/>
                </svg>
            </span>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari kegiatan..."
                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-2 pl-9 pr-4 text-sm text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
        </form>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- List Kegiatan --}}
    <div class="flex flex-col gap-3">
        @forelse($kegiatan as $item)
        @php
            // Konsisten dengan halaman detail: hanya PEMASUKAN APPROVED (dana
            // terkumpul), bukan menjumlahkan pemasukan + pengeluaran sekaligus.
            $porsi = $item->anggaran > 0
                ? min(100, round(($item->transaksi->where('status_approval', 'APPROVED')->where('jenis_transaksi', 'PEMASUKAN')->sum('jumlah') / $item->anggaran) * 100))
                : 0;
            $statusColor = match($item->status) {
                'AKTIF'   => 'text-green-600',
                'DRAFT'      => 'text-gray-500',
                'DITUTUP'    => 'text-blue-600',
                'DIBATALKAN' => 'text-red-500',
                default      => 'text-gray-500',
            };
            $statusLabel = match($item->status) {
                'AKTIF'   => 'Aktif',
                'DRAFT'      => 'Draft',
                'DITUTUP'    => 'Selesai',
                'DIBATALKAN' => 'Dibatalkan',
                default      => $item->status,
            };
            $bgColor = match($item->jenis_kegiatan) {
                'QURBAN' => 'bg-red-100',
                'ZAKAT'  => 'bg-green-100',
                'KAJIAN' => 'bg-blue-100',
                'SOSIAL' => 'bg-purple-100',
                default  => 'bg-gray-100',
            };
        @endphp
        <a href="{{ route('dashboard.transaksi-kegiatan.show', $item) }}"
           class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 hover:border-green-400 dark:hover:border-green-600 transition-colors">
            <div class="flex items-center justify-between">
                {{-- Kiri --}}
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl {{ $bgColor }} flex-shrink-0"></div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->nama_kegiatan }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $item->tanggal_mulai->format('j M Y') }}
                            – {{ $item->tanggal_selesai?->format('j M Y') ?? '...' }}
                            &nbsp;·&nbsp; {{ ucfirst(strtolower($item->jenis_kegiatan)) }}
                        </p>
                    </div>
                </div>

                {{-- Kanan --}}
                <div class="text-right">
                    <div class="mb-1 flex items-center justify-end gap-2">
                        <span class="text-sm font-medium {{ $statusColor }}">{{ $statusLabel }}</span>
                        @if($item->transaksi_pending_count > 0)
                            <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700">
                                {{ $item->transaksi_pending_count }} menunggu
                            </span>
                        @endif
                    </div>
                    <div class="mb-1 flex items-center justify-end gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Anggaran</span>
                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $porsi }}%</span>
                    </div>
                    <div class="mb-1 h-1.5 w-40 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full {{ $item->status === 'DITUTUP' ? 'bg-gray-400' : 'bg-green-600' }}"
                             style="width: {{ $porsi }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->transaksi_count }} transaksi</p>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl py-12 text-center text-sm text-gray-400">
            @if($search)
                Tidak ada kegiatan yang sesuai pencarian "{{ $search }}"
            @else
                Belum ada kegiatan
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($kegiatan->hasPages())
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ $kegiatan->firstItem() }}–{{ $kegiatan->lastItem() }}
            dari {{ $kegiatan->total() }} kegiatan
        </p>
        <div class="flex items-center gap-1">
            @if($kegiatan->onFirstPage())
                <span class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-300">&laquo;</span>
            @else
                <a href="{{ $kegiatan->previousPageUrl() }}&search={{ $search }}&status={{ $status }}"
                   class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">&laquo;</a>
            @endif

            @foreach($kegiatan->getUrlRange(1, $kegiatan->lastPage()) as $page => $url)
                @if($page == $kegiatan->currentPage())
                    <span class="rounded-xl bg-green-600 px-3 py-2 text-sm text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}&search={{ $search }}&status={{ $status }}"
                       class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ $page }}</a>
                @endif
            @endforeach

            @if($kegiatan->hasMorePages())
                <a href="{{ $kegiatan->nextPageUrl() }}&search={{ $search }}&status={{ $status }}"
                   class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">&raquo;</a>
            @else
                <span class="rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm text-gray-300">&raquo;</span>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection