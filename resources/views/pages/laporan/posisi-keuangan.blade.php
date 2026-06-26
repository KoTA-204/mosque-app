@extends('layouts.app')

@section('title', 'Laporan Posisi Keuangan')

@php
    if (!function_exists('fmt2')) {
        function fmt2($val) { return number_format(abs($val), 0, ',', '.'); }
    }
    if (!function_exists('signed2')) {
        function signed2($val) {
            return $val < 0 ? '(' . fmt2($val) . ')' : fmt2($val);
        }
    }
@endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    @include('pages.laporan.partials.nav', ['active' => 'posisi-keuangan'])

    {{-- Summary Cards --}}
    <div class="no-print grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Aset</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmt2($data['jumlahAset'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Liabilitas</p>
            <p class="text-2xl font-bold text-red-500 dark:text-red-400">Rp {{ fmt2($data['jumlahLiabilitas'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Aset Neto</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">Rp {{ fmt2($data['jumlahAsetNeto'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Filter & Report --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden" id="print-area">
        <div class="no-print flex items-center gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800">
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
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1">Laporan Posisi Keuangan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Per {{ $periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—' }}
                </p>
                <span class="inline-block mt-2 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1 rounded-full">
                    ISAK 335 — Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba
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

                        {{-- ══ ASET ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Aset</span>
                            </td>
                        </tr>

                        {{-- Aset Lancar — dinamis dari CoA (1-1xxx) --}}
                        <tr><td colspan="3" class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aset Lancar</span></td></tr>
                        @forelse($data['rincianAsetLancar'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt2($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['rincianAsetLancar'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmt2($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada aset lancar</td></tr>
                        @endforelse
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-2 pl-8 font-semibold text-gray-700 dark:text-gray-300">Jumlah Aset Lancar</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">{{ fmt2($data['jumlahAsetLancar'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">{{ fmt2($dataPrev['jumlahAsetLancar'] ?? 0) }}</td>
                        </tr>

                        {{-- Aset Tetap — dinamis dari CoA (1-2xxx) --}}
                        {{-- Akun dengan saldo_normal KREDIT (akumulasi) ditampilkan negatif --}}
                        <tr><td colspan="3" class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aset Tetap</span></td></tr>
                        @forelse($data['rincianAsetTetap'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 {{ $row->is_akumulasi ? 'pl-10 text-gray-500 dark:text-gray-500 italic text-xs' : 'pl-8 text-gray-600 dark:text-gray-400' }}">
                                {{ $row->nama_akun }}
                            </td>
                            <td class="px-4 py-2 text-right {{ $row->is_akumulasi ? 'text-red-400 dark:text-red-500 text-xs' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $row->is_akumulasi ? '(' . fmt2($row->saldo) . ')' : fmt2($row->saldo) }}
                            </td>
                            <td class="px-4 py-2 text-right {{ $row->is_akumulasi ? 'text-red-400 text-xs' : 'text-gray-500 dark:text-gray-500' }}">
                                @php $pr = collect($dataPrev['rincianAsetTetap'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                @if($pr)
                                    {{ $pr->is_akumulasi ? '(' . fmt2($pr->saldo) . ')' : fmt2($pr->saldo) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada aset tetap</td></tr>
                        @endforelse
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-2 pl-8 font-semibold text-gray-700 dark:text-gray-300">Jumlah Aset Tetap</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">{{ fmt2($data['jumlahAsetTetap'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">{{ fmt2($dataPrev['jumlahAsetTetap'] ?? 0) }}</td>
                        </tr>

                        {{-- Total Aset --}}
                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">Jumlah Aset</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ fmt2($data['jumlahAset'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">{{ fmt2($dataPrev['jumlahAset'] ?? 0) }}</td>
                        </tr>

                        <tr><td colspan="3" class="py-2"></td></tr>

                        {{-- ══ LIABILITAS — dinamis per grup dari CoA (2-xxxx) ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Liabilitas</span>
                            </td>
                        </tr>
                        @foreach($data['grupLiabilitas'] ?? [] as $grup)
                        <tr><td colspan="3" class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $grup->nama_akun }}</span></td></tr>
                        @foreach($grup->rincian as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt2($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php
                                    $prGrup = collect($dataPrev['grupLiabilitas'] ?? [])->firstWhere('kode_akun', $grup->kode_akun);
                                    $pr = $prGrup ? collect($prGrup->rincian)->firstWhere('kode_akun', $row->kode_akun) : null;
                                @endphp
                                {{ $pr ? fmt2($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                        @if(empty($data['grupLiabilitas']) || count($data['grupLiabilitas']) == 0)
                        <tr><td colspan="3" class="px-4 py-2 pl-8 text-gray-400 italic text-xs">Tidak ada liabilitas</td></tr>
                        @endif

                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">Jumlah Liabilitas</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ fmt2($data['jumlahLiabilitas'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">{{ fmt2($dataPrev['jumlahLiabilitas'] ?? 0) }}</td>
                        </tr>

                        <tr><td colspan="3" class="py-2"></td></tr>

                        {{-- ══ ASET NETO ══ --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/30">
                            <td colspan="3" class="px-4 py-3">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Aset Neto</span>
                            </td>
                        </tr>

                        {{-- Tanpa Pembatasan (3-1xxx) — dinamis --}}
                        <tr><td colspan="3" class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tanpa Pembatasan dari Pemberi Sumber Daya</span></td></tr>
                        @foreach($data['rincianAsetNetoTanpa'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt2($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['rincianAsetNetoTanpa'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmt2($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-2 pl-8 font-semibold text-gray-700 dark:text-gray-300">Jumlah Aset Neto Tanpa Pembatasan</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">{{ fmt2($data['asetNetoTanpaPembatasan'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">{{ fmt2($dataPrev['asetNetoTanpaPembatasan'] ?? 0) }}</td>
                        </tr>

                        {{-- Dengan Pembatasan (3-2xxx) — dinamis --}}
                        <tr><td colspan="3" class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Dengan Pembatasan dari Pemberi Sumber Daya</span></td></tr>
                        @forelse($data['rincianAsetNetoDengan'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="px-4 py-2 pl-8 text-gray-600 dark:text-gray-400">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt2($row->saldo) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500 dark:text-gray-500">
                                @php $pr = collect($dataPrev['rincianAsetNetoDengan'] ?? [])->firstWhere('kode_akun', $row->kode_akun); @endphp
                                {{ $pr ? fmt2($pr->saldo) : '—' }}
                            </td>
                        </tr>
                        @empty
                        @endforelse
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-2 pl-8 font-semibold text-gray-700 dark:text-gray-300">Jumlah Aset Neto Dengan Pembatasan</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800 dark:text-gray-200">{{ fmt2($data['asetNetoDenganPembatasan'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-400">{{ fmt2($dataPrev['asetNetoDenganPembatasan'] ?? 0) }}</td>
                        </tr>

                        {{-- Total Aset Neto --}}
                        <tr class="bg-green-700 dark:bg-green-800">
                            <td class="px-4 py-3 font-bold text-white uppercase tracking-wide text-sm">Jumlah Aset Neto</td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ fmt2($data['jumlahAsetNeto'] ?? 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-200">{{ fmt2($dataPrev['jumlahAsetNeto'] ?? 0) }}</td>
                        </tr>

                        {{-- Jumlah Liabilitas + Aset Neto (harus = Jumlah Aset) --}}
                        @php
                            $check = ($data['jumlahLiabilitas'] ?? 0) + ($data['jumlahAsetNeto'] ?? 0);
                            $checkPrev = ($dataPrev['jumlahLiabilitas'] ?? 0) + ($dataPrev['jumlahAsetNeto'] ?? 0);
                        @endphp
                        <tr class="border-t-2 border-green-700 dark:border-green-600">
                            <td class="px-4 py-3 font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide text-sm">Jumlah Liabilitas dan Aset Neto</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-100">{{ fmt2($check) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-600 dark:text-gray-400">{{ fmt2($checkPrev) }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>

            {{-- Balance check --}}
            @if(round($data['jumlahAset'] ?? 0) !== round($check))
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400">
                Perhatian: Total Aset (Rp {{ fmt2($data['jumlahAset'] ?? 0) }}) ≠ Liabilitas + Aset Neto (Rp {{ fmt2($check) }}). Periksa jurnal pembuka atau jurnal penutup.
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
