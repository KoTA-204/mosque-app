@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Role Management</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard.roles.create') }}"
               class="inline-flex items-center gap-2 border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                Tambah Role
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

    {{-- Card Utama --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        {{-- Toolbar: Search --}}
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-wrap">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if($search)
                    Hasil pencarian "<span class="font-medium text-gray-900 dark:text-white">{{ $search }}</span>":
                    <span class="font-medium">{{ $roles->total() }}</span> role ditemukan
                @else
                    Total <span class="font-medium text-gray-900 dark:text-white">{{ $roles->total() }}</span> role
                @endif
            </p>
            <form method="GET" action="{{ route('dashboard.roles.index') }}" class="flex items-center gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Search..."
                           class="pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-green-400 w-56 placeholder-gray-400">
                </div>
                @if($search)
                    <a href="{{ route('dashboard.roles.index') }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Card Grid --}}
        <div class="p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        @forelse($roles as $role)
        <div class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-shadow duration-200 hover:shadow-md">

            {{-- Card Body --}}
            <div class="flex flex-1 flex-col p-5">

                {{-- Top: Name + Badge --}}
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <h3 class="truncate text-base font-semibold text-gray-900 dark:text-white">
                            {{ $role->role_name }}
                        </h3>
                    </div>
                    @if($role->is_active)
                        <span class="flex-shrink-0 rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-xs font-medium text-green-600 dark:text-green-400">
                            Aktif
                        </span>
                    @else
                        <span class="flex-shrink-0 rounded-full bg-red-50 dark:bg-red-900/20 px-2.5 py-0.5 text-xs font-medium text-red-600 dark:text-red-400">
                            Nonaktif
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                <p class="mb-4 flex-1 text-sm leading-relaxed text-gray-500 dark:text-gray-400 line-clamp-2">
                    {{ $role->description ?? 'Tidak ada deskripsi' }}
                </p>

                {{-- Stats --}}
                <div class="flex items-center gap-4 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span><strong class="text-gray-800 dark:text-gray-200">{{ $role->users_count }}</strong> User</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span><strong class="text-gray-800 dark:text-gray-200">{{ $role->permissions->count() }}</strong> Permission</span>
                    </div>
                </div>
            </div>

            {{-- Card Footer Actions --}}
            <div class="flex items-center justify-between gap-2 border-t border-gray-100 dark:border-gray-800 px-5 py-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.roles.edit', $role) }}"
                       class="flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('dashboard.roles.destroy', $role) }}" method="POST"
                          onsubmit="return confirm('Yakin hapus role \'{{ addslashes($role->role_name) }}\'?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="flex items-center gap-1.5 rounded-lg border border-red-200 dark:border-red-800 px-3 py-1.5 text-xs font-medium text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                            Hapus
                        </button>
                    </form>
                </div>
                <a href="{{ route('dashboard.roles.show', $role) }}"
                   class="flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Detail
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                     class="text-gray-400">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <p class="mb-1 text-sm font-medium text-gray-900 dark:text-white">
                @if($search)
                    Tidak ada role yang sesuai pencarian
                @else
                    Belum ada role
                @endif
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if($search)
                    Coba kata kunci lain atau <a href="{{ route('dashboard.roles.index') }}" class="text-green-600 hover:underline">reset pencarian</a>
                @else
                    Mulai dengan <a href="{{ route('dashboard.roles.create') }}" class="text-green-600 hover:underline">menambahkan role baru</a>
                @endif
            </p>
        </div>
        @endforelse

        {{-- Card Tambah Role --}}
        @if(!$search && $roles->count() > 0)
        <a href="{{ route('dashboard.roles.create') }}"
           class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 hover:border-green-400 hover:bg-green-50/30 dark:hover:border-green-600 dark:hover:bg-green-900/10 transition-colors min-h-[180px]">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-700">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Tambah Role</span>
            <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Buat role baru</span>
        </a>
        @endif

        </div>{{-- end grid --}}
        </div>{{-- end p-5 --}}

    {{-- Pagination --}}
    @if($roles->hasPages())
    <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">
        <div class="flex items-center gap-1">

            {{-- Previous --}}
            @if($roles->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
            @else
                <a href="{{ $roles->previousPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
            @endif

            {{-- Page Numbers --}}
            @php
                $current = $roles->currentPage();
                $last    = $roles->lastPage();
                $pages   = [1];
                for ($i = max(2, $current - 1); $i <= min($last - 1, $current + 1); $i++) {
                    $pages[] = $i;
                }
                if ($last > 1) $pages[] = $last;
                $pages = array_unique($pages);
                sort($pages);
            @endphp

            @php $prev = null; @endphp
            @foreach($pages as $page)
                @if($prev !== null && $page - $prev > 1)
                    <span class="w-8 h-8 flex items-center justify-center text-sm text-gray-400">…</span>
                @endif
                <a href="{{ $roles->url($page) }}"
                   class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                       {{ $page === $current
                           ? 'bg-green-600 text-white font-medium'
                           : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $page }}
                </a>
                @php $prev = $page; @endphp
            @endforeach

            {{-- Next --}}
            @if($roles->hasMorePages())
                <a href="{{ $roles->nextPageUrl() }}"
                   class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
            @else
                <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600 border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
            @endif

        </div>

        <span class="text-xs text-gray-400 dark:text-gray-600">
            Showing {{ $roles->firstItem() }} to {{ $roles->lastItem() }} of {{ $roles->total() }} entries
        </span>
    </div>
    @endif

    </div>{{-- end card utama --}}

</div>
@endsection