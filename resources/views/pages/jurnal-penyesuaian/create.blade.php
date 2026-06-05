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

    {{-- Error --}}
    @if($errors->any())
    <div class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Stepper --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center">
            @foreach([1 => 'Informasi & Detail', 2 => 'Review & Simpan'] as $n => $label)
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-colors
                    {{ $n === 1 ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}"
                     id="step-circle-{{ $n }}">{{ $n }}</div>
                <div>
                    <p class="text-xs text-gray-400">Langkah {{ $n }}</p>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400" id="step-label-{{ $n }}">{{ $label }}</p>
                </div>
            </div>
            @if($n < 2)
            <div class="mx-4 flex-1 border-t-2 border-gray-100 dark:border-gray-800 transition-colors" id="step-line-{{ $n }}"></div>
            @endif
            @endforeach
        </div>
    </div>

    <form action="{{ route('dashboard.jurnal-penyesuaian.store') }}" method="POST" id="jurnalForm">
        @csrf

        {{-- ═══ STEP 1: Informasi Umum + Detail Jurnal ═══ --}}
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Jurnal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal"
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Periode <span class="text-red-500">*</span>
                        </label>
                        <select name="periode_id"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            @foreach($periodeList as $periode)
                            <option value="{{ $periode->id }}"
                                {{ ($periodeAktif && $periodeAktif->id == $periode->id) || old('periode_id') == $periode->id ? 'selected' : '' }}>
                                {{ $periode->nama_periode }}
                            </option>
                            @endforeach
                        </select>
                    </div>
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

                <div id="detailRows" class="space-y-3 mb-4"></div>

                <button type="button" onclick="addDetailRow()"
                        class="w-full rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 py-3 text-sm font-medium text-green-600 hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors">
                    + Tambah Baris
                </button>

                {{-- Ringkasan balance --}}
                <div class="mt-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 p-4 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</span>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Debit</p>
                            <p id="totalDebit" class="text-sm font-bold text-red-500">Rp 0</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Kredit</p>
                            <p id="totalKredit" class="text-sm font-bold text-green-600">Rp 0</p>
                        </div>
                        <div id="balanceStatus" class="flex items-center gap-1.5 text-xs font-medium text-yellow-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Belum seimbang
                        </div>
                    </div>
                </div>
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

            {{-- Footer step 1 --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <span class="text-xs text-gray-400">Langkah 1 dari 2</span>
                <button type="button" onclick="goToStep2()"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                    Lanjut ke Review
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
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
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="pb-2 text-left text-xs font-medium text-gray-400">Akun</th>
                                    <th class="pb-2 text-right text-xs font-medium text-gray-400">Debit</th>
                                    <th class="pb-2 text-right text-xs font-medium text-gray-400">Kredit</th>
                                </tr>
                            </thead>
                            <tbody id="reviewBody"></tbody>
                        </table>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3 text-center">
                                <p class="text-xs text-gray-400">Total Debit</p>
                                <p id="review_total_debit" class="text-base font-bold text-red-500">Rp 0</p>
                            </div>
                            <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3 text-center">
                                <p class="text-xs text-gray-400">Total Kredit</p>
                                <p id="review_total_kredit" class="text-base font-bold text-green-600">Rp 0</p>
                            </div>
                        </div>
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

            {{-- Footer step 2 --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
                <span class="text-xs text-gray-400">Langkah 2 dari 2</span>
                <div class="flex gap-3">
                    <button type="button" onclick="goToStep(1)"
                            class="inline-flex items-center gap-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Kembali
                    </button>
                    <button type="submit" name="submit_type" value="draft"
                            class="border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                        Simpan sebagai Draft
                    </button>
                    <button type="submit" name="submit_type" value="posting"
                            id="btnPosting"
                            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan & Posting
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Data dari server ───────────────────────────────────────────────────────
const akunPerTipe = @json($akunPerTipe);
const tipeLabels  = @json($tipeLabels);
const asetList    = @json($asetList);
const periodeList = @json($periodeList->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama_periode]));

let currentStep = 1;
let detailRows  = [];
let rowCounter  = 0;
let asetCounter = 0;

// ── Helpers ────────────────────────────────────────────────────────────────

const parseNominal = val =>
    parseFloat((String(val || '0')).replace(/\./g, '').replace(',', '.')) || 0;

const formatRp = n =>
    'Rp ' + parseFloat(n || 0).toLocaleString('id-ID');

function formatInput(input) {
    const raw = input.value.replace(/\D/g, '');
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}

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

// ── Stepper ────────────────────────────────────────────────────────────────

function goToStep(n) {
    document.getElementById('step' + currentStep).classList.add('hidden');
    document.getElementById('step' + n).classList.remove('hidden');
    currentStep = n;
    updateStepperUI();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateStepperUI() {
    for (let i = 1; i <= 2; i++) {
        const circle = document.getElementById('step-circle-' + i);
        if (i < currentStep) {
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
            circle.innerHTML = '✓';
        } else if (i === currentStep) {
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-green-600 text-white transition-colors';
            circle.textContent = i;
        } else {
            circle.className = 'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors';
            circle.textContent = i;
        }
        if (i < 2) {
            const line = document.getElementById('step-line-' + i);
            if (line) {
                line.className = i < currentStep
                    ? 'mx-4 flex-1 border-t-2 border-green-500 transition-colors'
                    : 'mx-4 flex-1 border-t-2 border-gray-100 dark:border-gray-800 transition-colors';
            }
        }
    }
}

// ── Step 1 → Step 2 (Review) ───────────────────────────────────────────────

function goToStep2() {
    if (!currentTipe()) {
        alert('Pilih jenis penyesuaian terlebih dahulu.');
        return;
    }
    const ket = document.querySelector('textarea[name="keterangan"]').value.trim();
    if (!ket) {
        alert('Keterangan jurnal wajib diisi.');
        return;
    }
    if (detailRows.length < 2) {
        alert('Minimal harus ada 2 baris detail jurnal.');
        return;
    }
    const { debit, kredit } = calcTotal();
    if (Math.round(debit * 100) !== Math.round(kredit * 100)) {
        alert('Total debit dan kredit harus sama sebelum melanjutkan.');
        return;
    }
    renderReview();
    goToStep(2);
}

// ── Tipe Penyesuaian ───────────────────────────────────────────────────────

function selectTipe(key) {
    document.getElementById('tipe_penyesuaian').value = key;

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
    if (btn) {
        btn.classList.remove('border-gray-200', 'dark:border-gray-700');
        btn.classList.add('border-green-600', 'bg-green-50', 'dark:bg-green-900/20');
    }
    if (icon) {
        icon.classList.remove('text-gray-400');
        icon.classList.add('text-green-600');
    }

    detailRows = [];
    rowCounter = 0;
    renderDetailRows();
}

// ── Detail Rows ────────────────────────────────────────────────────────────

function addDetailRow() {
    const isPenyusutan = currentTipe() === 'PENYUSUTAN_ASET';
    detailRows.push({
        id:        rowCounter++,
        akun_id:   '',
        tipe:      detailRows.length === 0 ? 'DEBIT' : 'KREDIT',
        nominal:   '',
        aset_rows: isPenyusutan ? [] : null,
    });
    renderDetailRows();
}

function removeDetailRow(id) {
    detailRows = detailRows.filter(r => r.id !== id);
    renderDetailRows();
    recalcBalance();
}

function updateRow(id, field, value) {
    const row = detailRows.find(r => r.id === id);
    if (row) row[field] = value;
}

function addAsetRow(rowId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    row.aset_rows.push({ id: asetCounter++, aset_id: '', nominal: '' });
    renderDetailRows();
    recalcBalance();
}

function removeAsetRow(rowId, asetId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    row.aset_rows = row.aset_rows.filter(a => a.id !== asetId);
    syncDebitFromAset(rowId);
    renderDetailRows();
    recalcBalance();
}

function updateAsetRow(rowId, asetId, field, value) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    const asetRow = row.aset_rows.find(a => a.id === asetId);
    if (asetRow) asetRow[field] = value;
}

function onAsetChange(rowId, asetId, selectEl) {
    updateAsetRow(rowId, asetId, 'aset_id', selectEl.value);
    if (!selectEl.value) {
        syncDebitFromAset(rowId);
        renderDetailRows();
        recalcBalance();
        return;
    }
    const aset = asetList.find(a => a.id == selectEl.value);
    if (!aset) return;
    const container = selectEl.closest('[data-aset-row]');
    if (container) {
        const nominalInput = container.querySelector('[data-aset-nominal]');
        if (nominalInput && !nominalInput.value) {
            const perBulan = parseFloat(aset.penyusutan_per_bulan || 0);
            const formatted = perBulan > 0 ? perBulan.toLocaleString('id-ID') : '';
            nominalInput.value = formatted;
            updateAsetRow(rowId, asetId, 'nominal', formatted);
        }
    }
    syncDebitFromAset(rowId);
    renderDetailRows();
    recalcBalance();
}

function syncDebitFromAset(rowId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row || !row.aset_rows) return;
    const total = row.aset_rows.reduce((sum, a) => sum + parseNominal(a.nominal), 0);
    row.nominal = total > 0 ? total.toLocaleString('id-ID') : '';
}

function renderDetailRows() {
    const container    = document.getElementById('detailRows');
    const tipe         = currentTipe();
    const isPenyusutan = tipe === 'PENYUSUTAN_ASET';

    const usedAsetIds = {};
    detailRows.forEach(row => {
        if (row.aset_rows) {
            usedAsetIds[row.id] = row.aset_rows.map(a => String(a.aset_id)).filter(Boolean);
        }
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
            const asetRowsContent = row.aset_rows.map((ar) => {
                const asetOpts = asetList.map(a => {
                    const isUsedElsewhere = usedInThisRow.includes(String(a.id)) && String(a.id) !== String(ar.aset_id);
                    const disabled = isUsedElsewhere ? 'disabled' : '';
                    const selected = String(ar.aset_id) === String(a.id) ? 'selected' : '';
                    return `<option value="${a.id}" ${selected} ${disabled}>${a.nama_aset}</option>`;
                }).join('');

                return `
                <div class="flex items-center gap-2 mt-2 pl-4 border-l-2 border-green-200 dark:border-green-800" data-aset-row>
                    <select onchange="onAsetChange(${row.id}, ${ar.id}, this)"
                            class="flex-1 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                        <option value="">Pilih aset</option>
                        ${asetOpts}
                    </select>
                    <input type="text"
                           value="${ar.nominal ?? ''}"
                           placeholder="Nominal"
                           data-aset-nominal
                           oninput="formatInput(this); updateAsetRow(${row.id}, ${ar.id}, 'nominal', this.value); syncDebitFromAset(${row.id}); updateNominalHidden(${row.id}); recalcBalance()"
                           class="w-40 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                    <button type="button" onclick="removeAsetRow(${row.id}, ${ar.id})"
                            class="text-gray-300 hover:text-red-500 transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>`;
            }).join('');

            asetRowsHtml = `
                <div class="mt-2">
                    ${asetRowsContent}
                    <button type="button" onclick="addAsetRow(${row.id})"
                            class="mt-2 ml-4 text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Aset
                    </button>
                </div>`;
        }

        const nominalCell = (isPenyusutan && row.tipe === 'DEBIT')
            ? `<span class="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[80px] inline-block">
                   ${row.nominal ? formatRp(parseNominal(row.nominal)) : '—'}
               </span>`
            : `<input type="text"
                      value="${row.nominal ?? ''}"
                      placeholder="0"
                      oninput="formatInput(this); updateRow(${row.id}, 'nominal', this.value); updateNominalHidden(${row.id}); recalcBalance()"
                      class="w-36 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">`;

        return `
        <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4 bg-gray-50/50 dark:bg-gray-800/30">
            ${hiddenInputs}
            ${asetHiddenInputs}
            <div class="flex items-start gap-3 flex-wrap">
                <span class="w-6 text-sm text-gray-400 pt-2.5">${idx + 1}</span>
                <div class="flex-1 min-w-48">
                    <select onchange="updateRow(${row.id}, 'akun_id', this.value); document.getElementById('hidden_akun_${row.id}').value = this.value"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                        ${currentAkunOptions(row.akun_id)}
                    </select>
                </div>
                <div>
                    <select onchange="updateRow(${row.id}, 'tipe', this.value); document.getElementById('hidden_tipe_${row.id}').value = this.value; renderDetailRows(); recalcBalance()"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500">
                        <option value="DEBIT"  ${row.tipe === 'DEBIT'  ? 'selected' : ''}>Debit</option>
                        <option value="KREDIT" ${row.tipe === 'KREDIT' ? 'selected' : ''}>Kredit</option>
                    </select>
                </div>
                <div class="flex items-center pt-1">
                    ${nominalCell}
                </div>
                <button type="button" onclick="removeDetailRow(${row.id})"
                        class="mt-1.5 text-gray-300 hover:text-red-500 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
            ${asetRowsHtml}
        </div>`;
    }).join('');

    recalcBalance();
}

function updateNominalHidden(rowId) {
    const row = detailRows.find(r => r.id === rowId);
    if (!row) return;
    const el = document.getElementById('hidden_nominal_' + rowId);
    if (el) el.value = row.nominal ?? '';
}

// ── Balance ────────────────────────────────────────────────────────────────

function calcTotal() {
    let debit = 0, kredit = 0;
    detailRows.forEach(row => {
        const n = parseNominal(row.nominal);
        if (row.tipe === 'DEBIT')  debit  += n;
        if (row.tipe === 'KREDIT') kredit += n;
    });
    return { debit, kredit };
}

function recalcBalance() {
    const { debit, kredit } = calcTotal();
    document.getElementById('totalDebit').textContent  = formatRp(debit);
    document.getElementById('totalKredit').textContent = formatRp(kredit);

    const status   = document.getElementById('balanceStatus');
    const balanced = debit > 0 && Math.round(debit * 100) === Math.round(kredit * 100);
    if (balanced) {
        status.className = 'flex items-center gap-1.5 text-xs font-medium text-green-600';
        status.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Seimbang`;
    } else {
        status.className = 'flex items-center gap-1.5 text-xs font-medium text-yellow-600';
        status.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Belum seimbang`;
    }
}

// ── Review ─────────────────────────────────────────────────────────────────

function renderReview() {
    const periodeId   = document.querySelector('select[name="periode_id"]').value;
    const periodeNama = periodeList.find(p => p.id == periodeId)?.nama ?? '—';

    document.getElementById('review_periode').textContent    = periodeNama;
    document.getElementById('review_tanggal').textContent    = document.querySelector('input[name="tanggal"]').value;
    document.getElementById('review_tipe').textContent       = tipeLabels[currentTipe()] ?? currentTipe();
    document.getElementById('review_keterangan').textContent = document.querySelector('textarea[name="keterangan"]').value || '—';

    const { debit, kredit } = calcTotal();
    document.getElementById('review_total_debit').textContent  = formatRp(debit);
    document.getElementById('review_total_kredit').textContent = formatRp(kredit);
    document.getElementById('btnPosting').disabled = Math.round(debit * 100) !== Math.round(kredit * 100);

    function getAkunLabel(akunId) {
        if (!akunId) return '—';
        const tipe   = currentTipe();
        const groups = akunPerTipe[tipe] || [];
        for (const group of groups) {
            const found = group.akun.find(a => String(a.id) === String(akunId));
            if (found) return found.label;
        }
        return '—';
    }

    const tbody = document.getElementById('reviewBody');
    tbody.innerHTML = detailRows.map(row => {
        const n = parseNominal(row.nominal);
        return `
            <tr class="border-b border-gray-50 dark:border-gray-800">
                <td class="py-2 text-gray-700 dark:text-gray-300 text-sm">${getAkunLabel(row.akun_id)}</td>
                <td class="py-2 text-right text-sm ${row.tipe === 'DEBIT'  ? 'text-red-500 font-medium'   : 'text-gray-300'}">${row.tipe === 'DEBIT'  ? formatRp(n) : '—'}</td>
                <td class="py-2 text-right text-sm ${row.tipe === 'KREDIT' ? 'text-green-600 font-medium' : 'text-gray-300'}">${row.tipe === 'KREDIT' ? formatRp(n) : '—'}</td>
            </tr>`;
    }).join('');
}

// ── Init ───────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    const oldTipe = '{{ old('tipe_penyesuaian', '') }}';
    if (oldTipe) {
        selectTipe(oldTipe);
    }
    // Render 2 baris default
    addDetailRow();
    addDetailRow();
});
</script>
@endpush