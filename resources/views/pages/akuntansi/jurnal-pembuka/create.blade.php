@extends('layouts.app')
@section('title', 'Jurnal Pembuka')
@section('content')
@php
    $canCreateJurnalPembuka = auth()->user()->hasPermission('CREATE_JURNAL_PEMBUKA');
@endphp
<div class="space-y-4 p-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Jurnal Pembuka</h1>
        <p>Entri saldo awal untuk periode baru</p>
    </div>

    <x-jurnal.stepper :steps="['Informasi Umum', 'Entri Saldo Awal', 'Review & Simpan']"/>

    <form id="formJurnalPembuka" action="{{ route('dashboard.jurnal-pembuka.store') }}" method="POST">
        @csrf

        @error('balance') <x-jurnal.alert type="error" :message="$message"/> @enderror

        @if (session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400">
                <p class="font-medium mb-1">Periksa kembali isian berikut:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->keys() as $key)
                        <li><span class="font-mono text-xs text-red-800 dark:text-red-300">{{ $key }}</span> - {{ $errors->first($key) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- STEP 1: Informasi Umum --}}
        <div id="step1" class="space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
                    <span class="text-xs text-gray-400">Tentukan tanggal awal periode</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Periode <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_periode" id="inputNamaPeriode" required value="{{ old('nama_periode') }}"
                            placeholder="Contoh: Juli 2026"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        <p class="text-xs text-gray-400 mt-1">Terisi otomatis, boleh diubah manual.</p>
                        @error('nama_periode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Awal Saldo <span class="text-red-500">*</span></label>
                        <input type="text" name="tanggal_awal" id="inputTanggalAwal" required value="{{ old('tanggal_awal') }}"
                            autocomplete="off" placeholder="Pilih tanggal"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        <p class="text-xs text-gray-400 mt-1">Tanggal awal pencatatan akuntansi.</p>
                        <p class="text-xs text-amber-600 dark:text-amber-500 mt-1">
                            Catatan: Periode berikutnya akan otomatis dimulai dari tanggal 1 setiap bulan saat dibuka lewat Jurnal Penutup.
                        </p>
                        @error('tanggal_awal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tanggal akhir: otomatis akhir bulan dari tanggal awal --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Akhir Periode</label>
                        <input type="text" id="previewTanggalAkhir" readonly placeholder="-"
                            class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-500">
                        <input type="hidden" name="tanggal_akhir" id="inputTanggalAkhirHidden">
                        <p class="text-xs text-gray-400 mt-1">Otomatis ke akhir bulan dari tanggal awal.</p>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Opsional - catatan tambahan" class="w-full h-10 px-3 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
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
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Entri Saldo Awal</h2>
                    <span class="text-xs text-gray-400">Masukkan saldo awal per akun</span>
                </div>

                <div class="mb-4 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-700 dark:text-amber-400">
                    Draft boleh disimpan meski belum seimbang, tetapi untuk <b>Posting</b> total Debit harus sama dengan total Kredit.
                </div>

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
                        <tbody id="bodyEntri"></tbody>
                    </table>
                </div>

                <button type="button" onclick="tambahBaris()" class="mt-3 w-full h-10 border border-dashed border-green-300 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">+ Tambah Baris</button>

                <x-jurnal.balance-bar prefix="create_"/>
            </div>

            <div id="alertContainerStep2"></div>
            <x-jurnal.form-footer :step="2" :total="3" :nextAction="'validasiStep2()'"/>
        </div>

        {{-- STEP 3: Review --}}
        <div id="step3" class="hidden space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Review Jurnal</h2>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Informasi Umum</p>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800"><span class="text-gray-500">Periode</span><span id="reviewTipePeriode" class="font-medium text-gray-800 dark:text-gray-200">-</span></div>
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800"><span class="text-gray-500">Tanggal Awal</span><span id="reviewTanggalAwal" class="font-medium text-gray-800 dark:text-gray-200">-</span></div>
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800"><span class="text-gray-500">Tanggal Akhir</span><span id="reviewTanggalAkhir" class="font-medium text-gray-800 dark:text-gray-200">-</span></div>
                            <div class="flex justify-between text-sm py-1.5 border-b border-gray-50 dark:border-gray-800"><span class="text-gray-500">Keseimbangan</span><span id="reviewStatus" class="font-medium text-gray-800 dark:text-gray-200">-</span></div>
                            <div class="flex justify-between text-sm py-1.5"><span class="text-gray-500">Catatan</span><span id="reviewKeterangan" class="font-medium text-gray-800 dark:text-gray-200">-</span></div>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Ringkasan Jurnal</p>
                        <x-jurnal.review-table bodyId="reviewBody" totalDebitId="review_total_debit" totalKreditId="review_total_kredit"/>
                    </div>
                </div>
            </div>

            <div id="alertContainerStep3"></div>
            <div class="flex items-center justify-between gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
                <button type="button" onclick="goToStep(2)" class="h-10 px-5 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Kembali</button>
                <div class="flex items-center gap-3">
                    <button type="submit" name="submit_type" value="draft" class="h-10 px-5 text-sm font-medium rounded-xl border border-green-300 dark:border-green-800 text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">Simpan sebagai Draft</button>
                    <button type="submit" name="submit_type" value="posting" class="h-10 px-5 text-sm font-semibold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors">Simpan &amp; Posting</button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
const AKUN_OPTIONS = @json($akuns);
let barisCount = 0;
const CAN_CREATE_JURNAL_PEMBUKA = @json($canCreateJurnalPembuka);

document.addEventListener('DOMContentLoaded', function () {
    tambahBaris();
    tambahBaris();
    updateBalanceBar();

    flatpickr('#inputTanggalAwal', {
        dateFormat: 'Y-m-d',   
        altInput: true,        
        altFormat: 'd F Y',    
        locale: 'id',
        maxDate: 'today',      
        disableMobile: true,   
        onChange: function () {
            hitungTanggalAkhirDanNama();
        }
    });

    hitungTanggalAkhirDanNama();
    @if($errors->any()) goToStep(2); @endif
});
const NAMA_BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

/**
 * Hitung tanggal akhir bulan dari tanggal awal yang dipilih user,
 */
function hitungTanggalAkhirDanNama() {
    const val = document.getElementById('inputTanggalAwal').value; // format: yyyy-mm-dd
    const outAkhir  = document.getElementById('previewTanggalAkhir');
    const hidAkhir  = document.getElementById('inputTanggalAkhirHidden');
    const namaInput = document.getElementById('inputNamaPeriode');

    if (!val) { outAkhir.value = '-'; hidAkhir.value = ''; return; }

    const [y, m, d] = val.split('-').map(Number);
    const akhirBulan = new Date(y, m, 0); // hari ke-0 bulan berikutnya = hari terakhir bulan ini

    const pad = (n) => String(n).padStart(2, '0');
    const isoAkhir = akhirBulan.getFullYear() + '-' + pad(akhirBulan.getMonth() + 1) + '-' + pad(akhirBulan.getDate());

    outAkhir.value = pad(akhirBulan.getDate()) + ' ' + NAMA_BULAN[akhirBulan.getMonth()] + ' ' + akhirBulan.getFullYear();
    hidAkhir.value = isoAkhir;

    // Auto-isi nama periode
    if (!namaInput.dataset.userEdited) {
        namaInput.value = NAMA_BULAN[m - 1] + ' ' + y;
    }
}

document.getElementById('inputNamaPeriode').addEventListener('input', function () {
    this.dataset.userEdited = 'true';
});

function goToStep(n) {
    if (n === 2 && !validasiStep1()) return;
    document.querySelectorAll('[id^=step]').forEach(function (el) { el.classList.add('hidden'); });
    var target = document.getElementById('step' + n);
    if (target) target.classList.remove('hidden');
    for (let i = 1; i <= 3; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const line   = document.getElementById('step-line-' + i);
        if (!circle) continue;
        if (i < n) {
            circle.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
        } else if (i === n) {
            circle.textContent = i;
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
        } else {
            circle.textContent = i;
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors';
        }
        if (line) {
            line.className = i < n ? 'mx-4 flex-1 border-t-2 border-green-500 transition-colors' : 'mx-4 flex-1 border-t-2 border-gray-100 dark:border-gray-800 transition-colors';
        }
    }
    if (n === 3) isiReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validasiStep1() {
    const nama = document.getElementById('inputNamaPeriode').value.trim();
    const awal = document.getElementById('inputTanggalAwal').value;
    if (!nama) { showAlert('error', 'Nama periode wajib diisi.', 'alertContainer'); return false; }
    if (!awal) { showAlert('error', 'Tanggal awal wajib dipilih.', 'alertContainer'); return false; }
    hideAlert('alertContainer');
    return true;
}

function validasiStep2() {
    const t = hitungTotal();
    if (Math.round(t.debit*100) !== Math.round(t.kredit*100)) {
        showAlert('warning', 'Entri belum seimbang. Boleh disimpan sebagai draft, tetapi tidak bisa diposting.', 'alertContainerStep2');
    } else {
        hideAlert('alertContainerStep2');
    }
    goToStep(3);
}

function tambahBaris() {
    barisCount++;
    const idx  = barisCount;
    const opts = AKUN_OPTIONS.map(function (a) { return '<option value="' + a.id + '">' + a.kode + ' - ' + a.nama + '</option>'; }).join('');
    const tr = document.createElement('tr');
    tr.id = 'baris-' + idx;
    tr.className = 'border-b border-gray-50 dark:border-gray-800';
    tr.innerHTML =
        '<td class="px-3 py-2.5 text-gray-400 text-xs">' + idx + '</td>' +
        '<td class="px-3 py-2.5"><select name="detail[' + idx + '][akun_id]" required class="w-full h-9 px-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300" onchange="onAkunChange(this, ' + idx + ')"><option value="">Pilih akun</option>' + opts + '</select></td>' +
        '<td class="px-3 py-2.5"><select name="detail[' + idx + '][tipe]" required class="w-full h-9 px-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300" onchange="onTipeChange(' + idx + ')"><option value="DEBIT">Debit</option><option value="KREDIT">Kredit</option></select></td>' +
        '<td class="px-3 py-2.5"><input type="number" id="debit-' + idx + '" placeholder="0" min="0" step="1" class="w-full h-9 px-2 text-sm text-right border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300" oninput="updateNominal(' + idx + ')"></td>' +
        '<td class="px-3 py-2.5"><input type="number" id="kredit-' + idx + '" placeholder="0" min="0" step="1" disabled class="w-full h-9 px-2 text-sm text-right border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900 text-gray-400" oninput="updateNominal(' + idx + ')"></td>' +
        '<td class="px-3 py-2.5 text-center"><button type="button" onclick="hapusBaris(' + idx + ')" class="w-7 h-7 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500 hover:bg-red-100 transition-colors">x</button></td>' +
        '<input type="hidden" name="detail[' + idx + '][nominal]" id="nominal-' + idx + '" value="0">';
    document.getElementById('bodyEntri').appendChild(tr);
    onTipeChange(idx);
    updateNomorBaris();
}

function hapusBaris(idx) {
    if (document.querySelectorAll('#bodyEntri tr').length <= 2) { showAlert('error', 'Minimal 2 baris entri harus ada.', 'alertContainerStep2'); return; }
    var el = document.getElementById('baris-' + idx);
    if (el) el.remove();
    updateNomorBaris();
    updateBalanceBar();
}

function updateNomorBaris() {
    document.querySelectorAll('#bodyEntri tr').forEach(function (tr, i) { tr.querySelector('td:first-child').textContent = i + 1; });
}

function updateNominal(idx) {
    const tipe = document.querySelector('[name="detail[' + idx + '][tipe]"]').value;
    const dVal = parseFloat((document.getElementById('debit-'  + idx) || {}).value) || 0;
    const kVal = parseFloat((document.getElementById('kredit-' + idx) || {}).value) || 0;
    document.getElementById('nominal-' + idx).value = (tipe === 'DEBIT') ? dVal : kVal;
    updateBalanceBar();
}

function onAkunChange(sel, idx) {
    const akun = AKUN_OPTIONS.find(function (a) { return a.id == sel.value; });
    if (!akun) return;
    const tipeSelect = document.querySelector('[name="detail[' + idx + '][tipe]"]');
    if (akun.saldo_normal) { tipeSelect.value = akun.saldo_normal; onTipeChange(idx); }
}

function onTipeChange(idx) {
    const tipe   = document.querySelector('[name="detail[' + idx + '][tipe]"]').value;
    const dInput = document.getElementById('debit-'  + idx);
    const kInput = document.getElementById('kredit-' + idx);
    if (tipe === 'DEBIT') {
        dInput.disabled = false; dInput.classList.remove('bg-gray-50','dark:bg-gray-900','text-gray-400');
        kInput.disabled = true;  kInput.value = ''; kInput.classList.add('bg-gray-50','dark:bg-gray-900','text-gray-400');
    } else {
        kInput.disabled = false; kInput.classList.remove('bg-gray-50','dark:bg-gray-900','text-gray-400');
        dInput.disabled = true;  dInput.value = ''; dInput.classList.add('bg-gray-50','dark:bg-gray-900','text-gray-400');
    }
    document.getElementById('nominal-' + idx).value = 0;
    updateBalanceBar();
}

function hitungTotal() {
    let debit = 0, kredit = 0;
    document.querySelectorAll('#bodyEntri tr').forEach(function (tr) {
        const tipeEl = tr.querySelector('select[name*="[tipe]"]');
        const nomEl  = tr.querySelector('input[type="hidden"][name*="[nominal]"]');
        if (!tipeEl || !nomEl) return;
        const nom = parseFloat(nomEl.value) || 0;
        if (tipeEl.value === 'DEBIT')  debit  += nom;
        if (tipeEl.value === 'KREDIT') kredit += nom;
    });
    return { debit: debit, kredit: kredit };
}

function updateBalanceBar() {
    const t = hitungTotal();
    const fmtRp = function (n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); };
    const seimbang = Math.round(t.debit*100) === Math.round(t.kredit*100);
    document.getElementById('create_TotalDebit').textContent  = fmtRp(t.debit);
    document.getElementById('create_TotalKredit').textContent = fmtRp(t.kredit);
    const s = document.getElementById('create_BalanceStatus');
    if (seimbang && t.debit > 0) { s.className = 'flex items-center gap-1.5 text-xs font-medium text-green-600'; s.textContent = 'Seimbang'; }
    else { s.className = 'flex items-center gap-1.5 text-xs font-medium text-amber-600'; s.textContent = 'Belum seimbang'; }
}

function isiReview() {
    const nama  = document.getElementById('inputNamaPeriode').value || '-';
    const awal  = document.getElementById('inputTanggalAwal').value || '-';
    const akhir = document.getElementById('previewTanggalAkhir').value || '-';
    const ket   = (document.querySelector('[name=keterangan]') || {}).value || '-';
    document.getElementById('reviewTipePeriode').textContent  = nama;
    document.getElementById('reviewTanggalAwal').textContent  = awal;
    document.getElementById('reviewTanggalAkhir').textContent = akhir;
    document.getElementById('reviewKeterangan').textContent   = ket;
    const t = hitungTotal();
    const seimbang = Math.round(t.debit*100) === Math.round(t.kredit*100);
    document.getElementById('reviewStatus').innerHTML = seimbang ? '<span class="text-green-600 font-medium">Seimbang</span>' : '<span class="text-amber-600 font-medium">Belum seimbang (hanya bisa draft)</span>';
    const fmtRp = function (n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); };
    const tbody = document.getElementById('reviewBody');
    tbody.innerHTML = '';
    document.querySelectorAll('#bodyEntri tr').forEach(function (tr) {
        const akunSel = tr.querySelector('select[name*="[akun_id]"]');
        const tipeSel = tr.querySelector('select[name*="[tipe]"]');
        if (!akunSel || !akunSel.value) return;
        const akun = AKUN_OPTIONS.find(function (a) { return a.id == akunSel.value; });
        const tp = tipeSel ? tipeSel.value : '';
        const nom = parseFloat((tr.querySelector('input[type="hidden"][name*="[nominal]"]') || {}).value) || 0;
        const row = document.createElement('tr');
        row.className = 'border-b border-gray-50 dark:border-gray-800';
        row.innerHTML =
            '<td class="py-2 text-sm text-gray-700 dark:text-gray-300">' + (akun ? akun.nama : '-') + '</td>' +
            '<td class="py-2 text-right text-sm ' + (tp === 'DEBIT' ? 'text-red-600' : 'text-gray-300') + '">' + (tp === 'DEBIT' ? fmtRp(nom) : '-') + '</td>' +
            '<td class="py-2 text-right text-sm ' + (tp === 'KREDIT' ? 'text-green-700' : 'text-gray-300') + '">' + (tp === 'KREDIT' ? fmtRp(nom) : '-') + '</td>';
        tbody.appendChild(row);
    });
    document.getElementById('review_total_debit').textContent  = fmtRp(t.debit);
    document.getElementById('review_total_kredit').textContent = fmtRp(t.kredit);
}

let _submitType = null;
document.addEventListener('click', function (e) {
    const btn = e.target.closest('button[type="submit"][name="submit_type"]');
    if (btn) _submitType = btn.value;
});

document.getElementById('formJurnalPembuka').addEventListener('submit', function (e) {
    e.preventDefault();
    if ((!_submitType || ['draft', 'posting'].indexOf(_submitType) === -1) && e.submitter && e.submitter.name === 'submit_type') _submitType = e.submitter.value;
    if (!_submitType || ['draft', 'posting'].indexOf(_submitType) === -1) return;
    if (!CAN_CREATE_JURNAL_PEMBUKA) { showAlert('error', 'Anda tidak memiliki akses untuk menyimpan jurnal pembuka.', 'alertContainerStep3'); goToStep(3); _submitType = null; return; }
    const t = hitungTotal();
    if (_submitType === 'posting' && Math.round(t.debit*100) !== Math.round(t.kredit*100)) { showAlert('error', 'Tidak dapat posting - total Debit dan Kredit belum seimbang.', 'alertContainerStep2'); goToStep(2); _submitType = null; return; }
    let hidden = document.getElementById('_submit_type');
    if (!hidden) { hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.id = '_submit_type'; hidden.name = 'submit_type'; this.appendChild(hidden); }
    hidden.value = _submitType;
    document.querySelectorAll('#bodyEntri input:disabled').forEach(function (el) { el.disabled = false; });
    _submitType = null;
    this.submit();
});

function showAlert(type, message, containerId) {
    containerId = containerId || 'alertContainer';
    const c = document.getElementById(containerId);
    if (!c) return;
    const color = type === 'error' ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400' : (type === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400' : 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400');
    c.innerHTML = '<div class="border rounded-xl px-4 py-3 text-sm ' + color + '">' + message + '</div>';
    setTimeout(function () { c.innerHTML = ''; }, 5000);
}
function hideAlert(containerId) {
    containerId = containerId || 'alertContainer';
    const c = document.getElementById(containerId);
    if (c) c.innerHTML = '';
}
</script>
@endpush