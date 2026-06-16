@extends('layouts.landing')
@section('title', 'Laporan Keuangan - Masjid Luqmanul Hakim')
@section('description', 'Transparansi laporan keuangan Masjid Luqmanul Hakim, Politeknik Negeri Bandung')
@section('content')

<section class="py-10 bg-gray-50 min-h-screen">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-400 uppercase tracking-widest mb-0.5">Keuangan</p>
            <h1 class="text-xl font-semibold text-gray-900">Laporan Keuangan</h1>
        </div>
        <p class="text-sm text-gray-400">{{ $now->translatedFormat('d F Y') }}</p>
    </div>

    {{-- ── Kartu Ringkasan ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Saldo Awal --}}
        <div class="bg-white rounded-2xl border border-gray-100 px-5 py-4 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Saldo Awal</p>
            <p class="text-xl font-bold text-gray-900">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</p>
            <p class="text-xs mt-2 text-gray-400">Per {{ $periodeAktif?->tanggal_awal?->translatedFormat('d F Y') ?? '-' }}</p>
        </div>

        {{-- Pemasukan --}}
        <div class="bg-white rounded-2xl border border-green-100 px-5 py-4 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Pemasukan</p>
            <p class="text-xl font-bold text-green-600">Rp {{ number_format($pemasukan, 0, ',', '.') }}</p>
            <p class="text-xs mt-2 text-gray-400">Yang masuk s.d. hari ini</p>
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-white rounded-2xl border border-red-100 px-5 py-4 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Pengeluaran</p>
            <p class="text-xl font-bold text-red-500">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</p>
            <p class="text-xs mt-2 text-gray-400">Yang keluar s.d. hari ini</p>
        </div>

        {{-- Saldo Akhir --}}
        <div class="bg-white rounded-2xl border border-gray-100 px-5 py-4 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-3">Saldo Akhir</p>
            <p class="text-xl font-bold text-gray-900">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
            <p class="text-xs mt-2 text-gray-400">Per {{ $now->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    {{-- ── Grafik Donut ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Donut Sumber Dana --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm overflow-hidden">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Sumber Pemasukan</p>
            <p class="text-xs text-gray-400 mb-4">Distribusi berdasarkan dompet</p>
            <div id="donutSumber" class="w-full"></div>
        </div>

        {{-- Donut Penggunaan Dana --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm overflow-hidden">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Distribusi Pengeluaran</p>
            <p class="text-xs text-gray-400 mb-4">Penggunaan dana per kegiatan</p>
            <div id="donutPengeluaran" class="w-full"></div>
        </div>
    </div>

    {{-- ── Perkembangan Dana & Kegiatan ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Perkembangan Dana --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Perkembangan Dana</p>
            <p class="text-xs text-gray-400 mb-3">30 hari terakhir</p>
            <div class="flex items-baseline gap-2 mb-1">
                <span class="text-xl font-bold text-gray-900">Rp{{ number_format($totalDonasi, 0, ',', '.') }}</span>
                <span class="inline-flex items-center text-xs {{ $persenPerkembangan >= 0 ? 'text-green-500' : 'text-red-400' }}">
                    {{ $persenPerkembangan >= 0 ? '↑' : '↓' }} {{ number_format(abs($persenPerkembangan), 1, ',', '.') }}% vs 30 hari lalu
                </span>
            </div>
            <div id="lineChart" class="w-full mt-3"></div>
            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-gray-100 text-center">
                <div>
                    <p class="text-sm font-bold text-gray-800">Rp{{ number_format($avgHari, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">avg/hari</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Rp{{ number_format($avgMinggu, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">avg/minggu</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Rp{{ number_format($avgBulan, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">avg/bulan</p>
                </div>
            </div>
        </div>

        {{-- Kegiatan Berjalan --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-5">Kegiatan Berjalan</p>
            <div class="space-y-3">
            @forelse($kegiatanBerjalan as $kegiatan)
                @php
                    $terkumpul = $kegiatan->terkumpul ?? 0;
                    $persen = $kegiatan->anggaran > 0
                        ? min(100, round(($terkumpul / $kegiatan->anggaran) * 100))
                        : 0;

                    // Warna & ikon berdasarkan progres
                    if ($persen >= 75) {
                        $iconBg = 'bg-green-50'; $iconColor = 'text-green-600'; $barColor = 'bg-green-500'; $pctColor = 'text-green-600';
                    } elseif ($persen >= 30) {
                        $iconBg = 'bg-blue-50'; $iconColor = 'text-blue-600'; $barColor = 'bg-blue-500'; $pctColor = 'text-blue-600';
                    } else {
                        $iconBg = 'bg-amber-50'; $iconColor = 'text-amber-600'; $barColor = 'bg-amber-500'; $pctColor = 'text-amber-600';
                    }
                @endphp
                <div class="flex gap-3 items-start p-3.5 rounded-xl bg-gray-50">
                    <div class="w-9 h-9 rounded-lg {{ $iconBg }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m4-14h6m-6 4h6m-6 4h6"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline gap-2 mb-1.5">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $kegiatan->nama_kegiatan }}</p>
                            <span class="text-xs font-semibold {{ $pctColor }} shrink-0">{{ $persen }}%</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-gray-200 overflow-hidden mb-1.5">
                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $persen }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400">
                            Rp{{ number_format($terkumpul, 0, ',', '.') }} dari Rp{{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-8">Belum ada kegiatan berjalan.</p>
            @endforelse
            </div>
        </div>
    </div>

    {{-- ── Transaksi Terbaru ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">
                Transaksi Periode {{ $periodeAktif?->nama_periode ?? '-' }}
            </p>
            <div class="flex items-center gap-2">
                <input type="date" value="{{ $tanggalFilter }}"
                    class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 outline-none focus:border-green-400">
                <button class="flex items-center gap-1.5 text-xs text-green-600 border border-green-200 bg-green-50 px-3 py-1.5 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
        <div class="max-h-[420px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10">
                    <tr class="border-y border-gray-50 bg-gray-50">
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Tanggal</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-4 py-2.5">Jenis</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-4 py-2.5">Keterangan</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-4 py-2.5">Kategori</th>
                        <th class="text-right text-xs font-medium text-gray-400 px-5 py-2.5">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse($transaksiTerbaru as $t)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">
                        {{ $t->tanggal_transaksi->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                            {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-500' }}">
                            {{ ucfirst(strtolower($t->jenis_transaksi)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-[140px] truncate">
                        {{ Str::limit($t->deskripsi ?? '—', 30) }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $t->kategoriTransaksi?->nama_kategori ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-semibold {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'text-green-600' : 'text-red-500' }}">
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

</div>
</section>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labelColor = '#9CA3AF';

    // ── Donut Sumber Pemasukan (per dompet) ────────────────────────────────
    const sumberLabels = @json($sumberDana->pluck('nama_kategori'));
    const sumberData   = @json($sumberDana->pluck('total')).map(Number);

    new ApexCharts(document.querySelector('#donutSumber'), {
        chart: { type: 'donut', height: 260, width: '100%', fontFamily: 'inherit', background: 'transparent' },
        series: sumberData.length ? sumberData : [1],
        labels: sumberLabels.length ? sumberLabels : ['Belum ada data'],
        colors: ['#3B82F6', '#6366F1', '#8B5CF6', '#A78BFA', '#C4B5FD'],
        legend: { position: 'bottom', fontSize: '11px', labels: { colors: labelColor } },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '70%' } } },
        stroke: { width: 0 },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } },
    }).render();

    // ── Donut Penggunaan Dana (per kegiatan, fallback "Operasional Umum") ──
    const penggunaanLabels = @json($penggunaanDana->pluck('nama_kategori'));
    const penggunaanData   = @json($penggunaanDana->pluck('total')).map(Number);

    new ApexCharts(document.querySelector('#donutPengeluaran'), {
        chart: { type: 'donut', height: 260, width: '100%', fontFamily: 'inherit', background: 'transparent' },
        series: penggunaanData.length ? penggunaanData : [1],
        labels: penggunaanLabels.length ? penggunaanLabels : ['Belum ada data'],
        colors: ['#EF4444', '#F97316', '#F59E0B', '#84CC16', '#10B981'],
        legend: { position: 'bottom', fontSize: '11px', labels: { colors: labelColor } },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '70%' } } },
        stroke: { width: 0 },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } },
    }).render();

    // ── Line Chart Perkembangan Dana (30 hari) ─────────────────────────────
    const perkembangan = @json($perkembangan);

    new ApexCharts(document.querySelector('#lineChart'), {
        chart: { type: 'area', height: 160, width: '100%', fontFamily: 'inherit', toolbar: { show: false }, background: 'transparent' },
        series: [{ name: 'Pemasukan', data: perkembangan.map(d => Number(d.total)) }],
        xaxis: {
            categories: perkembangan.map(d => d.tanggal),
            labels: { show: true, style: { colors: labelColor, fontSize: '9px' }, rotate: 0,
                formatter: (val, i) => (i % 7 === 0 ? val : '') },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: { labels: { style: { colors: labelColor, fontSize: '10px' }, formatter: v => 'Rp' + (v / 1000).toFixed(0) + 'k' } },
        grid: { borderColor: '#F3F4F6', strokeDashArray: 4 },
        colors: ['#3B82F6'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } },
    }).render();

    setTimeout(() => window.dispatchEvent(new Event('resize')), 100);
});
</script>
@endpush