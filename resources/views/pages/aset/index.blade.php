@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
<style>
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #16a34a !important; border-color: #16a34a !important; }
    .flatpickr-day:hover { background: #f0fdf4; }
</style>
@endpush

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Aset</h1>
        @if(!empty($asetTerkunci))
            <button type="button" id="btnTambahAset" disabled aria-disabled="true"
               title="Jurnal pembuka sudah diposting. Penambahan aset kini otomatis melalui pencatatan transaksi (pembelian aset)."
               class="inline-flex items-center gap-2 border border-gray-300 dark:border-gray-700 text-gray-400 dark:text-gray-500 text-sm font-medium px-4 py-2 rounded-lg cursor-not-allowed opacity-60">
                Tambah Aset
            </button>
        @else
            <button id="btnTambahAset" onclick="openCreateModal()"
               class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                Tambah Aset
            </button>
        @endif
    </div>

    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')" />
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p id="stat-total" class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total Aset</p>
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
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Aset Aktif</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4 flex items-center gap-4">
            <div class="shrink-0 w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <p id="stat-tidak-aktif" class="text-2xl font-bold text-red-500">{{ $stats['tidak_aktif'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Aset Non-Aktif</p>
            </div>
        </div>
    </div>

    {{-- Alert area --}}
    <div id="alertArea"></div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl">
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            {{-- Kiri: Show entries --}}
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                Show
                <select id="perPage" onchange="applyFilters()"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                entries
            </div>

            {{-- Kanan: Search + Filter --}}
            <div class="flex items-center gap-2">

                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="filterSearch" placeholder="Search..." autocomplete="off"
                        class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-48 placeholder-gray-400">
                </div>

                {{-- Filter button --}}
                <div id="filterWrapper">
                    <button id="filterBtn" onclick="toggleFilterPanel()"
                        class="flex items-center gap-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-green-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filter
                        <span id="filterBadge"
                            class="hidden items-center justify-center w-4 h-4 text-xs font-bold text-white bg-green-600 rounded-full">
                        </span>
                    </button>
                </div>

            </div>
        </div>

        <div id="tableWrapper" class="overflow-x-auto">
            @include('pages.aset.table')
        </div>
    </div>

</div>

{{-- Filter Panel — fixed, di luar semua container --}}
<div id="filterPanel"
    class="hidden z-[9998] w-72 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl">

    {{-- Header tetap di atas --}}
    <div class="flex items-center justify-between px-4 pt-4 pb-3 border-b border-gray-100 dark:border-gray-800">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filter</p>
        <button onclick="resetFilters()" class="text-xs text-green-600 hover:underline">Reset</button>
    </div>

    {{-- Konten scrollable --}}
    <div id="filterPanelBody" class="p-4 space-y-4 overflow-y-auto">

        {{-- Tahun --}}
        <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Tahun Perolehan</label>
            <select id="filterTahun" onchange="applyFilters()"
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">Semua Tahun</option>
                @foreach(range(date('Y'), 2000, -1) as $tahun)
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                @endforeach
            </select>
        </div>

        {{-- Lokasi --}}
        <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Lokasi</label>
            <select id="filterLokasi" onchange="applyFilters()"
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">Semua Lokasi</option>
                @foreach(['Ruang Utama Masjid','Ruang Wanita Masjid','Menara Masjid','Ruang Utilitas','Garasi Masjid','Gudang','Lainnya'] as $lok)
                    <option value="{{ $lok }}">{{ $lok }}</option>
                @endforeach
            </select>
        </div>

        {{-- Sumber --}}
        <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Sumber Perolehan</label>
            <select id="filterSumber" onchange="applyFilters()"
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">Semua Sumber</option>
                <option value="Wakaf">Wakaf</option>
                <option value="Hibah/Donasi">Hibah/Donasi</option>
                <option value="Pembelian">Pembelian</option>
                <option value="Infak Jamaah">Infak Jamaah</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
            <select id="filterStatus" onchange="applyFilters()"
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="tidak aktif">Tidak Aktif</option>
            </select>
        </div>

        {{-- Kondisi --}}
        <div>
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Kondisi</label>
            <select id="filterKondisi" onchange="applyFilters()"
                class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">Semua Kondisi</option>
                <option value="BAIK">Baik</option>
                <option value="RUSAK RINGAN">Rusak Ringan</option>
                <option value="RUSAK BERAT">Rusak Berat</option>
            </select>
        </div>

    </div>
</div>

<div id="nonaktifAsetModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);">
    <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-xl mx-4 p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Nonaktifkan Aset</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Pilih alasan penonaktifan. Ini menentukan perlakuan penyusutan dan apakah aset dapat diaktifkan kembali.</p>

        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5">Alasan <span class="text-red-500">*</span></label>
        <select id="nonaktifAlasan" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 mb-1">
            <option value="MENGANGGUR">Menganggur sementara — tetap disusutkan, bisa diaktifkan lagi</option>
            <option value="RUSAK_BERAT">Rusak berat — terkunci sampai kondisi diperbaiki</option>
            <option value="AKAN_DILEPAS">Akan dilepas / dibuang — tidak bisa diaktifkan lagi</option>
        </select>

        <div id="nonaktifJenisWrap" style="display:none;">
            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5 mt-3">Jenis pelepasan <span class="text-red-500">*</span></label>
            <select id="nonaktifJenisPelepasan" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                <option value="">— pilih —</option>
                <option value="DIJUAL">Dijual</option>
                <option value="DIHIBAHKAN">Dihibahkan</option>
                <option value="HILANG">Hilang</option>
                <option value="DIBUANG">Rusak total / Dibuang</option>
            </select>
            <p class="text-[11px] text-gray-400 mt-1">Wajib dipilih. Setelah ini, bendahara dapat mencatat pelepasannya di jurnal penyesuaian.</p>
        </div>

        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1.5 mt-3">Catatan</label>
        <textarea id="nonaktifCatatan" rows="3" placeholder="Opsional — mis. rencana pelepasan, uraian kerusakan" class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400"></textarea>

        <div class="flex justify-end gap-2 mt-5">
            <button type="button" onclick="closeModal('nonaktifAsetModal')" class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">Batal</button>
            <button type="button" id="nonaktifConfirmBtn" class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white">Nonaktifkan</button>
        </div>
    </div>
</div>

<x-confirm-modal
    id="hapusAsetModal"
    title="Hapus Aset"
    message="Aset akan dihapus dari daftar. Data tetap tersimpan untuk keperluan historis."
    confirmLabel="Hapus"
    confirmClass="bg-red-600 hover:bg-red-700"
    :onConfirm="'doHapusAset()'"
/>

{{-- Modal Container --}}
<div id="modalContainer"></div>


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.js"></script>
<script>
const modalContainer = document.getElementById('modalContainer');
const csrfToken      = document.querySelector('meta[name="csrf-token"]').content;
const baseUrl        = "{{ url('dashboard/aset') }}";
const filterUrl      = "{{ route('dashboard.aset.index') }}";
let filterDebounce;

// ── Stats realtime ────────────────────────────────────────────
function updateStats(delta) {
    const aktifEl      = document.getElementById('stat-aktif');
    const tidakAktifEl = document.getElementById('stat-tidak-aktif');
    aktifEl.textContent      = (parseInt(aktifEl.textContent) || 0) + delta;
    tidakAktifEl.textContent = (parseInt(tidakAktifEl.textContent) || 0) - delta;
}

function fetchStats() {
    fetch(`${filterUrl}?stats_only=1`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.stats) {
            document.getElementById('stat-total').textContent       = data.stats.total;
            document.getElementById('stat-aktif').textContent       = data.stats.aktif;
            document.getElementById('stat-tidak-aktif').textContent = data.stats.tidak_aktif;
        }
    });
}

