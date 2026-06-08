@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Buku Besar</h1>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Saldo Awal --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Awal</p>
                <p id="badgeSaldoAwal" class="text-sm font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($saldoAwal, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Total Debit --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Debit</p>
                <p id="badgeTotalDebit" class="text-sm font-bold text-gray-900 dark:text-white">
                   Rp {{ number_format($totalDebit, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Total Kredit --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8l-4 4m0 0l4 4m-4-4h18"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Kredit</p>
                <p id="badgeTotalKredit" class="text-sm font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($totalKredit, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Saldo Akhir --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Akhir</p>
                <p id="badgeSaldoAkhir" class="text-sm font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}
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
                {{-- Filter Periode --}}
                <select id="filterPeriode" onchange="applyFilters()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Pilih Periode</option>
                    @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama_periode ?? $p->tanggal_awal->translatedFormat('F Y') }}
                    </option>
                    @endforeach
                </select>

                {{-- Searchable Akun Dropdown --}}
                <div class="relative" id="akunDropdownWrapper">
                    <div class="relative">
                        <input type="text" id="akunSearch"
                            placeholder="Pilih Akun"
                            autocomplete="off"
                            value="{{ $akunId ? optional(\App\Models\Akun::find($akunId))->kode_akun . ' — ' . optional(\App\Models\Akun::find($akunId))->nama_akun : '' }}"
                            class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 pr-8 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-64"
                            onfocus="showAkunDropdown()"
                            oninput="filterAkunOptions()">
                        <input type="hidden" id="filterAkun" value="{{ $akunId }}">
                        <button type="button" onclick="toggleAkunDropdown()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    <div id="akunDropdown"
                        class="hidden absolute right-0 z-20 mt-1 w-72 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                        <div class="p-1">
                            <button type="button" onclick="selectAkun('', 'Pilih Akun')"
                                class="akun-option w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 italic">
                                Semua Akun
                            </button>
                            @foreach($akuns->groupBy(fn($a) => $a->kategoriAkun->nama_kategori ?? 'Lainnya') as $kategori => $group)
                            <div class="px-3 pt-2 pb-0.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                                {{ $kategori }}
                            </div>
                            @foreach($group as $akun)
                            <button type="button"
                                onclick="selectAkun('{{ $akun->id }}', '{{ $akun->kode_akun }} — {{ addslashes($akun->nama_akun) }}')"
                                data-label="{{ strtolower($akun->kode_akun . ' ' . $akun->nama_akun) }}"
                                class="akun-option w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors
                                    {{ $akunId == $akun->id ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-500 mr-1">{{ $akun->kode_akun }}</span>
                                {{ $akun->nama_akun }}
                            </button>
                            @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tableWrapper">
            @include('pages.buku-besar.table')
        </div>
    </div>
</div>

<script>
const filterUrl = "{{ route('dashboard.buku-besar.index') }}";

function applyFilters() {
    const periode = document.getElementById('filterPeriode').value;
    const akun    = document.getElementById('filterAkun').value;
    const perPage = document.getElementById('perPage').value;
    const params  = new URLSearchParams();
    if (periode) params.set('periode_id', periode);
    if (akun)    params.set('akun_id', akun);
    params.set('per_page', perPage);

    fetch(`${filterUrl}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('tableWrapper').innerHTML = data.html;

        // Update cards — nilai saja, tidak ada perubahan warna/badge
        document.getElementById('badgeSaldoAwal').textContent  = 'Rp ' + Number(data.saldoAwal).toLocaleString('id-ID');
        document.getElementById('badgeTotalDebit').textContent  = Number(data.totalDebit).toLocaleString('id-ID');
        document.getElementById('badgeTotalKredit').textContent = Number(data.totalKredit).toLocaleString('id-ID');
        document.getElementById('badgeSaldoAkhir').textContent  = 'Rp ' + Math.abs(Number(data.saldoAkhir)).toLocaleString('id-ID');
    });
}

// ── Searchable Akun Dropdown ──────────────────────────────────────────
function showAkunDropdown() {
    document.getElementById('akunDropdown').classList.remove('hidden');
}

function hideAkunDropdown() {
    document.getElementById('akunDropdown').classList.add('hidden');
}

function toggleAkunDropdown() {
    document.getElementById('akunDropdown').classList.toggle('hidden');
}

function filterAkunOptions() {
    const q = document.getElementById('akunSearch').value.toLowerCase();
    document.querySelectorAll('.akun-option').forEach(btn => {
        const label = (btn.dataset.label ?? btn.textContent).toLowerCase();
        btn.style.display = label.includes(q) ? '' : 'none';
    });
    showAkunDropdown();
}

function selectAkun(id, label) {
    document.getElementById('filterAkun').value = id;
    document.getElementById('akunSearch').value = id ? label : '';
    hideAkunDropdown();
    applyFilters();
}

// Tutup dropdown jika klik di luar
document.addEventListener('click', function(e) {
    if (!document.getElementById('akunDropdownWrapper').contains(e.target)) {
        hideAkunDropdown();
    }
});
</script>
@endsection