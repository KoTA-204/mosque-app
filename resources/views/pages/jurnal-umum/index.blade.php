@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Jurnal Umum</h1>
        <a href="{{ route('dashboard.jurnal-umum.create') }}"
            class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            + Tambah Jurnal
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="flex items-center gap-3">
                {{-- Per page --}}
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
                {{-- Bulk Post Button (muncul saat ada yang dicentang) --}}
                <button id="bulkPostBtn" onclick="bulkPost()"
                    class="hidden items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Posting Terpilih (<span id="selectedCount">0</span>)
                </button>
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
            @include('pages.jurnal-umum.table')
        </div>
    </div>
</div>

<script>
const filterUrl  = "{{ route('dashboard.jurnal-umum.index') }}";
const bulkPostUrl = "{{ route('dashboard.jurnal-umum.bulk-post') }}";
const csrfToken  = "{{ csrf_token() }}";
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
        updateBulkBtn();
    });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(applyFilters, 400);
});

function updateBulkBtn() {
    const checked = document.querySelectorAll('.jurnal-checkbox:checked').length;
    const btn = document.getElementById('bulkPostBtn');
    document.getElementById('selectedCount').textContent = checked;
    if (checked > 0) {
        btn.classList.remove('hidden');
        btn.classList.add('inline-flex');
    } else {
        btn.classList.add('hidden');
        btn.classList.remove('inline-flex');
    }
}

function toggleSelectAll(checkbox) {
    document.querySelectorAll('.jurnal-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBulkBtn();
}

function bulkPost() {
    const ids = [...document.querySelectorAll('.jurnal-checkbox:checked')].map(cb => cb.value);
    if (!ids.length) return;

    if (!confirm(`Posting ${ids.length} jurnal yang dipilih?`)) return;

    fetch(bulkPostUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ ids }),
    })
    .then(r => r.json())
    .then(data => {
        // Refresh tabel
        applyFilters();
        // Tampilkan notif
        const notif = document.createElement('div');
        notif.className = `fixed top-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium shadow-lg transition-all
            ${data.success ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'}`;
        notif.textContent = data.message;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 3500);
    });
}
</script>
@endsection