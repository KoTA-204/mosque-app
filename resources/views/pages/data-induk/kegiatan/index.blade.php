@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Kegiatan Khusus</h1>
        <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            Tambah Kegiatan
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p id="stat-total" class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray.400 mt-0.5">Total Kegiatan</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p id="stat-aktif" class="text-2xl font-bold text-green-600">{{ $stats['aktif'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kegiatan Aktif</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div>
                <p id="stat-ditutup" class="text-2xl font-bold text-red-500">{{ $stats['ditutup'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kegiatan Ditutup</p>
            </div>
        </div>
    </div>

    {{-- Alert area --}}
    <div id="alertArea"></div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                Show
                <select id="perPage" onchange="applyFilters()"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                entries
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filterJenis" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Jenis</option>
                    <option value="QURBAN">Qurban</option>
                    <option value="ZAKAT">Zakat</option>
                    <option value="KAJIAN">Kajian</option>
                    <option value="SOSIAL">Sosial</option>
                    <option value="LAINNYA">Lainnya</option>
                </select>
                <select id="filterStatus" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Status</option>
                    <option value="AKTIF">Aktif</option>
                    <option value="DITUTUP">Ditutup</option>
                </select>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="filterSearch" placeholder="Search..." autocomplete="off"
                        class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-48 placeholder-gray-400">
                </div>
            </div>
        </div>
        <div id="tableWrapper">
            @include('pages.data-induk.kegiatan.table')
        </div>
    </div>
</div>

{{-- Modal Container (create, edit, show) --}}
<div id="modalContainer"></div>

{{-- Delete Modal --}}
-- Modal hapus dimuat dinamis via AJAX (openDeleteModal) --

<script>
const modalContainer = document.getElementById('modalContainer');
const csrfToken      = document.querySelector('meta[name="csrf-token"]').content;
const baseUrl        = "{{ url('dashboard/kegiatan') }}";
const filterUrl      = "{{ route('dashboard.kegiatan.index') }}";
let filterDebounce;

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    if (id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            if (modalContainer.contains(el)) modalContainer.innerHTML = '';
        }
    } else {
        modalContainer.querySelectorAll('[id$="Modal"]').forEach(el => el.style.display = 'none');
        modalContainer.innerHTML = '';
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="Modal"]').forEach(el => {
            if (el.style.display === 'flex') closeModal(el.id);
        });
    }
});

function loadModal(url) {
    modalContainer.innerHTML = '';
    const loader = document.createElement('div');
    loader.style.cssText = 'display:flex;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);';
    loader.innerHTML = `
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-10 flex items-center justify-center">
            <svg class="animate-spin w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </div>`;
    document.body.appendChild(loader);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            loader.remove();
            modalContainer.innerHTML = data.html;
            modalContainer.querySelectorAll('script').forEach(old => {
                const s = document.createElement('script');
                old.src ? (s.src = old.src) : (s.textContent = old.textContent);
                document.head.appendChild(s);
                old.remove();
            });
            const modal = modalContainer.querySelector('[id$="Modal"]');
            if (modal) modal.style.display = 'flex';
        })
        .catch(() => {
            loader.remove();
            showAlert('Gagal memuat data.', 'error');
        });
}

function openCreateModal() { loadModal(`${baseUrl}/create`); }
function openShowModal(id)  { loadModal(`${baseUrl}/${id}`); }
function openEditModal(id)  { loadModal(`${baseUrl}/${id}/edit`); }

function openDeleteModal(id) {
    loadModal(`${baseUrl}/${id}/delete`);
}

function submitDeleteKegiatan(url) {
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: (() => { const f = new FormData(); f.append('_method', 'DELETE'); return f; })(),
    })
    .then(r => r.json())
    .then(res => {
        closeModal('deleteKegiatanModal');
        if (res.success) {
            showAlert(res.message ?? 'Kegiatan berhasil dihapus.', 'success');
            applyFilters();
        } else {
            showAlert(res.message ?? 'Gagal menghapus kegiatan.', 'error');
        }
    })
    .catch(() => showAlert('Terjadi kesalahan saat menghapus.', 'error'));
}

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

function updateStats(stats) {
    if (!stats) return;
    document.getElementById('stat-total').textContent   = stats.total;
    document.getElementById('stat-aktif').textContent   = stats.aktif;
    document.getElementById('stat-ditutup').textContent = stats.ditutup;
}

function submitKegiatanForm(formId, method, url) {
    const form = document.getElementById(formId);
    form.querySelectorAll('[id^="err-"]').forEach(el => el.textContent = '');
    form.querySelectorAll('input,select,textarea').forEach(el => el.classList.remove('border-red-400'));

    const data = new FormData(form);
    if (method === 'PUT') data.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const active = modalContainer.querySelector('[id$="Modal"]');
            if (active) closeModal(active.id);
            showAlert(res.message, 'success');
            applyFilters();
        } else if (res.errors) {
            Object.entries(res.errors).forEach(([field, messages]) => {
                const el    = form.querySelector(`[name="${field}"]`);
                const errEl = document.getElementById(`err-${field}`);
                if (el)    el.classList.add('border-red-400');
                if (errEl) errEl.textContent = messages[0];
            });
        }
    })
    .catch(() => showAlert('Terjadi kesalahan.', 'error'));
}

function applyFilters() {
    const params  = new URLSearchParams();
    const search  = document.getElementById('filterSearch').value;
    const jenis   = document.getElementById('filterJenis').value;
    const status  = document.getElementById('filterStatus').value;
    const perPage = document.getElementById('perPage').value;

    if (search)  params.set('search',  search);
    if (jenis)   params.set('jenis',   jenis);
    if (status)  params.set('status',  status);
    params.set('per_page', perPage);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
        updateStats(data.stats);
    });
}

function loadPage(e, url) {
    e.preventDefault();
    const current = new URLSearchParams(url.split('?')[1] || '');
    const params  = new URLSearchParams();
    const search  = document.getElementById('filterSearch').value;
    const jenis   = document.getElementById('filterJenis').value;
    const status  = document.getElementById('filterStatus').value;
    const perPage = document.getElementById('perPage').value;

    if (search)  params.set('search',  search);
    if (jenis)   params.set('jenis',   jenis);
    if (status)  params.set('status',  status);
    params.set('per_page', perPage);
    params.set('page', current.get('page') || 1);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
        updateStats(data.stats);
    });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(applyFilters, 400);
});

// Delete ditangani via submitDeleteKegiatan() (AJAX)
</script>
@endsection