@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Approval Transaksi</h1>
        </div>
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

    {{-- Bulk Action Bar --}}
    <div id="bulk-action-bar"
         class="hidden items-center justify-between bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl px-5 py-3">
        <span class="text-sm font-medium text-blue-700 dark:text-blue-400">
            <span id="selected-count">0</span> transaksi dipilih
        </span>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openBulkApproveModal()"
                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Approve Semua
            </button>
            <button type="button" onclick="openBulkRejectModal()"
                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reject Semua
            </button>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 space-y-3">

            {{-- Toolbar: satu baris, satu form --}}
            <form method="GET" action="{{ route('dashboard.approval.index') }}"
                  class="flex items-center gap-2 flex-wrap">

                {{-- Show entries --}}
                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                <select name="per_page" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    @foreach([10, 25, 50] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">entries</span>

                {{-- Filter Sumber --}}
                <div class="relative">
                    <select name="sumber" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg pl-3 pr-8 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 appearance-none">
                        <option value=""         {{ $sumber === ''         ? 'selected' : '' }}>Semua Sumber</option>
                        <option value="kegiatan" {{ $sumber === 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="kencleng" {{ $sumber === 'kencleng' ? 'selected' : '' }}>Kencleng</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Urutan --}}
                <div class="relative">
                    <select name="urut" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg pl-3 pr-8 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 appearance-none">
                        <option value="asc"  {{ $urut === 'asc'  ? 'selected' : '' }}>Terlama</option>
                        <option value="desc" {{ $urut === 'desc' ? 'selected' : '' }}>Terbaru</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Reset --}}
                @if($sumber || $dari || $sampai || $urut !== 'asc' || $search)
                <a href="{{ route('dashboard.approval.index') }}"
                   class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                    Reset
                </a>
                @endif

                {{-- Search (didorong ke kanan) --}}
                <div class="relative ml-auto">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search..."
                        class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-48 placeholder-gray-400">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-10">
                            <input type="checkbox" id="check-all"
                                   class="h-4 w-4 cursor-pointer rounded border-gray-300 dark:border-gray-600"
                                   onchange="toggleAll(this)">
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Kode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Sumber</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Dicatat oleh</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jenis</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jumlah</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($transaksi as $item)
                    @php
                        $jenis      = $item->jenis_transaksi;
                        $isKencleng = $item->kencleng !== null;
                        $label      = $isKencleng
                            ? 'Kencleng'
                            : ($item->kegiatan->nama_kegiatan ?? '-');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-5 py-3.5 text-center">
                            <input type="checkbox"
                                   class="row-check h-4 w-4 cursor-pointer rounded border-gray-300 dark:border-gray-600"
                                   value="{{ $item->id }}"
                                   data-label="{{ $label }}"
                                   onchange="updateBulkBar()">
                        </td>
                        <td class="px-4 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-400">
                            TRX-{{ now()->year }}-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                @if($isKencleng)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                                        Kencleng
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                        Kegiatan
                                    </span>
                                @endif
                                <span class="text-gray-700 dark:text-gray-300 truncate max-w-[140px]" title="{{ $label }}">{{ $label }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                            {{ $item->user->name }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $jenis === 'PEMASUKAN'
                                    ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400'
                                    : 'bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400' }}">
                                {{ ucfirst(strtolower($jenis)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 font-medium
                            {{ $jenis === 'PEMASUKAN' ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                            Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                            {{ $item->tanggal_transaksi->format('j M Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center">
                                <a href="{{ route('dashboard.approval.show', $item) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                            Tidak ada transaksi yang perlu diapprove.
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
                @if($transaksi->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                    <a href="{{ $transaksi->previousPageUrl() }}&search={{ $search }}&sumber={{ $sumber }}&dari={{ $dari }}&sampai={{ $sampai }}&urut={{ $urut }}"
                       class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                @foreach($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                <a href="{{ $url }}&search={{ $search }}&sumber={{ $sumber }}&dari={{ $dari }}&sampai={{ $sampai }}&urut={{ $urut }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $transaksi->currentPage()
                           ? 'bg-green-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($transaksi->hasMorePages())
                    <a href="{{ $transaksi->nextPageUrl() }}&search={{ $search }}&sumber={{ $sumber }}&dari={{ $dari }}&sampai={{ $sampai }}&urut={{ $urut }}"
                       class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $transaksi->firstItem() }} to {{ $transaksi->lastItem() }} of {{ $transaksi->total() }} entries
            </span>
        </div>
        @endif

    </div>
</div>

{{-- ============================================================
     MODAL: Bulk Approve
     ============================================================ --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Konfirmasi Bulk Approve</h3>
        </div>
        <div class="px-6 py-5">
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                Anda akan menyetujui <strong id="approve-count" class="text-gray-800 dark:text-white">0</strong> transaksi berikut:
            </p>
            <ul id="approve-list" class="mb-4 max-h-48 overflow-y-auto space-y-1 rounded-xl bg-gray-50 dark:bg-gray-800 p-3 text-sm text-gray-600 dark:text-gray-400">
            </ul>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan. Lanjutkan?</p>
        </div>
        <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 px-6 py-4">
            <button type="button" onclick="closeModal('modal-approve')"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <form id="form-bulk-approve" method="POST" action="{{ route('dashboard.approval.bulk-approve') }}">
                @csrf
                <input type="hidden" name="ids" id="approve-ids">
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                    Ya, Approve Semua
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL: Bulk Reject
     ============================================================ --}}
<div id="modal-reject" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <div class="border-b border-gray-100 dark:border-gray-800 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Bulk Reject — Isi Catatan</h3>
        </div>
        <form id="form-bulk-reject" method="POST" action="{{ route('dashboard.approval.bulk-reject') }}">
            @csrf
            <div class="px-6 py-5">
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    Isi catatan penolakan untuk masing-masing transaksi yang dipilih:
                </p>
                <div id="reject-items" class="max-h-96 overflow-y-auto space-y-4">
                    {{-- diisi oleh JS --}}
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 px-6 py-4">
                <button type="button" onclick="closeModal('modal-reject')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    Reject Semua
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function getChecked() {
        return [...document.querySelectorAll('.row-check:checked')];
    }

    function updateBulkBar() {
        const checked = getChecked();
        const bar     = document.getElementById('bulk-action-bar');
        document.getElementById('selected-count').textContent = checked.length;

        if (checked.length > 0) {
            bar.classList.remove('hidden');
            bar.classList.add('flex');
        } else {
            bar.classList.add('hidden');
            bar.classList.remove('flex');
        }

        const total = document.querySelectorAll('.row-check').length;
        document.getElementById('check-all').checked       = checked.length === total && total > 0;
        document.getElementById('check-all').indeterminate = checked.length > 0 && checked.length < total;
    }

    function toggleAll(source) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
        updateBulkBar();
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    ['modal-approve', 'modal-reject'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) closeModal(id);
        });
    });

    function openBulkApproveModal() {
        const checked = getChecked();
        if (checked.length === 0) return;

        document.getElementById('approve-count').textContent = checked.length;
        document.getElementById('approve-ids').value = checked.map(cb => cb.value).join(',');

        const list = document.getElementById('approve-list');
        list.innerHTML = checked.map(cb =>
            `<li class="flex items-center gap-2">
                <span class="text-green-500">✓</span>
                <span>TRX-{{ now()->year }}-${String(cb.value).padStart(3,'0')} — ${cb.dataset.label}</span>
             </li>`
        ).join('');

        openModal('modal-approve');
    }

    function openBulkRejectModal() {
        const checked = getChecked();
        if (checked.length === 0) return;

        const container = document.getElementById('reject-items');
        container.innerHTML = checked.map(cb => `
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <input type="hidden" name="ids[]" value="${cb.value}">
                <p class="mb-2 text-sm font-medium text-gray-800 dark:text-gray-200">
                    TRX-{{ now()->year }}-${String(cb.value).padStart(3,'0')}
                    <span class="font-normal text-gray-500 dark:text-gray-400"> — ${cb.dataset.label}</span>
                </p>
                <textarea name="catatan[${cb.value}]"
                          rows="2"
                          placeholder="Catatan penolakan (opsional)..."
                          class="w-full rounded-lg border border-gray-200 dark:border-gray-700 p-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-green-400 focus:outline-none placeholder-gray-400"></textarea>
            </div>
        `).join('');

        openModal('modal-reject');
    }
</script>
@endpush

@endsection