@extends('layouts.app')

@section('title', 'Manajemen Hak Akses')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Hak Akses</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.hak-akses.create') }}"
               class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Hak Akses
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
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

    {{-- Table Card --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar --}}
        <form method="GET" id="filter-form" class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">

            {{-- Left: show entries + filter dropdowns --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- Show entries --}}
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                    <select name="per_page" onchange="document.getElementById('filter-form').submit()"
                        class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-blue-400">
                        @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
                </div>

                {{-- Divider --}}
                <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 shrink-0"></div>

                {{-- Filter Module --}}
                <div class="relative shrink-0">
                    <select name="module" onchange="document.getElementById('filter-form').submit()"
                        class="text-sm border rounded-xl px-3 py-2 pr-8 appearance-none outline-none transition-colors bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                            {{ request('module') ? 'border-blue-400 text-blue-600 dark:text-blue-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}">
                        <option value="">Semua Module</option>
                        @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Filter Action --}}
                <div class="relative shrink-0">
                    <select name="action" onchange="document.getElementById('filter-form').submit()"
                        class="text-sm border rounded-xl px-3 py-2 pr-8 appearance-none outline-none transition-colors bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                            {{ request('action') ? 'border-blue-400 text-blue-600 dark:text-blue-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}">
                        <option value="">Semua Action</option>
                        @foreach(['view', 'create', 'update', 'delete', 'manage'] as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Reset — tampil hanya jika ada filter aktif --}}
                @if(request('search') || request('module') || request('action'))
                <a href="{{ route('dashboard.hak-akses.index') }}"
                   class="shrink-0 text-sm text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                    Reset
                </a>
                @endif

            </div>

            {{-- Right: search --}}
            <div class="relative shrink-0">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari hak_akses..."
                    class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-blue-400 w-52 placeholder-gray-400">
            </div>

        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3 w-12">No</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Kode</th>
                        <th class="text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Nama</th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Module
                            @if(request('module'))
                                <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-blue-500 align-middle"></span>
                            @endif
                        </th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">
                            Action
                            @if(request('action'))
                                <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-blue-500 align-middle"></span>
                            @endif
                        </th>

                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($hak_akses as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                    <td class="px-5 py-3.5 text-center text-gray-500 dark:text-gray-400">
                        {{ $hak_akses->firstItem() + $loop->index }}
                    </td>
                    <td class="px-4 py-3.5 font-mono text-xs text-gray-700 dark:text-gray-300">
                        {{ $item->kode_hak_akses }}
                    </td>
                    <td class="px-4 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                        {{ $item->nama_hak_akses }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                            {{ $item->modul }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @php
                            $actionStyles = [
                                'view'   => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400',
                                'create' => 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400',
                                'update' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400',
                                'delete' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400',
                            ];
                            $style = $actionStyles[$item->aksi] ?? 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $style }}">
                            {{ ucfirst($item->aksi) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-sm font-medium {{ $item->aktif ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                            {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Detail --}}
                            <a href="{{ route('dashboard.hak-akses.show', $item) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                               title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('dashboard.hak-akses.edit', $item) }}"
                               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Delete --}}
                            <form action="{{ route('dashboard.hak-akses.destroy', $item) }}" method="POST"
                                  data-confirm="Yakin hapus hak akses ini?" data-confirm-label="Hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                        title="Hapus">
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
                    <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-600">
                        Belum ada data hak akses.
                        <a href="{{ route('dashboard.hak-akses.create') }}" class="text-blue-600 hover:underline ml-1">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($hak_akses->hasPages())
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($hak_akses->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
                @else
                <a href="{{ $hak_akses->previousPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($hak_akses->getUrlRange(1, $hak_akses->lastPage()) as $page => $url)
                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $hak_akses->currentPage()
                           ? 'bg-blue-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @endforeach

                {{-- Next --}}
                @if($hak_akses->hasMorePages())
                <a href="{{ $hak_akses->nextPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
                @endif
            </div>

            <span class="text-xs text-gray-400 dark:text-gray-600">
                Showing {{ $hak_akses->firstItem() }} to {{ $hak_akses->lastItem() }} of {{ $hak_akses->total() }} entries
            </span>
        </div>
        @endif

    </div>
</div>
@endsection