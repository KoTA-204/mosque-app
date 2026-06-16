@extends('layouts.app')

@section('title', 'Laporan Penghasilan Komprehensif')

@php
    if (!function_exists('fmt')) {
        function fmt($val) { return number_format(abs($val), 0, ',', '.'); }
    }
    if (!function_exists('signed')) {
        function signed($val) {
            return $val < 0 ? '(' . fmt($val) . ')' : fmt($val);
        }
    }
@endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Penghasilan Komprehensif</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.laporan.posisi-keuangan') }}"
               class="text-sm text-gray-600 dark:text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Posisi Keuangan
            </a>
            <a href="{{ route('dashboard.laporan.perubahan-aset-neto') }}"
               class="text-sm text-gray-600 dark:text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Perubahan Aset Neto
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
        $totalPend = ($data['pendapatanTanpaPembatasan'] ?? 0) + ($data['pendapatanDenganPembatasan'] ?? 0);
        $totalPendPrev = $dataPrev ? (($dataPrev['pendapatanTanpaPembatasan'] ?? 0) + ($dataPrev['pendapatanDenganPembatasan'] ?? 0)) : 0;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Pendapatan</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmt($totalPend) }}</p>
            @if($dataPrev && $totalPendPrev > 0)
                @php $sel = $totalPend - $totalPendPrev; $pct = round(($sel / $totalPendPrev) * 100); @endphp
                <p class="text-xs mt-1 {{ $sel >= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $sel >= 0 ? '▲' : '▼' }} {{ abs($pct) }}% vs {{ $periodePrev->nama_periode }}
                </p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Beban</p>
            <p class="text-2xl font-bold text-red-500 dark:text-red-400">Rp {{ fmt($data['jumlahBeban'] ?? 0) }}</p>
            @if($dataPrev && ($dataPrev['jumlahBeban'] ?? 0) > 0)
                @php $sel = ($data['jumlahBeban'] ?? 0) - ($dataPrev['jumlahBeban'] ?? 0); $pct = round(($sel / $dataPrev['jumlahBeban']) * 100); @endphp
                <p class="text-xs mt-1 {{ $sel <= 0 ? 'text-green-500' : 'text-red-500' }}">
                    {{ $sel >= 0 ? '▲' : '▼' }} {{ abs($pct) }}% vs {{ $periodePrev->nama_periode }}
                </p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Surplus / (Defisit)</p>
            <p class="text-2xl font-bold {{ ($data['surplusDefisit'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                Rp {{ fmt($data['surplusDefisit'] ?? 0) }}
            </p>
        </div>
    </div>

    {{-- Filter --}}
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
            <div class="text-center mb-6">
                <p class="text-sm font-bold text-green-700 dark:text-green-500 uppercase tracking-wider">Masjid Luqmanul Hakim</p>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">Laporan Penghasilan Komprehensif</h2>
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

                        {{-- ══ TANPA PEMBATASAN ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Tanpa Pembatasan dari Pemberi Sumber Daya
                                </span>
                            </td>
                        </tr>

                        {{-- Pendapatan Tanpa Pembatasan — dinamis --}}
                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pendapatan</span>
                            </td>
                        </tr>
                        @forelse($data['rincianTanpaPembatasan'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['rincianTanpaPembatasan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmt($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada pendapatan pada periode ini</td></tr>
                        @endforelse
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2 pl-8 font-semibold text-gray-700 dark:text-gray-300">Total Pendapatan</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">{{ fmt($data['pendapatanTanpaPembatasan'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">{{ fmt($dataPrev['pendapatanTanpaPembatasan'] ?? 0) }}</td>
                        </tr>

                        {{-- Beban — dinamis per grup dari CoA --}}
                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Beban</span>
                            </td>
                        </tr>
                        @foreach($data['grupBeban'] ?? [] as $grup)
                        {{-- Sub-header grup beban --}}
                        <tr>
                            <td colspan="3" class="px-4 pt-2 pb-0.5 pl-8">
                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">{{ $grup->nama_akun }}</span>
                            </td>
                        </tr>
                        @foreach($grup->rincian as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-12 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php
                                    $prGrup = collect($dataPrev['grupBeban'] ?? [])->firstWhere('kode_akun', $grup->kode_akun);
                                    $pr = $prGrup ? collect($prGrup->rincian)->firstWhere('kode_akun', $row->kode_akun) : null;
                                @endphp
                                {{ $pr ? fmt($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                        @endforeach

                        @if(($data['jumlahBeban'] ?? 0) == 0)
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada beban pada periode ini</td></tr>
                        @endif

                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2 pl-8 font-semibold text-gray-700 dark:text-gray-300">Total Beban</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">({{ fmt($data['jumlahBeban'] ?? 0) }})</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">({{ fmt($dataPrev['jumlahBeban'] ?? 0) }})</td>
                        </tr>

                        {{-- Surplus Tanpa Pembatasan --}}
                        <tr class="bg-green-50 dark:bg-green-900/10 border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-semibold text-gray-800 dark:text-gray-200">Surplus (Defisit) Tanpa Pembatasan</td>
                            <td class="px-4 py-2.5 text-right font-semibold {{ ($data['surplusTanpaPembatasan'] ?? 0) >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-500' }}">
                                {{ signed($data['surplusTanpaPembatasan'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">
                                {{ signed($dataPrev['surplusTanpaPembatasan'] ?? 0) }}
                            </td>
                        </tr>

                        <tr><td colspan="3" class="py-1"></td></tr>

                        {{-- ══ DENGAN PEMBATASAN ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    Dengan Pembatasan dari Pemberi Sumber Daya
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-4 pt-3 pb-1">
                                <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pendapatan</span>
                            </td>
                        </tr>
                        @forelse($data['rincianDenganPembatasan'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['rincianDenganPembatasan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmt($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada pendapatan terikat pada periode ini</td></tr>
                        @endforelse
                        <tr class="bg-green-50 dark:bg-green-900/10 border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2.5 font-semibold text-gray-800 dark:text-gray-200">Surplus (Defisit) Dengan Pembatasan</td>
                            <td class="px-4 py-2.5 text-right font-semibold {{ ($data['surplusDenganPembatasan'] ?? 0) >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-500' }}">
                                {{ signed($data['surplusDenganPembatasan'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-600 dark:text-gray-400">
                                {{ signed($dataPrev['surplusDenganPembatasan'] ?? 0) }}
                            </td>
                        </tr>

                        <tr><td colspan="3" class="py-1"></td></tr>

                        {{-- Surplus/Defisit Gabungan --}}
                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">Surplus / (Defisit) Periode Berjalan</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ signed($data['surplusDefisit'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">{{ signed($dataPrev['surplusDefisit'] ?? 0) }}</td>
                        </tr>

                        <tr><td colspan="3" class="py-1"></td></tr>


                        {{-- Total Komprehensif --}}
                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">Total Penghasilan Komprehensif Periode Berjalan</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ signed($data['totalKomprehensif'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">{{ signed($dataPrev['totalKomprehensif'] ?? 0) }}</td>
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
