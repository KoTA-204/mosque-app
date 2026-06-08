@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Neraca Saldo</h1>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Total Debit --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Debit</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Rp. {{ number_format($grandTotalDebit, 0, ',', '.') }}</p>
            </div>
        </div>
        {{-- Total Kredit --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8l-4 4m0 0l4 4m-4-4h18"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Kredit</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">Rp. {{ number_format($grandTotalKredit, 0, ',', '.') }}</p>
            </div>
        </div>
        {{-- Selisih --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Selisih</p>
                <p class="text-sm font-bold {{ $selisih == 0 ? 'text-green-600' : 'text-red-500' }}">
                    Rp. {{ number_format(abs($selisih), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                Tampil
                <select id="perPage" onchange="applyFilters()"
                    class="border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                data
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filterPeriode" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Pilih Periode</option>
                    @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_periode ?? $p->tanggal_awal->translatedFormat('F Y') }}
                    </option>
                    @endforeach
                </select>
                <select id="filterAkun" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="semua">Semua Akun</option>
                    @foreach($kategoriAkuns as $kat)
                    <option value="{{ $kat->kode_kategori }}" {{ $akunFilter == $kat->kode_kategori ? 'selected' : '' }}>
                        {{ $kat->nama_kategori }}
                    </option>
                    @endforeach
                </select>
                <select id="filterSort" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="kode_akun_asc" {{ $sortBy === 'kode_akun_asc' ? 'selected' : '' }}>Kode Akun (ASC)</option>
                    <option value="kode_akun_desc" {{ $sortBy === 'kode_akun_desc' ? 'selected' : '' }}>Kode Akun (DESC)</option>
                    <option value="nama_asc" {{ $sortBy === 'nama_asc' ? 'selected' : '' }}>Nama Akun (A-Z)</option>
                </select>
            </div>
        </div>

        <div id="tableWrapper">
            @include('pages.neraca-saldo.table')
        </div>
    </div>
</div>

<script>
const filterUrl = "{{ route('dashboard.neraca-saldo.index') }}";

function applyFilters() {
    const periode = document.getElementById('filterPeriode').value;
    const akun    = document.getElementById('filterAkun').value;
    const sort    = document.getElementById('filterSort').value;
    const perPage = document.getElementById('perPage').value;
    const params  = new URLSearchParams();
    if (periode) params.set('periode_id', periode);
    if (akun)    params.set('akun_filter', akun);
    if (sort)    params.set('sort_by', sort);
    params.set('per_page', perPage);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;
    });
}
</script>
@endsection