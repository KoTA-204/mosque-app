@extends('layouts.app')

@section('title', 'Kategori Transaksi')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Kategori Transaksi</h1>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openModal('createKategoriModal')"
                class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                Tambah Kategori
            </button>
        </div>
    </div>

    @if(session('success'))
    <div id="success-alert"
        class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3 text-sm text-green-700 dark:text-green-400 transition-all duration-500">

        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"/>
        </svg>

        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div id="error-alert"
        class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400 transition-all duration-500">

        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                clip-rule="evenodd"/>
        </svg>

        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            {{-- Show entries --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                <form method="GET" id="per-page-form">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="jenis"  value="{{ request('jenis') }}">
                    <select name="per_page" onchange="document.getElementById('per-page-form').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
            </div>

            {{-- Search --}}
            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <select name="jenis" onchange="this.form.submit()"
                    class="text-sm border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                    <option value="">Semua Jenis Transaksi</option>
                    <option value="PEMASUKAN"   {{ request('jenis') === 'PEMASUKAN'   ? 'selected' : '' }}>Pemasukan</option>
                    <option value="PENGELUARAN" {{ request('jenis') === 'PENGELUARAN' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search..."
                        class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">No</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Nama Kategori</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Deskripsi</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jenis Transaksi</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                Status
                                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            </div>
                        </th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($kategori as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                    <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                        {{ $kategori->firstItem() + $loop->index }}
                    </td>
                    <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                        {{ $item->nama_kategori }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 max-w-xs">
                        <span class="line-clamp-1">{{ $item->deskripsi ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-medium
                            {{ $item->jenis_transaksi === 'PEMASUKAN'
                                ? 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400'
                                : 'bg-pink-50 dark:bg-pink-900/20 text-pink-600 dark:text-pink-400' }}">
                            {{ ucfirst(strtolower($item->jenis_transaksi)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-medium
                            {{ in_array($item->status, ['aktif', 'AKTIF'])
                                ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                                : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' }}">
                            {{ in_array($item->status, ['aktif', 'AKTIF']) ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            @if($item->status === 'tidak_aktif')
                            <button type="button"
                                onclick="openDeleteModal(
                                    '{{ route('dashboard.kategori-transaksi.destroy', $item) }}'
                                )"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif

                            <button type="button" onclick="openModal('editKategoriModal{{ $item->id }}')"
                                class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                        Belum ada data kategori transaksi.
                        <a href="{{ route('dashboard.kategori-transaksi.create') }}" class="text-green-600 hover:underline ml-1">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($kategori->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($kategori->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $kategori->previousPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
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
                <a href="{{ $kategori->nextPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $kategori->firstItem() }} to {{ $kategori->lastItem() }} of {{ $kategori->total() }} entries
            </span>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    setTimeout(() => {
        const successAlert = document.getElementById('success-alert');

        if (successAlert) {
            successAlert.classList.add('opacity-0');

            setTimeout(() => {
                successAlert.remove();
            }, 500);
        }
    }, 5000);

    setTimeout(() => {
        const errorAlert = document.getElementById('error-alert');

        if (errorAlert) {
            errorAlert.classList.add('opacity-0');

            setTimeout(() => {
                errorAlert.remove();
            }, 500);
        }
    }, 5000);
</script>
@endpush

@endsection

<x-confirm-modal
    id="deleteModal"
    title="Hapus Kategori Transaksi"
    message="Data kategori transaksi yang dihapus tidak dapat dikembalikan."
/>

@include('dashboard.kategori-transaksi.create')
@include('dashboard.kategori-transaksi.edit')