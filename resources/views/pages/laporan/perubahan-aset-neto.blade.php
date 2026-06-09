@extends('layouts.app')

@section('title', 'Laporan Perubahan Aset Neto')

@php
    function fmtPan($val) { return number_format(abs($val), 0, ',', '.'); }
    function signedPan($val) {
        return $val < 0 ? '(' . fmtPan($val) . ')' : fmtPan($val);
    }
@endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Perubahan Aset Neto</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.laporan.penghasilan-komprehensif') }}"
               class="text-sm text-gray-600 dark:text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Penghasilan Komprehensif
            </a>
            <a href="{{ route('dashboard.laporan.posisi-keuangan') }}"
               class="text-sm text-gray-600 dark:text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Posisi Keuangan
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php $kenaikan = ($data['totalSaldoAkhir'] ?? 0) - ($data['totalSaldoAwal'] ?? 0); @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Aset Neto Awal</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmtPan($data['totalSaldoAwal'] ?? 0) }}</p>
            <p class="text-xs mt-1 text-gray-400">{{ $periodePrev?->nama_periode ?? 'Sebelum periode ini' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kenaikan Periode Ini</p>
            <p class="text-2xl font-bold {{ $kenaikan >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                {{ $kenaikan >= 0 ? '+' : '' }}Rp {{ fmtPan($kenaikan) }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Aset Neto Akhir</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmtPan($data['totalSaldoAkhir'] ?? 0) }}</p>
            <p class="text-xs mt-1 text-gray-400">{{ $periode?->nama_periode ?? '—' }}</p>
        </div>
    </div>

    {{-- Filter & Report --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm text-gray-500 dark:text-gray-400">Pilih Periode</label>
                <select name="periode_id" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 min-w-[180px]">
                    <option value="">-- Semua Periode --</option>
                    @foreach($periodeList as $p)
                        <option value="{{ $p->id }}" {{ $selectedPeriodeId == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="p-6">
            {{-- Report Header --}}
            <div class="text-center mb-6">
                <p class="text-sm font-bold text-green-700 dark:text-green-500 uppercase tracking-wider">Masjid Luqmanul Hakim</p>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">Laporan Perubahan Aset Neto</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Untuk Periode yang Berakhir {{ $periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—' }}
                </p>
                <span class="inline-block mt-2 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1 rounded-full">
                    ISAK 35 — Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/2">Uraian</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $periode?->nama_periode ?? 'Periode Ini' }}
                            </th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $periodePrev?->nama_periode ?? 'Periode Lalu' }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">

                        {{-- ══════════════════════════════════════════════════════
                             BLOK 1: ASET NETO TANPA PEMBATASAN DARI PEMBERI SUMBER DAYA
                             Mengikuti urutan vertikal contoh ilustratif ISAK 35:
                               Saldo awal
                               Surplus tahun berjalan
                               Aset neto dibebaskan dari pembatasan
                               Saldo akhir
                        ══════════════════════════════════════════════════════ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Aset Neto Tanpa Pembatasan dari Pemberi Sumber Daya
                                </span>
                            </td>
                        </tr>

                        {{-- Saldo Awal --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Saldo awal</td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ signedPan($data['saldoAwalTanpa'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>

                        {{-- Surplus Tahun Berjalan (bold italic, sesuai contoh ISAK 35) --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 font-semibold italic text-gray-700 dark:text-gray-300">Surplus tahun berjalan</td>
                            <td class="px-4 py-2.5 text-right font-semibold italic {{ ($data['surplusTanpa'] ?? 0) >= 0 ? 'text-gray-700 dark:text-gray-300' : 'text-red-500 dark:text-red-400' }}">
                                {{ signedPan($data['surplusTanpa'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>

                        {{-- Aset Neto Dibebaskan dari Pembatasan --}}
                        @if(($data['dibebaskan'] ?? 0) != 0)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                Aset neto yang dibebaskan dari pembatasan
                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">(catatan C)</span>
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ signedPan($data['dibebaskan'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>
                        @endif

                        {{-- Saldo Akhir Tanpa Pembatasan --}}
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Saldo akhir</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">{{ signedPan($data['saldoAkhirTanpa'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">—</td>
                        </tr>

                        {{-- ══════════════════════════════════════════════════════
                             BLOK 2: PENGHASILAN KOMPREHENSIF LAIN
                             (hanya tampil jika ada nilainya)
                        ══════════════════════════════════════════════════════ --}}
                        @if(($data['pkl'] ?? 0) != 0)
                        <tr><td colspan="3" class="py-2"></td></tr>
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider italic">
                                    Penghasilan Komprehensif Lain
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Saldo awal</td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">—</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Penghasilan komprehensif tahun berjalan</td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ signedPan($data['pkl'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Saldo akhir</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">{{ signedPan($data['pkl'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">—</td>
                        </tr>
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">
                                {{ signedPan(($data['saldoAkhirTanpa'] ?? 0) + ($data['pkl'] ?? 0)) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">—</td>
                        </tr>
                        @endif

                        {{-- Spacer antar blok --}}
                        <tr><td colspan="3" class="py-2"></td></tr>

                        {{-- ══════════════════════════════════════════════════════
                             BLOK 3: ASET NETO DENGAN PEMBATASAN DARI PEMBERI SUMBER DAYA
                               Saldo awal
                               Surplus tahun berjalan (pendapatan terikat)
                               Aset neto dibebaskan dari pembatasan (dalam kurung = pengurang)
                               Saldo akhir
                        ══════════════════════════════════════════════════════ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Aset Neto Dengan Pembatasan dari Pemberi Sumber Daya
                                </span>
                            </td>
                        </tr>

                        {{-- Saldo Awal --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Saldo awal</td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ signedPan($data['saldoAwalDengan'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>

                        {{-- Surplus Dengan Pembatasan (pendapatan terikat) --}}
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 font-semibold italic text-gray-700 dark:text-gray-300">Surplus tahun berjalan</td>
                            <td class="px-4 py-2.5 text-right font-semibold italic {{ ($data['surplusDengan'] ?? 0) >= 0 ? 'text-gray-700 dark:text-gray-300' : 'text-red-500 dark:text-red-400' }}">
                                {{ signedPan($data['surplusDengan'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>

                        {{-- Aset Neto Dibebaskan — dalam kurung karena mengurangi saldo ini --}}
                        @if(($data['dibebaskan'] ?? 0) != 0)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                Aset neto yang dibebaskan dari pembatasan
                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">(catatan C)</span>
                            </td>
                            <td class="px-4 py-2.5 text-right text-red-500 dark:text-red-400">({{ fmtPan($data['dibebaskan'] ?? 0) }})</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>
                        @endif

                        {{-- Saldo Akhir Dengan Pembatasan --}}
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Saldo akhir</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">{{ signedPan($data['saldoAkhirDengan'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">—</td>
                        </tr>

                        {{-- Spacer --}}
                        <tr><td colspan="3" class="py-1"></td></tr>

                        {{-- ══ TOTAL ASET NETO ══ --}}
                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">Total Aset Neto</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ signedPan($data['totalSaldoAkhir'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">—</td>
                        </tr>

                    </tbody>
                </table>
            </div>

            {{-- Catatan --}}
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl text-xs text-gray-500 dark:text-gray-400 space-y-1.5">
                <p class="font-semibold text-gray-600 dark:text-gray-300 mb-2">Catatan:</p>
                <p><strong>Tanpa Pembatasan</strong> — sumber daya yang diterima tanpa syarat penggunaan dari pemberi (akun 4-1xxx). Semua beban operasional dan penyusutan dibebankan ke klasifikasi ini.</p>
                <p><strong>Dengan Pembatasan</strong> — sumber daya yang diterima dengan syarat penggunaan tertentu dari pemberi (akun 4-2xxx), mencakup zakat, qurban, wakaf, dan donasi pembangunan.</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>@media print { nav, .no-print { display: none !important; } body { background: white; } }</style>
@endpush
@endsection
