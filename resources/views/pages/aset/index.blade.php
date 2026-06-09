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
        <button onclick="openCreateModal()"
           class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
            + Tambah Aset
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Aset</p>
            <p id="stat-total" class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Aset Aktif</p>
            <p id="stat-aktif" class="text-2xl font-bold text-green-600">{{ $stats['aktif'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-5 py-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Aset Non-Aktif</p>
            <p id="stat-tidak-aktif" class="text-2xl font-bold text-red-500">{{ $stats['tidak_aktif'] }}</p>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
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

                {{-- Filter button + panel --}}
                <div class="relative" id="filterWrapper">
                    <button onclick="toggleFilterPanel()"
                        class="flex items-center gap-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-green-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filter
                        <span id="filterBadge"
                            class="hidden items-center justify-center w-4 h-4 text-xs font-bold text-white bg-green-600 rounded-full">
                        </span>
                    </button>

                    {{-- Panel --}}
                    <div id="filterPanel"
                        class="hidden absolute right-0 top-10 z-30 w-72 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-4 space-y-4">

                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filter</p>
                            <button onclick="resetFilters()" class="text-xs text-green-600 hover:underline">Reset</button>
                        </div>

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

            </div>
        </div>
        <div id="tableWrapper">
            @include('pages.aset.table')
        </div>
    </div>

</div>

{{-- Modal Container --}}
<div id="modalContainer"></div>

{{-- Toast --}}
<div id="toast" class="hidden fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium"></div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.js"></script>
<script>
const modalContainer = document.getElementById('modalContainer');
const csrfToken      = document.querySelector('meta[name="csrf-token"]').content;
const baseUrl        = "{{ url('dashboard/aset') }}";

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

// ── Filter panel ──────────────────────────────────────────────
function toggleFilterPanel() {
    document.getElementById('filterPanel').classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('filterWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('filterPanel').classList.add('hidden');
    }
});

function resetFilters() {
    ['filterTahun','filterLokasi','filterSumber','filterStatus','filterKondisi'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('filterSearch').value = '';
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
            showToast('Gagal memuat data.', 'error');
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
                    const d = selectedDates[0];
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

// ── Toast ─────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium ${
        type === 'success'
            ? 'bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400'
            : 'bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400'
    }`;
    toast.innerHTML = msg;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

// ── AJAX Filter ───────────────────────────────────────────────
const filterUrl = "{{ route('dashboard.aset.index') }}";
let filterDebounce;

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
function submitAsetForm(formId, method, url) {
    const form = document.getElementById(formId);
    form.querySelectorAll('[id^="err-"]').forEach(el => el.textContent = '');
    form.querySelectorAll('input,select,textarea').forEach(el => el.classList.remove('border-red-400'));

    const data = new FormData(form);
    if (method === 'PUT') data.append('_method', 'PUT');

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
            showToast(res.message, 'success');
            applyFilters();
            fetchStats();
        } else if (res.errors) {
            Object.entries(res.errors).forEach(([field, messages]) => {
                const el    = form.querySelector(`[name="${field}"]`);
                const errEl = document.getElementById(`err-${field}`);
                if (el)    el.classList.add('border-red-400');
                if (errEl) errEl.textContent = messages[0];
            });
        }
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'));
}
</script>
@endpush
@endsection