@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="p-6 space-y-4">

    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Review Transaksi</h1>
            @if(!empty($meta['periode']))
                <p class="text-sm text-gray-500 mt-0.5">
                    Periode {{ $meta['periode'] }}
                </p>
            @endif
        </div>
        <a href="{{ route('dashboard.transaksi.index') }}"
            class="h-9 px-4 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <div id="success-alert"
        class="hidden items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span id="success-alert-msg"></span>
    </div>

    <div id="error-alert"
        class="hidden items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span id="error-alert-msg"></span>
    </div>

    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p id="statTotalBaris" class="text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Total baris</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p id="statPerluKlasifikasi" class="text-3xl font-semibold text-green-700">{{ $stats['bersih'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Perlu diklasifikasi</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-3xl font-semibold text-amber-500">{{ $stats['duplikat'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Duplikat (dilewati)</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-3xl font-semibold text-rose-500">{{ $stats['tidak_sesuai'] }}</p>
            <p class="text-sm text-gray-500 mt-1">Tak sesuai jenis (dilewati)</p>
        </div>
    </div>

    @if(!empty($peringatanJenis))
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm text-rose-700">{{ $peringatanJenis }}</p>
    </div>
    @endif

    @if(!empty($warnings))
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="text-sm text-amber-700">
            <strong>Peringatan parser:</strong>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach($warnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div id="bulkActionBar"
            class="hidden px-5 py-3 bg-green-50 border-b border-green-100 items-center gap-3 flex-wrap">
            <span id="bulkCount" class="text-sm font-medium text-green-800">0 baris dipilih</span>
            <div class="flex items-center gap-2 flex-wrap ml-auto">
                <select id="bulkDebit"
                    class="h-8 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Akun debit...</option>
                    @foreach($akuns as $a)
                        <option value="{{ $a->id }}">{{ $a->kode_akun }} – {{ $a->nama_akun }}</option>
                    @endforeach
                </select>
                <select id="bulkKredit"
                    class="h-8 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500">
                    <option value="">Akun kredit...</option>
                    @foreach($akuns as $a)
                        <option value="{{ $a->id }}">{{ $a->kode_akun }} – {{ $a->nama_akun }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="applyBulk()"
                    class="h-8 px-3 bg-green-700 text-white text-xs font-medium rounded-lg hover:bg-green-800 transition-colors">
                    Terapkan
                </button>
                <p class="text-xs text-gray-400 w-full sm:w-auto">Bulk hanya mengisi entri debit/kredit pertama tiap baris.</p>
                <div class="w-px h-5 bg-gray-300"></div>
                <button type="button" onclick="bulkHapus()"
                    class="h-8 px-3 bg-red-50 text-red-600 border border-red-200 text-xs font-medium rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>

        <form id="formKlasifikasi">
            @csrf
            <input type="hidden" name="import_key" value="{{ $key }}">

            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:1000px">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 w-8">
                                <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)"
                                    class="rounded border-gray-300">
                            </th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500">No Ref</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500">Tanggal</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 text-right">Jumlah</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500">Keterangan</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 w-80">Klasifikasi Akun (Debit / Kredit)</th>
                            <th class="px-3 py-3 text-xs font-medium text-gray-500 w-8 text-center">...</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    @foreach($rows as $i => $row)
                        <tr @class([
                            'transition-colors',
                            'bg-amber-50/60 opacity-60' => $row['is_duplikat'],
                            'hover:bg-gray-50'          => !$row['is_duplikat'],
                        ])>
                            <td class="px-4 py-3">
                                @if(!$row['is_duplikat'])
                                    <input type="checkbox"
                                        class="rowCheck rounded border-gray-300"
                                        data-idx="{{ $i }}">
                                    <input type="hidden" name="klasifikasi[{{ $i }}][no_referensi]"
                                        value="{{ $row['no_referensi'] }}">
                                @else
                                    <svg class="w-4 h-4 text-amber-400 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-500 font-mono">
                                {{ Str::limit($row['no_referensi'], 14) }}
                                @if($row['is_duplikat'])
                                    <span class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-xs">Duplikat</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-600 whitespace-nowrap">
                                {{ isset($row['waktu_transaksi'])
                                    ? \Carbon\Carbon::parse($row['waktu_transaksi'])->translatedFormat('d M Y')
                                    : '-' }}
                            </td>
                            <td class="px-3 py-3 text-xs font-medium text-right whitespace-nowrap @if($row['jenis_transaksi']==='PENGELUARAN') text-red-600 @else text-green-700 @endif">
                                Rp {{ number_format($row['jumlah'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-600 max-w-xs">
                                <div class="line-clamp-2">{{ $row['deskripsi'] ?: '-' }}</div>
                                @if($row['nama_pengirim'])
                                    <div class="text-gray-400 mt-0.5">{{ $row['nama_pengirim'] }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if(!$row['is_duplikat'])
                                    <div id="entriesRow{{ $i }}" class="space-y-1.5"></div>
                                    <div class="flex items-center justify-between mt-1.5">
                                        <button type="button" onclick="tambahEntriReview({{ $i }})"
                                            class="text-xs text-green-700 hover:underline">
                                            + Tambah Akun
                                        </button>
                                        <span class="text-xs text-gray-400">
                                            Selisih: <span id="selisihRow{{ $i }}" class="font-medium text-gray-400">Rp 0</span>
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if(!$row['is_duplikat'])
                                    <button type="button"
                                        onclick="hapusRow(this)"
                                        class="text-gray-400 hover:text-red-500 transition-colors"
                                        title="Hapus baris ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div id="reviewErrorBox" class="hidden mx-5 my-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                <ul id="reviewErrorList" class="text-sm text-red-600 space-y-0.5 list-disc list-inside"></ul>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between">
        <span class="text-sm text-gray-500" data-entry-count>
            Showing 1 to {{ $stats['total'] }} of {{ $stats['total'] }} entries
        </span>
        <button type="button" onclick="simpanKlasifikasi()"
            id="btnSimpanKlasifikasi"
            class="h-9 px-5 bg-green-700 text-white text-sm font-medium rounded-xl hover:bg-green-800 transition-colors flex items-center gap-2">
            <svg id="spinnerKlasifikasi" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Simpan
        </button>
    </div>

</div>

@include('components.modal', [
    'id'    => 'modalHasilImport',
    'title' => 'Impor Transaksi',
    'slot'  => view('pages.operasional.transaksi.import-result'),
])

<script>
const AKUN_LIST_REVIEW = @json($akuns->map(fn($a) => ['id' => $a->id, 'label' => $a->kode_akun . ' – ' . $a->nama_akun]));
const ROWS_DATA = @json($rows);

// ── Modal ──────────────────────────────────────────────────────
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

// ── Klasifikasi akun dinamis per baris ───────────────────────────

function opsiAkunReview(selected = '') {
    let html = '<option value="">Pilih akun</option>';
    AKUN_LIST_REVIEW.forEach(a => {
        html += `<option value="${a.id}" ${String(a.id) === String(selected) ? 'selected' : ''}>${a.label}</option>`;
    });
    return html;
}

function buatBarisEntriReview(rowIndex, tipe = 'DEBIT', akunId = '', nominal = '') {
    const container = document.getElementById(`entriesRow${rowIndex}`);
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'flex items-center gap-1';
    div.innerHTML = `
        <select class="entriAkun flex-1 h-7 px-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500">
            ${opsiAkunReview(akunId)}
        </select>
        <select class="entriTipe w-16 h-7 px-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500"
            onchange="hitungSelisihReview(${rowIndex})">
            <option value="DEBIT" ${tipe === 'DEBIT' ? 'selected' : ''}>Debit</option>
            <option value="KREDIT" ${tipe === 'KREDIT' ? 'selected' : ''}>Kredit</option>
        </select>
        <input type="number" min="1" value="${nominal}"
            class="entriNominal w-24 h-7 px-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500"
            oninput="hitungSelisihReview(${rowIndex})">
        <button type="button" onclick="hapusEntriReview(${rowIndex}, this)" class="text-gray-300 hover:text-red-500 flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(div);
    hitungSelisihReview(rowIndex);
}

function tambahEntriReview(rowIndex) {
    buatBarisEntriReview(rowIndex, 'DEBIT', '', '');
}

function hapusEntriReview(rowIndex, btn) {
    const container = document.getElementById(`entriesRow${rowIndex}`);
    if (container.children.length <= 2) {
        alert('Setiap baris minimal harus memiliki 1 akun debit dan 1 akun kredit.');
        return;
    }
    btn.closest('div').remove();
    hitungSelisihReview(rowIndex);
}

function hitungSelisihReview(rowIndex) {
    const container = document.getElementById(`entriesRow${rowIndex}`);
    if (!container) return { debit: 0, kredit: 0 };

    let debit = 0, kredit = 0;
    container.querySelectorAll(':scope > div').forEach(div => {
        const tipe    = div.querySelector('.entriTipe').value;
        const nominal = parseFloat(div.querySelector('.entriNominal').value) || 0;
        if (tipe === 'DEBIT') debit += nominal; else kredit += nominal;
    });

    const el = document.getElementById(`selisihRow${rowIndex}`);
    const selisih = debit - kredit;
    if (el) {
        if (selisih === 0 && debit > 0) {
            el.textContent = 'Balance ✓';
            el.className = 'font-medium text-green-600';
        } else {
            el.textContent = 'Rp ' + Math.abs(selisih).toLocaleString('id-ID') + ' tidak balance';
            el.className = 'font-medium text-red-500';
        }
    }
    return { debit, kredit };
}

document.addEventListener('DOMContentLoaded', function () {
    ROWS_DATA.forEach((row, i) => {
        if (row.is_duplikat) return;
        // Default: 1 baris debit + 1 baris kredit, nominal otomatis = nominal mutasi
        buatBarisEntriReview(i, 'DEBIT',  '', row.jumlah);
        buatBarisEntriReview(i, 'KREDIT', '', row.jumlah);
    });
});

// ── Checkbox & Bulk Action ──────────────────────────────────────

function toggleCheckAll(master) {
    document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.rowCheck:checked').length;
    const bar     = document.getElementById('bulkActionBar');
    const count   = document.getElementById('bulkCount');

    if (checked > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        count.textContent = checked + ' baris dipilih';
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }

    const total = document.querySelectorAll('.rowCheck').length;
    document.getElementById('checkAll').checked = total > 0 && total === checked;
}

document.querySelectorAll('.rowCheck').forEach(cb => {
    cb.addEventListener('change', () => updateBulkBar());
});

function applyBulk() {
    const debitVal  = document.getElementById('bulkDebit').value;
    const kreditVal = document.getElementById('bulkKredit').value;

    if (!debitVal && !kreditVal) {
        alert('Pilih minimal akun debit atau akun kredit untuk diterapkan.');
        return;
    }

    document.querySelectorAll('.rowCheck:checked').forEach(cb => {
        const idx = cb.dataset.idx;
        const container = document.getElementById(`entriesRow${idx}`);
        if (!container) return;

        const divs = [...container.querySelectorAll(':scope > div')];

        if (debitVal) {
            const debitDiv = divs.find(d => d.querySelector('.entriTipe').value === 'DEBIT');
            if (debitDiv) debitDiv.querySelector('.entriAkun').value = debitVal;
        }
        if (kreditVal) {
            const kreditDiv = divs.find(d => d.querySelector('.entriTipe').value === 'KREDIT');
            if (kreditDiv) kreditDiv.querySelector('.entriAkun').value = kreditVal;
        }
    });

    document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = false);
    document.getElementById('checkAll').checked = false;
    document.getElementById('bulkDebit').value  = '';
    document.getElementById('bulkKredit').value = '';

    updateBulkBar();
}

async function bulkHapus() {
    const checked = document.querySelectorAll('.rowCheck:checked');
    if (checked.length === 0) return;
    if (!await confirmAsync(`Hapus ${checked.length} baris yang dipilih?`, { confirmLabel: 'Hapus' })) return;

    checked.forEach(cb => cb.closest('tr').remove());
    updateBulkBar();
    updateEntryCount();
}

function hapusRow(btn) {
    btn.closest('tr').remove();
    updateBulkBar();
    updateEntryCount();
}

function updateEntryCount() {
    const totalRows  = document.querySelectorAll('tbody tr').length;
    const aktifRows  = document.querySelectorAll('tbody tr:not(.bg-amber-50\\/60)').length;

    const countEl = document.querySelector('[data-entry-count]');
    if (countEl) countEl.textContent = `Showing 1 to ${totalRows} of ${totalRows} entries`;

    const totalEl = document.getElementById('statTotalBaris');
    if (totalEl) totalEl.textContent = totalRows;

    const perluEl = document.getElementById('statPerluKlasifikasi');
    if (perluEl) perluEl.textContent = aktifRows;
}

// ── Simpan ──────────────────────────────────────────────────────

async function simpanKlasifikasi() {
    const btn     = document.getElementById('btnSimpanKlasifikasi');
    const spinner = document.getElementById('spinnerKlasifikasi');

    if (btn.disabled) return;
    hideAlert('success');
    hideAlert('error');

    let valid = true;
    const klasifikasi = [];

    document.querySelectorAll('tbody tr').forEach(row => {
        const noRefInput = row.querySelector('input[name^="klasifikasi"][name$="[no_referensi]"]');
        if (!noRefInput) {
            showAlert('error', 'Tidak dapat menemukan input no_referensi pada baris.');
            return; // baris duplikat, dilewati
        }

        const idxMatch = noRefInput.name.match(/klasifikasi\[(\d+)\]/);
        const idx = idxMatch ? idxMatch[1] : null;
        const container = document.getElementById(`entriesRow${idx}`);

        const entries = [];
        let totalDebit = 0, totalKredit = 0;

        container?.querySelectorAll(':scope > div').forEach(div => {
            const akunSelect = div.querySelector('.entriAkun');
            const tipe        = div.querySelector('.entriTipe').value;
            const nominal     = parseFloat(div.querySelector('.entriNominal').value) || 0;

            if (!akunSelect.value || nominal <= 0) {
                valid = false;
                akunSelect.classList.add('border-red-400');
                return;
            }
            akunSelect.classList.remove('border-red-400');

            entries.push({ akun_id: akunSelect.value, tipe, nominal });
            if (tipe === 'DEBIT') totalDebit += nominal; else totalKredit += nominal;
        });

        if (Math.abs(totalDebit - totalKredit) > 0.5) {
            valid = false;
        }

        klasifikasi.push({
            no_referensi: noRefInput.value,
            entries: entries,
        });
    });

    if (!valid) {
        showAlert('error', 'Pastikan setiap baris memiliki akun yang valid dan total debit = total kredit.');
        return;
    }

    btn.disabled = true;
    spinner.classList.remove('hidden');

    try {
        const fd = new FormData();
        fd.append('import_key', '{{ $key }}');
        klasifikasi.forEach((row, i) => {
            fd.append(`klasifikasi[${i}][no_referensi]`, row.no_referensi);
            row.entries.forEach((e, j) => {
                fd.append(`klasifikasi[${i}][entries][${j}][akun_id]`, e.akun_id);
                fd.append(`klasifikasi[${i}][entries][${j}][tipe]`, e.tipe);
                fd.append(`klasifikasi[${i}][entries][${j}][nominal]`, e.nominal);
            });
        });

        const res = await fetch('{{ route("dashboard.transaksi.import.simpan") }}', {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });

        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Non-JSON response:', text);
            showAlert('error', 'Server mengembalikan response tidak valid. Cek console untuk detail.');
            btn.disabled = false;
            spinner.classList.add('hidden');
            return;
        }

        const data = await res.json();

        if (data.success && data.type === 'import_success') {
            const setEl = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val ?? '';
            };
            setEl('hasilTersimpan',    data.tersimpan);
            setEl('hasilTersimpanBox', data.tersimpan);
            setEl('hasilDuplikat',     data.duplikat);
            setEl('hasilTotal',        data.total);
            setEl('hasilPeriode',      (data.periode ?? '') + ' ' + (data.pesan_tambahan ?? ''));
            openModal('modalHasilImport');
        } else {
            let pesan = data.message ?? 'Terjadi kesalahan.';
            if (res.status === 422 && data.errors) {
                pesan = Object.values(data.errors).flat().join(' ');
            }
            showAlert('error', pesan);
            btn.disabled = false;
        }
    } catch (e) {
        console.error('Fetch error:', e);
        showAlert('error', 'Gagal menghubungi server. Coba lagi.');
        btn.disabled = false;
    } finally {
        spinner.classList.add('hidden');
    }
}

function showAlert(type, message) {
    const el  = document.getElementById(type + '-alert');
    const msg = document.getElementById(type + '-alert-msg');
    if (!el || !msg) return;
    msg.textContent = message;
    el.classList.remove('hidden');
    el.classList.add('flex');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    setTimeout(() => {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }, 4000);
}

function hideAlert(type) {
    const el = document.getElementById(type + '-alert');
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
}
</script>
@endsection