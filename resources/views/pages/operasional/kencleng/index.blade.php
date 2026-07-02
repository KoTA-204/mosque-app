@extends('layouts.app')

@section('title', 'Kencleng')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Kencleng</h1>
        </div>
        @if(auth()->user()->hasPermission('CREATE_KENCLENG'))
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.kencleng.create') }}"
               class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                Catat Kencleng Baru
            </a>
        </div>
        @endif
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            {{-- Kiri: Show entries + filter tanggal + filter status --}}
            <div class="flex items-center gap-2 flex-wrap">

                {{-- Show entries --}}
                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                <form method="GET" action="{{ route('dashboard.kencleng.index') }}" id="perPageForm">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="sort"   value="{{ $sort }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <select name="per_page" onchange="document.getElementById('perPageForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        @foreach([10, 25, 50] as $val)
                            <option value="{{ $val }}" {{ $perPage == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>

                {{-- Divider --}}
                <span class="h-5 w-px bg-gray-200 dark:bg-gray-700 mx-1"></span>

                {{-- Filter Tanggal --}}
                <form method="GET" action="{{ route('dashboard.kencleng.index') }}" id="sortForm">
                    <input type="hidden" name="search"   value="{{ $search }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="status"   value="{{ $status }}">
                    <select name="sort" onchange="document.getElementById('sortForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </form>

                {{-- Filter Status --}}
                <form method="GET" action="{{ route('dashboard.kencleng.index') }}" id="statusForm">
                    <input type="hidden" name="search"   value="{{ $search }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="sort"     value="{{ $sort }}">
                    <select name="status" onchange="document.getElementById('statusForm').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value=""         {{ $status === ''         ? 'selected' : '' }}>Semua Status</option>
                        <option value="PENDING"  {{ $status === 'PENDING'  ? 'selected' : '' }}>Menunggu</option>
                        <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>Disetujui</option>
                        <option value="REVISION" {{ $status === 'REVISION' ? 'selected' : '' }}>Revisi</option>
                        <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </form>

                {{-- Reset filter (tampil kalau ada filter aktif) --}}
                @if($status || $sort !== 'terbaru' || $search)
                <a href="{{ route('dashboard.kencleng.index') }}"
                   class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                    Reset
                </a>
                @endif
            </div>

            {{-- Kanan: Search --}}
            <form method="GET" action="{{ route('dashboard.kencleng.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="sort"     value="{{ $sort }}">
                <input type="hidden" name="status"   value="{{ $status }}">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search..."
                        class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">No</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal Hitung</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Total Fisik</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Disetor</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($kencleng as $index => $item)
                    @php
                        $totalFisik  = $item->detail->sum(fn($d) => $d->pecahan * $d->jumlah_pecahan);
                        $itemStatus  = $item->transaksi->status_approval;
                        $isDeletable = in_array($itemStatus, ['PENDING', 'REVISION', 'DRAFT']);
                        $isEditable  = in_array($itemStatus, ['PENDING', 'REVISION', 'DRAFT']);

                        $statusLabel = match($itemStatus) {
                            'APPROVED' => 'Disetujui',
                            'PENDING'  => 'Menunggu',
                            'REVISION' => 'Revisi',
                            'REJECTED' => 'Ditolak',
                            'DRAFT'    => 'Draf',
                            default    => $itemStatus,
                        };

                        $statusClass = match($itemStatus) {
                            'APPROVED' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                            'PENDING'  => 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400',
                            'REVISION' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                            'REJECTED' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                            'DRAFT'    => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
                            default    => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                            {{ $kencleng->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-800 dark:text-gray-200">
                            {{ $item->transaksi->tanggal_transaksi->format('j M Y') }}
                        </td>
                        <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                            Rp {{ number_format($totalFisik, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-800 dark:text-gray-200">
                            Rp {{ number_format($item->transaksi->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1">
                                {{-- View: selalu tampil --}}
                                <a href="{{ route('dashboard.kencleng.show', $item) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                {{-- Edit: hanya kalau masih bisa diedit --}}
                                @if($isEditable)
                                <a href="{{ route('dashboard.kencleng.edit', $item) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif

                                {{-- Delete --}}
                                @if($isDeletable)
                                <form action="{{ route('dashboard.kencleng.destroy', $item) }}" method="POST"
                                      data-confirm="Yakin hapus kencleng ini?" data-confirm-label="Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Belum ada data kencleng.
                            @if(auth()->user()->hasPermission('CREATE_KENCLENG'))
                            <a href="{{ route('dashboard.kencleng.create') }}" class="text-green-600 hover:underline ml-1">Catat sekarang</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($kencleng->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($kencleng->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $kencleng->previousPageUrl() }}&search={{ $search }}&per_page={{ $perPage }}&sort={{ $sort }}&status={{ $status }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($kencleng->getUrlRange(1, $kencleng->lastPage()) as $page => $url)
                <a href="{{ $url }}&search={{ $search }}&per_page={{ $perPage }}&sort={{ $sort }}&status={{ $status }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $kencleng->currentPage()
                           ? 'bg-green-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                {{-- Next --}}
                @if($kencleng->hasMorePages())
                <a href="{{ $kencleng->nextPageUrl() }}&search={{ $search }}&per_page={{ $perPage }}&sort={{ $sort }}&status={{ $status }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                @if($kencleng->total() > 0)
                    Showing {{ $kencleng->firstItem() }} to {{ $kencleng->lastItem() }} of {{ $kencleng->total() }} entries
                @else
                    No entries
                @endif
            </span>
        </div>
        @endif

    </div>
</div>
@endsection