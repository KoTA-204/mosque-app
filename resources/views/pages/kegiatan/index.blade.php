@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.js"></script>

<div class="p-6 space-y-6">

    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Kegiatan Khusus</h1>
        <button onclick="openCreateModal()"
            class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            Tambah Kegiatan
        </button>
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

            {{-- Kiri: Show entries --}}
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

            {{-- Kanan: Filter --}}
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
            @include('pages.kegiatan.table')
        </div>

    </div>
</div>

{{-- Modal Backdrop --}}
<div id="modalBackdrop" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-40 flex items-center justify-center p-4"
     onclick="closeAllModals(event)">
    @include('pages.kegiatan.show')
    @include('pages.kegiatan.edit')
    @include('pages.kegiatan.create')
    @include('pages.kegiatan.delete')
</div>

<script>
const backdrop    = document.getElementById('modalBackdrop');
const showModal   = document.getElementById('showModal');
const editModal   = document.getElementById('editModal');
const deleteModal = document.getElementById('deleteModal');
const createModal = document.getElementById('createModal');

function showBackdrop() { backdrop.classList.remove('hidden'); }
function hideBackdrop() { backdrop.classList.add('hidden'); }

function closeAllModals(e) {
    if (e && e.target !== backdrop) return;
    [showModal, editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
    hideBackdrop();
}

function openModal(modal) {
    [showModal, editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
    modal.classList.remove('hidden');
    showBackdrop();
}

// ── Flatpickr — inisialisasi sekali saat DOM ready ────────────
let createPicker, editPicker;

document.addEventListener('DOMContentLoaded', () => {

    // Create picker
    createPicker = flatpickr('#create_daterange', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        locale: 'id',
        onChange(dates) {
            document.getElementById('create_tanggal_mulai').value   = dates[0] ? flatpickr.formatDate(dates[0], 'Y-m-d') : '';
            document.getElementById('create_tanggal_selesai').value = dates[1] ? flatpickr.formatDate(dates[1], 'Y-m-d') : '';
        }
    });

    // Kalau ada old value (setelah validation fail), set kembali
    const oldMulai   = document.getElementById('create_tanggal_mulai').value;
    const oldSelesai = document.getElementById('create_tanggal_selesai').value;
    if (oldMulai) {
        createPicker.setDate(oldSelesai ? [oldMulai, oldSelesai] : [oldMulai]);
    }

    // Edit picker
    editPicker = flatpickr('#edit_daterange', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        locale: 'id',
        onChange(dates) {
            document.getElementById('edit_tanggal_mulai').value   = dates[0] ? flatpickr.formatDate(dates[0], 'Y-m-d') : '';
            document.getElementById('edit_tanggal_selesai').value = dates[1] ? flatpickr.formatDate(dates[1], 'Y-m-d') : '';
        }
    });

    // Buka modal create otomatis kalau ada validation error
    @if($errors->any())
    openCreateModal();
    @endif
});

// ── Modal functions ───────────────────────────────────────────
function statusBadge(status) {
    if (status === 'AKTIF') {
        return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Aktif</span>`;
    }
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Ditutup</span>`;
}

function fmtDate(str) {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

function fmtDateInput(str) {
    if (!str) return '';
    return str.substring(0, 10);
}

function openShowModal(item) {
    document.getElementById('show_nama').textContent          = item.nama_kegiatan;
    document.getElementById('show_jenis').textContent         = item.jenis_kegiatan;
    document.getElementById('show_status_badge').innerHTML    = statusBadge(item.status);
    document.getElementById('show_tgl_mulai').textContent     = fmtDate(item.tanggal_mulai);
    document.getElementById('show_tgl_selesai').textContent   = fmtDate(item.tanggal_selesai);
    document.getElementById('show_anggaran').textContent      = 'Rp ' + Number(item.anggaran).toLocaleString('id-ID');
    document.getElementById('show_panitia_nama').textContent  = item.panitia?.name  ?? '-';
    document.getElementById('show_panitia_email').textContent = item.panitia?.email ?? '';
    openModal(showModal);
}

function openEditModal(item, panitias) {
    const route = "{{ route('dashboard.kegiatan.update', ':id') }}".replace(':id', item.id);
    document.getElementById('editForm').action         = route;
    document.getElementById('edit_nama').value         = item.nama_kegiatan;
    document.getElementById('edit_jenis').value        = item.jenis_kegiatan;
    document.getElementById('edit_anggaran').value     = item.anggaran;

    const sel = document.getElementById('edit_panitia');
    sel.innerHTML = panitias.map(p =>
        `<option value="${p.id}" ${p.id == item.panitia_id ? 'selected' : ''}>${p.name}</option>`
    ).join('');

    const mulai   = fmtDateInput(item.tanggal_mulai);
    const selesai = fmtDateInput(item.tanggal_selesai);

    document.getElementById('edit_tanggal_mulai').value   = mulai;
    document.getElementById('edit_tanggal_selesai').value = selesai;

    editPicker.setDate(selesai ? [mulai, selesai] : [mulai]);

    openModal(editModal);
}

function openDeleteModal(id, nama) {
    const route = "{{ route('dashboard.kegiatan.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteForm').action       = route;
    document.getElementById('delete_nama').textContent = nama;
    openModal(deleteModal);
}

function openCreateModal() {
    // Reset form & picker
    createModal.querySelector('form').reset();
    createPicker.clear();
    document.getElementById('create_tanggal_mulai').value   = '';
    document.getElementById('create_tanggal_selesai').value = '';
    openModal(createModal);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        [showModal, editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
        hideBackdrop();
    }
});

// ── AJAX Filter ───────────────────────────────────────────────
const filterUrl = "{{ route('dashboard.kegiatan.index') }}";
let filterDebounce;

function applyFilters() {
    const search  = document.getElementById('filterSearch').value;
    const jenis   = document.getElementById('filterJenis').value;
    const status  = document.getElementById('filterStatus').value;
    const perPage = document.getElementById('perPage').value;

    const params = new URLSearchParams();
    if (search)  params.set('search', search);
    if (jenis)   params.set('jenis', jenis);
    if (status)  params.set('status', status);
    params.set('per_page', perPage);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}

function loadPage(e, url) {
    e.preventDefault();
    const current = new URLSearchParams(url.split('?')[1] || '');
    const params  = new URLSearchParams();

    // Pertahankan filter aktif
    const search  = document.getElementById('filterSearch').value;
    const jenis   = document.getElementById('filterJenis').value;
    const status  = document.getElementById('filterStatus').value;
    const perPage = document.getElementById('perPage').value;

    if (search)  params.set('search', search);
    if (jenis)   params.set('jenis', jenis);
    if (status)  params.set('status', status);
    params.set('per_page', perPage);
    params.set('page', current.get('page') || 1);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(applyFilters, 400);
});
</script>
@endsection