<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Publik — Keuangan Masjid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-200 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900">Dashboard</h1>
    </div>

    {{-- ── Kartu Ringkasan ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl border border-gray-200 px-5 py-4">
            <p class="text-sm text-gray-500">Saldo Kas Keseluruhan</p>
            <p class="text-xs text-gray-400 mb-1">Per {{ $now->translatedFormat('l, d F Y') }}</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($saldoKas, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 px-5 py-4">
            <p class="text-sm text-gray-500">Pemasukan</p>
            <p class="text-xs text-gray-400 mb-1">Yang masuk hingga hari ini</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($pemasukan, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 px-5 py-4">
            <p class="text-sm text-gray-500">Pengeluaran</p>
            <p class="text-xs text-gray-400 mb-1">Yang keluar hingga hari ini</p>
            <p class="text-2xl font-bold text-red-500">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 px-5 py-4">
            <p class="text-sm text-gray-500">Saldo Akhir</p>
            <p class="text-xs text-gray-400 mb-1">Per {{ $now->translatedFormat('d F Y') }}</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ── Grafik Sumber & Penggunaan Dana ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Sumber Dana Masjid</h2>
            <div id="donutSumber"></div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Penggunaan Dana Masjid</h2>
            <div id="barPenggunaan"></div>
        </div>
    </div>

    {{-- ── Kegiatan & Perkembangan Dana ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Kegiatan Berjalan --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Kegiatan Berjalan</h2>
            <div class="space-y-5">
            @forelse($kegiatanBerjalan as $kegiatan)
                @php
                    $persen = $kegiatan->anggaran > 0
                        ? min(100, round(($kegiatan->terkumpul / $kegiatan->anggaran) * 100))
                        : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $kegiatan->nama_kegiatan }}</p>
                            <p class="text-xs text-gray-400">Total anggaran: Rp{{ number_format($kegiatan->anggaran, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ $persen }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $persen }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada kegiatan berjalan.</p>
            @endforelse
            </div>
        </div>

        {{-- Perkembangan Dana --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-base font-semibold text-gray-800">Perkembangan Dana Masjid</h2>
            </div>
            <div class="flex items-baseline gap-2 mb-1">
                <span class="text-3xl font-bold text-gray-800">{{ number_format($totalDonasi) }}</span>
                <span class="text-sm text-gray-500">Infak/Donasi</span>
                <span class="inline-flex items-center text-xs text-green-500 ml-2">↑ +12% dari periode sebelumnya</span>
            </div>
            <div id="lineChart" class="mt-2"></div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                <div>
                    <p class="text-sm font-bold text-gray-800">Rp{{ number_format($avgHari, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">avg/hari</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Rp{{ number_format($avgMinggu, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">avg/minggu</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Rp{{ number_format($avgBulan, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">avg/bulan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Transaksi Terbaru ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Transaksi</h2>
            <div class="flex items-center gap-2">
                <input type="date" value="{{ $tanggalFilter }}"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 outline-none focus:border-blue-400">
                <button class="flex items-center gap-1.5 text-sm text-green-600 border border-green-200 bg-green-50 px-3 py-1.5 rounded-lg hover:bg-green-100">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-3">Tanggal</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-4 py-3">Jenis Transaksi</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-4 py-3">Keterangan</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-4 py-3">Kategori</th>
                        <th class="text-right text-xs font-medium text-gray-400 px-5 py-3">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                @forelse($transaksiTerbaru as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($t->tanggalTransaksi)->translatedFormat('d F Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $t->jenis_transaksi === 'PEMASUKAN' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' }}">
                            {{ ucfirst(strtolower($t->jenis_transaksi)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 max-w-[160px] truncate">{{ $t->keterangan }}</td>
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

</div>

<script>
    // ── Donut Sumber Dana ──────────────────────────────────────────────────
    const sumberLabels = @json($sumberDana->pluck('nama_kategori'));
    const sumberData   = @json($sumberDana->pluck('total'));

    new ApexCharts(document.querySelector('#donutSumber'), {
        chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
        series: sumberData.length ? sumberData : [1],
        labels: sumberLabels.length ? sumberLabels : ['Belum ada data'],
        colors: ['#3B82F6', '#6366F1', '#A5B4FC'],
        legend: { position: 'bottom', fontSize: '12px' },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '65%' } } },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } }
    }).render();

    // ── Bar Penggunaan Dana ────────────────────────────────────────────────
    const penggunaanLabels = @json($penggunaanDana->pluck('nama_kategori'));
    const penggunaanData   = @json($penggunaanDana->pluck('total'));

    new ApexCharts(document.querySelector('#barPenggunaan'), {
        chart: { type: 'bar', height: 280, fontFamily: 'inherit', toolbar: { show: false } },
        series: [{ name: 'Pengeluaran', data: penggunaanData.length ? penggunaanData : [0] }],
        xaxis: { categories: penggunaanLabels.length ? penggunaanLabels : ['Belum ada data'] },
        colors: ['#3B82F6'],
        dataLabels: { enabled: false },
        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } }
    }).render();

    // ── Line Chart Perkembangan Dana ───────────────────────────────────────
    const perkembangan = @json($perkembangan);

    new ApexCharts(document.querySelector('#lineChart'), {
        chart: { type: 'area', height: 160, fontFamily: 'inherit', toolbar: { show: false }, sparkline: { enabled: false } },
        series: [{ name: 'Pemasukan', data: perkembangan.map(d => d.total) }],
        xaxis: { categories: perkembangan.map(d => d.tanggal), labels: { show: false }, axisBorder: { show: false } },
        yaxis: { labels: { show: false } },
        grid: { show: false },
        colors: ['#EF4444'],
        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: val => 'Rp ' + val.toLocaleString('id-ID') } }
    }).render();
</script>

</body>
</html>