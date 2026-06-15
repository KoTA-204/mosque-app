@extends('layouts.app')
@section('title', 'Laporan Keuangan')
@section('content')

<div class="min-h-screen bg-gray-50 dark:bg-gray-950 p-6 space-y-5">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-0.5">Keuangan</p>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
        </div>
        <p class="text-sm text-gray-400">{{ $now->translatedFormat('d F Y') }}</p>
    </div>

    {{-- ── Kartu Ringkasan ── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">

        {{-- Saldo Awal --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-5 py-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Saldo Awal</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
            @php $selisihAwal = $saldoAwal - $saldoAwalBulanLalu; @endphp
            <p class="text-xs mt-2 {{ $selisihAwal >= 0 ? 'text-emerald-500' : 'text-red-400' }}">
                {{ $selisihAwal >= 0 ? '↑' : '↓' }} Rp {{ number_format(abs($selisihAwal), 0, ',', '.') }} vs bulan lalu
            </p>
        </div>

        {{-- Pemasukan --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-5 py-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Pemasukan</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</p>
            @php $selisihPemasukan = $pemasukanBulanIni - $pemasukanBulanLalu; @endphp
            <p class="text-xs mt-2 {{ $selisihPemasukan >= 0 ? 'text-emerald-500' : 'text-red-400' }}">
                {{ $selisihPemasukan >= 0 ? '↑' : '↓' }} Rp {{ number_format(abs($selisihPemasukan), 0, ',', '.') }} vs bulan lalu
            </p>
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-5 py-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Pengeluaran</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</p>
            @php $selisihPengeluaran = $pengeluaranBulanIni - $pengeluaranBulanLalu; @endphp
            <p class="text-xs mt-2 {{ $selisihPengeluaran <= 0 ? 'text-emerald-500' : 'text-red-400' }}">
                {{ $selisihPengeluaran >= 0 ? '↑' : '↓' }} Rp {{ number_format(abs($selisihPengeluaran), 0, ',', '.') }} vs bulan lalu
            </p>
        </div>

        {{-- Saldo Akhir --}}
        <div class="bg-white rounded-xl border border-gray-100 px-5 py-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Saldo Akhir</p>
            <p class="text-xl font-semibold text-gray-900">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
            <p class="text-xs mt-2 text-gray-400">Per {{ $now->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    {{-- ── Saldo Dompet ── --}}
    <div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($saldoDompet as $d)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
                <div class="w-9 h-9 rounded-lg {{ $d->jenis_dompet === 'CASH' ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-violet-50 dark:bg-violet-900/20' }} flex items-center justify-center shrink-0">
                    @if($d->jenis_dompet === 'CASH')
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 truncate">
                        {{ $d->nama_dompet }}
                        @if($d->jenis_dompet === 'BANK' && $d->nama_bank)
                            · {{ $d->nama_bank }}
                        @endif
                    </p>
                    <p class="text-base font-semibold text-gray-900 dark:text-white mt-0.5">
                        Rp {{ number_format($d->saldo, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 col-span-full py-4 text-center">Belum ada dompet terdaftar.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Grafik ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 overflow-hidden">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-4">Distribusi Pengeluaran</p>
            <div id="donutPengeluaran" class="w-full"></div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 overflow-hidden">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-4">Pemasukan vs Pengeluaran</p>
            <div id="barChart" class="w-full"></div>
        </div>
    </div>

    {{-- ── Laporan Ringkasan ── --}}
    <div>
        <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-3">Laporan</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">

            {{-- Posisi Keuangan --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 flex flex-col">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-4">Posisi Keuangan</p>
                <div class="space-y-3 flex-1 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Aset</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp{{ number_format($totalAset, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Liabilitas</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp{{ number_format($totalLiabilitas, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-gray-100 dark:bg-gray-800"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Aset Neto</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp{{ number_format($totalAsetNeto, 0, ',', '.') }}</span>
                    </div>
                </div>
                <a href="dashboard.laporan.posisi-keuangan" class="mt-4 flex items-center justify-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors pt-3 border-t border-gray-100 dark:border-gray-800">
                    Lihat detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Penghasilan Komprehensif --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 flex flex-col">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-4">Penghasilan Komprehensif</p>
                <div class="space-y-3 flex-1 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Penghasilan</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp{{ number_format($totalPenghasilan, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Beban</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp{{ number_format($totalBeban, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-gray-100 dark:bg-gray-800"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Surplus</span>
                        <span class="font-semibold {{ $surplus >= 0 ? 'text-emerald-600' : 'text-red-500' }}">Rp{{ number_format($surplus, 0, ',', '.') }}</span>
                    </div>
                </div>
                <a href="dashboard.laporan.penghasilan-komprehensif" class="mt-4 flex items-center justify-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors pt-3 border-t border-gray-100 dark:border-gray-800">
                    Lihat detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Perubahan Aset Neto --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 flex flex-col">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-4">Perubahan Aset Neto</p>
                <div class="space-y-3 flex-1 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Tidak terikat</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp{{ number_format($tidakTerikat, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Terikat temporer</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp{{ number_format($terikatTemporer, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-gray-100 dark:bg-gray-800"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Terikat permanen</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200">Rp0</span>
                    </div>
                </div>
                <a href="dashboard.laporan.perubahan-aset-neto" class="mt-4 flex items-center justify-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors pt-3 border-t border-gray-100 dark:border-gray-800">
                    Lihat detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Arus Kas --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 flex flex-col">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-4">Arus Kas</p>
                <div class="space-y-3 flex-1 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-xs">Operasional</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-xs">Rp{{ number_format($arusOperasional, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-xs">Investasi</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-xs">Rp{{ number_format($arusInvestasi, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-xs">Pendanaan</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-xs">Rp{{ number_format($arusPendanaan, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-gray-100 dark:bg-gray-800"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium text-xs">Kenaikan neto</span>
                        <span class="font-semibold text-xs {{ $kenaikanNetoKas >= 0 ? 'text-emerald-600' : 'text-red-500' }}">Rp{{ number_format($kenaikanNetoKas, 0, ',', '.') }}</span>
                    </div>
                </div>
                <a href="dashboard.laporan.arus-kas" class="mt-4 flex items-center justify-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors pt-3 border-t border-gray-100 dark:border-gray-800">
                    Lihat detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Bawah: Transaksi & Kegiatan ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- Transaksi Terbaru --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Transaksi Terbaru</p>
                <a href="{{ route('dashboard.transaksi.index') }}"
                    class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex items-center gap-1">
                    Lihat semua <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-y border-gray-50 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/30">
                            <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Tanggal</th>
                            <th class="text-left text-xs font-medium text-gray-400 px-4 py-2.5">Jenis</th>
                            <th class="text-left text-xs font-medium text-gray-400 px-4 py-2.5">Keterangan</th>
                            <th class="text-left text-xs font-medium text-gray-400 px-4 py-2.5">Kategori</th>
                            <th class="text-right text-xs font-medium text-gray-400 px-5 py-2.5">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($transaksiTerbaru as $t)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">
                            {{ $t->tanggal_transaksi->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                {{ $t->jenis_transaksi === 'PEMASUKAN'
                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
                                    : 'bg-red-50 text-red-500 dark:bg-red-900/20 dark:text-red-400' }}">
                                {{ ucfirst(strtolower($t->jenis_transaksi)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-[120px] truncate">{{ Str::limit($t->deskripsi ?? '—', 30) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $t->kategoriTransaksi?->nama_kategori ?? '—' }}</td>
                        <td class="px-5 py-3 text-right text-sm font-semibold {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $t->jenis_transaksi === 'PEMASUKAN' ? '+' : '-' }}Rp{{ number_format($t->jumlah, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">Belum ada transaksi.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kegiatan Aktif --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5">
            <div class="flex items-center justify-between mb-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Kegiatan Aktif</p>
                <a href="{{ route('dashboard.kegiatan.index') }}" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex items-center gap-1">
                    Lihat semua <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="space-y-5">
            @forelse($kegiatanAktif as $kegiatan)
                @php
                    $terkumpul = $kegiatan->total_terkumpul ?? 0;
                    $persen = $kegiatan->anggaran > 0
                        ? min(100, round(($terkumpul / $kegiatan->anggaran) * 100))
                        : 0;
                @endphp
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $kegiatan->nama_kegiatan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Rp{{ number_format($terkumpul, 0, ',', '.') }}
                                <span class="text-gray-300 dark:text-gray-600 mx-1">/</span>
                                Rp{{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold tabular-nums {{ $persen >= 100 ? 'text-emerald-500' : 'text-gray-500 dark:text-gray-400' }}">{{ $persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full transition-all {{ $persen >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-8">Belum ada kegiatan aktif.</p>
            @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const labelColor = isDark ? '#6B7280' : '#9CA3AF';

    const distribusiLabels = @json($distribusiPengeluaran->pluck('nama_kategori'));
    const distribusiData   = @json($distribusiPengeluaran->pluck('total')).map(Number);

    const donutChart = new ApexCharts(document.querySelector('#donutPengeluaran'), {
        chart: { type: 'donut', height: 260, width: '100%', fontFamily: 'inherit', background: 'transparent' },
        series: distribusiData.length ? distribusiData : [1],
        labels: distribusiLabels.length ? distribusiLabels : ['Belum ada data'],
        colors: ['#3B82F6', '#6366F1', '#8B5CF6', '#A78BFA', '#C4B5FD'],
        legend: { position: 'bottom', fontSize: '11px', labels: { colors: labelColor } },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '70%' } } },
        stroke: { width: 0 },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } },
        theme: { mode: isDark ? 'dark' : 'light' }
    });
    donutChart.render();

    const grafikData = @json($grafikData);

    const barChart = new ApexCharts(document.querySelector('#barChart'), {
        chart: {
            type: 'bar',
            height: 260,
            width: '100%',
            fontFamily: 'inherit',
            toolbar: { show: false },
            background: 'transparent'
        },
        series: [
            { name: 'Pemasukan',   data: grafikData.map(d => Number(d.pemasukan)) },
            { name: 'Pengeluaran', data: grafikData.map(d => Number(d.pengeluaran)) },
        ],
        xaxis: {
            categories: grafikData.map(d => d.label),
            labels: { style: { colors: labelColor, fontSize: '11px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: labelColor, fontSize: '11px' },
                formatter: v => 'Rp' + (v / 1000).toFixed(0) + 'k'
            }
        },
        colors: ['#3B82F6', '#BFDBFE'],
        legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px', labels: { colors: labelColor } },
        dataLabels: { enabled: false },
        plotOptions: { bar: { columnWidth: '50%', borderRadius: 3 } },
        grid: { borderColor: isDark ? '#1F2937' : '#F3F4F6', strokeDashArray: 4 },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } },
        theme: { mode: isDark ? 'dark' : 'light' }
    });
    barChart.render();

    // Recalculasi lebar setelah layout grid selesai (mengatasi overflow saat render awal)
    setTimeout(() => window.dispatchEvent(new Event('resize')), 100);
});
</script>
@endpush
@endsection