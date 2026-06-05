@extends('layouts.app')

@section('content')
{{-- Flatpickr --}}
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
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="filterSearch" placeholder="Search..."
                    class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
            </div>

            <div class="flex items-center gap-2">
                <select id="filterJenis"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Jenis</option>
                    <option value="QURBAN">Qurban</option>
                    <option value="ZAKAT">Zakat</option>
                    <option value="KAJIAN">Kajian</option>
                    <option value="SOSIAL">Sosial</option>
                    <option value="LAINNYA">Lainnya</option>
                </select>
                <select id="filterStatus"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Status</option>
                    <option value="AKTIF">Aktif</option>
                    <option value="DITUTUP">Ditutup</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">No</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Nama Kegiatan</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jenis</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Anggaran</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Panitia</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($kegiatan as $index => $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors table-row"
                    data-nama="{{ strtolower($item->nama_kegiatan) }}"
                    data-jenis="{{ $item->jenis_kegiatan }}"
                    data-status="{{ $item->status }}">
                    <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                        {{ $kegiatan->firstItem() + $index }}
                    </td>
                    <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">{{ $item->nama_kegiatan }}</td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                            {{ $item->jenis_kegiatan }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                        {{ $item->tanggal_mulai->format('d/m/Y') }}
                        @if($item->tanggal_selesai) – {{ $item->tanggal_selesai->format('d/m/Y') }} @endif
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                        Rp {{ number_format($item->anggaran, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">{{ $item->panitia->name }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if($item->status === 'AKTIF')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Ditutup</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            <button onclick='openShowModal(@json($item->load("panitia")))'
                                class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button onclick='openEditModal(@json($item), @json($panitias))'
                                class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="openDeleteModal({{ $item->id }}, '{{ addslashes($item->nama_kegiatan) }}')"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                        Tidak ada data kegiatan.
                        <button onclick="openCreateModal()" class="text-green-600 hover:underline ml-1">Tambah sekarang</button>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div id="emptyFilter" class="hidden px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
            Tidak ada data yang sesuai filter.
        </div>

        {{-- Pagination --}}
        @if($kegiatan->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                @if($kegiatan->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $kegiatan->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                @foreach($kegiatan->getUrlRange(1, $kegiatan->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors {{ $page === $kegiatan->currentPage() ? 'bg-green-600 text-white font-medium' : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($kegiatan->hasMorePages())
                <a href="{{ $kegiatan->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $kegiatan->firstItem() }} to {{ $kegiatan->lastItem() }} of {{ $kegiatan->total() }} entries
            </span>
        </div>
        @endif
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

// SHOW
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

// EDIT
let editDatePicker = null;

function openEditModal(item, panitias) {
    const route = "{{ route('dashboard.kegiatan.update', ':id') }}".replace(':id', item.id);
    document.getElementById('editForm').action        = route;
    document.getElementById('edit_nama').value        = item.nama_kegiatan;
    document.getElementById('edit_jenis').value       = item.jenis_kegiatan;
    document.getElementById('edit_anggaran').value    = item.anggaran;

    const sel = document.getElementById('edit_panitia');
    sel.innerHTML = panitias.map(p =>
        `<option value="${p.id}" ${p.id == item.panitia_id ? 'selected' : ''}>${p.name}</option>`
    ).join('');

    // Set date range
    const mulai   = fmtDateInput(item.tanggal_mulai);
    const selesai = fmtDateInput(item.tanggal_selesai);

    if (editDatePicker) editDatePicker.destroy();
    editDatePicker = flatpickr('#edit_daterange', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        locale: 'id',
        defaultDate: selesai ? [mulai, selesai] : [mulai],
        onChange: function(selectedDates) {
            document.getElementById('edit_tanggal_mulai').value   = selectedDates[0] ? flatpickr.formatDate(selectedDates[0], 'Y-m-d') : '';
            document.getElementById('edit_tanggal_selesai').value = selectedDates[1] ? flatpickr.formatDate(selectedDates[1], 'Y-m-d') : '';
        }
    });

    // Set hidden inputs
    document.getElementById('edit_tanggal_mulai').value   = mulai;
    document.getElementById('edit_tanggal_selesai').value = selesai;

    openModal(editModal);
}

// DELETE
function openDeleteModal(id, nama) {
    const route = "{{ route('dashboard.kegiatan.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteForm').action       = route;
    document.getElementById('delete_nama').textContent = nama;
    openModal(deleteModal);
}

// CREATE
let createDatePicker = null;

function openCreateModal() {
    if (createDatePicker) createDatePicker.destroy();
    createDatePicker = flatpickr('#create_daterange', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        locale: 'id',
        onChange: function(selectedDates) {
            document.getElementById('create_tanggal_mulai').value   = selectedDates[0] ? flatpickr.formatDate(selectedDates[0], 'Y-m-d') : '';
            document.getElementById('create_tanggal_selesai').value = selectedDates[1] ? flatpickr.formatDate(selectedDates[1], 'Y-m-d') : '';
        }
    });
    openModal(createModal);
}

// ESC
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        [showModal, editModal, deleteModal, createModal].forEach(m => m.classList.add('hidden'));
        hideBackdrop();
    }
});

// Real-time filter
function applyFilters() {
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const jenis  = document.getElementById('filterJenis').value;
    const status = document.getElementById('filterStatus').value;
    const rows   = document.querySelectorAll('#tableBody .table-row');
    let visible  = 0;

    rows.forEach(row => {
        const match = row.dataset.nama.includes(search)
            && (!jenis  || row.dataset.jenis  === jenis)
            && (!status || row.dataset.status === status);
        row.classList.toggle('hidden', !match);
        if (match) visible++;
    });

    document.getElementById('emptyFilter').classList.toggle('hidden', visible > 0);
}

let debounce;
document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(applyFilters, 200);
});
document.getElementById('filterJenis').addEventListener('change', applyFilters);
document.getElementById('filterStatus').addEventListener('change', applyFilters);
</script>
@endsection