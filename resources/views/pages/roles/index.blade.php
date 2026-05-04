@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-dark dark:text-white shrink-0">
            Role Management
        </h2>

        <div class="flex items-center gap-3 flex-1 justify-end">
            {{-- Search Bar --}}
            <form method="GET" action="{{ route('dashboard.roles.index') }}"
                class="relative flex-1 max-w-sm">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11 4C7.13401 4 4 7.13401 4 11C4 14.866 7.13401 18 11 18C14.866 18 18 14.866 18 11C18 7.13401 14.866 4 11 4ZM2 11C2 6.02944 6.02944 2 11 2C15.9706 2 20 6.02944 20 11C20 15.9706 15.9706 20 11 20C6.02944 20 2 15.9706 2 11Z" fill="currentColor"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9429 15.9429C16.3334 15.5524 16.9666 15.5524 17.3571 15.9429L21.7071 20.2929C22.0976 20.6834 22.0976 21.3166 21.7071 21.7071C21.3166 22.0976 20.6834 22.0976 20.2929 21.7071L15.9429 17.3571C15.5524 16.9666 15.5524 16.3334 15.9429 15.9429Z" fill="currentColor"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari role..."
                    class="w-full rounded-lg border border-stroke py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
            </form>

            {{-- Tambah Role Button --}}
            <a href="{{ route('dashboard.roles.create') }}"
            class="flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors duration-150 shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Role
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-red-800">
        {{ session('error') }}
    </div>
    @endif

    {{-- Card Grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

        @forelse($roles as $role)
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

            {{-- Card Header --}}
            <div class="mb-3 flex items-start justify-between">
                <h3 class="text-lg font-semibold text-black dark:text-white">
                    {{ $role->role_name }}
                </h3>
                <span class="rounded-full border border-stroke px-3 py-1 text-xs text-body dark:border-strokedark dark:text-bodydark">
                    {{ $role->users_count }} User
                </span>
            </div>

            {{-- Deskripsi --}}
            <p class="mb-6 text-sm text-body dark:text-bodydark">
                {{ $role->description ?? '-' }}
            </p>

            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.roles.edit', $role) }}"
                       class="rounded-lg border border-stroke px-4 py-2 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                        Edit
                    </a>
                    <form action="{{ route('dashboard.roles.destroy', $role) }}" method="POST"
                          onsubmit="return confirm('Yakin hapus role {{ $role->role_name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-500 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900">
                            Hapus
                        </button>
                    </form>
                </div>
                <a href="{{ route('dashboard.roles.show', $role) }}"
                   class="rounded-lg border border-stroke px-4 py-2 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                    Detail
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-10 text-center text-sm text-body dark:text-bodydark">
            @if($search)
                Tidak ada role yang sesuai pencarian "{{ $search }}"
            @else
                Belum ada role
            @endif
        </div>
        @endforelse

        {{-- Card Tambah Role --}}
        @if(!$search)
        <a href="{{ route('dashboard.roles.create') }}"
           class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-stroke bg-white p-6 shadow-default hover:border-primary hover:bg-gray-50 dark:border-strokedark dark:bg-boxdark dark:hover:border-primary dark:hover:bg-meta-4">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full border-2 border-stroke dark:border-strokedark">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="text-sm font-medium text-black dark:text-white">Tambah Role</span>
        </a>
        @endif

    </div>

    {{-- Pagination --}}
    @if($roles->hasPages())
    <div class="mt-6 flex items-center justify-between">

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Menampilkan {{ $roles->firstItem() }}–{{ $roles->lastItem() }}
            dari {{ $roles->total() }} role
        </p>

        <div class="flex items-center gap-1">

            {{-- Previous --}}
            @if($roles->onFirstPage())
                <span class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </span>
            @else
                <a href="{{ $roles->previousPageUrl() }}"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
            @endif

            {{-- Page Numbers with Ellipsis --}}
            @php
                $current = $roles->currentPage();
                $last = $roles->lastPage();
                $pages = [];

                $pages[] = 1;

                for ($i = max(2, $current - 1); $i <= min($last - 1, $current + 1); $i++) {
                    $pages[] = $i;
                }

                if ($last > 1) {
                    $pages[] = $last;
                }

                $pages = array_unique($pages);
                sort($pages);
            @endphp

            @php $prev = null; @endphp
            @foreach($pages as $page)
                @if($prev !== null && $page - $prev > 1)
                    <span class="flex items-center justify-center w-9 h-9 text-sm text-gray-400 dark:text-gray-500">
                        ...
                    </span>
                @endif

                @if($page == $current)
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-500 text-white text-sm font-medium">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $roles->url($page) }}"
                       class="flex items-center justify-center w-9 h-9 rounded-lg text-sm text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors">
                        {{ $page }}
                    </a>
                @endif

                @php $prev = $page; @endphp
            @endforeach

            {{-- Next --}}
            @if($roles->hasMorePages())
                <a href="{{ $roles->nextPageUrl() }}"
                   class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            @else
                <span class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </span>
            @endif

        </div>
    </div>
    @endif

</div>
@endsection