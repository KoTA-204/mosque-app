@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-dark dark:text-white shrink-0">
                Approval Transaksi
            </h2>
            <form method="GET" action="{{ route('dashboard.approval.index') }}" class="relative w-64">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11 4C7.13401 4 4 7.13401 4 11C4 14.866 7.13401 18 11 18C14.866 18 18 14.866 18 11C18 7.13401 14.866 4 11 4ZM2 11C2 6.02944 6.02944 2 11 2C15.9706 2 20 6.02944 20 11C20 15.9706 15.9706 20 11 20C6.02944 20 2 15.9706 2 11Z" fill="currentColor"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9429 15.9429C16.3334 15.5524 16.9666 15.5524 17.3571 15.9429L21.7071 20.2929C22.0976 20.6834 22.0976 21.3166 21.7071 21.7071C21.3166 22.0976 20.6834 22.0976 20.2929 21.7071L15.9429 17.3571C15.5524 16.9666 15.5524 16.3334 15.9429 15.9429Z" fill="currentColor"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari transaksi..."
                       class="w-full rounded-lg border border-stroke py-2 pl-9 pr-4 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
            </form>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Bulk Action Bar (muncul kalau ada yang dipilih) --}}
    <div id="bulk-action-bar"
         class="mb-4 hidden items-center justify-between rounded-xl border border-primary bg-blue-50 px-5 py-3 dark:border-strokedark dark:bg-boxdark">
        <span class="text-sm font-medium text-primary dark:text-white">
            <span id="selected-count">0</span> transaksi dipilih
        </span>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openBulkApproveModal()"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                ✓ Approve Semua
            </button>
            <button type="button" onclick="openBulkRejectModal()"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                ✕ Reject Semua
            </button>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-4 py-4">
                            <input type="checkbox" id="check-all"
                                   class="h-4 w-4 cursor-pointer rounded border-stroke"
                                   onchange="toggleAll(this)">
                        </th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Kode</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Sumber</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Dicatat oleh</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Jenis</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Jumlah</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Tanggal</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $item)
                    @php
                        $jenis     = $item->kategoriTransaksi->jenis_transaksi;
                        $isKencleng= $item->kencleng !== null;
                        $label     = $isKencleng
                            ? 'Kencleng'
                            : ($item->kegiatan->nama_kegiatan ?? '-');
                    @endphp
                    <tr class="border-t border-stroke dark:border-strokedark hover:bg-gray-50 dark:hover:bg-meta-4 transition-colors"
                        data-id="{{ $item->id }}"
                        data-label="{{ $label }}">
                        <td class="px-4 py-4">
                            <input type="checkbox"
                                   class="row-check h-4 w-4 cursor-pointer rounded border-stroke"
                                   value="{{ $item->id }}"
                                   data-label="{{ $label }}"
                                   onchange="updateBulkBar()">
                        </td>
                        <td class="px-4 py-4 text-sm font-mono text-black dark:text-white">
                            TRX-{{ now()->year }}-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-4 text-sm text-black dark:text-white">
                            <div class="flex items-center gap-2">
                                @if($isKencleng)
                                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">
                                        Kencleng
                                    </span>
                                @else
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">
                                        Kegiatan
                                    </span>
                                @endif
                                <span class="truncate max-w-[140px]" title="{{ $label }}">{{ $label }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-body dark:text-bodydark">
                            {{ $item->user->name }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $jenis === 'PEMASUKAN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst(strtolower($jenis)) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm font-medium
                            {{ $jenis === 'PEMASUKAN' ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-sm text-body dark:text-bodydark">
                            {{ $item->tanggal_transaksi->format('j M Y') }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <a href="{{ route('dashboard.approval.show', $item) }}"
                               class="rounded bg-blue-100 px-3 py-1 text-xs text-blue-700 hover:bg-blue-200">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-body dark:text-bodydark">
                            Tidak ada transaksi yang perlu diapprove
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($transaksi->hasPages())
    <div class="mt-4 flex items-center justify-between">
        <p class="text-sm text-body dark:text-bodydark">
            Menampilkan {{ $transaksi->firstItem() }}–{{ $transaksi->lastItem() }}
            dari {{ $transaksi->total() }} transaksi
        </p>
        <div class="flex items-center gap-1">
            @if($transaksi->onFirstPage())
                <span class="rounded-lg border border-stroke px-3 py-2 text-sm text-gray-300">&laquo;</span>
            @else
                <a href="{{ $transaksi->previousPageUrl() }}&search={{ $search }}"
                   class="rounded-lg border border-stroke px-3 py-2 text-sm hover:bg-gray-100">&laquo;</a>
            @endif
            @foreach($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                @if($page == $transaksi->currentPage())
                    <span class="rounded-lg bg-primary px-3 py-2 text-sm text-white">{{ $page }}</span>
                @else
                    <a href="{{ $url }}&search={{ $search }}"
                       class="rounded-lg border border-stroke px-3 py-2 text-sm hover:bg-gray-100">{{ $page }}</a>
                @endif
            @endforeach
            @if($transaksi->hasMorePages())
                <a href="{{ $transaksi->nextPageUrl() }}&search={{ $search }}"
                   class="rounded-lg border border-stroke px-3 py-2 text-sm hover:bg-gray-100">&raquo;</a>
            @else
                <span class="rounded-lg border border-stroke px-3 py-2 text-sm text-gray-300">&raquo;</span>
            @endif
        </div>
    </div>
    @endif

</div>

{{-- ============================================================
     MODAL: Bulk Approve
     ============================================================ --}}
<div id="modal-approve" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl dark:bg-boxdark">
        <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
            <h3 class="text-lg font-semibold text-black dark:text-white">Konfirmasi Bulk Approve</h3>
        </div>
        <div class="px-6 py-5">
            <p class="mb-4 text-sm text-body dark:text-bodydark">
                Anda akan menyetujui <strong id="approve-count" class="text-black dark:text-white">0</strong> transaksi berikut:
            </p>
            <ul id="approve-list" class="mb-4 max-h-48 overflow-y-auto space-y-1 rounded-lg bg-gray-50 p-3 dark:bg-meta-4 text-sm text-body dark:text-bodydark">
            </ul>
            <p class="text-sm text-body dark:text-bodydark">Tindakan ini tidak dapat dibatalkan. Lanjutkan?</p>
        </div>
        <div class="flex justify-end gap-3 border-t border-stroke px-6 py-4 dark:border-strokedark">
            <button type="button" onclick="closeModal('modal-approve')"
                    class="rounded-lg border border-stroke px-4 py-2 text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                Batal
            </button>
            <form id="form-bulk-approve" method="POST" action="{{ route('dashboard.approval.bulk-approve') }}">
                @csrf
                <input type="hidden" name="ids" id="approve-ids">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                    Ya, Approve Semua
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL: Bulk Reject — catatan per transaksi
     ============================================================ --}}
<div id="modal-reject" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl dark:bg-boxdark">
        <div class="border-b border-stroke px-6 py-4 dark:border-strokedark">
            <h3 class="text-lg font-semibold text-black dark:text-white">Bulk Reject — Isi Catatan</h3>
        </div>
        <form id="form-bulk-reject" method="POST" action="{{ route('dashboard.approval.bulk-reject') }}">
            @csrf
            <div class="px-6 py-5">
                <p class="mb-4 text-sm text-body dark:text-bodydark">
                    Isi catatan penolakan untuk masing-masing transaksi yang dipilih:
                </p>
                <div id="reject-items" class="max-h-96 overflow-y-auto space-y-4">
                    {{-- diisi oleh JS --}}
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-stroke px-6 py-4 dark:border-strokedark">
                <button type="button" onclick="closeModal('modal-reject')"
                        class="rounded-lg border border-stroke px-4 py-2 text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Reject Semua
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ── Helpers ────────────────────────────────────────────────

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

        // Sinkronisasi check-all
        const total = document.querySelectorAll('.row-check').length;
        document.getElementById('check-all').checked  = checked.length === total && total > 0;
        document.getElementById('check-all').indeterminate = checked.length > 0 && checked.length < total;
    }

    function toggleAll(source) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = source.checked);
        updateBulkBar();
    }

    // ── Modal helpers ──────────────────────────────────────────

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

    // Tutup modal kalau klik backdrop
    ['modal-approve', 'modal-reject'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) closeModal(id);
        });
    });

    // ── Bulk Approve ───────────────────────────────────────────

    function openBulkApproveModal() {
        const checked = getChecked();
        if (checked.length === 0) return;

        document.getElementById('approve-count').textContent = checked.length;
        document.getElementById('approve-ids').value = checked.map(cb => cb.value).join(',');

        const list = document.getElementById('approve-list');
        list.innerHTML = checked.map(cb =>
            `<li class="flex items-center gap-2">
                <span class="text-green-500">✓</span>
                <span>TRX-{{ now()->year }}-${String(cb.value).padStart(3,'0')}
                — ${cb.dataset.label}</span>
             </li>`
        ).join('');

        openModal('modal-approve');
    }

    // ── Bulk Reject ────────────────────────────────────────────

    function openBulkRejectModal() {
        const checked = getChecked();
        if (checked.length === 0) return;

        const container = document.getElementById('reject-items');
        container.innerHTML = checked.map(cb => `
            <div class="rounded-lg border border-stroke p-4 dark:border-strokedark">
                <input type="hidden" name="ids[]" value="${cb.value}">
                <p class="mb-2 text-sm font-medium text-black dark:text-white">
                    TRX-{{ now()->year }}-${String(cb.value).padStart(3,'0')}
                    <span class="font-normal text-body dark:text-bodydark">— ${cb.dataset.label}</span>
                </p>
                <textarea name="catatan[${cb.value}]"
                          rows="2"
                          placeholder="Catatan penolakan (opsional)..."
                          class="w-full rounded-lg border border-stroke p-2 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white"></textarea>
            </div>
        `).join('');

        openModal('modal-reject');
    }
</script>
@endpush

@endsection