// ── Filter panel (fixed positioning) ─────────────────────────
function toggleFilterPanel() {
    const panel = document.getElementById('filterPanel');
    const body  = document.getElementById('filterPanelBody');
    const btn   = document.getElementById('filterBtn');
    const rect  = btn.getBoundingClientRect();
    const scrollY = window.pageYOffset || document.documentElement.scrollTop;

    if (panel.classList.contains('hidden')) {
        const top  = rect.bottom + scrollY + 8;
        const left = Math.max(8, rect.right - 288);

        panel.style.position   = 'absolute';
        panel.style.top        = top + 'px';
        panel.style.left       = left + 'px';
        body.style.maxHeight   = Math.min(400, window.innerHeight - rect.bottom - 24) + 'px';
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
    }
}

document.addEventListener('click', function(e) {
    const panel   = document.getElementById('filterPanel');
    const wrapper = document.getElementById('filterWrapper');
    if (!panel.classList.contains('hidden') &&
        !panel.contains(e.target) &&
        !wrapper.contains(e.target)) {
        panel.classList.add('hidden');
    }
});

function resetFilters() {
    ['filterTahun','filterLokasi','filterSumber','filterStatus','filterKondisi'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('filterSearch').value = '';
    countActiveFilters();
    applyFilters();
}

function countActiveFilters() {
    const ids = ['filterTahun','filterLokasi','filterSumber','filterStatus','filterKondisi'];
    let count = ids.filter(id => {
        const el = document.getElementById(id);
        return el && el.value !== '';
    }).length;
    if (document.getElementById('filterSearch').value) count++;
    const badge = document.getElementById('filterBadge');
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('hidden');
        badge.classList.add('flex');
    } else {
        badge.classList.add('hidden');
        badge.classList.remove('flex');
    }
}

