@extends('layouts.app')
@section('title', 'Jurnal Pembuka')
@section('content')
<div class="space-y-4 p-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Tambah Jurnal Pembuka</h1>
        @if($periodeAktif)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Periode aktif: {{ $periodeAktif->nama_periode }}
            </p>
        @endif
    </div>

    {{-- ── Stepper ─────────────────────────────────────────────────────────── --}}
    <x-jurnal.stepper :steps="['Informasi Umum', 'Entri Saldo Awal', 'Review & Simpan']"/>

    {{-- ── Form ───────────────────────────────────────────────────────────── --}}
    <form id="formJurnalPembuka"
          action="{{ route('dashboard.jurnal-pembuka.store') }}"
          method="POST">
        @csrf

        {{-- Validasi balance dari server --}}
        @error('balance')
            <x-jurnal.alert type="error" :message="$message"/>
        @enderror

        {{-- STEP 1: Informasi Umum --}}
        <div id="step1" class="space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
                    </div>
                    <span class="text-xs text-gray-400">Lengkapi data jurnal pembuka</span>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nama Periode <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_periode" id="inputNamaPeriode"
                            value="{{ old('nama_periode') }}"
                            placeholder="Contoh: Periode 2024"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                    focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500
                                    placeholder-gray-400">
                        @error('nama_periode')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Mulai Periode <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="inputTanggalMulai"
                            value="{{ old('tanggal_mulai') }}"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                    focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        @error('tanggal_mulai')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Akhir Periode <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_akhir" id="inputTanggalAkhir"
                            value="{{ old('tanggal_akhir') }}"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white
                                    focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        @error('tanggal_akhir')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Catatan
                        </label>
                        <input type="text" name="keterangan"
                            value="{{ old('keterangan') }}"
                            placeholder="Opsional — catatan tambahan"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl
                                    bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                                    focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500
                                    placeholder-gray-400">
                    </div>
                </div>
            </div>

            <div id="alertContainer"></div>
            <x-jurnal.form-footer :step="1" :total="3" :nextAction="'goToStep(2)'"/>
        </div>

        {{-- STEP 2: Entri Saldo Awal --}}
        <div id="step2" class="hidden space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Entri Saldo Awal</h2>
                    </div>
                    <span class="text-xs text-gray-400">Masukkan saldo awal per akun</span>
                </div>

                {{-- Banner peringatan --}}
                <div class="mb-4 flex items-start gap-2.5 px-4 py-3 bg-amber-50 dark:bg-amber-900/20
                            border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-700
                            dark:text-amber-400">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3
                               L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Total Debit harus sama dengan total Kredit sebelum dapat melanjutkan ke langkah berikutnya.
                    Perbedaan menunjukkan adanya entri yang belum seimbang.
                </div>

                {{-- Tabel entri --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="tabelEntri">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 w-8">No</th>
                                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500">Akun</th>
                                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 w-28">Posisi</th>
                                <th class="px-3 py-2.5 text-right text-xs font-medium text-gray-500 w-36">Debit (Rp)</th>
                                <th class="px-3 py-2.5 text-right text-xs font-medium text-gray-500 w-36">Kredit (Rp)</th>
                                <th class="px-3 py-2.5 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="bodyEntri">
                            {{-- Diisi awal dengan 2 baris default --}}
                        </tbody>
                    </table>
                </div>

                {{-- Tambah baris --}}
                <button type="button" onclick="tambahBaris()"
                    class="mt-3 w-full h-10 border border-dashed border-green-300 dark:border-green-800
                           text-green-700 dark:text-green-400 text-sm font-medium rounded-xl
                           hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Baris
                </button>

                {{-- Balance bar --}}
                <x-jurnal.balance-bar prefix="create_"/>
            </div>

            <div id="alertContainerStep2"></div>
            <x-jurnal.form-footer :step="2" :total="3" :nextAction="'validasiStep2()'"/>
        </div>

        {{-- STEP 3: Review & Simpan --}}
        <div id="step3" class="hidden space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center gap-2 mb-5">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                               M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Review Jurnal</h2>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    {{-- Kiri: Informasi Umum --}}
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">
                            Informasi Umum
                        </p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800">
                                <span class="text-gray-500">Nama Periode</span>
                                <span id="reviewNamaPeriode" class="font-medium text-gray-800 dark:text-gray-200">—</span>
                            </div>
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800">
                                <span class="text-gray-500">Tanggal Mulai</span>
                                <span id="reviewTanggalMulai" class="font-medium text-gray-800 dark:text-gray-200">—</span>
                            </div>
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800">
                                <span class="text-gray-500">Tanggal Akhir</span>
                                <span id="reviewTanggalAkhir" class="font-medium text-gray-800 dark:text-gray-200">—</span>
                            </div>
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800">
                                <span class="text-gray-500">Status</span>
                                <span id="reviewStatus" class="font-medium text-gray-800 dark:text-gray-200">Seimbang</span>
                            </div>
                            <div class="flex justify-between text-sm py-1.5">
                                <span class="text-gray-500">Catatan</span>
                                <span id="reviewKeterangan" class="font-medium text-gray-800 dark:text-gray-200">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- Kanan: Ringkasan jurnal --}}
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">
                            Ringkasan Jurnal
                        </p>
                        <x-jurnal.review-table bodyId="reviewBody"
                                        totalDebitId="review_total_debit"
                                        totalKreditId="review_total_kredit"/>
                    </div>
                </div>

                {{-- Keterangan status --}}
                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">
                        Keterangan Status
                    </p>
                    <div class="flex items-center gap-6 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                         bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                Posted
                            </span>
                            <span>: Jurnal sudah disimpan dan diposting ke buku besar</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                         bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                Draft
                            </span>
                            <span>: Jurnal disimpan sementara, belum diposting</span>
                        </div>
                    </div>
                </div>
            </div>

            <x-jurnal.form-footer :step="3" :total="3" :showSubmit="true"/>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
