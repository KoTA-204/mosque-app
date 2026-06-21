@extends('layouts.app')
@section('title', 'Laporan Arus Kas')

@php
    if (!function_exists('fmtAk')) {
        function fmtAk($val) { return number_format(abs($val), 0, ',', '.'); }
    }
    if (!function_exists('signedAk')) {
        function signedAk($val) {
            return $val < 0 ? '(' . fmtAk($val) . ')' : fmtAk($val);
        }
    }
@endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Arus Kas</h1>
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
    @php
        $totalMasuk = ($data['penerimaanOperasional']->sum('saldo') ?? 0)
                    + ($data['penerimaanPendanaan']->sum('saldo') ?? 0)
                    + ($data['penerimaanInvestasi']->sum('saldo') ?? 0);

        $totalKeluar = ($data['pengeluaranOperasional']->sum('saldo') ?? 0)
                     + ($data['pengeluaranInvestasi']->sum('saldo') ?? 0)
                     + ($data['penyaluranPendanaan']->sum('saldo') ?? 0);

        $totalMasukPrev = $dataPrev
            ? ($dataPrev['penerimaanOperasional']->sum('saldo')
             + $dataPrev['penerimaanPendanaan']->sum('saldo')
             + $dataPrev['penerimaanInvestasi']->sum('saldo'))
            : 0;

        $totalKeluarPrev = $dataPrev
            ? ($dataPrev['pengeluaranOperasional']->sum('saldo')
             + $dataPrev['pengeluaranInvestasi']->sum('saldo')
             + $dataPrev['penyaluranPendanaan']->sum('saldo'))
            : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Total Arus Kas Masuk --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Arus Kas Masuk</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmtAk($totalMasuk) }}</p>
            @if($dataPrev && $totalMasukPrev > 0)
                @php $selisih = $totalMasuk - $totalMasukPrev; @endphp
                <p class="text-xs mt-1 {{ $selisih >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $selisih >= 0 ? '▲' : '▼' }} Rp {{ fmtAk($selisih) }} dari periode sebelumnya
                </p>
            @endif
        </div>

        {{-- Total Arus Kas Keluar --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Arus Kas Keluar</p>
            <p class="text-2xl font-bold text-red-500 dark:text-red-400">Rp {{ fmtAk($totalKeluar) }}</p>
            @if($dataPrev && $totalKeluarPrev > 0)
                @php $selisih = $totalKeluar - $totalKeluarPrev; @endphp
                <p class="text-xs mt-1 {{ $selisih <= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $selisih >= 0 ? '▲' : '▼' }} Rp {{ fmtAk($selisih) }} dari periode sebelumnya
                </p>
            @endif
        </div>

        {{-- Kas Neto --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kas Neto Periode Ini</p>
            @php $neto = $data['kenaikanNeto'] ?? 0; @endphp
            <p class="text-2xl font-bold {{ $neto >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                Rp {{ fmtAk($neto) }}
            </p>
            @if($dataPrev)
                @php $selisih = $neto - ($dataPrev['kenaikanNeto'] ?? 0); @endphp
                <p class="text-xs mt-1 {{ $selisih >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $selisih >= 0 ? '▲' : '▼' }} Rp {{ fmtAk($selisih) }} dari periode sebelumnya
                </p>
            @endif
        </div>

        {{-- Saldo Kas Akhir --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Saldo Kas Akhir Periode</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmtAk($data['kasAkhir'] ?? 0) }}</p>
            <p class="text-xs mt-1 text-gray-400">Per {{ $periode?->tanggal_akhir->translatedFormat('d M Y') ?? '—' }}</p>
        </div>
    </div>

    {{-- Filter & Report Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">

        {{-- Filter --}}
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
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">Laporan Arus Kas</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Per {{ $periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—' }}
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

                        {{-- ══ AKTIVITAS OPERASIONAL ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Aktivitas Operasional
                                </span>
                            </td>
                        </tr>

                        {{-- Penerimaan Kas --}}
                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Penerimaan Kas</span>
                            </td>
                        </tr>
                        @forelse($data['penerimaanOperasional'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmtAk($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['penerimaanOperasional'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmtAk($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada penerimaan operasional</td></tr>
                        @endforelse

                        {{-- Pengeluaran Kas --}}
                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pengeluaran Kas</span>
                            </td>
                        </tr>
                        @forelse($data['pengeluaranOperasional'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-red-500 dark:text-red-400">({{ fmtAk($row->saldo) }})</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['pengeluaranOperasional'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? '(' . fmtAk($pr->saldo) . ')' : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada pengeluaran operasional</td></tr>
                        @endforelse

                        {{-- Kas Neto Operasional --}}
                        <tr class="border-t border-gray-200 dark:border-gray-700 bg-green-50 dark:bg-green-900/10">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Kas Neto dari Aktivitas Operasi</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ ($data['kasNetoOperasional'] ?? 0) >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedAk($data['kasNetoOperasional'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">
                                {{ $dataPrev ? signedAk($dataPrev['kasNetoOperasional'] ?? 0) : '—' }}
                            </td>
                        </tr>

                        <tr><td colspan="3" class="py-2"></td></tr>

                        {{-- ══ AKTIVITAS INVESTASI ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Aktivitas Investasi
                                </span>
                            </td>
                        </tr>

                        {{-- Penerimaan dari Penjualan Aset --}}
                        @foreach($data['penerimaanInvestasi'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmtAk($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">—</td>
                        </tr>
                        @endforeach

                        {{-- Pengeluaran Investasi (pembelian aset) --}}
                        @foreach($data['pengeluaranInvestasi'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-red-500 dark:text-red-400">({{ fmtAk($row->saldo) }})</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['pengeluaranInvestasi'] ?? [])->first(); @endphp
                                {{ $pr ? '(' . fmtAk($pr->saldo) . ')' : '—' }}
                            </td>
                        </tr>
                        @endforeach

                        @if(empty($data['penerimaanInvestasi']) && empty($data['pengeluaranInvestasi']))
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada aktivitas investasi</td></tr>
                        @endif

                        <tr class="border-t border-gray-200 dark:border-gray-700 bg-green-50 dark:bg-green-900/10">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Kas Neto untuk Investasi</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ ($data['kasNetoInvestasi'] ?? 0) >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedAk($data['kasNetoInvestasi'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">
                                {{ $dataPrev ? signedAk($dataPrev['kasNetoInvestasi'] ?? 0) : '—' }}
                            </td>
                        </tr>

                        <tr><td colspan="3" class="py-2"></td></tr>

                        {{-- ══ AKTIVITAS PENDANAAN ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Aktivitas Pendanaan
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Penerimaan dari Sumbangan Terikat untuk:</span>
                            </td>
                        </tr>
                        @forelse($data['penerimaanPendanaan'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmtAk($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['penerimaanPendanaan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmtAk($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada penerimaan dana terikat</td></tr>
                        @endforelse

                        @if(($data['penyaluranPendanaan'] ?? collect())->count() > 0)
                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Penyaluran Dana Terikat untuk:</span>
                            </td>
                        </tr>
                        @foreach($data['penyaluranPendanaan'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-red-500 dark:text-red-400">({{ fmtAk($row->saldo) }})</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['penyaluranPendanaan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? '(' . fmtAk($pr->saldo) . ')' : '—' }}
                            </td>
                        </tr>
                        @endforeach
                        @endif

                        <tr class="border-t border-gray-200 dark:border-gray-700 bg-green-50 dark:bg-green-900/10">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Kas Neto dari Aktivitas Pendanaan</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ ($data['kasNetoPendanaan'] ?? 0) >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedAk($data['kasNetoPendanaan'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">
                                {{ $dataPrev ? signedAk($dataPrev['kasNetoPendanaan'] ?? 0) : '—' }}
                            </td>
                        </tr>

                        <tr><td colspan="3" class="py-2"></td></tr>

                        {{-- ══ REKONSILIASI ══ --}}
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Kenaikan Neto Kas dan Setara Kas</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">{{ signedAk($data['kenaikanNeto'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-600 dark:text-gray-400">
                                {{ $dataPrev ? signedAk($dataPrev['kenaikanNeto'] ?? 0) : '—' }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Kas dan setara kas pada awal periode</td>
                            <td class="px-4 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ fmtAk($data['kasAwal'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-500">
                                {{ $dataPrev ? fmtAk($dataPrev['kasAwal'] ?? 0) : '—' }}
                            </td>
                        </tr>

                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">KAS DAN SETARA KAS AKHIR PERIODE</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ fmtAk($data['kasAkhir'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">
                                {{ $dataPrev ? fmtAk($dataPrev['kasAkhir'] ?? 0) : '—' }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>@media print { nav, .no-print { display: none !important; } body { background: white; } }</style>
@endpush
@endsection