// ── Open & close modal ────────────────────────────────────────
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
        modalContainer.querySelectorAll('[id$="Modal"]').forEach(el => {
            el.style.display = 'none';
        });
        modalContainer.innerHTML = '';
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('filterPanel').classList.add('hidden');
        modalContainer.querySelectorAll('[id$="Modal"]').forEach(el => {
            if (el.style.display === 'flex') closeModal(el.id);
        });
    }
});

// ── Load modal via AJAX ───────────────────────────────────────
function loadModal(url) {
    modalContainer.innerHTML = '';

    const loader = document.createElement('div');
    loader.id = 'modalLoader';
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
            modalContainer.querySelectorAll('script').forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                document.head.appendChild(newScript);
                oldScript.remove();
            });
            const modal = modalContainer.querySelector('[id$="Modal"]');
            if (modal) modal.style.display = 'flex';
            initModalFeatures();
        })
        .catch(() => {
            loader.remove();
            showAlert('Gagal memuat data.', 'error');
        });
}

function openCreateModal() { loadModal(`${baseUrl}/create`); }
function openShowModal(id)  { loadModal(`${baseUrl}/${id}`); }
function openEditModal(id)  { loadModal(`${baseUrl}/${id}/edit`); }

// ── Init modal features ───────────────────────────────────────
function initModalFeatures() {
    function formatRibuan(num) {
        if (!num && num !== 0) return '';
        return Math.floor(num).toLocaleString('id-ID');
    }
    function parseRibuan(str) {
        return parseInt((str || '').replace(/\./g, '').replace(/[^0-9]/g, ''), 10) || 0;
    }
    function buatFlatpickr(displayId, hiddenId) {
        const displayEl = document.getElementById(displayId);
        const hiddenEl  = document.getElementById(hiddenId);
        if (!displayEl) return;
        flatpickr(displayEl, {
            locale:     'id',
            dateFormat: 'd F Y',
            maxDate:    'today',
            onChange: function(selectedDates) {
                if (selectedDates[0]) {
                    const d   = selectedDates[0];
                    const ymd = d.getFullYear() + '-' +
                        String(d.getMonth()+1).padStart(2,'0') + '-' +
                        String(d.getDate()).padStart(2,'0');
                    if (hiddenEl) hiddenEl.value = ymd;
                    updatePreview();
                }
            }
        });
    }
    function buatFormatNilai(displayId, hiddenId) {
        const displayEl = document.getElementById(displayId);
        const hiddenEl  = document.getElementById(hiddenId);
        if (!displayEl) return;
        displayEl.addEventListener('input', function() {
            const raw     = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
            const num     = parseInt(raw, 10) || 0;
            const prevLen = this.value.length;
            const pos     = this.selectionStart;
            this.value    = num > 0 ? formatRibuan(num) : '';
            if (hiddenEl) hiddenEl.value = num > 0 ? num : '';
            const diff = this.value.length - prevLen;
            try { this.setSelectionRange(pos + diff, pos + diff); } catch(e) {}
            updatePreview();
        });
    }

    const isCreate = !!document.getElementById('createAsetForm');
    const isEdit   = !!document.getElementById('editAsetForm');
    if (!isCreate && !isEdit) return;

    const prefix = isCreate ? 'create' : 'edit';

    buatFlatpickr(`${prefix}-fp-tanggal_perolehan`, `${prefix}-tanggal_perolehan`);
    buatFlatpickr(`${prefix}-fp-tanggal_mulai`,     `${prefix}-tanggal_mulai_penyusutan`);
    buatFormatNilai(`${prefix}-display-nilai`,       `${prefix}-nilai_tercatat`);

    const labelNilaiMap = {
        'Wakaf':        'Nilai Wajar Aset (IDR)',
        'Hibah/Donasi': 'Nilai Wajar Aset (IDR)',
        'Infak Jamaah': 'Nilai Wajar Aset (IDR)',
        'Pembelian':    'Nilai Perolehan / Harga Beli (IDR)',
        'Lainnya':      'Nilai Perolehan (IDR)',
        '':             'Nilai Perolehan (IDR)',
    };
    const labelPemberiMap = {
        'Wakaf':        'Nama Wakif (Pemberi Wakaf)',
        'Hibah/Donasi': 'Nama Donatur / Pemberi Hibah',
        'Infak Jamaah': 'Nama Pemberi Infak',
    };
    const showPemberi = ['Wakaf','Hibah/Donasi','Infak Jamaah'];

    const sumberEl = document.getElementById(`${prefix}-sumberPerolehan`);
    if (sumberEl) {
        sumberEl.addEventListener('change', function() {
            const val     = this.value;
            const labelEl = document.getElementById(`${prefix}-labelNilai`);
            if (labelEl) labelEl.innerHTML = `${labelNilaiMap[val] ?? 'Nilai Perolehan (IDR)'} <span class="text-red-500">*</span>`;
            const fp = document.getElementById(`${prefix}-fieldNamaPemberi`);
            if (fp) {
                fp.classList.toggle('hidden', !showPemberi.includes(val));
                const labelPemberiEl = document.getElementById(`${prefix}-labelNamaPemberi`);
                if (labelPemberiEl && showPemberi.includes(val)) {
                    labelPemberiEl.textContent = labelPemberiMap[val] ?? 'Nama Pemberi';
                }
            }
        });
    }

    const cb      = document.getElementById(`${prefix}-cbDisusutkan`);
    const section = document.getElementById(`${prefix}-sectionPenyusutan`);
    if (cb && section) {
        cb.addEventListener('change', function() {
            if (this.checked) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
                const fpDisplay = document.getElementById(`${prefix}-fp-tanggal_mulai`);
                const fpHidden  = document.getElementById(`${prefix}-tanggal_mulai_penyusutan`);
                const umurEl    = document.getElementById(`${prefix}-umurManfaat`);
                const prevEl    = document.getElementById(`${prefix}-previewPenyusutan`);
                if (fpDisplay) fpDisplay.value = '';
                if (fpHidden)  fpHidden.value  = '';
                if (umurEl)    umurEl.value     = '';
                if (prevEl)    prevEl.classList.add('hidden');
            }
        });
    }

    const umurEl = document.getElementById(`${prefix}-umurManfaat`);
    if (umurEl) umurEl.addEventListener('input', updatePreview);

    function updatePreview() {
        const displayNilai = document.getElementById(`${prefix}-display-nilai`);
        const umur         = parseInt(document.getElementById(`${prefix}-umurManfaat`)?.value) || 0;
        const tgl          = document.getElementById(`${prefix}-tanggal_mulai_penyusutan`)?.value;
        const prev         = document.getElementById(`${prefix}-previewPenyusutan`);
        if (!prev) return;
        const nilai = parseRibuan(displayNilai?.value || '');
        if (nilai > 0 && umur > 0) {
            prev.classList.remove('hidden');
            const tahunan   = nilai / umur;
            const bulanan   = tahunan / 12;
            const tahunanEl = document.getElementById(`${prefix}-prev-tahunan`);
            const bulananEl = document.getElementById(`${prefix}-prev-bulanan`);
            const selesaiEl = document.getElementById(`${prefix}-prev-selesai`);
            if (tahunanEl) tahunanEl.textContent = 'Rp ' + formatRibuan(tahunan);
            if (bulananEl) bulananEl.textContent = 'Rp ' + formatRibuan(bulanan);
            if (selesaiEl) selesaiEl.textContent = tgl
                ? (new Date(tgl).getFullYear() + umur - 1).toString()
                : '–';
        } else {
            prev.classList.add('hidden');
        }
    }

    if (isEdit) updatePreview();
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

