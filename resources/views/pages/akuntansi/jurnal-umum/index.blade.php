@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Jurnal Umum</h1>
    </div>

    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-jurnal.alert type="error" :message="session('error')" />
    @endif

    {{-- Alert area (dipakai oleh aksi AJAX, misal bulk post) --}}
    <div id="alertArea"></div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Bulk Action Bar --}}
        <x-jurnal.bulk-action-bar
            post-label="Post Terpilih"
            on-post="submitBulkPost()"
        />

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                Tampil
                <select id="perPage" onchange="applyFilters()"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                data
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                {{-- Filter Bulan --}}
                <input type="month" id="filterBulan" value="{{ $bulan }}" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                {{-- Filter Status --}}
                <select id="filterStatus" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="posted" {{ $status === 'posted' ? 'selected' : '' }}>Posted</option>
                </select>
                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="filterSearch" value="{{ $search }}"
                        placeholder="Cari keterangan..."
                        autocomplete="off"
                        class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-44 placeholder-gray-400">
                </div>
            </div>
        </div>

        <div id="tableWrapper">
            @include('pages.akuntansi.jurnal-umum.table')
        </div>
    </div>
</div>

{{-- Modal Konfirmasi Bulk Post --}}
<x-confirm-modal
    id="confirmBulkPostModal"
    title="Posting Jurnal Terpilih"
    message="Posting jurnal yang dipilih?"
    confirm-label="Ya, Post"
    confirm-class="bg-green-600 hover:bg-green-700"
    :on-confirm="'confirmBulkPost()'"
/>

<script>
const filterUrl   = "{{ route('dashboard.jurnal-umum.index') }}";
const bulkPostUrl = "{{ route('dashboard.jurnal-umum.bulk-post') }}";
const csrfToken   = "{{ csrf_token() }}";
let filterDebounce;

function applyFilters() {
    const bulan   = document.getElementById('filterBulan').value;
    const search  = document.getElementById('filterSearch').value;
    const status  = document.getElementById('filterStatus').value;
    const perPage = document.getElementById('perPage').value;
    const params  = new URLSearchParams();
    if (bulan)  params.set('bulan', bulan);
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    params.set('per_page', perPage);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
        updateBulkBar();
    });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(applyFilters, 400);
});

// ── Modal helper (dipakai x-confirm-modal) ──────────────────────────────────
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

// ── Bulk Selection ─────────────────────────────────────────────────────────
function updateBulkBar() {
    const checked = document.querySelectorAll('.jurnal-checkbox:checked').length;
    const bar     = document.getElementById('bulkActionBar');
    const badge   = document.getElementById('bulkCountBadge');
    if (badge) badge.textContent = checked;
    if (bar)   bar.style.display = checked > 0 ? 'flex' : 'none';
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.jurnal-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBulkBar();
}

function clearSelection() {
    document.querySelectorAll('.jurnal-checkbox').forEach(cb => cb.checked = false);
    const master = document.querySelector('thead input[type="checkbox"]');
    if (master) master.checked = false;
    updateBulkBar();
}

// Klik "Post Terpilih" → buka modal konfirmasi (bukan langsung fetch)
function submitBulkPost() {
    const ids = [...document.querySelectorAll('.jurnal-checkbox:checked')].map(cb => cb.value);
    if (!ids.length) return;

    document.getElementById('confirmBulkPostModalMessage').textContent =
        `Posting ${ids.length} jurnal yang dipilih? Jurnal yang sudah di-posting tidak dapat diedit atau dihapus.`;
    openModal('confirmBulkPostModal');
}

// Dipanggil oleh tombol "Ya, Post" di modal (lewat prop on-confirm)
function confirmBulkPost() {
    const ids = [...document.querySelectorAll('.jurnal-checkbox:checked')].map(cb => cb.value);
    if (!ids.length) return;

    fetch(bulkPostUrl, {
        method: 'POST',
        headers: {
            'Content-Type':     'application/json',
            'X-CSRF-TOKEN':     csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ ids }),
    })
    .then(r => r.json())
    .then(data => {
        applyFilters();

        if (data.alert) {
            renderAlert(data.alert);
        } else {
            showAlert(data.message, data.success ? 'success' : 'error');
        }
    });
}

// Render HTML komponen x-alert yang sudah dirender server ke #alertArea
function renderAlert(html) {
    const area = document.getElementById('alertArea');
    area.innerHTML = html;
    setTimeout(() => { area.innerHTML = ''; }, 4000);
}

// Fallback kalau response belum punya field `alert`
function showAlert(msg, type = 'success') {
    const area   = document.getElementById('alertArea');
    const colors = {
        success: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400',
        error:   'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400',
    };
    const icons = {
        success: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
        error:   '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
    };
    area.innerHTML = `
        <div class="flex items-center gap-3 ${colors[type] ?? colors.success} border rounded-xl px-4 py-3 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                ${icons[type] ?? icons.success}
            </svg>
            ${msg}
        </div>`;
    setTimeout(() => { area.innerHTML = ''; }, 4000);
}
</script>
@endsection