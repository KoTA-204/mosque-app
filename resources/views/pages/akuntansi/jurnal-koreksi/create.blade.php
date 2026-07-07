@extends('layouts.app')

@section('title', 'Tambah Jurnal Koreksi')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Jurnal Koreksi</h1>
            @if($periodeAktif)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Periode aktif: <span class="font-medium text-green-600 dark:text-green-400">{{ $periodeAktif->nama_periode }}</span>
                · {{ $periodeAktif->jurnal()->koreksi()->count() }} koreksi tercatat
            </p>
            @endif
        </div>
        <a href="{{ route('dashboard.jurnal-koreksi.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <x-jurnal.error-banner />

    <x-jurnal.stepper :steps="['Jurnal yang Dikoreksi', 'Jurnal Koreksi Baru', 'Review & Simpan']" />

    <form action="{{ route('dashboard.jurnal-koreksi.store') }}" method="POST" id="jurnalForm">
        @csrf

        {{-- ═══ STEP 1 ═══ --}}
        <div id="step1" class="space-y-4">

            {{-- Pilih Jurnal --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Pilih Jurnal yang Akan Dikoreksi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Periode Jurnal <span class="text-red-500">*</span>
                        </label>
                        <select name="periode_id" id="periodeSelect" onchange="onPeriodeChange(this.value)"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            <option value="">Pilih periode</option>
                            @foreach($periodeList as $periode)
                            <option value="{{ $periode->id }}"
                                {{ ($periodeAktif && $periodeAktif->id == $periode->id) || old('periode_id') == $periode->id ? 'selected' : '' }}>
                                {{ $periode->nama_periode }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nomor Jurnal yang Dikoreksi <span class="text-red-500">*</span>
                        </label>
                        <select name="jurnal_ref_id" id="jurnalSelect" onchange="onJurnalChange(this.value)"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            <option value="">Pilih jurnal</option>
                            @foreach($jurnalData as $jr)
                            <option value="{{ $jr['id'] }}"
                                    data-periode="{{ $jr['periode_id'] }}"
                                    {{ old('jurnal_ref_id') == $jr['id'] ? 'selected' : '' }}>
                                {{ $jr['nomor'] }} — {{ $jr['keterangan'] }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Hanya jurnal yang sudah posted yang dapat dikoreksi</p>
                    </div>
                </div>

                {{-- Preview jurnal --}}
                <div id="jurnalPreview" class="hidden mt-5">
                    <div class="rounded-xl border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/10 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p id="preview_nomor" class="text-sm font-bold text-yellow-700 dark:text-yellow-400">—</p>
                            <span class="text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 px-2 py-0.5 rounded-full">Jurnal yang akan dikoreksi</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                            Tanggal: <span id="preview_tanggal" class="font-medium text-gray-700 dark:text-gray-300">—</span>
                            &nbsp;&nbsp;Keterangan: <span id="preview_keterangan" class="font-medium text-gray-700 dark:text-gray-300">—</span>
                        </p>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-yellow-200 dark:border-yellow-800">
                                    <th class="pb-2 text-left text-xs font-medium text-gray-400">Akun</th>
                                    <th class="pb-2 text-center text-xs font-medium text-gray-400">Posisi</th>
                                    <th class="pb-2 text-right text-xs font-medium text-gray-400">Debit</th>
                                    <th class="pb-2 text-right text-xs font-medium text-gray-400">Kredit</th>
                                </tr>
                            </thead>
                            <tbody id="preview_detail"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Informasi Koreksi --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informasi Koreksi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Koreksi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal"
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Periode</label>
                        <input type="text" id="periodeReadonly" readonly
                               value="{{ $periodeAktif?->nama_periode ?? '' }}"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Alasan Koreksi <span class="text-red-500">*</span>
                    </label>
                    <textarea name="keterangan" rows="3"
                              placeholder="Jelaskan kesalahan yang terjadi dan mengapa perlu dikoreksi..."
                              class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 resize-none placeholder-gray-400">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <x-jurnal.form-footer
                :step="1" :total="3"
                :back-route="route('dashboard.jurnal-koreksi.index')"
                next-action="goToStep2()"
                next-label="Lanjut ke Detail"
            />
        </div>

        {{-- ═══ STEP 2 ═══ --}}
        <div id="step2" class="hidden space-y-4">

            {{-- Side-by-side: Lama vs Baru --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Jurnal yang Dikoreksi vs Entri Baru
                    </h3>
                    <p class="text-xs text-gray-400">Pastikan entri baru sudah benar</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                    <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/10 p-4">
                        <p class="text-xs font-semibold text-red-500 mb-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span id="step2_nomor_lama">Jurnal Lama</span>
                        </p>
                        <table class="w-full text-sm" id="step2_tabel_lama"><tbody></tbody></table>
                    </div>
                    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10 p-4 min-h-[80px]">
                        <p class="text-xs font-semibold text-green-600 mb-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Jurnal Koreksi (Entri yang benar)
                        </p>
                        <div id="step2_preview_baru">
                            <p class="text-xs text-gray-400 italic">Isi entri koreksi di bawah...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Entri Jurnal Koreksi --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10m0-10a2 2 0 012 2h2a2 2 0 012-2"/>
                        </svg>
                        Entri Jurnal Koreksi
                    </h3>
                    <p class="text-xs text-gray-400">Masukkan entri yang benar</p>
                </div>

                <div class="grid grid-cols-12 gap-3 mb-2 px-1 text-xs font-medium text-gray-400">
                    <div class="col-span-1">No</div>
                    <div class="col-span-5">Akun</div>
                    <div class="col-span-2">Posisi</div>
                    <div class="col-span-2 text-right">Debit (Rp)</div>
                    <div class="col-span-2 text-right">Kredit (Rp)</div>
                </div>

                <div id="detailRows" class="space-y-2 mb-4"></div>

                <button type="button" onclick="addDetailRow()"
                        class="w-full rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 py-3 text-sm font-medium text-green-600 hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors">
                    + Tambah Baris
                </button>

                <x-jurnal.balance-bar />
            </div>

            <x-jurnal.form-footer
                :step="2" :total="3"
                back-action="goToStep(1)"
                next-action="goToStep3()"
                next-label="Lanjut ke Review"
            />
        </div>

        {{-- ═══ STEP 3: Review & Simpan ═══ --}}
        <div id="step3" class="hidden space-y-4">

            <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Pastikan entri koreksi sudah benar. Setelah diposting, jurnal ini tidak dapat dihapus — hanya bisa dikoreksi ulang dengan jurnal koreksi baru.
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Review Jurnal Koreksi
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Informasi Koreksi</p>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">No. Jurnal Dikoreksi</span>
                                <span id="review_nomor_dikoreksi" class="font-semibold text-yellow-600 dark:text-yellow-400">—</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Tanggal Koreksi</span>
                                <span id="review_tanggal" class="font-medium text-gray-900 dark:text-white">—</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Dibuat oleh</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->name ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Alasan Koreksi</p>
                            <div id="review_keterangan" class="rounded-xl bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-100 dark:border-yellow-800 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 min-h-[48px]">—</div>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Entri Jurnal Koreksi</p>
                        <x-jurnal.review-table />
                    </div>
                </div>
            </div>

            <x-jurnal.form-footer
                :step="3" :total="3"
                back-action="goToStep(2)"
                :show-submit="true"
            />
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script type="module">
import { parseNominal, formatRp, formatInput, makeStepperController, makeBalanceController, renderReviewRows } from '/js/jurnal-helpers.js';

// ── Data dari server ───────────────────────────────────────────────────────
const periodeList  = @json($periodeList->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama_periode]));
const jurnalData   = @json($jurnalData ?? []);
const akunList     = @json($akunList ?? []);

// ── State ──────────────────────────────────────────────────────────────────
let detailRows     = [];
let rowCounter     = 0;
let selectedJurnal = null;

// ── Controllers ────────────────────────────────────────────────────────────
const stepper = makeStepperController(3);
const balance = makeBalanceController(() => detailRows);

window.goToStep     = (n) => stepper.goToStep(n);
window.formatInput  = formatInput;
window.recalcBalance = () => balance.recalc();

// ── Helpers lokal ──────────────────────────────────────────────────────────
function getAkunLabel(akunId) {
    if (!akunId) return '—';
    for (const group of (akunList ?? [])) {
        const a = group.akun.find(a => String(a.id) === String(akunId));
        if (a) return a.label;
    }
    return '—';
}

// ── Periode & Jurnal Select ────────────────────────────────────────────────
window.onPeriodeChange = function(periodeId) {
    const periode = periodeList.find(p => String(p.id) === String(periodeId));
    document.getElementById('periodeReadonly').value = periode?.nama ?? '';

    const select  = document.getElementById('jurnalSelect');
    select.querySelectorAll('option[data-periode]').forEach(opt => {
        opt.hidden = periodeId ? String(opt.dataset.periode) !== String(periodeId) : false;
    });
    select.value = '';
    document.getElementById('jurnalPreview').classList.add('hidden');
    selectedJurnal = null;
};

window.onJurnalChange = function(jurnalId) {
    if (!jurnalId) { document.getElementById('jurnalPreview').classList.add('hidden'); selectedJurnal = null; return; }
    selectedJurnal = jurnalData.find(j => String(j.id) === String(jurnalId));
    if (!selectedJurnal) return;

    document.getElementById('jurnalPreview').classList.remove('hidden');
    document.getElementById('preview_nomor').textContent      = selectedJurnal.nomor ?? '—';
    document.getElementById('preview_tanggal').textContent    = selectedJurnal.tanggal ?? '—';
    document.getElementById('preview_keterangan').textContent = selectedJurnal.keterangan ?? '—';

    document.getElementById('preview_detail').innerHTML = (selectedJurnal.detail ?? []).map(d => `
        <tr class="border-b border-yellow-100 dark:border-yellow-900/30">
            <td class="py-1.5 text-gray-700 dark:text-gray-300 text-sm">${d.akun ?? '—'}</td>
            <td class="py-1.5 text-center text-xs font-bold ${d.posisi === 'D' ? 'text-red-500' : 'text-green-600'}">${d.posisi ?? '—'}</td>
            <td class="py-1.5 text-right text-sm text-gray-700 dark:text-gray-300">${d.debit  > 0 ? formatRp(d.debit)  : '—'}</td>
            <td class="py-1.5 text-right text-sm text-gray-700 dark:text-gray-300">${d.kredit > 0 ? formatRp(d.kredit) : '—'}</td>
        </tr>`).join('');
};

// ── Step 1 → 2 ─────────────────────────────────────────────────────────────
window.goToStep2 = function() {
    if (!document.getElementById('jurnalSelect').value)  { alert('Pilih jurnal yang akan dikoreksi terlebih dahulu.'); return; }
    if (!document.querySelector('textarea[name="keterangan"]').value.trim()) { alert('Alasan koreksi wajib diisi.'); return; }

    if (selectedJurnal) {
        document.getElementById('step2_nomor_lama').textContent = 'Jurnal Lama (' + selectedJurnal.nomor + ')';
        document.querySelector('#step2_tabel_lama tbody').innerHTML = (selectedJurnal.detail ?? []).map(d => `
            <tr>
                <td class="py-1 text-gray-700 dark:text-gray-300 text-sm">${d.akun ?? '—'}</td>
                <td class="py-1 text-right text-sm font-medium ${d.posisi === 'D' ? 'text-red-500' : 'text-green-600'}">
                    ${d.posisi === 'D' ? 'D ' + formatRp(d.debit) : 'K ' + formatRp(d.kredit)}
                </td>
            </tr>`).join('');
    }
    stepper.goToStep(2);
};

// ── Step 2 → 3 ─────────────────────────────────────────────────────────────
window.goToStep3 = function() {
    if (detailRows.length < 2)    { alert('Minimal harus ada 2 baris detail jurnal.'); return; }
    if (!balance.isBalanced())    { alert('Total debit dan kredit harus sama sebelum melanjutkan.'); return; }
    renderReview();
    stepper.goToStep(3);
};

// ── Detail Rows ─────────────────────────────────────────────────────────────
window.addDetailRow = function() {
    detailRows.push({ id: rowCounter++, akun_id: '', tipe: detailRows.length === 0 ? 'KREDIT' : 'DEBIT', nominal: '' });
    renderDetailRows();
};

window.removeDetailRow = function(id) {
    detailRows = detailRows.filter(r => r.id !== id);
    renderDetailRows();
    balance.recalc();
};

window.updateRow = function(id, field, value) {
    const row = detailRows.find(r => r.id === id);
    if (row) row[field] = value;
};

function buildAkunOptions(selectedId) {
    let html = '<option value="">Pilih akun</option>';
    (akunList ?? []).forEach(group => {
        html += `<optgroup label="${group.kategori}">`;
        group.akun.forEach(a => {
            html += `<option value="${a.id}" ${String(row?.akun_id) === String(a.id) ? 'selected' : ''}>${a.label}</option>`;
        });
        html += '</optgroup>';
    });
    return html;
}

function renderDetailRows() {
    const container = document.getElementById('detailRows');
    container.innerHTML = detailRows.map((row, idx) => {
        const akunOptions = (akunList ?? []).map(group => `
            <optgroup label="${group.kategori}">
                ${group.akun.map(a => `<option value="${a.id}" ${String(row.akun_id) === String(a.id) ? 'selected' : ''}>${a.label}</option>`).join('')}
            </optgroup>`).join('');
        const isDebit  = row.tipe === 'DEBIT';
        const isKredit = row.tipe === 'KREDIT';
        const n        = parseNominal(row.nominal);

        return `
        <div class="grid grid-cols-12 gap-3 items-center">
            <input type="hidden" id="h_akun_${row.id}"    name="detail[${idx}][akun_id]" value="${row.akun_id ?? ''}">
            <input type="hidden" id="h_tipe_${row.id}"    name="detail[${idx}][tipe]"    value="${row.tipe}">
            <input type="hidden" id="h_nominal_${row.id}" name="detail[${idx}][nominal]" value="${row.nominal ?? ''}">
            <div class="col-span-1 text-sm text-gray-400 text-center">${idx + 1}</div>
            <div class="col-span-5">
                <select onchange="updateRow(${row.id}, 'akun_id', this.value); document.getElementById('h_akun_${row.id}').value = this.value; renderStep2Preview()"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                    <option value="">Pilih akun</option>${akunOptions}
                </select>
            </div>
            <div class="col-span-2">
                <select onchange="updateRow(${row.id}, 'tipe', this.value); document.getElementById('h_tipe_${row.id}').value = this.value; renderDetailRows(); balance.recalc(); renderStep2Preview()"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                    <option value="DEBIT"  ${isDebit  ? 'selected' : ''}>Debit</option>
                    <option value="KREDIT" ${isKredit ? 'selected' : ''}>Kredit</option>
                </select>
            </div>
            <div class="col-span-2 text-right">
                <input type="text" value="${isDebit ? (row.nominal ?? '') : ''}" placeholder="0" ${isKredit ? 'readonly tabindex="-1"' : ''}
                       oninput="formatInput(this); updateRow(${row.id}, 'nominal', this.value); document.getElementById('h_nominal_${row.id}').value = this.value; balance.recalc(); renderStep2Preview()"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm text-right bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 ${isKredit ? 'bg-gray-50 dark:bg-gray-800/50 text-gray-400 cursor-not-allowed' : ''}">
            </div>
            <div class="col-span-2 text-right flex items-center gap-1">
                <input type="text" value="${isKredit ? (row.nominal ?? '') : ''}" placeholder="0" ${isDebit ? 'readonly tabindex="-1"' : ''}
                       oninput="formatInput(this); updateRow(${row.id}, 'nominal', this.value); document.getElementById('h_nominal_${row.id}').value = this.value; balance.recalc(); renderStep2Preview()"
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm text-right bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 ${isDebit ? 'bg-gray-50 dark:bg-gray-800/50 text-gray-400 cursor-not-allowed' : ''}">
                <button type="button" onclick="removeDetailRow(${row.id})"
                        class="text-gray-300 hover:text-red-500 transition-colors shrink-0 ml-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>`;
    }).join('');

    balance.recalc();
    renderStep2Preview();
}

function renderStep2Preview() {
    const el = document.getElementById('step2_preview_baru');
    if (!el) return;
    if (detailRows.length === 0) { el.innerHTML = '<p class="text-xs text-gray-400 italic">Isi entri koreksi di bawah...</p>'; return; }
    el.innerHTML = `<table class="w-full text-sm">${detailRows.map(row => {
        const n = parseNominal(row.nominal);
        return `<tr>
            <td class="py-1 text-gray-700 dark:text-gray-300">${getAkunLabel(row.akun_id)}</td>
            <td class="py-1 text-right font-medium ${row.tipe === 'DEBIT' ? 'text-red-500' : 'text-green-600'}">
                ${n > 0 ? (row.tipe === 'DEBIT' ? 'D ' : 'K ') + formatRp(n) : '—'}
            </td>
        </tr>`;
    }).join('')}</table>`;
}

// ── Review ──────────────────────────────────────────────────────────────────
function renderReview() {
    document.getElementById('review_nomor_dikoreksi').textContent = selectedJurnal?.nomor ?? '—';
    document.getElementById('review_tanggal').textContent         = document.querySelector('input[name="tanggal"]').value;
    document.getElementById('review_keterangan').textContent      = document.querySelector('textarea[name="keterangan"]').value || '—';
    renderReviewRows(detailRows, getAkunLabel);
}

// ── Init ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const periodeSelect = document.getElementById('periodeSelect');
    if (periodeSelect.value) {
        onPeriodeChange(periodeSelect.value);
        const oldJurnalId = '{{ old('jurnal_ref_id', '') }}';
        if (oldJurnalId) { document.getElementById('jurnalSelect').value = oldJurnalId; onJurnalChange(oldJurnalId); }
    }
    addDetailRow();
    addDetailRow();
});
</script>
@endpush