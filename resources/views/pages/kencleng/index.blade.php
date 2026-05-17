@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between rounded-xl border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Kencleng</h2>
        @if(auth()->user()->hasPermission('CREATE_KENCLENG'))
        <a href="{{ route('dashboard.kencleng.create') }}"
        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-green-700">
            
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>

            Catat Kencleng Baru
        </a>
        @endif
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Tabel --}}
    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">

        {{-- Controls --}}
        <div class="flex items-center justify-between gap-4 border-b border-stroke px-5 py-4 dark:border-strokedark">
            {{-- Show entries --}}
            <div class="flex items-center gap-2 text-sm text-body dark:text-bodydark">
                <span>Show</span>
                <form method="GET" action="{{ route('dashboard.kencleng.index') }}" id="perPageForm">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <select name="per_page" onchange="document.getElementById('perPageForm').submit()"
                            class="rounded border border-stroke px-2 py-1 text-sm focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                        @foreach([10, 25, 50] as $val)
                            <option value="{{ $val }}" {{ $perPage == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                </form>
                <span>entries</span>
            </div>

            {{-- Search --}}
            <form method="GET" action="{{ route('dashboard.kencleng.index') }}" class="relative">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search..."
                       class="rounded-lg border border-stroke py-2 pl-4 pr-10 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-5 py-4 font-medium text-black dark:text-white">Nomor</th>
                        <th class="px-5 py-4 font-medium text-black dark:text-white">Tanggal Hitung</th>
                        <th class="px-5 py-4 font-medium text-black dark:text-white">Total Fisik</th>
                        <th class="px-5 py-4 font-medium text-black dark:text-white">Disetor</th>
                        <th class="px-5 py-4 font-medium text-black dark:text-white">Status</th>
                        <th class="px-5 py-4 font-medium text-black dark:text-white">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kencleng as $index => $item)
                    @php
                        $totalFisik    = $item->detail->sum(fn($d) => $d->pecahan * $d->jumlah_pecahan);
                        $status        = $item->transaksi->status_approval;
                        $isDeletable   = in_array($status, ['PENDING', 'REVISION', 'DRAFT']);
                        $isEditable    = in_array($status, ['PENDING', 'REVISION', 'DRAFT']);

                        $statusLabel   = match($status) {
                            'APPROVED' => 'Aktif',
                            'PENDING'  => 'Menunggu',
                            'REVISION' => 'Revisi',
                            'REJECTED' => 'Ditolak',
                            'DRAFT'    => 'Draf',
                            default    => $status,
                        };
                        $statusStyle   = match($status) {
                            'APPROVED' => 'color:#2E7D32',
                            'PENDING'  => 'color:#E65100',
                            'REVISION' => 'color:#1565C0',
                            'REJECTED' => 'color:#C62828',
                            'DRAFT'    => 'color:#9E9E9E',
                            default    => 'color:#555',
                        };
                    @endphp
                    <tr class="border-t border-stroke dark:border-strokedark hover:bg-gray-50 dark:hover:bg-meta-4 transition-colors">
                        <td class="px-5 py-4 text-sm text-black dark:text-white">
                            {{ $kencleng->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-4 text-sm text-body dark:text-bodydark">
                            {{ $item->transaksi->tanggal_transaksi->format('j M Y') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-black dark:text-white">
                            Rp {{ number_format($totalFisik, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-black dark:text-white">
                            Rp {{ number_format($item->transaksi->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-sm font-medium" style="{{ $statusStyle }}">
                            {{ $statusLabel }}
                        </td>
                        <td class="px-5 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                @if($isEditable)
                                <a href="{{ route('dashboard.kencleng.edit', $item) }}"
                                   class="text-gray-400 hover:text-primary transition-colors">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
                                @else
                                <a href="{{ route('dashboard.kencleng.show', $item) }}"
                                   class="text-gray-400 hover:text-primary transition-colors">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                @endif

                                @if($isDeletable)
                                <form action="{{ route('dashboard.kencleng.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus kencleng ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-body dark:text-bodydark">
                            Belum ada data kencleng
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex items-center justify-between border-t border-stroke px-5 py-4 dark:border-strokedark">
            <p class="text-sm text-body dark:text-bodydark">
                @if($kencleng->total() > 0)
                    Showing {{ $kencleng->firstItem() }} to {{ $kencleng->lastItem() }}
                    of {{ $kencleng->total() }} entries
                @else
                    No entries
                @endif
            </p>
            <div class="flex items-center gap-2">
                @if($kencleng->onFirstPage())
                    <span class="rounded-lg border border-stroke px-4 py-2 text-sm text-gray-300 dark:border-strokedark">Previous</span>
                @else
                    <a href="{{ $kencleng->previousPageUrl() }}&search={{ $search }}&per_page={{ $perPage }}"
                       class="rounded-lg border border-stroke px-4 py-2 text-sm text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                        Previous
                    </a>
                @endif

                @foreach($kencleng->getUrlRange(1, $kencleng->lastPage()) as $page => $url)
                    @if($page == $kencleng->currentPage())
                        <span class="rounded-lg bg-primary px-4 py-2 text-sm text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}&search={{ $search }}&per_page={{ $perPage }}"
                           class="rounded-lg border border-stroke px-4 py-2 text-sm text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if($kencleng->hasMorePages())
                    <a href="{{ $kencleng->nextPageUrl() }}&search={{ $search }}&per_page={{ $perPage }}"
                       class="rounded-lg border border-stroke px-4 py-2 text-sm text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                        Next
                    </a>
                @else
                    <span class="rounded-lg border border-stroke px-4 py-2 text-sm text-gray-300 dark:border-strokedark">Next</span>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection