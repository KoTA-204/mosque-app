@extends('layouts.app')

@section('title', 'Proses Penutupan Periode')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Proses Penutupan Periode</h1>
        @if($periodeAktif)
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Periode aktif: {{ $periodeAktif->nama_periode }}</p>
        @endif
    </div>

    <x-jurnal.error-banner />

    <x-jurnal.stepper :steps="['Periode & Ringkasan', 'Proses Penutupan', 'Review & Posting']" />

    <form action="{{ route('dashboard.jurnal-penutup.store') }}" method="POST" id="penutupForm">
        @csrf

        {{-- ═══ STEP 1 ═══ --}}
        <div id="step1" class="space-y-4">

            <div class="flex items-start gap-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl px-4 py-3 text-sm text-yellow-700 dark:text-yellow-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>
                    <strong>Jurnal penutup harus dibuat secara berurutan</strong>:
                    (1) Tutup Pendapatan → (2) Tutup Beban.
                    Pastikan semua jurnal penyesuaian sudah diposting sebelum memulai.
                </span>
            </div>

            {{-- Informasi Periode --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/>
                    </svg>
                    Informasi Periode
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Periode yang Ditutup <span class="text-red-500">*</span>
                        </label>
                        <select name="periode_id" id="periodeSelect"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            @foreach($periodeList as $p)
                            <option value="{{ $p->id }}" {{ $periodeAktif && $periodeAktif->id == $p->id ? 'selected' : '' }}>
                                {{ $p->nama_periode }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Penutupan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal"
                               value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    </div>
                </div>
            </div>

            {{-- Ringkasan Saldo Periode --}}
            @if($ringkasan)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Ringkasan Saldo Periode
                    </h3>
                    <p class="text-xs text-gray-400">Data dari neraca saldo setelah penyesuaian</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4">
                        <p class="text-xs text-gray-400 mb-1">Total Pendapatan</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($ringkasan['total_pendapatan'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $ringkasan['pendapatan']->count() }} akun pendapatan</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4">
                        <p class="text-xs text-gray-400 mb-1">Total Beban</p>
                        <p class="text-xl font-bold text-red-500">Rp {{ number_format($ringkasan['total_beban'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $ringkasan['beban']->count() }} akun beban</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-4">
                        <p class="text-xs text-gray-400 mb-1">Surplus / Defisit</p>
                        <p class="text-xl font-bold {{ $ringkasan['surplus'] >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            Rp {{ number_format(abs($ringkasan['surplus']), 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $ringkasan['surplus'] >= 0 ? 'Surplus — akan ditutup ke Aset Neto' : 'Defisit' }}</p>
                    </div>
                </div>

                @if($ringkasan['ada_draft_belum_posting'])
                <div class="flex items-start gap-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Masih ada jurnal penyesuaian yang belum diposting. Posting semua jurnal penyesuaian terlebih dahulu sebelum melanjutkan penutupan.
                </div>
                @else
                <div class="flex items-center gap-2 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400 mb-4">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Semua jurnal penyesuaian periode {{ $periodeAktif->nama_periode }} sudah diposting. Siap untuk proses penutupan.
                </div>
                @endif

                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Rincian Akun yang Akan Ditutup</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="pb-2 text-left text-xs font-medium text-gray-400">Akun</th>
                            <th class="pb-2 text-center text-xs font-medium text-gray-400">Tipe</th>
                            <th class="pb-2 text-right text-xs font-medium text-gray-400">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($ringkasan['pendapatan'] as $item)
                        <tr>
                            <td class="py-2 text-gray-700 dark:text-gray-300">
                                <span class="text-xs text-gray-400 mr-1">{{ $item['akun']->kode_akun }}</span>{{ $item['akun']->nama_akun }}
                            </td>
                            <td class="py-2 text-center">
                                <span class="text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded-full">Pendapatan</span>
                            </td>
                            <td class="py-2 text-right font-medium text-green-600">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        @foreach($ringkasan['beban'] as $item)
                        <tr>
                            <td class="py-2 text-gray-700 dark:text-gray-300">
                                <span class="text-xs text-gray-400 mr-1">{{ $item['akun']->kode_akun }}</span>{{ $item['akun']->nama_akun }}
                            </td>
                            <td class="py-2 text-center">
                                <span class="text-xs font-medium text-red-600 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full">Beban</span>
                            </td>
                            <td class="py-2 text-right font-medium text-red-500">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <x-jurnal.form-footer
                :step="1" :total="3"
                :back-route="route('dashboard.jurnal-penutup.index')"
                next-action="goToStep2()"
                next-label="Lanjut ke Detail"
            />

        </div>

        {{-- ═══ STEP 2: Proses Penutupan ═══ --}}
        <div id="step2" class="hidden space-y-4">

            @php
            $tahapDefs = [
                'TUTUP_PENDAPATAN' => [
                    'label' => 'Tutup Pendapatan',
                    'sub'   => 'Menutup semua akun pendapatan ke Aset Neto',
                    'entri' => 'Debit semua akun Pendapatan → Kredit Aset Neto (3-1xxx / 3-2xxx)',
                ],
                'TUTUP_BEBAN' => [
                    'label' => 'Tutup Beban',
                    'sub'   => 'Menutup semua akun beban dari Aset Neto Tanpa Pembatasan',
                    'entri' => 'Debit Aset Neto Tanpa Pembatasan (3-1xxx) → Kredit semua akun Beban',
                ],
            ];
            @endphp

            {{-- Progress --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-400" id="progressLabel">Tahap 1 dari 2</p>
                    <p class="text-xs font-medium text-green-600" id="progressPct">0%</p>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 mb-4">
                    <div id="progressBar" class="bg-green-600 h-1.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($tahapDefs as $tipeKey => $tahap)
                    @php $stTahap = $statusTahap[$tipeKey] ?? ['selesai' => false]; $idxTahap = array_search($tipeKey, array_keys($tahapDefs)); @endphp
                    <div class="rounded-xl border p-3 cursor-pointer transition-colors
                        {{ $stTahap['selesai'] ? 'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/10' : 'border-gray-100 dark:border-gray-800' }}"
                         id="tahap-card-{{ $tipeKey }}"
                         onclick="selectTahap('{{ $tipeKey }}')">
                        <div class="flex items-center gap-1.5 mb-1">
                            <div id="tahap-icon-{{ $tipeKey }}"
                                 class="flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold
                                 {{ $stTahap['selesai'] ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                                @if($stTahap['selesai'])
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $idxTahap + 1 }}
                                @endif
                            </div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $stTahap['selesai'] ? 'Selesai' : 'Belum' }}</span>
                        </div>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $tahap['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $tahap['sub'] }}</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1 italic">{{ $tahap['entri'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Panel detail tahap aktif --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6" id="tahapDetailPanel">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10m0-10a2 2 0 012 2h2a2 2 0 012-2"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white" id="tahapDetailTitle">Pilih tahap di atas</h3>
                </div>

                <div id="tahapInfoBanner" class="hidden rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-800 px-4 py-3 text-sm text-green-700 dark:text-green-400 mb-4"></div>

                <div id="tahapEntriContainer" class="hidden">
                    <div class="grid grid-cols-12 gap-3 mb-2 px-1 text-xs font-medium text-gray-400">
                        <div class="col-span-1">No</div>
                        <div class="col-span-6">Akun</div>
                        <div class="col-span-2 text-center">Posisi</div>
                        <div class="col-span-2 text-right">Debit (Rp)</div>
                        <div class="col-span-1 text-right">Kredit (Rp)</div>
                    </div>
                    <div id="tahapEntriRows" class="space-y-1 mb-4"></div>

                    <x-jurnal.balance-bar prefix="tahap" />

                    <div class="mt-4">
                        <button type="button" id="btnKonfirmasi" onclick="konfirmasiTahap()"
                                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Konfirmasi & Lanjut ke Tahap Berikutnya
                        </button>
                    </div>
                </div>

                <div id="tahapEmptyState" class="py-8 text-center text-sm text-gray-400">
                    Klik salah satu tahap di atas untuk melihat detail entri jurnal
                </div>
            </div>

            <x-jurnal.form-footer
                :step="2" :total="3"
                back-action="goToStep(1)"
                next-action="goToStep3()"
                next-label="Lanjut ke Review"
            />
        </div>

        {{-- ═══ STEP 3: Review & Posting ═══ --}}
        <div id="step3" class="hidden space-y-4">

            <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Semua 2 tahap jurnal penutup sudah digenerate. Klik "Posting Semua" untuk memposting seluruh jurnal penutup sekaligus ke buku besar.
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Review Semua Jurnal Penutup
                </h3>
                <div id="reviewContent" class="space-y-6"></div>
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
import { formatRp, makeStepperController } from '/js/jurnal-helpers.js';

const ringkasan         = @json($ringkasan ?? []);
const statusTahapServer = @json($statusTahap ?? []);

const TIPE_URUT = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN'];
const TIPE_LABELS = {
    TUTUP_PENDAPATAN: 'Tutup Pendapatan',
    TUTUP_BEBAN:      'Tutup Beban',
};
const TIPE_INFO = {
    TUTUP_PENDAPATAN: 'Debit semua akun pendapatan → Kredit Aset Neto sesuai klasifikasi dana (3-1xxx untuk tidak terikat, 3-2xxx untuk terikat). Sesuai ISAK 35.',
    TUTUP_BEBAN:      'Debit Aset Neto Tanpa Pembatasan (3-1xxx) → Kredit semua akun beban. Dana terikat tidak digunakan untuk menutup beban operasional.',
};

// ── Session state ──────────────────────────────────────────────────────────
const periodeId  = document.getElementById('periodeSelect')?.value ?? '';
const SESSION_KEY = 'penutup_' + periodeId;

function saveState(step) {
    sessionStorage.setItem(SESSION_KEY, JSON.stringify({
        step,
        tahapSelesai,
        entriPerTahap,
    }));
}

function loadState() {
    try { return JSON.parse(sessionStorage.getItem(SESSION_KEY)); }
    catch { return null; }
}

function clearState() {
    sessionStorage.removeItem(SESSION_KEY);
}

// ── Init state ─────────────────────────────────────────────────────────────
const savedState = loadState();

const entriPerTahap = savedState?.entriPerTahap ?? {};
let   currentTipe   = null;
const tahapSelesai  = savedState?.tahapSelesai ?? {};

// Fallback ke server jika tidak ada state tersimpan
TIPE_URUT.forEach(t => {
    if (tahapSelesai[t] === undefined) {
        tahapSelesai[t] = statusTahapServer[t]?.selesai ?? false;
    }
});

// ── Stepper ────────────────────────────────────────────────────────────────
const stepper = makeStepperController(3);
window.goToStep = (n) => {
    stepper.goToStep(n);
    saveState(n);
};

window.goToStep2 = function() {
    stepper.goToStep(2);
    saveState(2);
    updateProgressBar();
    const pertama = TIPE_URUT.find(t => !tahapSelesai[t]) ?? TIPE_URUT[0];
    selectTahap(pertama);
};

window.goToStep3 = function() {
    const belumSelesai = TIPE_URUT.filter(t => !tahapSelesai[t] && !entriPerTahap[t]);
    if (belumSelesai.length > 0) {
        alert('Konfirmasi semua ' + belumSelesai.length + ' tahap terlebih dahulu sebelum melanjutkan.');
        return;
    }
    renderReview();
    stepper.goToStep(3);
    saveState(3);
};

window.selectTahap = function(tipe) {
    currentTipe = tipe;
    const detail = generateEntriTahap(tipe);
    entriPerTahap[tipe] = entriPerTahap[tipe] || detail;

    document.getElementById('tahapDetailTitle').textContent = 'Tahap ' + (TIPE_URUT.indexOf(tipe) + 1) + ': ' + TIPE_LABELS[tipe];
    document.getElementById('tahapInfoBanner').textContent  = TIPE_INFO[tipe];
    document.getElementById('tahapInfoBanner').classList.remove('hidden');
    document.getElementById('tahapEntriContainer').classList.remove('hidden');
    document.getElementById('tahapEmptyState').classList.add('hidden');

    renderEntriRows(entriPerTahap[tipe]);

    const btnK = document.getElementById('btnKonfirmasi');
    if (tahapSelesai[tipe]) {
        btnK.disabled    = true;
        btnK.textContent = '✓ Tahap ini sudah selesai';
        btnK.className   = 'inline-flex items-center gap-2 bg-gray-200 dark:bg-gray-700 text-gray-500 text-sm font-medium px-5 py-2.5 rounded-xl cursor-not-allowed';
    } else {
        btnK.disabled  = false;
        btnK.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Konfirmasi & Lanjut ke Tahap Berikutnya`;
        btnK.className = 'inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors';
    }
};

function generateEntriTahap(tipe) {
    const pendapatan = ringkasan.pendapatan ?? [];
    const beban      = ringkasan.beban ?? [];
    const detail     = [];

    if (tipe === 'TUTUP_PENDAPATAN') {
        const tanpa  = pendapatan.filter(i => !i.akun.kode_akun.startsWith('4-2'));
        const dengan = pendapatan.filter(i =>  i.akun.kode_akun.startsWith('4-2'));

        tanpa.forEach(i => { if (i.saldo > 0) detail.push({ akun: i.akun.nama_akun, posisi: 'DEBIT', nominal: i.saldo }); });
        const totalTanpa = tanpa.reduce((s, i) => s + i.saldo, 0);
        if (totalTanpa > 0) detail.push({ akun: 'Aset Neto Tanpa Pembatasan (3-1000)', posisi: 'KREDIT', nominal: totalTanpa });

        dengan.forEach(i => { if (i.saldo > 0) detail.push({ akun: i.akun.nama_akun, posisi: 'DEBIT', nominal: i.saldo }); });
        const totalDengan = dengan.reduce((s, i) => s + i.saldo, 0);
        if (totalDengan > 0) detail.push({ akun: 'Aset Neto Dengan Pembatasan (3-2000)', posisi: 'KREDIT', nominal: totalDengan });
    }

    if (tipe === 'TUTUP_BEBAN') {
        const totalB = beban.reduce((s, i) => s + i.saldo, 0);
        if (totalB > 0) detail.push({ akun: 'Aset Neto Tanpa Pembatasan (3-1000)', posisi: 'DEBIT', nominal: totalB });
        beban.forEach(i => { if (i.saldo > 0) detail.push({ akun: i.akun.nama_akun, posisi: 'KREDIT', nominal: i.saldo }); });
    }

    return detail;
}

function renderEntriRows(detail) {
    const container = document.getElementById('tahapEntriRows');
    let totalD = 0, totalK = 0;
    container.innerHTML = detail.map((d, i) => {
        const isD = d.posisi === 'DEBIT';
        if (isD) totalD += d.nominal; else totalK += d.nominal;
        return `
        <div class="grid grid-cols-12 gap-3 items-center py-2 border-b border-gray-50 dark:border-gray-800">
            <div class="col-span-1 text-sm text-gray-400 text-center">${i + 1}</div>
            <div class="col-span-6 text-sm text-gray-700 dark:text-gray-300">${d.akun}</div>
            <div class="col-span-2 text-center"><span class="text-xs font-bold ${isD ? 'text-red-500' : 'text-green-600'}">${isD ? 'Debit' : 'Kredit'}</span></div>
            <div class="col-span-2 text-right text-sm ${isD  ? 'text-red-500 font-medium' : 'text-gray-300'}">${isD  ? formatRp(d.nominal) : '—'}</div>
            <div class="col-span-1 text-right text-sm ${!isD ? 'text-green-600 font-medium' : 'text-gray-300'}">${!isD ? formatRp(d.nominal) : '—'}</div>
        </div>`;
    }).join('');

    const elD = document.getElementById('tahapTotalDebit');
    const elK = document.getElementById('tahapTotalKredit');
    if (elD) elD.textContent = formatRp(totalD);
    if (elK) elK.textContent = formatRp(totalK);

    const elStatus = document.getElementById('tahapBalanceStatus');
    if (elStatus) {
        const balanced = totalD === totalK && totalD > 0;
        elStatus.className = `flex items-center gap-1.5 text-xs font-medium ${balanced ? 'text-green-600' : 'text-yellow-600'}`;
        elStatus.innerHTML = balanced
            ? `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Seimbang`
            : `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Belum seimbang`;
    }
}

// ── Konfirmasi tahap — hanya update state FE, tidak hit DB ────────────────
window.konfirmasiTahap = function() {
    if (!currentTipe) return;

    tahapSelesai[currentTipe] = true;
    updateTahapCardUI(currentTipe);
    updateProgressBar();
    saveState(stepper.currentStep);

    const idx = TIPE_URUT.indexOf(currentTipe);
    if (idx < TIPE_URUT.length - 1) {
        selectTahap(TIPE_URUT[idx + 1]);
    } else {
        const btn = document.getElementById('btnKonfirmasi');
        btn.disabled    = true;
    }
};

function updateTahapCardUI(tipe) {
    const card = document.getElementById('tahap-card-' + tipe);
    const icon = document.getElementById('tahap-icon-' + tipe);
    if (card) card.classList.add('border-green-200', 'dark:border-green-800', 'bg-green-50', 'dark:bg-green-900/10');
    if (icon) {
        icon.className = 'flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold bg-green-600 text-white';
        icon.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
    }
}

function updateProgressBar() {
    const selesai = TIPE_URUT.filter(t => tahapSelesai[t]).length;
    const pct     = Math.round((selesai / 2) * 100);
    document.getElementById('progressBar').style.width   = pct + '%';
    document.getElementById('progressPct').textContent   = pct + '%';
    document.getElementById('progressLabel').textContent = 'Tahap ' + selesai + ' dari 2';
}

function renderReview() {
    const container = document.getElementById('reviewContent');
    container.innerHTML = TIPE_URUT.map(tipe => {
        const detail = entriPerTahap[tipe] ?? generateEntriTahap(tipe);
        const totalD = detail.filter(d => d.posisi === 'DEBIT').reduce((s, d) => s + d.nominal, 0);
        const totalK = detail.filter(d => d.posisi === 'KREDIT').reduce((s, d) => s + d.nominal, 0);
        const rows   = detail.map(d => {
            const isD = d.posisi === 'DEBIT';
            return `<tr class="border-b border-gray-50 dark:border-gray-800">
                <td class="py-1.5 text-sm text-gray-700 dark:text-gray-300">${d.akun}</td>
                <td class="py-1.5 text-right text-sm ${isD  ? 'text-red-500 font-medium' : 'text-gray-300'}">${isD  ? formatRp(d.nominal) : '—'}</td>
                <td class="py-1.5 text-right text-sm ${!isD ? 'text-green-600 font-medium' : 'text-gray-300'}">${!isD ? formatRp(d.nominal) : '—'}</td>
            </tr>`;
        }).join('');
        return `
        <div>
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tahap ${TIPE_URUT.indexOf(tipe) + 1}: ${TIPE_LABELS[tipe]}</p>
                <span class="text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">Generate ✓</span>
            </div>
            <table class="w-full text-sm mb-3">
                <thead><tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="pb-1.5 text-left text-xs font-medium text-gray-400">Akun</th>
                    <th class="pb-1.5 text-right text-xs font-medium text-gray-400">Debit</th>
                    <th class="pb-1.5 text-right text-xs font-medium text-gray-400">Kredit</th>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3 text-center">
                    <p class="text-xs text-gray-400">Total Debit</p>
                    <p class="text-sm font-bold text-red-500">${formatRp(totalD)}</p>
                </div>
                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3 text-center">
                    <p class="text-xs text-gray-400">Total Kredit</p>
                    <p class="text-sm font-bold text-green-600">${formatRp(totalK)}</p>
                </div>
            </div>
        </div>`;
    }).join('<hr class="border-gray-100 dark:border-gray-800">');
}

// ── Restore state saat load ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    updateProgressBar();

    if (savedState?.step && savedState.step > 1) {
        if (savedState.step === 2) {
            stepper.goToStep(2);
            updateProgressBar();
            // restore tahap card UI
            TIPE_URUT.forEach(t => {
                if (tahapSelesai[t]) updateTahapCardUI(t);
            });
            const pertama = TIPE_URUT.find(t => !tahapSelesai[t]) ?? TIPE_URUT[0];
            selectTahap(pertama);
        } else if (savedState.step === 3) {
            stepper.goToStep(3);
            renderReview();
        }
    }

    // Clear state setelah form submit
    document.getElementById('penutupForm').addEventListener('submit', () => {
        clearState();
    });
});
</script>
@endpush