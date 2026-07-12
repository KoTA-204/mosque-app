@extends('layouts.app')

@section('title', 'Catatan Atas Laporan Keuangan (CALK)')

@php
    if (!function_exists('fmtC')) {
        function fmtC($val) { return number_format(abs((float)$val), 2, ',', '.'); }
    }
    if (!function_exists('signedC')) {
        function signedC($val) {
            $val = (float)$val;
            return $val < 0 ? '(Rp ' . fmtC($val) . ')' : 'Rp ' . fmtC($val);
        }
    }
@endphp

@section('content')
<div class="p-6 space-y-6">

    {{-- ══ PAGE HEADER ══ --}}
    @include('pages.laporan.partials.nav', ['active' => 'calk', 'printOnclick' => 'cetakLaporan()'])

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        {{-- ══ FILTER BAR ══ --}}
        <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-gray-100 dark:border-gray-800 no-print">
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

        {{-- ══ DOKUMEN CALK ══ --}}
        <div class="p-6" id="calkDokumen">

            {{-- KOP --}}
            <div class="text-center mb-8">
                <p class="text-sm font-bold text-green-700 dark:text-green-500 uppercase tracking-widest">Masjid Luqmanul Hakim</p>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-1 uppercase tracking-wide">
                    Catatan Atas Laporan Keuangan (CALK)
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Untuk Periode yang Berakhir
                    {{ $periode ? $periode->tanggal_akhir->translatedFormat('d F Y') : '—' }}
                </p>
                <span class="inline-block mt-2 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1 rounded-full">
                    ISAK 335 — Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba
                </span>
            </div>

            @if(!$periode)
            <div class="text-center py-16 text-gray-400">
                <p class="text-base">Pilih periode terlebih dahulu untuk menampilkan CALK.</p>
            </div>
            @else

            {{-- ══ CATATAN 1 — INFORMASI UMUM ══ --}}
            <div class="catatan-section mb-8" id="catatan-1">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">1. Informasi Umum</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                    Masjid Luqmanul Hakim merupakan entitas berorientasi nonlaba yang didirikan pada tanggal
                    10 Januari 2010 dan beralamat di Jl. Moilati No. 10, Kecamatan Ilir Barat I, Kota Palembang.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    Masjid ini bergerak dalam kegiatan pelayanan ibadah, dakwah, pendidikan, dan sosial kemasyarakatan.
                </p>
                <table class="info-table text-sm w-full max-w-lg">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @php
                            $infoUmum = [
                                'Pendirian'  => '10 Januari 2010',
                                'Legalitas'  => 'Akta Pendirian No. 08 Tanggal 10 Januari 2010',
                                'Ketua DKM'  => 'H. Abdul Latif, SE., MM',
                                'Sekretaris' => 'M. Ridwan, S.Pd',
                                'Bendahara'  => 'Narul Hidayah',
                            ];
                        @endphp
                        @foreach($infoUmum as $label => $nilai)
                        <tr>
                            <td class="py-1.5 pr-4 text-gray-500 dark:text-gray-400 w-36">{{ $label }}</td>
                            <td class="py-1.5 pr-2 text-gray-500 dark:text-gray-400 w-4">:</td>
                            <td class="py-1.5 text-gray-700 dark:text-gray-300">{{ $nilai }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ══ CATATAN 2 — DASAR PENYUSUNAN ══ --}}
            <div class="catatan-section mb-8" id="catatan-2">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">2. Dasar Penyusunan Laporan Keuangan</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Laporan keuangan disusun sesuai dengan Interpretasi Standar Akuntansi Keuangan (ISAK) 35
                    tentang Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba. Laporan keuangan disusun
                    dengan basis akrual dan menggunakan mata uang Rupiah (IDR).
                </p>
            </div>

            {{-- ══ CATATAN 3 — KAS DAN SETARA KAS ══ --}}
            <div class="catatan-section mb-8" id="catatan-3">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">3. Kas dan Setara Kas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian kas dan setara kas per {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $periode->tanggal_akhir->translatedFormat('d M Y') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['kasSetaraKas'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($row->saldo) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-400 italic text-xs">Tidak ada data kas</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total Kas dan Setara Kas</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($data['totalKas']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 4 — PIUTANG ══ --}}
            <div class="catatan-section mb-8" id="catatan-4">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">4. Piutang</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian piutang per {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $periode->tanggal_akhir->translatedFormat('d M Y') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['piutang'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($row->saldo) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-400 italic text-xs">Tidak ada piutang</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total Piutang</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($data['totalPiutang']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 5 — ASET TETAP ══ --}}
            <div class="catatan-section mb-8" id="catatan-5">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">5. Aset Tetap</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian aset tetap per {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga Perolehan</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Akm. Penyusutan</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nilai Buku</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['asetTetap'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 {{ $row->is_akumulasi ? 'text-gray-400 dark:text-gray-500 text-xs italic' : '' }}">
                            <td class="px-4 py-2 {{ $row->is_akumulasi ? 'pl-8' : '' }}">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right">
                                {{ $row->harga_perolehan != 0 ? 'Rp ' . fmtC($row->harga_perolehan) : '–' }}
                            </td>
                            <td class="px-4 py-2 text-right text-red-500 dark:text-red-400">
                                {{ $row->akumulasi != 0 ? '(Rp ' . fmtC($row->akumulasi) . ')' : '–' }}
                            </td>
                            <td class="px-4 py-2 text-right {{ $row->nilai_buku < 0 ? 'text-red-500 dark:text-red-400' : '' }}">
                                {{ $row->is_akumulasi
                                    ? '(Rp ' . fmtC(abs($row->nilai_buku)) . ')'
                                    : 'Rp ' . fmtC(abs($row->nilai_buku)) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-3 text-center text-gray-400 italic text-xs">Tidak ada aset tetap</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total Aset Tetap</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($data['totalHargaPerolehan']) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-red-500 dark:text-red-400">(Rp {{ fmtC($data['totalAkumulasi']) }})</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($data['totalNilaiBuku']) }}</td>
                        </tr>
                    </tfoot>
                </table>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 italic">
                    Metode penyusutan menggunakan garis lurus dengan estimasi umur manfaat sesuai kebijakan entitas.
                </p>
            </div>

            {{-- ══ CATATAN 6 — LIABILITAS ══ --}}
            <div class="catatan-section mb-8" id="catatan-6">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">6. Liabilitas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian liabilitas per {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $periode->tanggal_akhir->translatedFormat('d M Y') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['liabilitas'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($row->saldo) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-400 italic text-xs">Tidak ada liabilitas</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total Liabilitas</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($data['totalLiabilitas']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 7 — PENDAPATAN INFAK DAN SEDEKAH ══ --}}
            <div class="catatan-section mb-8" id="catatan-7">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">7. Pendapatan Infak dan Sedekah</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian pendapatan infak dan sedekah untuk periode yang berakhir
                    {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['pendapatanTanpa'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($row->saldo) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-400 italic text-xs">Tidak ada pendapatan pada periode ini</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total Pendapatan Infak dan Sedekah</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($data['totalPendapatanTanpa']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 8 — BEBAN OPERASIONAL ══ --}}
            <div class="catatan-section mb-8" id="catatan-8">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">8. Beban Operasional</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian beban operasional untuk periode yang berakhir
                    {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($data['beban'] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row->nama_akun }}</td>
                            <td class="px-4 py-2 text-right text-red-500 dark:text-red-400">(Rp {{ fmtC($row->saldo) }})</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-400 italic text-xs">Tidak ada beban pada periode ini</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Total Beban Operasional</td>
                            <td class="px-4 py-2.5 text-right font-bold text-red-500 dark:text-red-400">(Rp {{ fmtC($data['totalBeban']) }})</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 9 — ASET NETO ══ --}}
            @php $an = $data['asetNeto']; @endphp
            <div class="catatan-section mb-8" id="catatan-9">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">9. Aset Neto</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Rincian aset neto per {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanpa Pembatasan</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dengan Pembatasan</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Aset Neto Awal</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($an['saldoAwalTanpa'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($an['saldoAwalDengan'] ?? 0) }}</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($an['totalSaldoAwal'] ?? 0) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Surplus (Defisit) Periode Ini</td>
                            <td class="px-4 py-2 text-right {{ ($an['surplusTanpa'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedC($an['surplusTanpa'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2 text-right {{ ($an['surplusDengan'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedC($an['surplusDengan'] ?? 0) }}
                            </td>
                            <td class="px-4 py-2 text-right {{ (($an['surplusTanpa'] ?? 0) + ($an['surplusDengan'] ?? 0)) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedC(($an['surplusTanpa'] ?? 0) + ($an['surplusDengan'] ?? 0)) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 text-gray-400 dark:text-gray-500 text-xs">
                            <td class="px-4 py-2">Reklasifikasi</td>
                            <td class="px-4 py-2 text-right">Rp 0</td>
                            <td class="px-4 py-2 text-right">Rp 0</td>
                            <td class="px-4 py-2 text-right">Rp 0</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Saldo Aset Neto Akhir</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($an['saldoAkhirTanpa'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($an['saldoAkhirDengan'] ?? 0) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($an['totalSaldoAkhir'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 10 — ARUS KAS ══ --}}
            @php $ak = $data['arusKas']; @endphp
            <div class="catatan-section mb-8" id="catatan-10">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">10. Arus Kas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                    Informasi arus kas untuk periode yang berakhir
                    {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}:
                </p>
                <table class="fin-table w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uraian</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Kas Neto dari Aktivitas Operasional</td>
                            <td class="px-4 py-2 text-right {{ ($ak['operasional'] ?? 0) >= 0 ? 'text-gray-700 dark:text-gray-300' : 'text-red-500 dark:text-red-400' }}">
                                {{ signedC($ak['operasional'] ?? 0) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Kas Neto dari Aktivitas Investasi</td>
                            <td class="px-4 py-2 text-right {{ ($ak['investasi'] ?? 0) >= 0 ? 'text-gray-700 dark:text-gray-300' : 'text-red-500 dark:text-red-400' }}">
                                {{ signedC($ak['investasi'] ?? 0) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Kas Neto dari Aktivitas Pendanaan</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp 0</td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 border-t border-gray-200 dark:border-gray-700">
                            <td class="px-4 py-2 font-semibold text-gray-700 dark:text-gray-300">Kenaikan (Penurunan) Kas dan Setara Kas</td>
                            <td class="px-4 py-2 text-right font-semibold {{ ($ak['kenaikan'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                {{ signedC($ak['kenaikan'] ?? 0) }}
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">Kas dan Setara Kas Awal</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-gray-300">Rp {{ fmtC($ak['kasAwal'] ?? 0) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-gray-200">Kas dan Setara Kas Akhir</td>
                            <td class="px-4 py-2.5 text-right font-bold text-gray-800 dark:text-gray-200">Rp {{ fmtC($ak['kasAkhir'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ══ CATATAN 11 — PERISTIWA SETELAH TANGGAL PELAPORAN ══ --}}
            <div class="catatan-section mb-4" id="catatan-11">
                <h3 class="text-base font-bold text-green-700 dark:text-green-500 mb-3">11. Peristiwa Setelah Tanggal Pelaporan</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Tidak terdapat peristiwa penting setelah tanggal
                    {{ $periode->tanggal_akhir->translatedFormat('d F Y') }}
                    yang mempengaruhi laporan keuangan.
                </p>
            </div>

            @endif
        </div>{{-- end #calkContent --}}
    </div>
</div>

@push('scripts')
<script>
function cetakLaporan() {
    @if(!$periode)
        alert('Pilih periode terlebih dahulu.');
        return;
    @endif

    const periodeLabel = @json($periode?->nama_periode ?? '');
    const tanggalAkhir = @json($periode?->tanggal_akhir?->translatedFormat('d F Y') ?? '');

    const bulanID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const dCetak = new Date();
    const tglFile = dCetak.getDate() + bulanID[dCetak.getMonth()] + dCetak.getFullYear();
    const namaFile = tglFile + '_Laporan CALK_MosQue';
    const konten = document.getElementById('calkDokumen').innerHTML;

    // Inject garis hijau pemisah setelah blok KOP, sebelum Catatan 1
    const kontenFinal = konten.replace(
        /(<\/span>\s*<\/div>)\s*(<div class="catatan-section)/,
        '$1<hr class="kop-divider">$2'
    );

    const printWindow = window.open('', '_blank', 'width=900,height=700');

    printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>${namaFile}</title>
    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        html, body {
            margin: 0;
            padding: 0;
            background: white;
            font-family: 'Segoe UI', Arial, sans-serif;   /* ← sans-serif, bukan Times */
            font-size: 10pt;
            color: #111;
            line-height: 1.6;
        }

        /* ── Layout wrapper ── */
        .print-wrapper {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
        }

        /* ── KOP ── */
        .text-center { text-align: center; }
        .mb-8 { margin-bottom: 22pt; }
        .mb-3 { margin-bottom: 8pt; }
        .mb-4 { margin-bottom: 10pt; }
        .mt-1 { margin-top: 3pt; }
        .mt-2 { margin-top: 6pt; }

        /* Nama masjid — kecil hijau uppercase seperti preview */
        .kop-nama,
        p.text-sm.font-bold.text-green-700 {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #15803d !important;
            margin-bottom: 2pt;
        }

        /* Judul CALK — besar bold uppercase */
        h2.text-xl,
        h2.font-bold {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #111 !important;
            margin: 4pt 0;
        }

        /* Sub judul periode */
        p.text-sm.text-gray-500 {
            font-size: 10pt;
            color: #6b7280 !important;
            margin: 3pt 0;
        }

        /* Badge ISAK 335 — pill hijau seperti preview */
        span.inline-block.text-xs {
            display: inline-block;
            margin-top: 6pt;
            font-size: 8pt;
            color: #15803d !important;
            background-color: #dcfce7 !important;
            padding: 2pt 12pt;
            border-radius: 20pt;
        }

        /* Garis pemisah setelah KOP */
        .kop-divider {
            border: none;
            border-top: 1.5pt solid #16a34a;
            margin: 12pt 0 18pt 0;
        }

        /* ── Catatan Section ── */
        .catatan-section {
            margin-bottom: 18pt;
            page-break-inside: avoid;
        }
        .catatan-section h3 {
            font-size: 11pt;
            font-weight: bold;
            color: #15803d;
            margin: 0 0 6pt 0;
            padding-bottom: 2pt;
            border-bottom: 0.5pt solid #d1fae5;
        }
        .catatan-section p {
            font-size: 10pt;
            color: #333;
            margin: 0 0 6pt 0;
            line-height: 1.6;
        }

        /* ── Tabel Info Umum (Catatan 1) ── */
        table.info-table {
            border: none;
            font-size: 10pt;
            width: auto;
            max-width: 60%;
        }
        table.info-table td {
            border: none !important;
            padding: 2pt 8pt 2pt 0 !important;
            color: #333;
            vertical-align: top;
        }
        table.info-table td:first-child { color: #555; width: 100pt; }
        table.info-table td:nth-child(2) { width: 12pt; color: #555; }

        /* ── Tabel Keuangan ── */
        table.fin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 4pt;
        }
        table.fin-table thead tr th {
            background-color: #f3f4f6 !important;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 5pt 8pt;
            border: 0.5pt solid #9ca3af;
            color: #374151;
        }
        table.fin-table thead tr th.text-right,
        table.fin-table td.text-right { text-align: right; }
        table.fin-table thead tr th.text-left,
        table.fin-table td.text-left { text-align: left; }

        table.fin-table tbody tr td {
            padding: 4pt 8pt;
            border: 0.5pt solid #d1d5db;
            color: #111;
            background: white;
            font-size: 10pt;
            vertical-align: middle;
        }
        table.fin-table tbody tr.row-akumulasi td {
            font-style: italic;
            font-size: 9.5pt;
            color: #6b7280;
            padding-left: 20pt;
        }
        table.fin-table tbody tr td.td-indent { padding-left: 20pt; }
        table.fin-table tbody tr td.color-red { color: #dc2626; }
        table.fin-table tbody tr td.color-green { color: #15803d; }

        table.fin-table tfoot tr td {
            padding: 5pt 8pt;
            border: 0.5pt solid #9ca3af;
            border-top: 1.5pt solid #6b7280 !important;
            background-color: #f3f4f6 !important;
            font-weight: bold;
            font-size: 10pt;
            color: #111;
        }
        table.fin-table tfoot tr td.color-red { color: #dc2626; }

        /* ── Catatan kaki tabel ── */
        .tabel-note {
            font-size: 8.5pt;
            color: #6b7280;
            font-style: italic;
            margin-top: 4pt;
        }

        /* ── Ukuran halaman & margin ── */
        @page {
            size: A4 portrait;
            margin: 1.8cm 2cm 2cm 2cm;
        }

        /* ── Nama file saat simpan PDF ── */
        /* (diatur via title tag di atas) */
    </style>
</head>
<body>
<div class="print-wrapper">
    ${konten}
</div>
<script>
    // Auto print setelah render
    window.onload = function() {
        // Beri jeda agar font/style render sempurna
        setTimeout(function() {
            window.print();
        }, 400);
    };
<\/script>
</body>
</html>`);

    printWindow.document.close();
}
</script>
@endpush
@endsection