function renderAlert(html) {
    const area = document.getElementById('alertArea');
    area.innerHTML = html;
    setTimeout(() => { area.innerHTML = ''; }, 4000);
}

// ── AJAX Filter ───────────────────────────────────────────────
function applyFilters() {
    const search  = document.getElementById('filterSearch').value;
    const tahun   = document.getElementById('filterTahun').value;
    const lokasi  = document.getElementById('filterLokasi').value;
    const sumber  = document.getElementById('filterSumber').value;
    const status  = document.getElementById('filterStatus').value;
    const kondisi = document.getElementById('filterKondisi').value;
    const perPage = document.getElementById('perPage').value;

    const params = new URLSearchParams();
    if (search)  params.set('search',  search);
    if (tahun)   params.set('tahun',   tahun);
    if (lokasi)  params.set('lokasi',  lokasi);
    if (sumber)  params.set('sumber',  sumber);
    if (status)  params.set('status',  status);
    if (kondisi) params.set('kondisi', kondisi);
    params.set('per_page', perPage);

    countActiveFilters();

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => { document.getElementById('tableWrapper').innerHTML = data.html; });
}

document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(filterDebounce);
    filterDebounce = setTimeout(() => { countActiveFilters(); applyFilters(); }, 300);
});

// ── Submit form ───────────────────────────────────────────────
function renderAsetDuplikatWarning(form, formId, method, url, res) {
    var existing = form.querySelector('.duplikat-warning-box');
    if (existing) existing.remove();

    var items = '';
    (res.matches || []).forEach(function (m) {
        var nilai = (typeof m.nilai_tercatat !== 'undefined') ? Number(m.nilai_tercatat).toLocaleString('id-ID') : '';
        var kode = m.kode_aset ? (m.kode_aset + ' — ') : '';
        items += '<li class="flex items-start gap-2"><span class="mt-0.5">•</span><span>' + kode + '<strong>' + m.nama_aset + '</strong> — ' + m.tanggal_perolehan + ' — Rp ' + nilai + '</span></li>';
    });

    var box = document.createElement('div');
    box.className = 'duplikat-warning-box mb-4 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 text-sm text-amber-800 dark:text-amber-300';
    box.innerHTML =
        '<div class="font-semibold mb-1">Kemungkinan input ganda</div>' +
        '<div class="mb-2">' + (res.message || 'Aset dengan nama, tanggal, dan nilai yang sama sudah tercatat.') + '</div>' +
        '<ul class="space-y-1 mb-3">' + items + '</ul>' +
        '<div class="text-xs mb-3">Kalau ini memang unit/aset yang berbeda, silakan tetap simpan.</div>' +
        '<div class="flex items-center gap-2">' +
            '<button type="button" class="duplikat-batal px-3 py-1.5 rounded-lg text-xs font-medium border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-800/40">Batal, periksa lagi</button>' +
            '<button type="button" class="duplikat-lanjut px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-amber-600 hover:bg-amber-700">Ya, tetap simpan</button>' +
        '</div>';

    form.insertBefore(box, form.firstChild);
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    box.querySelector('.duplikat-batal').addEventListener('click', function () {
        box.remove();
    });
    box.querySelector('.duplikat-lanjut').addEventListener('click', function () {
        box.remove();
        submitAsetForm(formId, method, url, true);
    });
}