const AKUN_OPTIONS = @json($akuns);
let barisCount = 0;
let stepSaat   = 1;

document.addEventListener('DOMContentLoaded', () => {
    tambahBaris();
    tambahBaris();
    updateBalanceBar();

    @if($errors->any())
        goToStep(2);
    @endif
});

// ─── Navigasi step ────────────────────────────────────────────────────────────
function goToStep(n) {
    if (n === 2 && !validasiStep1()) return;

    document.querySelectorAll('[id^=step]').forEach(el => el.classList.add('hidden'));
    document.getElementById('step' + n)?.classList.remove('hidden');
    stepSaat = n;

    for (let i = 1; i <= 3; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const line   = document.getElementById('step-line-' + i);
        if (!circle) continue;

        if (i < n) {
            circle.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`;
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
        } else if (i === n) {
            circle.textContent = i;
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
        } else {
            circle.textContent = i;
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors';
        }

        if (line) {
            line.className = i < n
                ? 'mx-4 flex-1 border-t-2 border-green-500 transition-colors'
                : 'mx-4 flex-1 border-t-2 border-gray-100 dark:border-gray-800 transition-colors';
        }
    }

    if (n === 3) isiReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── Validasi step 1 ──────────────────────────────────────────────────────────
function validasiStep1() {
    const nama  = document.querySelector('[name=nama_periode]')?.value?.trim();
    const mulai = document.querySelector('[name=tanggal_mulai]')?.value;
    const akhir = document.querySelector('[name=tanggal_akhir]')?.value;

    if (!nama) { alert('Nama periode wajib diisi.'); return false; }
    if (!mulai) { alert('Tanggal mulai wajib diisi.'); return false; }
    if (!akhir) { alert('Tanggal akhir wajib diisi.'); return false; }
    if (akhir < mulai) { showAlert('error', 'Tanggal akhir tidak boleh sebelum tanggal mulai.', 'alertContainer'); return false; }

    hideAlert('alertContainer');
    return true;
}

// ─── Validasi step 2 ──────────────────────────────────────────────────────────
function validasiStep2() {
    const { debit, kredit } = hitungTotal();
    if (Math.round(debit * 100) !== Math.round(kredit * 100)) {
        showAlert('error', 'Total Debit dan Kredit belum seimbang. Periksa kembali entri Anda.', 'alertContainerStep2');
        return;
    }
    hideAlert('alertContainerStep2');
    goToStep(3);
}

// ─── Tambah baris ─────────────────────────────────────────────────────────────
function tambahBaris() {
    barisCount++;
    const idx  = barisCount;
    const opts = AKUN_OPTIONS.map(a =>
        `<option value="${a.id}">${a.kode} — ${a.nama}</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.id = `baris-${idx}`;
    tr.className = 'border-b border-gray-50 dark:border-gray-800';
    tr.innerHTML = `
        <td class="px-3 py-2.5 text-gray-400 text-xs">${idx}</td>
        <td class="px-3 py-2.5">
            <select name="detail[${idx}][akun_id]" required
                class="w-full h-9 px-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg
                       bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                       focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
                onchange="onAkunChange(this, ${idx})">
                <option value="">Pilih akun</option>
                ${opts}
            </select>
        </td>
        <td class="px-3 py-2.5">
            <select name="detail[${idx}][tipe]" required
                class="w-full h-9 px-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg
                       bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                       focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
                onchange="onTipeChange(${idx})">
                <option value="DEBIT">Debit</option>
                <option value="KREDIT">Kredit</option>
            </select>
        </td>
        <td class="px-3 py-2.5">
            <input type="number" name="detail[${idx}][nominal_debit]" id="debit-${idx}"
                placeholder="0" min="0" step="1"
                class="w-full h-9 px-2 text-sm text-right border border-gray-200 dark:border-gray-700 rounded-lg
                       bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                       focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
                oninput="updateNominal(${idx})">
        </td>
        <td class="px-3 py-2.5">
            <input type="number" name="detail[${idx}][nominal_kredit]" id="kredit-${idx}"
                placeholder="0" min="0" step="1"
                class="w-full h-9 px-2 text-sm text-right border border-gray-200 dark:border-gray-700 rounded-lg
                       bg-gray-50 dark:bg-gray-900 text-gray-400"
                disabled
                oninput="updateNominal(${idx})">
        </td>
        <td class="px-3 py-2.5 text-center">
            <button type="button" onclick="hapusBaris(${idx})"
                class="w-7 h-7 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-900/20
                       flex items-center justify-center text-red-500 hover:bg-red-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858
                           L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>
        <input type="hidden" name="detail[${idx}][nominal]" id="nominal-${idx}" value="0">
    `;

    document.getElementById('bodyEntri').appendChild(tr);
    onTipeChange(idx);
    updateNomorBaris();
}

// ─── Hapus baris ──────────────────────────────────────────────────────────────
function hapusBaris(idx) {
    if (document.querySelectorAll('#bodyEntri tr').length <= 2) {
        showAlert('error', 'Minimal 2 baris entri harus ada.', 'alertContainerStep2');
        return;
    }
    document.getElementById('baris-' + idx)?.remove();
    updateNomorBaris();
    updateBalanceBar();
}

// ─── Update nomor baris ───────────────────────────────────────────────────────
function updateNomorBaris() {
    document.querySelectorAll('#bodyEntri tr').forEach((tr, i) => {
        tr.querySelector('td:first-child').textContent = i + 1;
    });
}

// ─── Update nominal (satu fungsi, tanpa duplikat) ─────────────────────────────
function updateNominal(idx) {
    const tipe = document.querySelector(`[name="detail[${idx}][tipe]"]`).value;
    const dVal = parseFloat(document.getElementById('debit-'  + idx)?.value) || 0;
    const kVal = parseFloat(document.getElementById('kredit-' + idx)?.value) || 0;
    const nom  = tipe === 'DEBIT' ? dVal : kVal;
    document.getElementById('nominal-' + idx).value = nom;
    updateBalanceBar();
}

// ─── Saat akun dipilih ────────────────────────────────────────────────────────
function onAkunChange(sel, idx) {
    const akun = AKUN_OPTIONS.find(a => a.id == sel.value);
    if (!akun) return;
    const tipeSelect = document.querySelector(`[name="detail[${idx}][tipe]"]`);
    if (akun.saldo_normal) {
        tipeSelect.value = akun.saldo_normal;
        onTipeChange(idx);
    }
}

// ─── Saat posisi berubah ──────────────────────────────────────────────────────
function onTipeChange(idx) {
    const tipe   = document.querySelector(`[name="detail[${idx}][tipe]"]`).value;
    const dInput = document.getElementById('debit-'  + idx);
    const kInput = document.getElementById('kredit-' + idx);

    if (tipe === 'DEBIT') {
        dInput.disabled = false;
        dInput.classList.remove('bg-gray-50', 'dark:bg-gray-900', 'text-gray-400');
        kInput.disabled = true;
        kInput.value    = '';
        kInput.classList.add('bg-gray-50', 'dark:bg-gray-900', 'text-gray-400');
    } else {
        kInput.disabled = false;
        kInput.classList.remove('bg-gray-50', 'dark:bg-gray-900', 'text-gray-400');
        dInput.disabled = true;
        dInput.value    = '';
        dInput.classList.add('bg-gray-50', 'dark:bg-gray-900', 'text-gray-400');
    }

    document.getElementById('nominal-' + idx).value = 0;
    updateBalanceBar();
}

// ─── Hitung total ─────────────────────────────────────────────────────────────
function hitungTotal() {
    let debit = 0, kredit = 0;
    document.querySelectorAll('#bodyEntri tr').forEach(tr => {
        const tipeEl = tr.querySelector('select[name*="[tipe]"]');
        const nomEl  = tr.querySelector('input[type="hidden"][name*="[nominal]"]');
        if (!tipeEl || !nomEl) return;
        const nom = parseFloat(nomEl.value) || 0;
        if (tipeEl.value === 'DEBIT')  debit  += nom;
        if (tipeEl.value === 'KREDIT') kredit += nom;
    });
    return { debit, kredit };
}

// ─── Balance bar ──────────────────────────────────────────────────────────────
function updateBalanceBar() {
    const { debit, kredit } = hitungTotal();
    const fmtRp    = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    const seimbang = Math.round(debit * 100) === Math.round(kredit * 100);

    document.getElementById('create_TotalDebit').textContent  = fmtRp(debit);
    document.getElementById('create_TotalKredit').textContent = fmtRp(kredit);

    const statusEl = document.getElementById('create_BalanceStatus');
    if (seimbang && debit > 0) {
        statusEl.className = 'flex items-center gap-1.5 text-xs font-medium text-green-600';
        statusEl.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Seimbang`;
    } else {
        statusEl.className = 'flex items-center gap-1.5 text-xs font-medium text-amber-600';
        statusEl.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3
                   L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Belum seimbang`;
    }
}

// ─── Isi review ───────────────────────────────────────────────────────────────
function isiReview() {
    const nama  = document.querySelector('[name=nama_periode]')?.value  || '—';
    const mulai = document.querySelector('[name=tanggal_mulai]')?.value || '';
    const akhir = document.querySelector('[name=tanggal_akhir]')?.value || '';
    const ket   = document.querySelector('[name=keterangan]')?.value    || '—';

    document.getElementById('reviewNamaPeriode').textContent  = nama;
    document.getElementById('reviewTanggalMulai').textContent = mulai
        ? new Date(mulai).toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' }) : '—';
    document.getElementById('reviewTanggalAkhir').textContent = akhir
        ? new Date(akhir).toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' }) : '—';
    document.getElementById('reviewKeterangan').textContent   = ket;

    const { debit, kredit } = hitungTotal();
    const seimbang = Math.round(debit * 100) === Math.round(kredit * 100);
    document.getElementById('reviewStatus').innerHTML = seimbang
        ? '<span class="text-green-600 font-medium">Seimbang ✓</span>'
        : '<span class="text-red-500 font-medium">Belum seimbang ✗</span>';

    const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    const tbody = document.getElementById('reviewBody');
    tbody.innerHTML = '';

    document.querySelectorAll('#bodyEntri tr').forEach(tr => {
        const akunSel = tr.querySelector('select[name*="[akun_id]"]');
        const tipeSel = tr.querySelector('select[name*="[tipe]"]');
        if (!akunSel?.value) return;

        const akun = AKUN_OPTIONS.find(a => a.id == akunSel.value);
        const tipe = tipeSel?.value;
        const nom = parseFloat(tr.querySelector('input[type="hidden"][name*="[nominal]"]')?.value) || 0;

        const row = document.createElement('tr');
        row.className = 'border-b border-gray-50 dark:border-gray-800';
        row.innerHTML = `
            <td class="py-2 text-sm text-gray-700 dark:text-gray-300">${akun?.nama ?? '—'}</td>
            <td class="py-2 text-right text-sm ${tipe === 'DEBIT' ? 'text-red-600' : 'text-gray-300'}">${tipe === 'DEBIT' ? fmtRp(nom) : '—'}</td>
            <td class="py-2 text-right text-sm ${tipe === 'KREDIT' ? 'text-green-700' : 'text-gray-300'}">${tipe === 'KREDIT' ? fmtRp(nom) : '—'}</td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('review_total_debit').textContent  = fmtRp(debit);
    document.getElementById('review_total_kredit').textContent = fmtRp(kredit);
}

// ─── Submit ───────────────────────────────────────────────────────────────────
let _submitType = null;

// Tangkap klik dari tombol submit sebelum form submit
document.addEventListener('click', function (e) {
    const btn = e.target.closest('button[type="submit"][name="submit_type"]');
    if (btn) _submitType = btn.value;
});

document.getElementById('formJurnalPembuka').addEventListener('submit', function (e) {
    e.preventDefault();

    if (!_submitType || !['draft', 'posting'].includes(_submitType)) {
        console.warn('submitType tidak valid');
        return;
    }

    const { debit, kredit } = hitungTotal();

    if (_submitType === 'posting' && Math.round(debit * 100) !== Math.round(kredit * 100)) {
        showAlert('error', 'Tidak dapat posting — total Debit dan Kredit belum seimbang.', 'alertContainerStep2');
        goToStep(2);
        _submitType = null;
        return;
    }

    // Inject hidden submit_type
    let hiddenInput = document.getElementById('_submit_type');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id   = '_submit_type';
        hiddenInput.name = 'submit_type';
        this.appendChild(hiddenInput);
    }
    hiddenInput.value = _submitType;

    // Enable disabled inputs sebelum submit
    document.querySelectorAll('#bodyEntri input:disabled').forEach(el => {
        el.disabled = false;
    });

    _submitType = null;
    this.submit();
});

// ─── Helper show alert ────────────────────────────────────────────────────────
function showAlert(type, message, containerId = 'alertContainer') {
    const container = document.getElementById(containerId);
    if (!container) return;

    const color = type === 'error'
        ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400'
        : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400';

    const icon = type === 'error'
        ? '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>'
        : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>';

    container.innerHTML = `
        <div class="flex items-center gap-3 ${color} border rounded-xl px-4 py-3 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                ${icon}
            </svg>
            ${message}
        </div>
    `;

    // Auto hide setelah 5 detik
    setTimeout(() => container.innerHTML = '', 5000);
}

function hideAlert(containerId = 'alertContainer') {
    const container = document.getElementById(containerId);
    if (container) container.innerHTML = '';
}
</script>
@endpush