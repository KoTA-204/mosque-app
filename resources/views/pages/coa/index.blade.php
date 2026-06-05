{{-- dashboard/chart-of-account/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Chart of Account')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Chart of Account</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola struktur akun keuangan masjid</p>
        </div>
        <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
            </svg>
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Kategori Akun --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 flex flex-col gap-4">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="text-lg font-bold text-green-700 dark:text-green-400">{{ $totalKategori }}</span>
                </div>
            </div>
            <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white">Kategori</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                    Kelompok utama dalam struktur Chart of Account (CoA) yang digunakan untuk mengelompokkan akun berdasarkan jenis unsur laporan keuangan
                </p>
            </div>
            <a href="{{ route('dashboard.coa.kategori.create') }}"
               class="inline-flex items-center justify-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors w-fit">
                Tambah Kategori
            </a>
        </div>

        {{-- Sub Kategori --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 flex flex-col gap-4">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="text-lg font-bold text-green-700 dark:text-green-400">{{ $totalSubKategori }}</span>
                </div>
            </div>
            <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white">Sub Kategori</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                    Pengelompokan turunan dari kategori yang digunakan untuk mengelompokkan akun dengan karakteristik yang lebih spesifik.
                </p>
            </div>
            <a href="{{ route('dashboard.coa.sub-kategori.create') }}"
               class="inline-flex items-center justify-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors w-fit">
                Tambah Sub Kategori
            </a>
        </div>

        {{-- Akun --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 flex flex-col gap-4">
            <div class="flex items-start justify-between">
                <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <span class="text-lg font-bold text-green-700 dark:text-green-400">{{ $totalAkun }}</span>
                </div>
            </div>
            <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white">Akun</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                    Unit pencatatan transaksi keuangan yang digunakan dalam proses akuntansi untuk mencatat saldo dan mutasi transaksi tertentu.
                </p>
            </div>
            <a href="{{ route('dashboard.coa.akun.create') }}"
               class="inline-flex items-center justify-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors w-fit">
                Tambah Akun
            </a>
        </div>

    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            @if($isFiltered)
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Tampil</span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">1</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">kategori</span>
            </div>
            @else
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan semua <strong>{{ $kategori->count() }}</strong> kategori
                </span>
            </div>
            @endif

            {{-- Search / Filter kategori --}}
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <select name="kategori"
                        onchange="this.form.submit()"
                        class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 appearance-none min-w-[200px]">
                        <option value="">Semua Kategori</option>
                        @foreach($allKategori as $k)
                        <option value="{{ $k->id }}" {{ request('kategori') == $k->id ? 'selected' : '' }}>
                            ({{ $k->kode_kategori }}) {{ $k->nama_kategori }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- Tree Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-1/2">Nama Akun</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Kode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Saldo Normal</th>
                        <th class="text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">

                @forelse($kategori as $kat)

                    {{-- ── LEVEL 1: KATEGORI ── --}}
                    <tr class="bg-green-700 dark:bg-green-800">
                        <td colspan="4" class="px-5 py-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-white">
                                    ({{ $kat->kode_kategori }}) {{ $kat->nama_kategori }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('dashboard.coa.kategori.edit', $kat) }}"
                                       class="p-1.5 text-green-200 hover:text-white hover:bg-green-600 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.coa.kategori.destroy', $kat) }}"
                                          onsubmit="return confirm('Hapus kategori {{ $kat->nama_kategori }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-green-200 hover:text-red-300 hover:bg-green-600 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @forelse($kat->akunKeuangan as $subKat)

                        {{-- ── LEVEL 2: SUB KATEGORI ── --}}
                        <tr class="bg-gray-50 dark:bg-gray-800/60 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 pl-4">
                                    <div class="w-px h-4 bg-gray-300 dark:bg-gray-600"></div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $subKat->nama_akun }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $subKat->kode_akun }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    —
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('dashboard.coa.sub-kategori.edit', $subKat) }}"
                                       class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.coa.sub-kategori.destroy', $subKat) }}"
                                          onsubmit="return confirm('Hapus sub kategori {{ $subKat->nama_akun }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- ── LEVEL 3: AKUN ── --}}
                        @forelse($subKat->children as $akun)
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 pl-10">
                                    <div class="w-px h-4 bg-gray-200 dark:bg-gray-700"></div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ $akun->nama_akun }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-400 dark:text-gray-500">{{ $akun->kode_akun }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $akun->saldo_normal === 'debit'
                                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                        : 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400' }}">
                                    {{ ucfirst($akun->saldo_normal) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('dashboard.coa.akun.edit', $akun) }}"
                                       class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('dashboard.coa.akun.destroy', $akun) }}"
                                          onsubmit="return confirm('Hapus akun {{ $akun->nama_akun }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr class="bg-white dark:bg-gray-900">
                            <td colspan="4" class="pl-14 py-2.5 text-xs text-gray-400 dark:text-gray-600 italic">
                                Belum ada akun dalam sub kategori ini.
                            </td>
                        </tr>
                        @endforelse

                    @empty
                    <tr class="bg-white dark:bg-gray-900">
                        <td colspan="4" class="px-5 py-4 text-sm text-gray-400 dark:text-gray-600 text-center italic">
                            Belum ada sub kategori dalam kategori ini.
                        </td>
                    </tr>
                    @endforelse

                @empty
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                        Belum ada data Chart of Account.
                        <a href="{{ route('dashboard.coa.kategori.create') }}" class="text-green-600 hover:underline ml-1">Tambah kategori pertama</a>
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($isFiltered && $kategori->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($kategori->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Sebelumnya</span>
                @else
                <a href="{{ $kategori->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Sebelumnya</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($kategori->getUrlRange(1, $kategori->lastPage()) as $page => $url)
                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $kategori->currentPage()
                           ? 'bg-green-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                {{-- Next --}}
                @if($kategori->hasMorePages())
                <a href="{{ $kategori->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Selanjutnya</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Selanjutnya</span>
                @endif
            </div>
            <span class="text-xs text-gray-400 dark:text-gray-600">
                Tampil {{ $kategori->firstItem() }}–{{ $kategori->lastItem() }} dari {{ $kategori->total() }} kategori
            </span>
        </div>
        @endif

    </div>
</div>
@endsection

<x-confirm-modal
    id="deleteKategoriModal"
    title="Hapus Kategori"
    message="Kategori yang dihapus tidak dapat dikembalikan."
/>

<x-confirm-modal
    id="deleteSubKategoriModal"
    title="Hapus Sub Kategori"
    message="Sub kategori yang dihapus tidak dapat dikembalikan."
/>

<x-confirm-modal
    id="deleteAkunModal"
    title="Hapus Akun"
    message="Akun yang dihapus tidak dapat dikembalikan."
/>

@push('scripts') 
<script>
    function openModal(id) {
        const modal = document.getElementById(id);

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        modal.style.display = 'none';
        modal.classList.add('hidden');
    }
</script>
@endpush

@include('pages.coa.create-kategori')
@include('pages.coa.edit-kategori')

@include('pages.coa.create-subkategori')
@include('pages.coa.edit-subkategori')

@include('pages.coa.create-akun')
@include('pages.coa.edit-akun')