function submitAsetForm(formId, method, url, force = false) {
    const form = document.getElementById(formId);
    form.querySelectorAll('[id^="err-"]').forEach(el => el.textContent = '');
    form.querySelectorAll('input,select,textarea').forEach(el => el.classList.remove('border-red-400'));

    const data = new FormData(form);
    if (method === 'PUT') data.append('_method', 'PUT');
    if (force) data.append('abaikan_duplikat', '1');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':     csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const activeModal = modalContainer.querySelector('[id$="Modal"]');
            if (activeModal) closeModal(activeModal.id);

            if (res.alert) {
                renderAlert(res.alert);
            } else {
                showAlert(res.message, 'success');
            }

            applyFilters();
            fetchStats();
        } else if (res.duplicate_warning) {
            renderAsetDuplikatWarning(form, formId, method, url, res);
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

// ── Modal alasan penonaktifan aset ────────────────────────
let nonaktifPendingId = null;
function toggleJenisPelepasanVisibility() {
    const alasanEl = document.getElementById('nonaktifAlasan');
    const wrap     = document.getElementById('nonaktifJenisWrap');
    if (!alasanEl || !wrap) return;
    wrap.style.display = alasanEl.value === 'AKAN_DILEPAS' ? 'block' : 'none';
}
function openNonaktifModal(id) {
    nonaktifPendingId = id;
    const modal    = document.getElementById('nonaktifAsetModal');
    const alasanEl = document.getElementById('nonaktifAlasan');
    const catatan  = document.getElementById('nonaktifCatatan');
    const jenisEl  = document.getElementById('nonaktifJenisPelepasan');
    if (alasanEl) alasanEl.value = 'MENGANGGUR';
    if (catatan)  catatan.value  = '';
    if (jenisEl)  jenisEl.value  = '';
    toggleJenisPelepasanVisibility();
    if (modal)    modal.style.display = 'flex';
}
document.getElementById('nonaktifAlasan')?.addEventListener('change', toggleJenisPelepasanVisibility);
document.getElementById('nonaktifConfirmBtn')?.addEventListener('click', function () {
    if (nonaktifPendingId === null) return;
    const alasan  = document.getElementById('nonaktifAlasan').value;
    const catatan = document.getElementById('nonaktifCatatan').value;
    const jenis   = document.getElementById('nonaktifJenisPelepasan')?.value || '';
    if (alasan === 'AKAN_DILEPAS' && !jenis) {
        if (typeof showAlert === 'function') showAlert('Pilih jenis pelepasan terlebih dahulu.', 'error');
        else alert('Pilih jenis pelepasan terlebih dahulu.');
        return;
    }
    const id = nonaktifPendingId;
    nonaktifPendingId = null;
    if (typeof kirimToggle === 'function') {
        kirimToggle(id, { alasan_nonaktif: alasan, catatan_nonaktif: catatan, jenis_pelepasan: jenis });
    }
});
</script>
@endpush

<script>
/* Auto-save driven: menjaga isian form modal (Aset/Kegiatan) agar tidak hilang saat refresh atau crash. */
(function () {
    var PREFIX = 'mosque:autosave:';

    function keyFor(form) {
        return PREFIX + (form.getAttribute('data-autosave-key') || form.id || 'form');
    }

    function fieldNodes(form) {
        return Array.prototype.filter.call(
            form.querySelectorAll('input, select, textarea'),
            function (el) {
                var t = (el.type || '').toLowerCase();
                if (t === 'file' || t === 'submit' || t === 'button' || t === 'reset') return false;
                if (el.name === '_token' || el.name === '_method') return false;
                return true;
            }
        );
    }

    function nodeId(el, idx) {
        return el.id || el.name || ('field_' + idx);
    }

    function snapshot(form) {
        var data = {};
        fieldNodes(form).forEach(function (el, idx) {
            var id = nodeId(el, idx);
            var t = (el.type || '').toLowerCase();
            if (t === 'checkbox' || t === 'radio') {
                data[id] = { checked: el.checked };
            } else {
                data[id] = { value: el.value };
            }
        });
        return data;
    }

    function save(form) {
        try { localStorage.setItem(keyFor(form), JSON.stringify(snapshot(form))); } catch (e) {}
    }

    function restore(form) {
        var raw;
        try { raw = localStorage.getItem(keyFor(form)); } catch (e) { return; }
        if (!raw) return;
        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }
        fieldNodes(form).forEach(function (el, idx) {
            var id = nodeId(el, idx);
            var saved = data[id];
            if (!saved) return;
            var t = (el.type || '').toLowerCase();
            if (t === 'checkbox' || t === 'radio') {
                if (typeof saved.checked === 'boolean') el.checked = saved.checked;
            } else if (typeof saved.value !== 'undefined') {
                el.value = saved.value;
            }
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function clearKey(key) {
        try { localStorage.removeItem(key); } catch (e) {}
    }

    function bind(form) {
        if (form.__autosaveBound) return;
        form.__autosaveBound = true;
        var timer;
        form.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { save(form); }, 250);
        });
        form.addEventListener('change', function () { save(form); });
    }

    function attach(root) {
        if (!root || !root.querySelectorAll) return;
        Array.prototype.forEach.call(root.querySelectorAll('form[data-autosave-key]'), function (form) {
            restore(form);
            bind(form);
        });
    }

    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            Array.prototype.forEach.call(m.addedNodes, function (n) {
                if (n.nodeType !== 1) return;
                if (n.matches && n.matches('form[data-autosave-key]')) {
                    restore(n);
                    bind(n);
                } else {
                    attach(n);
                }
            });
            Array.prototype.forEach.call(m.removedNodes, function (n) {
                if (n.nodeType !== 1) return;
                var forms = (n.matches && n.matches('form[data-autosave-key]'))
                    ? [n]
                    : (n.querySelectorAll ? Array.prototype.slice.call(n.querySelectorAll('form[data-autosave-key]')) : []);
                forms.forEach(function (form) { clearKey(keyFor(form)); });
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
    document.addEventListener('DOMContentLoaded', function () { attach(document); });
    attach(document);
})();
</script>

@endsection