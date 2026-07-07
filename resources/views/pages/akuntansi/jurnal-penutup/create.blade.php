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

    <x-jurnal.stepper :steps="['Periode & Ringkasan', 'Preview Entri', 'Review & Posting']" />

    {{-- ═══════════════════════════════════════════════════════════════════
         FORM A — Simpan baru (DRAFT atau POSTING) dari Step 1–3
    ════════════════════════════════════════════════════════════════════ --}}
    <form action="{{ route('dashboard.jurnal-penutup.store') }}" method="POST" id="penutupForm">
        @csrf
        <input type="hidden" name="aksi" id="inputAksi" value="draft">

        {{-- ═══ STEP 1 ═══ --}}
        <div id="step1" class="space-y-4">

            <div class="flex items-start gap-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl px-4 py-3 text-sm text-yellow-700 dark:text-yellow-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>
                    <strong>Jurnal penutup mencakup 2 tahap</strong>:
                    (1) Tutup Pendapatan → (2) Tutup Beban.
                    Pastikan semua jurnal umum, penyesuaian, dan koreksi sudah diposting sebelum memulai.
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
                        <input type="date" name="tanggal" id="inputTanggal"
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
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $ringkasan['surplus'] >= 0 ? 'Surplus — akan menambah Aset Neto' : 'Defisit — akan mengurangi Aset Neto' }}
                        </p>
                    </div>
                </div>

                {{-- Banner status kesiapan periode (Opsi B) --}}
                @if($ringkasan['pesan_tidak_siap'])
                <div class="flex items-start gap-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ $ringkasan['pesan_tidak_siap'] }}
                </div>
                @else
                <div class="flex items-center gap-2 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400 mb-4">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Semua jurnal periode {{ $periodeAktif->nama_periode }} sudah diposting. Siap untuk proses penutupan.
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
                next-label="Lanjut ke Preview"
            />
        </div>

        {{-- ═══ STEP 2: Preview Entri (pure display) ═══ --}}
        <div id="step2" class="hidden space-y-4">

            <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Ini adalah preview entri jurnal yang akan dibuat. Belum ada data yang disimpan — posting atau simpan draft dilakukan di langkah berikutnya.
            </div>

            {{-- Tahap 1 --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-600 text-white text-xs font-bold">1</span>
                    Tutup Pendapatan
                </h3>
                <p class="text-xs text-gray-400 mb-4">Akun pendapatan ditutup ke Aset Neto per klasifikasi dana sesuai ISAK 335.</p>
                <div id="previewPendapatanRows" class="space-y-1 mb-4"></div>
                <x-jurnal.balance-bar prefix="prev1" />
            </div>

            {{-- Tahap 2 --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-600 text-white text-xs font-bold">2</span>
                    Tutup Beban
                </h3>
                <p class="text-xs text-gray-400 mb-4">Semua beban ditutup dari Aset Neto Tanpa Pembatasan sesuai ISAK 335.</p>
                <div id="previewBebanRows" class="space-y-1 mb-4"></div>
                <x-jurnal.balance-bar prefix="prev2" />
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

            {{-- Banner: mode existing DRAFT --}}
            <div id="bannerDraftExisting" class="hidden flex items-start gap-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl px-4 py-3 text-sm text-yellow-700 dark:text-yellow-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>
                    Periode ini sudah memiliki jurnal penutup berstatus <strong>Draft</strong>.
                    Klik <strong>Posting Semua</strong> untuk mengubah status menjadi Posted tanpa membuat entri baru.
                    Atau hapus draft terlebih dahulu di halaman index jika ingin membuat ulang.
                </span>
            </div>

            {{-- Banner: mode baru --}}
            <div id="bannerBaru" class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 text-sm text-blue-700 dark:text-blue-400">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Periksa semua entri jurnal. Klik <strong>Posting Semua</strong> untuk langsung memposting, atau <strong>Simpan Draft</strong> jika ingin menyimpan sementara.
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

            {{-- Footer Step 3 --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <button type="button" onclick="goToStep(2)"
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Kembali
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="button" id="btnDraft" onclick="submitAksi('draft')"
                                class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Simpan Draft
                        </button>

                        <button type="button" id="btnPosting" onclick="submitAksi('posting')"
                                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Posting Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

    {{-- ═══ FORM B — Post existing DRAFT ═══ --}}
    <form action="{{ route('dashboard.jurnal-penutup.post-draft') }}" method="POST" id="postDraftForm" class="hidden">
        @csrf
        <input type="hidden" name="periode_id" value="{{ $periodeAktif?->id }}">
    </form>

</div>
@endsection

@push('scripts')
<script type="module">
import { formatRp, makeStepperController } from '/js/jurnal-helpers.js';

// ── Data dari server ───────────────────────────────────────────────────────
const ringkasan     = @json($ringkasan ?? []);
const existingDraft = @json($existingDraft ?? []);

// pesan_tidak_siap: string jika periode belum siap, null jika siap
const pesanTidakSiap = ringkasan.pesan_tidak_siap ?? null;

const TIPE_URUT = ['TUTUP_PENDAPATAN', 'TUTUP_BEBAN'];
const TIPE_LABELS = {
    TUTUP_PENDAPATAN: 'Tutup Pendapatan',
    TUTUP_BEBAN:      'Tutup Beban',
};

/**
 * Mode existing DRAFT:
 * true  → semua tahap sudah ada sebagai DRAFT di DB
 * false → belum ada, buat baru
 */
const adaDraftExisting = Object.keys(existingDraft).length > 0 &&
    TIPE_URUT.every(t => existingDraft[t]);

// ── Generate entri dari ringkasan ──────────────────────────────────────────
function generateEntriTahap(tipe) {
    const pendapatan = ringkasan.pendapatan ?? [];
    const beban      = ringkasan.beban ?? [];
    const detail     = [];

    if (tipe === 'TUTUP_PENDAPATAN') {
        const tanpa  = pendapatan.filter(i => !i.akun.kode_akun.startsWith('4-2') && i.saldo > 0);
        const dengan = pendapatan.filter(i =>  i.akun.kode_akun.startsWith('4-2') && i.saldo > 0);

        tanpa.forEach(i => detail.push({ akun: i.akun.nama_akun, kode: i.akun.kode_akun, posisi: 'DEBIT', nominal: i.saldo }));
        const totalTanpa = tanpa.reduce((s, i) => s + i.saldo, 0);
        if (totalTanpa > 0) detail.push({ akun: 'Aset Neto Tanpa Pembatasan', kode: '3-1000', posisi: 'KREDIT', nominal: totalTanpa });

        dengan.forEach(i => detail.push({ akun: i.akun.nama_akun, kode: i.akun.kode_akun, posisi: 'DEBIT', nominal: i.saldo }));
        const totalDengan = dengan.reduce((s, i) => s + i.saldo, 0);
        if (totalDengan > 0) detail.push({ akun: 'Aset Neto Dengan Pembatasan', kode: '3-2000', posisi: 'KREDIT', nominal: totalDengan });
    }

    if (tipe === 'TUTUP_BEBAN') {
        const totalB = beban.reduce((s, i) => s + i.saldo, 0);
        if (totalB > 0) detail.push({ akun: 'Aset Neto Tanpa Pembatasan', kode: '3-1000', posisi: 'DEBIT', nominal: totalB });
        beban.filter(i => i.saldo > 0).forEach(i => {
            detail.push({ akun: i.akun.nama_akun, kode: i.akun.kode_akun, posisi: 'KREDIT', nominal: i.saldo });
        });
    }

    return detail;
}

// ── Render baris entri ─────────────────────────────────────────────────────
function renderEntriRows(detail, containerId, prefixBalance) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let totalD = 0, totalK = 0;

    const header = `
    <div class="grid grid-cols-12 gap-3 mb-2 px-1 text-xs font-medium text-gray-400">
        <div class="col-span-1">No</div>
        <div class="col-span-6">Akun</div>
        <div class="col-span-2 text-center">Posisi</div>
        <div class="col-span-2 text-right">Debit (Rp)</div>
        <div class="col-span-1 text-right">Kredit (Rp)</div>
    </div>`;

    const rows = detail.map((d, i) => {
        const isD = d.posisi === 'DEBIT';
        if (isD) totalD += d.nominal; else totalK += d.nominal;
        return `
        <div class="grid grid-cols-12 gap-3 items-center py-2 border-b border-gray-50 dark:border-gray-800">
            <div class="col-span-1 text-sm text-gray-400 text-center">${i + 1}</div>
            <div class="col-span-6 text-sm text-gray-700 dark:text-gray-300">
                <span class="text-xs text-gray-400 mr-1">${d.kode ?? ''}</span>${d.akun}
            </div>
            <div class="col-span-2 text-center">
                <span class="text-xs font-bold ${isD ? 'text-red-500' : 'text-green-600'}">${isD ? 'Debit' : 'Kredit'}</span>
            </div>
            <div class="col-span-2 text-right text-sm ${isD  ? 'text-red-500 font-medium' : 'text-gray-300'}">${isD  ? formatRp(d.nominal) : '—'}</div>
            <div class="col-span-1 text-right text-sm ${!isD ? 'text-green-600 font-medium' : 'text-gray-300'}">${!isD ? formatRp(d.nominal) : '—'}</div>
        </div>`;
    }).join('');

    container.innerHTML = header + rows;

    const elD = document.getElementById(prefixBalance + 'TotalDebit');
    const elK = document.getElementById(prefixBalance + 'TotalKredit');
    if (elD) elD.textContent = formatRp(totalD);
    if (elK) elK.textContent = formatRp(totalK);

    const elStatus = document.getElementById(prefixBalance + 'BalanceStatus');
    if (elStatus) {
        const balanced = totalD === totalK && totalD > 0;
        elStatus.className = `flex items-center gap-1.5 text-xs font-medium ${balanced ? 'text-green-600' : 'text-yellow-600'}`;
        elStatus.innerHTML = balanced
            ? `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Seimbang`
            : `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Belum seimbang`;
    }
}

// ── Render review di Step 3 ────────────────────────────────────────────────
function renderReview() {
    const container = document.getElementById('reviewContent');
    container.innerHTML = TIPE_URUT.map(tipe => {
        const detail = generateEntriTahap(tipe);
        const totalD = detail.filter(d => d.posisi === 'DEBIT').reduce((s, d) => s + d.nominal, 0);
        const totalK = detail.filter(d => d.posisi === 'KREDIT').reduce((s, d) => s + d.nominal, 0);

        const rows = detail.map(d => {
            const isD = d.posisi === 'DEBIT';
            return `<tr class="border-b border-gray-50 dark:border-gray-800">
                <td class="py-1.5 text-sm text-gray-700 dark:text-gray-300">
                    <span class="text-xs text-gray-400 mr-1">${d.kode ?? ''}</span>${d.akun}
                </td>
                <td class="py-1.5 text-right text-sm ${isD  ? 'text-red-500 font-medium' : 'text-gray-300'}">${isD  ? formatRp(d.nominal) : '—'}</td>
                <td class="py-1.5 text-right text-sm ${!isD ? 'text-green-600 font-medium' : 'text-gray-300'}">${!isD ? formatRp(d.nominal) : '—'}</td>
            </tr>`;
        }).join('');

        return `
        <div>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">
                Tahap ${TIPE_URUT.indexOf(tipe) + 1}: ${TIPE_LABELS[tipe]}
            </p>
            <table class="w-full text-sm mb-3">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-1.5 text-left text-xs font-medium text-gray-400">Akun</th>
                        <th class="pb-1.5 text-right text-xs font-medium text-gray-400">Debit</th>
                        <th class="pb-1.5 text-right text-xs font-medium text-gray-400">Kredit</th>
                    </tr>
                </thead>
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

// ── Stepper ────────────────────────────────────────────────────────────────
const stepper = makeStepperController(3);
window.goToStep = (n) => stepper.goToStep(n);

window.goToStep2 = function() {
    // Guard client-side: periode belum siap
    if (pesanTidakSiap) {
        alert(pesanTidakSiap);
        return;
    }

    stepper.goToStep(2);
    renderEntriRows(generateEntriTahap('TUTUP_PENDAPATAN'), 'previewPendapatanRows', 'prev1');
    renderEntriRows(generateEntriTahap('TUTUP_BEBAN'),      'previewBebanRows',      'prev2');
};

window.goToStep3 = function() {
    renderReview();
    stepper.goToStep(3);

    if (adaDraftExisting) {
        document.getElementById('bannerDraftExisting').classList.remove('hidden');
        document.getElementById('bannerBaru').classList.add('hidden');
        document.getElementById('btnDraft').classList.add('hidden');
    }
};

// ── Submit ─────────────────────────────────────────────────────────────────
window.submitAksi = function(aksi) {
    if (aksi === 'posting' && adaDraftExisting) {
        document.getElementById('postDraftForm').submit();
        return;
    }

    document.getElementById('inputAksi').value = aksi;
    document.getElementById('penutupForm').submit();
};

// ── Init ───────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (adaDraftExisting) {
        renderReview();
        stepper.goToStep(3);
        document.getElementById('bannerDraftExisting').classList.remove('hidden');
        document.getElementById('bannerBaru').classList.add('hidden');
        document.getElementById('btnDraft').classList.add('hidden');
    }
});
</script>
@endpush