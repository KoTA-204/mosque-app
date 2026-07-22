@extends('layouts.app')

@section('title', 'Tambah Jurnal Penyesuaian')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Jurnal Penyesuaian</h1>
            @if($periodeAktif)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Periode aktif: <span class="font-medium text-green-600 dark:text-green-400">{{ $periodeAktif->nama_periode }}</span>
                · {{ $periodeAktif->jurnal()->penyesuaian()->count() }} jurnal tercatat
            </p>
            @endif
        </div>
        <a href="{{ route('dashboard.jurnal-penyesuaian.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <x-jurnal.error-banner />

    @if(session('error'))
        <x-jurnal.alert type="error" :message="session('error')" />
    @endif
    @if(session('success'))
        <x-jurnal.alert type="success" :message="session('success')" />
    @endif

    <x-jurnal.stepper :steps="['Informasi & Detail', 'Review & Simpan']" />

    <form action="{{ route('dashboard.jurnal-penyesuaian.store') }}" method="POST" id="jurnalForm">
        <div id="formAlertBox" class="hidden mb-4">
            <x-ui.alert variant="error" title="Tidak dapat melanjutkan">
                <span id="formAlertMsg" class="text-sm text-gray-500 dark:text-gray-400"></span>
            </x-ui.alert>
        </div>

        @csrf

        {{-- ═══ STEP 1 ═══ --}}
        <div id="step1" class="space-y-4">

            {{-- Informasi Umum --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                    </svg>
                    Informasi Umum
                </h3>
                @php
                    $periodeTerpilih = collect($periodeList)->firstWhere('id', (int) old('periode_id')) ?? $periodeAktif ?? collect($periodeList)->first();
                    $tanggalKunci = $periodeTerpilih?->tanggal_akhir;
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Jurnal <span class="text-red-500">*</span>
                        </label>
                        <input type="hidden" name="tanggal" id="tanggalPenyesuaian" value="{{ $tanggalKunci?->format('Y-m-d') }}">
                        <div id="tanggalPenyesuaianLabel" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-700/40 text-gray-500 dark:text-gray-400 cursor-not-allowed">{{ $tanggalKunci?->translatedFormat('d F Y') ?? '—' }}</div>
                        <p class="text-xs text-gray-400 mt-1">Otomatis mengikuti akhir periode (konvensi jurnal penyesuaian).</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Periode <span class="text-red-500">*</span>
                        </label>
                        <select name="periode_id" id="periodeSelectPenyesuaian" onchange="syncTanggalPenyesuaian()"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            @foreach($periodeList as $periode)
                            <option value="{{ $periode->id }}"
                                data-akhir="{{ $periode->tanggal_akhir->format('Y-m-d') }}"
                                data-akhir-label="{{ $periode->tanggal_akhir->translatedFormat('d F Y') }}"
                                {{ ($periodeAktif && $periodeAktif->id == $periode->id) || old('periode_id') == $periode->id ? 'selected' : '' }}>
                                {{ $periode->nama_periode }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <script>
                        function syncTanggalPenyesuaian() {
                            var sel = document.getElementById('periodeSelectPenyesuaian');
                            if (!sel) return;
                            var opt = sel.options[sel.selectedIndex];
                            if (!opt) return;
                            var akhir = opt.getAttribute('data-akhir');
                            var label = opt.getAttribute('data-akhir-label');
                            var input = document.getElementById('tanggalPenyesuaian');
                            var disp  = document.getElementById('tanggalPenyesuaianLabel');
                            if (akhir && input) input.value = akhir;
                            if (label && disp) disp.textContent = label;
                        }
                        document.addEventListener('DOMContentLoaded', syncTanggalPenyesuaian);
                    </script>
                </div>
            </div>

            {{-- Jenis Penyesuaian --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Jenis Penyesuaian <span class="text-red-500">*</span>
                </h3>
                <input type="hidden" name="tipe_penyesuaian" id="tipe_penyesuaian" value="{{ old('tipe_penyesuaian', '') }}">

                @php
                    $tipeIcons = [
                        'PENYUSUTAN_ASET'          => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                        'BEBAN_BELUM_DIBAYAR'       => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        'PENDAPATAN_BELUM_DICATAT'  => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'BEBAN_DIBAYAR_DIMUKA'      => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                        'ZAKAT_INFAQ'               => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                        'PELEPASAN_ASET'            => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4l-2 3h-4l-2-3H4',
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($tipeLabels as $key => $label)
                    <button type="button"
                            onclick="selectTipe('{{ $key }}')"
                            id="btn_{{ $key }}"
                            class="tipe-btn flex items-start gap-3 rounded-xl border-2 px-4 py-3 text-left transition-all
                                   {{ old('tipe_penyesuaian') === $key
                                       ? 'border-green-600 bg-green-50 dark:bg-green-900/20'
                                       : 'border-gray-200 dark:border-gray-700 hover:border-green-400 dark:hover:border-green-600' }}">
                        <span class="shrink-0 mt-0.5 {{ old('tipe_penyesuaian') === $key ? 'text-green-600' : 'text-gray-400' }}" id="btn-icon-{{ $key }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tipeIcons[$key] ?? '' }}"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $label }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $tipeDescs[$key] ?? '' }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Detail Jurnal --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10m0-10a2 2 0 012 2h2a2 2 0 012-2"/>
                        </svg>
                        Detail Jurnal
                    </h3>
                    <p class="text-xs text-gray-400">Pastikan debit = kredit sebelum melanjutkan</p>
                </div>

                <div id="asetPelepasanCard" class="hidden mb-5 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/10 p-4">
                    <div class="flex items-start justify-between mb-3 gap-4">
                        <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300">Aset yang Dilepas <span class="text-red-500">*</span></h4>
                        <p class="text-xs text-amber-700/80 dark:text-amber-400/80 text-right">Pilih aset yang dikeluarkan dari pembukuan. Setelah jurnal diposting, nilai buku aset menjadi 0 dan aset ditandai 'akan dilepas'.</p>
                    </div>
                    <div id="asetDilepasRows" class="space-y-2"></div>
                    <button type="button" onclick="addAsetDilepas()"
                            class="mt-3 text-xs text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-200 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Aset
                    </button>
                </div>

                <div id="detailRows" class="space-y-3 mb-4"></div>

                <button type="button" onclick="addDetailRow()"
                        class="w-full rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 py-3 text-sm font-medium text-green-600 hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors">
                    + Tambah Baris
                </button>

                <x-jurnal.balance-bar />
            </div>

            {{-- Keterangan --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    Keterangan <span class="text-red-500">*</span>
                </h3>
                <textarea name="keterangan" rows="3"
                          placeholder="Deskripsikan penyesuaian ini..."
                          class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 resize-none placeholder-gray-400">{{ old('keterangan') }}</textarea>
            </div>

            <x-jurnal.form-footer
                :step="1" :total="2"
                :back-route="route('dashboard.jurnal-penyesuaian.index')"
                next-action="goToStep2()"
                next-label="Lanjut ke Review"
            />
        </div>

        {{-- ═══ STEP 2: Review & Simpan ═══ --}}
        <div id="step2" class="hidden space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Review Jurnal
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Info Umum --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Informasi Umum</p>
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl px-4 py-3 space-y-2.5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Periode</span>
                                <span id="review_periode" class="font-medium text-gray-900 dark:text-white">—</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Tanggal</span>
                                <span id="review_tanggal" class="font-medium text-gray-900 dark:text-white">—</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Jenis Penyesuaian</span>
                                <span id="review_tipe" class="font-medium text-gray-900 dark:text-white">—</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Keterangan</span>
                                <span id="review_keterangan" class="font-medium text-gray-900 dark:text-white text-right">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- Ringkasan Jurnal --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Ringkasan Jurnal</p>
                        <x-jurnal.review-table />
                    </div>
                </div>

                {{-- Info status --}}
                <div class="mt-5 rounded-xl bg-gray-50 dark:bg-gray-800/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Keterangan Status</p>
                    <div class="flex flex-wrap gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                            <span class="text-gray-500 dark:text-gray-400"><span class="font-medium text-green-600">Posted</span> — Langsung masuk ke buku besar</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-yellow-500 inline-block"></span>
                            <span class="text-gray-500 dark:text-gray-400"><span class="font-medium text-yellow-600">Draft</span> — Disimpan sementara, belum diposting</span>
                        </div>
                    </div>
                </div>
            </div>

            <x-jurnal.form-footer
                :step="2" :total="2"
                back-action="goToStep(1)"
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
const akunPerTipe = @json($akunPerTipe);
const tipeLabels  = @json($tipeLabels);
const asetList    = @json($asetList);
const asetPelepasanList = @json($asetPelepasanList);
const periodeList = @json($periodeList->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama_periode]));

// ── State ──────────────────────────────────────────────────────────────────
let detailRows  = [];
let rowCounter  = 0;
let asetCounter = 0;
let asetDilepas = [];
let asetDilepasCounter = 0;

// ── Controllers ────────────────────────────────────────────────────────────
const stepper = makeStepperController(2);
const balance = makeBalanceController(() => detailRows);

// Expose goToStep untuk onclick di form-footer
window.goToStep = (n) => { hideFormAlert(); stepper.goToStep(n); };

// ── Helpers lokal ──────────────────────────────────────────────────────────
function currentTipe() {
    return document.getElementById('tipe_penyesuaian').value;
}

function currentAkunOptions(selectedId) {
    const tipe   = currentTipe();
    const groups = akunPerTipe[tipe] || [];
    let html = '<option value="">Pilih akun</option>';
    groups.forEach(group => {
        html += `<optgroup label="${group.kategori}">`;
        group.akun.forEach(a => {
            const sel = String(a.id) === String(selectedId) ? 'selected' : '';
            html += `<option value="${a.id}" data-saldo="${a.saldo_normal}" ${sel}>${a.label}</option>`;
        });
        html += '</optgroup>';
    });
    return html;
}

function getAkunLabel(akunId) {
    if (!akunId) return '—';
    const groups = akunPerTipe[currentTipe()] || [];
    for (const group of groups) {
        const found = group.akun.find(a => String(a.id) === String(akunId));
        if (found) return found.label;
    }
    return '—';
}

// ── Step 1 → 2 ─────────────────────────────────────────────────────────────
function hideFormAlert() {
    var b = document.getElementById('formAlertBox');
    if (b) b.classList.add("hidden");
}
function showFormAlert(msg) {
    var b = document.getElementById('formAlertBox');
    var m = document.getElementById('formAlertMsg');
    if (m) m.textContent = msg;
    if (b) {
        b.classList.remove("hidden");
        b.scrollIntoView({ behavior: 'smooth', block: 'center' });
        b.classList.add("ring-2", "ring-red-400", "ring-offset-2", "rounded-xl");
        setTimeout(function () { b.classList.remove("ring-2", "ring-red-400", "ring-offset-2", "rounded-xl"); }, 1500);
    }
}

window.goToStep2 = function() {
    hideFormAlert();
    if (!currentTipe())       { showFormAlert('Pilih jenis penyesuaian terlebih dahulu.'); return; }
    if (!document.querySelector('textarea[name="keterangan"]').value.trim())
                              { showFormAlert('Keterangan jurnal wajib diisi.'); return; }
    if (detailRows.length < 2){ showFormAlert('Minimal harus ada 2 baris detail jurnal.'); return; }
    if (!balance.isBalanced()){ showFormAlert('Total debit dan kredit harus sama sebelum melanjutkan.'); return; }
    renderReview();
    stepper.goToStep(2);
};

// ── Tipe Penyesuaian ────────────────────────────────────────────────────────
function applyTipeButtonStyle(key) {
    document.querySelectorAll('.tipe-btn').forEach(btn => {
        btn.classList.remove('border-green-600', 'bg-green-50', 'dark:bg-green-900/20');
        btn.classList.add('border-gray-200', 'dark:border-gray-700');
    });
    document.querySelectorAll('[id^="btn-icon-"]').forEach(ic => {
        ic.classList.remove('text-green-600');
        ic.classList.add('text-gray-400');
    });

    const btn  = document.getElementById('btn_' + key);
    const icon = document.getElementById('btn-icon-' + key);
    if (btn)  { btn.classList.remove('border-gray-200', 'dark:border-gray-700'); btn.classList.add('border-green-600', 'bg-green-50', 'dark:bg-green-900/20'); }
    if (icon) { icon.classList.remove('text-gray-400'); icon.classList.add('text-green-600'); }

    const pelCard = document.getElementById('asetPelepasanCard');
    if (pelCard) pelCard.classList.toggle('hidden', key !== 'PELEPASAN_ASET');
}

window.selectTipe = function(key) {
    document.getElementById('tipe_penyesuaian').value = key;
    applyTipeButtonStyle(key);

    detailRows = [];
    rowCounter = 0;
    renderDetailRows();

    if (key === 'PELEPASAN_ASET' && asetDilepas.length === 0) addAsetDilepas();
    renderAsetDilepas();
};

// ── Detail Rows ─────────────────────────────────────────────────────────────
window.addDetailRow = function() {
    const isPenyusutan = currentTipe() === 'PENYUSUTAN_ASET';
    detailRows.push({
        id: rowCounter++, akun_id: '',
        tipe: detailRows.length === 0 ? 'DEBIT' : 'KREDIT',
        nominal: '',
        aset_rows: isPenyusutan ? [] : null,
    });
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

window.addAsetRow = function(rowId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    row.aset_rows.push({ id: asetCounter++, aset_id: '', nominal: '' });
    renderDetailRows();
    balance.recalc();
};

window.removeAsetRow = function(rowId, asetId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    row.aset_rows = row.aset_rows.filter(a => a.id !== asetId);
    syncDebitFromAset(rowId);
    renderDetailRows();
    balance.recalc();
};

window.updateAsetRow = function(rowId, asetId, field, value) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    const asetRow = row.aset_rows.find(a => a.id === asetId);
    if (asetRow) asetRow[field] = value;
};

window.onAsetChange = function(rowId, asetId, selectEl) {
    updateAsetRow(rowId, asetId, 'aset_id', selectEl.value);
    if (!selectEl.value) { syncDebitFromAset(rowId); renderDetailRows(); balance.recalc(); return; }
    const aset = asetList.find(a => a.id == selectEl.value);
    if (!aset) return;
    const container = selectEl.closest('[data-aset-row]');
    if (container) {
        const nominalInput = container.querySelector('[data-aset-nominal]');
        if (nominalInput && !nominalInput.value) {
            const perBulan  = parseFloat(aset.penyusutan_per_bulan || 0);
            const formatted = perBulan > 0 ? perBulan.toLocaleString('id-ID') : '';
            nominalInput.value = formatted;
            updateAsetRow(rowId, asetId, 'nominal', formatted);
        }
    }
    syncDebitFromAset(rowId);
    renderDetailRows();
    balance.recalc();
};

window.syncDebitFromAset = function (rowId) {
    const debitRow = detailRows.find(r => r.id === rowId);
    if (!debitRow || !debitRow.aset_rows) return;

    const opsi = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
    const totalCents = debitRow.aset_rows
        .reduce((s, a) => s + Math.round(parseNominal(a.nominal) * 100), 0);
    debitRow.nominal = totalCents > 0 ? (totalCents / 100).toLocaleString('id-ID', opsi) : '';
};

window.updateNominalHidden = function(rowId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row) return;
    const el = document.getElementById('hidden_nominal_' + rowId);
    if (el) el.value = row.nominal ?? '';
};

// Expose formatInput agar bisa dipakai di inline oninput HTML
window.formatInput = formatInput;

// ── Aset yang Dilepas (tipe PELEPASAN_ASET) ─────────────────────────
function fmtRpP(n) { return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID'); }

window.addAsetDilepas = function() {
    asetDilepas.push({ id: asetDilepasCounter++, aset_id: '' });
    renderAsetDilepas();
};

window.removeAsetDilepas = function(id) {
    asetDilepas = asetDilepas.filter(a => a.id !== id);
    renderAsetDilepas();
};

window.updateAsetDilepas = function(id, value) {
    const a = asetDilepas.find(x => x.id === id);
    if (a) a.aset_id = value;
    renderAsetDilepas();
};

function renderAsetDilepas() {
    const c = document.getElementById('asetDilepasRows');
    if (!c) return;
    const used = asetDilepas.map(a => String(a.aset_id)).filter(Boolean);
    c.innerHTML = asetDilepas.map((a, idx) => {
        const opts = asetPelepasanList.map(x => {
            const dis = used.includes(String(x.id)) && String(x.id) !== String(a.aset_id);
            return `<option value="${x.id}" ${String(a.aset_id) === String(x.id) ? 'selected' : ''} ${dis ? 'disabled' : ''}>${x.kode_aset} — ${x.nama_aset}</option>`;
        }).join('');
        const sel = asetPelepasanList.find(x => String(x.id) === String(a.aset_id));
        const info = sel
            ? `<p class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-1.5">Nilai perolehan ${fmtRpP(sel.nilai_tercatat)} · Akumulasi ${fmtRpP(sel.akumulasi_penyusutan)} · <span class="font-semibold">Nilai buku ${fmtRpP(sel.nilai_buku)}</span></p>`
            : '';
        return `
        <div class="rounded-lg bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-800 px-3 py-2">
            <div class="flex items-center gap-2">
                <input type="hidden" name="aset_dilepas[${idx}]" value="${a.aset_id ?? ''}">
                <select onchange="updateAsetDilepas(${a.id}, this.value)"
                        class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-amber-500">
                    <option value="">Pilih aset</option>${opts}
                </select>
                <button type="button" onclick="removeAsetDilepas(${a.id})" class="text-gray-300 hover:text-red-500 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            ${info}
        </div>`;
    }).join('');
}

function renderDetailRows() {
    const container    = document.getElementById('detailRows');
    const tipe         = currentTipe();
    const isPenyusutan = tipe === 'PENYUSUTAN_ASET';

    const usedAsetIds = {};
    detailRows.forEach(row => {
        if (row.aset_rows) usedAsetIds[row.id] = row.aset_rows.map(a => String(a.aset_id)).filter(Boolean);
    });

    container.innerHTML = detailRows.map((row, idx) => {
        const hiddenInputs = `
            <input type="hidden" id="hidden_akun_${row.id}"    name="detail[${idx}][akun_id]" value="${row.akun_id ?? ''}">
            <input type="hidden" id="hidden_tipe_${row.id}"    name="detail[${idx}][tipe]"    value="${row.tipe}">
            <input type="hidden" id="hidden_nominal_${row.id}" name="detail[${idx}][nominal]" value="${row.nominal ?? ''}" data-row-nominal="${row.id}">
        `;

        let asetHiddenInputs = '';
        if (isPenyusutan && row.aset_rows) {
            row.aset_rows.forEach((ar, ai) => {
                asetHiddenInputs += `
                    <input type="hidden" name="detail[${idx}][aset_rows][${ai}][aset_id]" value="${ar.aset_id ?? ''}">
                    <input type="hidden" name="detail[${idx}][aset_rows][${ai}][nominal]" value="${ar.nominal ?? ''}">
                `;
            });
        }

        let asetRowsHtml = '';
        if (isPenyusutan && row.tipe === 'DEBIT' && row.aset_rows !== null) {
            const usedInThisRow = usedAsetIds[row.id] || [];
            const asetRowsContent = row.aset_rows.map(ar => {
                const asetOpts = asetList.map(a => {
                    const isUsedElsewhere = usedInThisRow.includes(String(a.id)) && String(a.id) !== String(ar.aset_id);
                    return `<option value="${a.id}" ${String(ar.aset_id) === String(a.id) ? 'selected' : ''} ${isUsedElsewhere ? 'disabled' : ''}>${a.nama_aset}</option>`;
                }).join('');
                return `
                <div class="flex items-center gap-2 mt-2 pl-4 border-l-2 border-green-200 dark:border-green-800" data-aset-row>
                    <select onchange="onAsetChange(${row.id}, ${ar.id}, this)"
                            class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                        <option value="">Pilih aset</option>${asetOpts}
                    </select>
                    <input type="text" value="${ar.nominal ?? ''}" placeholder="Nominal" data-aset-nominal
                           oninput="formatInput(this); updateAsetRow(${row.id}, ${ar.id}, 'nominal', this.value); syncDebitFromAset(${row.id}); updateNominalHidden(${row.id}); recalcBalance()"
                           class="w-40 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                    <button type="button" onclick="removeAsetRow(${row.id}, ${ar.id})"
                            class="text-gray-300 hover:text-red-500 transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>`;
            }).join('');
            asetRowsHtml = `<div class="mt-2">
                ${asetRowsContent}
                <button type="button" onclick="addAsetRow(${row.id})"
                        class="mt-2 ml-4 text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Aset
                </button>
            </div>`;
        }

        const nominalCell = (isPenyusutan && row.tipe === 'DEBIT')
            ? `<span class="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[80px] inline-block">${row.nominal ? formatRp(parseNominal(row.nominal)) : '—'}</span>`
            : `<input type="text" value="${row.nominal ?? ''}" placeholder="0"
                      oninput="formatInput(this); updateRow(${row.id}, 'nominal', this.value); updateNominalHidden(${row.id}); recalcBalance()"
                      class="w-36 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">`;

        return `
        <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4 bg-gray-50/50 dark:bg-gray-800/30">
            ${hiddenInputs}${asetHiddenInputs}
            <div class="flex items-start gap-3 flex-wrap">
                <span class="w-6 text-sm text-gray-400 pt-2.5">${idx + 1}</span>
                <div class="flex-1 min-w-48">
                    <select onchange="updateRow(${row.id}, 'akun_id', this.value); document.getElementById('hidden_akun_${row.id}').value = this.value"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                        ${currentAkunOptions(row.akun_id)}
                    </select>
                </div>
                <div>
                    <select onchange="updateRow(${row.id}, 'tipe', this.value); document.getElementById('hidden_tipe_${row.id}').value = this.value; renderDetailRows(); balance.recalc()"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                        <option value="DEBIT"  ${row.tipe === 'DEBIT'  ? 'selected' : ''}>Debit</option>
                        <option value="KREDIT" ${row.tipe === 'KREDIT' ? 'selected' : ''}>Kredit</option>
                    </select>
                </div>
                <div class="flex items-center pt-1">${nominalCell}</div>
                <button type="button" onclick="removeDetailRow(${row.id})"
                        class="mt-1.5 text-gray-300 hover:text-red-500 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            ${asetRowsHtml}
        </div>`;
    }).join('');

    balance.recalc();
}

// Expose recalcBalance untuk inline oninput
window.recalcBalance = () => balance.recalc();

// ── Review ──────────────────────────────────────────────────────────────────
function renderReview() {
    const periodeId   = document.querySelector('select[name="periode_id"]').value;
    const periodeNama = periodeList.find(p => p.id == periodeId)?.nama ?? '—';

    document.getElementById('review_periode').textContent    = periodeNama;
    document.getElementById('review_tanggal').textContent    = document.querySelector('input[name="tanggal"]').value;
    document.getElementById('review_tipe').textContent       = tipeLabels[currentTipe()] ?? currentTipe();
    document.getElementById('review_keterangan').textContent = document.querySelector('textarea[name="keterangan"]').value || '—';

    renderReviewRows(detailRows, getAkunLabel);
}

// ── Init ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    FormDraft.init({
        formId: 'jurnalForm',
        storageKey: 'draft_jurnal_penyesuaian',
        getExtraData: () => ({
            detailRows, rowCounter, asetCounter, asetDilepas, asetDilepasCounter,
        }),
        setExtraData: (extra) => {
            detailRows         = extra.detailRows         || [];
            rowCounter          = extra.rowCounter         || 0;
            asetCounter          = extra.asetCounter        || 0;
            asetDilepas          = extra.asetDilepas        || [];
            asetDilepasCounter   = extra.asetDilepasCounter || 0;
        },
        onRestore: (data) => {
            if (data.tipe_penyesuaian) applyTipeButtonStyle(data.tipe_penyesuaian);
            renderDetailRows();
            renderAsetDilepas();
        },
    });

    if (detailRows.length === 0) {
        const oldTipe = '{{ old('tipe_penyesuaian', '') }}';
        if (oldTipe) selectTipe(oldTipe);
        addDetailRow();
        addDetailRow();
    }
});
</script>
@endpush