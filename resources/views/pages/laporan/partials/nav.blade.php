@php
    $laporanTabs = [
        'posisi-keuangan'          => ['route' => 'dashboard.laporan.posisi-keuangan',          'label' => 'Posisi Keuangan'],
        'penghasilan-komprehensif' => ['route' => 'dashboard.laporan.penghasilan-komprehensif', 'label' => 'Penghasilan Komprehensif'],
        'perubahan-aset-neto'      => ['route' => 'dashboard.laporan.perubahan-aset-neto',      'label' => 'Perubahan Aset Neto'],
        'arus-kas'                 => ['route' => 'dashboard.laporan.arus-kas',                 'label' => 'Arus Kas'],
        'calk'                     => ['route' => 'dashboard.laporan.calk',                     'label' => 'CALK'],
    ];
    $laporanTitles = [
        'posisi-keuangan'          => 'Laporan Posisi Keuangan',
        'penghasilan-komprehensif' => 'Laporan Penghasilan Komprehensif',
        'perubahan-aset-neto'      => 'Laporan Perubahan Aset Neto',
        'arus-kas'                 => 'Laporan Arus Kas',
        'calk'                     => 'Laporan Catatan Atas Laporan Keuangan (CALK)',
    ];
@endphp
<div class="flex items-center justify-between flex-wrap gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 no-print">
    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $laporanTitles[$active] ?? 'Laporan Keuangan' }}</h1>
    <div class="flex items-center gap-2 flex-wrap">
        @foreach($laporanTabs as $key => $tab)
            @if($key === $active)
                <span class="text-sm font-semibold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-4 py-2 rounded-lg">{{ $tab['label'] }}</span>
            @else
                <a href="{{ route($tab['route']) }}"
                   class="text-sm text-gray-600 dark:text-gray-400 px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">{{ $tab['label'] }}</a>
            @endif
        @endforeach
        <a href="{{ route('dashboard.laporan.pdf', ['jenis' => $active, 'periode_id' => request('periode_id')]) }}"
            class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download PDF
        </a>
    </div>
</div>
