@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Kegiatan Khusus</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.kegiatan.create') }}"
               class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                Tambah Kegiatan
            </a>
        </div>
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

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            {{-- Search --}}
            <form method="GET" action="{{ route('dashboard.kegiatan.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search..."
                        class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                </div>
            </form>

            {{-- Filters --}}
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('dashboard.kegiatan.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <select name="jenis" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="">Semua Jenis</option>
                        <option value="QURBAN"  {{ request('jenis') == 'QURBAN'    ? 'selected' : '' }}>Qurban</option>
                        <option value="ZAKAT"   {{ request('jenis') == 'ZAKAT'     ? 'selected' : '' }}>Zakat</option>
                        <option value="KAJIAN"  {{ request('jenis') == 'KAJIAN'    ? 'selected' : '' }}>Kajian</option>
                        <option value="SOSIAL"  {{ request('jenis') == 'SOSIAL'    ? 'selected' : '' }}>Sosial</option>
                        <option value="LAINNYA" {{ request('jenis') == 'LAINNYA'   ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </form>

                <form method="GET" action="{{ route('dashboard.kegiatan.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                    <select name="status" onchange="this.form.submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400">
                        <option value="">Semua Status</option>
                        <option value="DRAFT"      {{ request('status') == 'DRAFT'      ? 'selected' : '' }}>Draft</option>
                        <option value="BERJALAN"   {{ request('status') == 'BERJALAN'   ? 'selected' : '' }}>Berjalan</option>
                        <option value="SELESAI"    {{ request('status') == 'SELESAI'    ? 'selected' : '' }}>Selesai</option>
                        <option value="DIBATALKAN" {{ request('status') == 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">No</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Nama Kegiatan</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Jenis</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Anggaran</th>
                        <th class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Panitia</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($kegiatan as $index => $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                    <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                        {{ $kegiatan->firstItem() + $index }}
                    </td>
                    <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                        {{ $item->nama_kegiatan }}
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                            {{ $item->jenis_kegiatan }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                        {{ $item->tanggal_mulai->format('d/m/Y') }}
                        @if($item->tanggal_selesai)
                            - {{ $item->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                        Rp {{ number_format($item->anggaran, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                        {{ $item->panitia->name }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($item->status == 'DRAFT')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                Draft
                            </span>
                        @elseif($item->status == 'BERJALAN')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                                Berjalan
                            </span>
                        @elseif($item->status == 'SELESAI')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                Selesai
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400">
                                Dibatalkan
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('dashboard.kegiatan.show', $item) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('dashboard.kegiatan.edit', $item) }}"
                               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('dashboard.kegiatan.destroy', $item) }}"
                                  onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                        Tidak ada data kegiatan.
                        <a href="{{ route('dashboard.kegiatan.create') }}" class="text-green-600 hover:underline ml-1">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($kegiatan->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                @if($kegiatan->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $kegiatan->previousPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                @foreach($kegiatan->getUrlRange(1, $kegiatan->lastPage()) as $page => $url)
                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $kegiatan->currentPage()
                           ? 'bg-green-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                @if($kegiatan->hasMorePages())
                <a href="{{ $kegiatan->nextPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $kegiatan->firstItem() }} to {{ $kegiatan->lastItem() }} of {{ $kegiatan->total() }} entries
            </span>
        </div>
        @endif

    </div>
</div>
@endsection