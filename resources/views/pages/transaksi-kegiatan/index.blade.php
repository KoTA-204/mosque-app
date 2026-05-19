@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">List Kegiatan</h2>
    </div>

    {{-- Summary Cards --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-stroke bg-white p-5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <p class="mb-1 text-sm text-body dark:text-bodydark">Total kegiatan</p>
            <p class="text-3xl font-bold text-black dark:text-white">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-xl border border-stroke bg-white p-5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <p class="mb-1 text-sm text-body dark:text-bodydark">Sedang aktif</p>
            <p class="text-3xl font-bold text-black dark:text-white">{{ $summary['aktif'] }}</p>
        </div>
        <div class="rounded-xl border border-stroke bg-white p-5 shadow-default dark:border-strokedark dark:bg-boxdark">
            <p class="mb-1 text-sm text-body dark:text-bodydark">Transaksi pending</p>
            <p class="text-3xl font-bold text-yellow-500">{{ $summary['pending'] }}</p>
        </div>
    </div>

    {{-- Filter + Search --}}
    <div class="mb-4 flex items-center justify-between gap-4">

        {{-- Filter Tab --}}
        <div class="flex items-center gap-3 rounded-full p-1">

            @foreach(['', 'BERJALAN', 'SELESAI'] as $tab)
                @php
                    $label = match($tab) {
                        'BERJALAN' => 'Aktif',
                        'SELESAI'  => 'Selesai',
                        default    => 'Semua',
                    };

                    $isActive = ($status ?? '') == $tab;
                @endphp

                <a href="{{ route('dashboard.kegiatan-panitia.index', [
                        'status' => $tab,
                        'search' => $search
                    ]) }}"
                class="rounded-full px-6 py-2 text-sm font-medium transition-all duration-200
                        {{ $isActive
                            ? 'bg-green-600 text-white shadow-md'
                            : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 shadow-sm'
                        }}">
                    {{ $label }}
                </a>
            @endforeach

        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('dashboard.kegiatan-panitia.index') }}" class="relative w-64">
            <input type="hidden" name="status" value="{{ $status }}">

            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11 4C7.13401 4 4 7.13401 4 11C4 14.866 7.13401 18 11 18C14.866 18 18 14.866 18 11C18 7.13401 14.866 4 11 4ZM2 11C2 6.02944 6.02944 2 11 2C15.9706 2 20 6.02944 20 11C20 15.9706 15.9706 20 11 20C6.02944 20 2 15.9706 2 11Z" fill="currentColor"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9429 15.9429C16.3334 15.5524 16.9666 15.5524 17.3571 15.9429L21.7071 20.2929C22.0976 20.6834 22.0976 21.3166 21.7071 21.7071C21.3166 22.0976 20.6834 22.0976 20.2929 21.7071L15.9429 17.3571C15.5524 16.9666 15.5524 16.3334 15.9429 15.9429Z" fill="currentColor"/>
                </svg>
            </span>

            <input type="text" name="search" value="{{ $search }}"
                placeholder="Cari kegiatan..."
                class="w-full rounded-lg border border-stroke py-2 pl-9 pr-4 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
        </form>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    {{-- List Kegiatan --}}
    <div class="flex flex-col gap-3">
        @forelse($kegiatan as $item)
        @php
            $porsi = $item->anggaran > 0
                ? min(100, round(($item->transaksi->where('status_approval', 'APPROVED')->sum('jumlah') / $item->anggaran) * 100))
                : 0;
            $statusColor = match($item->status) {
                'BERJALAN'   => 'text-green-600',
                'DRAFT'      => 'text-gray-500',
                'SELESAI'    => 'text-blue-600',
                'DIBATALKAN' => 'text-red-500',
                default      => 'text-gray-500',
            };
            $statusLabel = match($item->status) {
                'BERJALAN'   => 'Aktif',
                'DRAFT'      => 'Draft',
                'SELESAI'    => 'Selesai',
                'DIBATALKAN' => 'Dibatalkan',
                default      => $item->status,
            };
        @endphp
        <a href="{{ route('dashboard.kegiatan-panitia.show', $item) }}"
           class="rounded-xl border border-stroke bg-white p-5 shadow-default hover:border-primary dark:border-strokedark dark:bg-boxdark">
            <div class="flex items-center justify-between">
                {{-- Kiri --}}
                <div class="flex items-center gap-4">
                    {{-- Avatar warna --}}
                    @php
                        $bgColor = match($item->jenis_kegiatan) {
                            'QURBAN' => 'bg-red-100',
                            'ZAKAT'  => 'bg-green-100',
                            'KAJIAN' => 'bg-blue-100',
                            'SOSIAL' => 'bg-purple-100',
                            default  => 'bg-gray-100',
                        };
                    @endphp
                    <div class="h-12 w-12 rounded-xl {{ $bgColor }}"></div>

                    <div>
                        <p class="font-semibold text-black dark:text-white">{{ $item->nama_kegiatan }}</p>
                        <p class="text-xs text-body dark:text-bodydark">
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
                                {{ $item->transaksi_pending_count }} pending
                            </span>
                        @endif
                    </div>
                    <div class="mb-1 flex items-center justify-end gap-2">
                        <span class="text-xs text-body dark:text-bodydark">Anggaran</span>
                        <span class="text-xs font-medium text-black dark:text-white">{{ $porsi }}%</span>
                    </div>
                    <div class="mb-1 h-1.5 w-40 overflow-hidden rounded-full bg-gray-200">
                        <div class="h-full rounded-full {{ $item->status === 'SELESAI' ? 'bg-gray-400' : 'bg-primary' }}"
                             style="width: {{ $porsi }}%"></div>
                    </div>
                    <p class="text-xs text-body dark:text-bodydark">{{ $item->transaksi_count }} transaksi</p>
                </div>
            </div>
        </a>
        @empty
        <div class="rounded-xl border border-stroke bg-white py-12 text-center text-sm text-body dark:border-strokedark dark:bg-boxdark">
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
    <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-body dark:text-bodydark">
            Menampilkan {{ $kegiatan->firstItem() }}–{{ $kegiatan->lastItem() }}
            dari {{ $kegiatan->total() }} kegiatan
        </p>
        <div class="flex items-center gap-1">
            @if($kegiatan->onFirstPage())
                <span class="rounded-lg border border-stroke px-3 py-2 text-sm text-gray-300">&laquo;</span>
            @else
                <a href="{{ $kegiatan->previousPageUrl() }}&search={{ $search }}&status={{ $status }}"
                   class="rounded-lg border border-stroke px-3 py-2 text-sm hover:bg-gray-100">&laquo;</a>
            @endif

            @foreach($kegiatan->getUrlRange(1, $kegiatan->lastPage()) as $page => $url)
                @if($page == $kegiatan->currentPage())
                    <span class="rounded-lg bg-primary px-3 py-2 text-sm text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}&search={{ $search }}&status={{ $status }}"
                       class="rounded-lg border border-stroke px-3 py-2 text-sm hover:bg-gray-100">{{ $page }}</a>
                @endif
            @endforeach

            @if($kegiatan->hasMorePages())
                <a href="{{ $kegiatan->nextPageUrl() }}&search={{ $search }}&status={{ $status }}"
                   class="rounded-lg border border-stroke px-3 py-2 text-sm hover:bg-gray-100">&raquo;</a>
            @else
                <span class="rounded-lg border border-stroke px-3 py-2 text-sm text-gray-300">&raquo;</span>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection