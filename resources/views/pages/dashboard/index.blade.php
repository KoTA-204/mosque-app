@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Keuangan</h1>
    </div>

    {{-- ── Kartu Ringkasan ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Saldo Awal --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Saldo Awal</p>
            <p class="text-2xl font-bold text-red-500 mt-1">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
            @php $selisihAwal = $saldoAwal - $saldoAwalBulanLalu; @endphp
            <p class="text-xs mt-1 {{ $selisihAwal >= 0 ? 'text-green-500' : 'text-red-400' }}">
                {{ $selisihAwal >= 0 ? '↑' : '↓' }}
                {{ $selisihAwal >= 0 ? '+' : '' }}Rp {{ number_format(abs($selisihAwal), 0, ',', '.') }} dari periode sebelumnya
            </p>
        </div>

        {{-- Pemasukan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pemasukan</p>
            <p class="text-2xl font-bold text-green-500 mt-1">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</p>
            @php $selisihPemasukan = $pemasukanBulanIni - $pemasukanBulanLalu; @endphp
            <p class="text-xs mt-1 {{ $selisihPemasukan >= 0 ? 'text-green-500' : 'text-red-400' }}">
                {{ $selisihPemasukan >= 0 ? '↑ +' : '↓ ' }}Rp {{ number_format(abs($selisihPemasukan), 0, ',', '.') }} dari periode sebelumnya
            </p>
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pengeluaran</p>
            <p class="text-2xl font-bold text-red-500 mt-1">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</p>
            @php $selisihPengeluaran = $pengeluaranBulanIni - $pengeluaranBulanLalu; @endphp
            <p class="text-xs mt-1 {{ $selisihPengeluaran <= 0 ? 'text-green-500' : 'text-red-400' }}">
                {{ $selisihPengeluaran >= 0 ? '↑ +' : '↓ ' }}Rp {{ number_format(abs($selisihPengeluaran), 0, ',', '.') }} dari periode sebelumnya
            </p>
        </div>

        {{-- Saldo Akhir --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Saldo Akhir</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
            <p class="text-xs mt-1 text-gray-400">Per {{ $now->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    {{-- ── Rekening Kas ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kas Tunai</p>
                <p class="text-lg font-bold text-blue-500">Rp {{ number_format($kasTunai, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rekening Infak</p>
                <p class="text-lg font-bold text-green-500">Rp {{ number_format($rekeningInfak, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rekening Zakat</p>
                <p class="text-lg font-bold text-red-400">Rp {{ number_format($rekeningZakat, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- ── Grafik ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Distribusi Pengeluaran --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Distribusi Pengeluaran</h2>
            </div>
            <div id="donutPengeluaran"></div>
        </div>

        {{-- Pemasukan vs Pengeluaran --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Pemasukan vs Pengeluaran</h2>
            </div>
            <div id="barChart"></div>
        </div>
    </div>

    {{-- ── Laporan Ringkasan ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Laporan Posisi Keuangan --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 flex flex-col gap-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Laporan Posisi Keuangan</h3>
            <div class="flex justify-between text-xs text-gray-400 border-b border-gray-100 dark:border-gray-800 pb-1">
                <span>Source</span><span>Jumlah</span>
            </div>
            <div class="space-y-2 text-sm flex-1">
                <div class="flex justify-between"><span class="text-gray-500">Aset</span><span class="font-medium">Rp{{ number_format($totalAset/1000000, 3, ',', '.') }}000</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Liabilitas</span><span class="font-medium">Rp{{ number_format($totalLiabilitas, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Aset Neto</span><span class="font-medium">Rp{{ number_format($totalAsetNeto/1000000, 3, ',', '.') }}000</span></div>
            </div>
            <a href="#" class="flex items-center justify-center gap-1 text-xs text-gray-500 border border-gray-200 dark:border-gray-700 rounded-lg py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Detail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        {{-- Laporan Penghasilan Komprehensif --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 flex flex-col gap-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Laporan Penghasilan Komprehensif</h3>
            <div class="flex justify-between text-xs text-gray-400 border-b border-gray-100 dark:border-gray-800 pb-1">
                <span>Source</span><span>Jumlah</span>
            </div>
            <div class="space-y-2 text-sm flex-1">
                <div class="flex justify-between"><span class="text-gray-500">Penghasilan</span><span class="font-medium">Rp{{ number_format($totalPenghasilan, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Beban</span><span class="font-medium">Rp{{ number_format($totalBeban, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Surplus</span><span class="font-medium {{ $surplus >= 0 ? 'text-green-600' : 'text-red-500' }}">Rp{{ number_format($surplus, 0, ',', '.') }}</span></div>
            </div>
            <a href="#" class="flex items-center justify-center gap-1 text-xs text-gray-500 border border-gray-200 dark:border-gray-700 rounded-lg py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Detail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        {{-- Laporan Perubahan Aset Neto --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 flex flex-col gap-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Laporan Perubahan Aset Neto</h3>
            <div class="flex justify-between text-xs text-gray-400 border-b border-gray-100 dark:border-gray-800 pb-1">
                <span>Source</span><span>Jumlah</span>
            </div>
            <div class="space-y-2 text-sm flex-1">
                <div class="flex justify-between"><span class="text-gray-500">Tidak terikat</span><span class="font-medium">Rp{{ number_format($tidakTerikat, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Terikan temporer</span><span class="font-medium">Rp{{ number_format($terikatTemporer, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Terikat permanen</span><span class="font-medium">Rp0</span></div>
            </div>
            <a href="#" class="flex items-center justify-center gap-1 text-xs text-gray-500 border border-gray-200 dark:border-gray-700 rounded-lg py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Detail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        {{-- Laporan Arus Kas --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 flex flex-col gap-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 text-center">Laporan Arus Kas</h3>
            <div class="flex justify-between text-xs text-gray-400 border-b border-gray-100 dark:border-gray-800 pb-1">
                <span>Source</span><span>Jumlah</span>
            </div>
            <div class="space-y-2 text-sm flex-1">
                <div class="flex justify-between text-xs"><span class="text-gray-500">Aktivitas operasional</span><span class="font-medium">Rp{{ number_format($pemasukanBulanIni * 0.8, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-xs"><span class="text-gray-500">Aktivitas investasi</span><span class="font-medium">Rp{{ number_format($pemasukanBulanIni * 0.1, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-xs"><span class="text-gray-500">Aktivitas pendanaan</span><span class="font-medium">Rp{{ number_format($pemasukanBulanIni * 0.1, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-xs border-t border-gray-100 dark:border-gray-800 pt-1"><span class="text-gray-500">Kenaikan neto kas</span><span class="font-medium text-green-600">Rp{{ number_format($surplus, 0, ',', '.') }}</span></div>
            </div>
            <a href="#" class="flex items-center justify-center gap-1 text-xs text-gray-500 border border-gray-200 dark:border-gray-700 rounded-lg py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Detail <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>

    {{-- ── Bawah: Transaksi & Kegiatan ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Transaksi Terbaru --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Transaksi Terbaru</h2>
                <div class="flex items-center gap-2">
                    <button class="flex items-center gap-1.5 text-sm text-gray-500 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                        Filter
                    </button>
                    <a href="{{ route('dashboard.transaksi.index') }}" class="text-sm text-gray-500 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                        Lihat Detail
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-50 dark:border-gray-800">
                            <th class="text-left text-xs font-medium text-gray-400 px-5 py-3">Tanggal</th>
                            <th class="text-left text-xs font-medium text-gray-400 px-4 py-3">Jenis</th>
                            <th class="text-left text-xs font-medium text-gray-400 px-4 py-3">Keterangan</th>
                            <th class="text-left text-xs font-medium text-gray-400 px-4 py-3">Kategori</th>
                            <th class="text-right text-xs font-medium text-gray-400 px-5 py-3">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($transaksiTerbaru as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($t->tanggalTransaksi)->translatedFormat('d F Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $t->jenis_transaksi === 'PEMASUKAN'
                                    ? 'bg-blue-50 text-blue-600'
                                    : 'bg-pink-50 text-pink-600' }}">
                                {{ ucfirst(strtolower($t->jenis_transaksi)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-[120px] truncate">{{ $t->keterangan }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->kategoriTransaksi?->nama_kategori ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-medium {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'text-green-600' : 'text-red-500' }}">
                            Rp{{ number_format($t->jumlah, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">Belum ada transaksi.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kegiatan Aktif --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white">Kegiatan Aktif</h2>
                <a href="{{ route('dashboard.kegiatan.index') }}" class="text-sm text-gray-500 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    Lihat Detail
                </a>
            </div>
            <div class="space-y-5">
            @forelse($kegiatanAktif as $kegiatan)
                @php
                    $persen = $kegiatan->anggaran > 0
                        ? min(100, round(($kegiatan->total_terkumpul / $kegiatan->anggaran) * 100))
                        : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $kegiatan->nama_kegiatan }}</p>
                            <p class="text-xs text-gray-400">Total anggaran: Rp{{ number_format($kegiatan->anggaran, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada kegiatan aktif.</p>
            @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Donut Chart Distribusi Pengeluaran ─────────────────────────────────
    const distribusiLabels = @json($distribusiPengeluaran->pluck('nama_kategori'));
    const distribusiData   = @json($distribusiPengeluaran->pluck('total'));

    new ApexCharts(document.querySelector('#donutPengeluaran'), {
        chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
        series: distribusiData.length ? distribusiData : [1],
        labels: distribusiLabels.length ? distribusiLabels : ['Belum ada data'],
        colors: ['#3B82F6', '#6366F1', '#A5B4FC', '#93C5FD', '#BFDBFE'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '65%' } } },
        tooltip: {
            y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') }
        }
    }).render();

    // ── Bar Chart Pemasukan vs Pengeluaran ─────────────────────────────────
    const grafikData = @json($grafikData);

    new ApexCharts(document.querySelector('#barChart'), {
        chart: { type: 'bar', height: 280, fontFamily: 'inherit', toolbar: { show: false } },
        series: [
            { name: 'Pemasukan',   data: grafikData.map(d => d.pemasukan) },
            { name: 'Pengeluaran', data: grafikData.map(d => d.pengeluaran) },
        ],
        xaxis: { categories: grafikData.map(d => d.label) },
        colors: ['#3B82F6', '#BFDBFE'],
        legend: { position: 'top', horizontalAlign: 'right' },
        dataLabels: { enabled: false },
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
        tooltip: {
            y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') }
        }
    }).render();
</script>
@endpush
@endsection