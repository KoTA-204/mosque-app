@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6 flex items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-dark dark:text-white shrink-0">Manajemen Permission</h2>

        <div class="flex items-center gap-3 flex-1 justify-end">
            {{-- Search Bar --}}
            <form method="GET" action="{{ route('dashboard.permissions.index') }}"
                  class="relative flex-1 max-w-sm">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11 4C7.13401 4 4 7.13401 4 11C4 14.866 7.13401 18 11 18C14.866 18 18 14.866 18 11C18 7.13401 14.866 4 11 4ZM2 11C2 6.02944 6.02944 2 11 2C15.9706 2 20 6.02944 20 11C20 15.9706 15.9706 20 11 20C6.02944 20 2 15.9706 2 11Z" fill="currentColor"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9429 15.9429C16.3334 15.5524 16.9666 15.5524 17.3571 15.9429L21.7071 20.2929C22.0976 20.6834 22.0976 21.3166 21.7071 21.7071C21.3166 22.0976 20.6834 22.0976 20.2929 21.7071L15.9429 17.3571C15.5524 16.9666 15.5524 16.3334 15.9429 15.9429Z" fill="currentColor"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                       placeholder="Cari permission..."
                       class="w-full rounded-lg border border-stroke py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
            </form>

            {{-- Tambah Permission Button --}}
            <a href="{{ route('dashboard.permissions.create') }}"
               class="flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors duration-150 shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Permission
            </a>
        </div>
    </div>

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

    <div class="rounded-xl border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="px-4 py-4 font-medium text-black dark:text-white">#</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Kode</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Nama</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Module</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Action</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Status</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                    <tr class="border-t border-stroke dark:border-strokedark">
                        <td class="px-4 py-4 text-sm">{{ ($permissions->currentPage() - 1) * $permissions->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-4 text-sm font-mono text-black dark:text-white">
                            {{ $permission->permission_code }}
                        </td>
                        <td class="px-4 py-4 text-sm text-black dark:text-white">
                            {{ $permission->permission_name }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                {{ $permission->module }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @php
                                $actionColors = [
                                    'view'   => 'bg-blue-100 text-blue-800',
                                    'create' => 'bg-green-100 text-green-800',
                                    'update' => 'bg-yellow-100 text-yellow-800',
                                    'delete' => 'bg-red-100 text-red-800',
                                ];
                                $color = $actionColors[$permission->action] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $color }}">
                                {{ $permission->action }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if($permission->is_active)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('dashboard.permissions.show', $permission) }}"
                                   class="rounded bg-blue-100 px-3 py-1 text-xs text-blue-700 hover:bg-blue-200">
                                    Detail
                                </a>
                                <a href="{{ route('dashboard.permissions.edit', $permission) }}"
                                   class="rounded bg-yellow-100 px-3 py-1 text-xs text-yellow-700 hover:bg-yellow-200">
                                    Edit
                                </a>
                                <form action="{{ route('dashboard.permissions.destroy', $permission) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus permission ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded bg-red-100 px-3 py-1 text-xs text-red-700 hover:bg-red-200">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-body dark:text-bodydark">
                            Belum ada permission
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($permissions->hasPages())
        <div class="flex items-center justify-between px-4 py-4 border-t border-stroke dark:border-strokedark">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ $permissions->firstItem() }}–{{ $permissions->lastItem() }}
                dari {{ $permissions->total() }} permission
            </p>

            <div class="flex items-center gap-1">

                {{-- Previous --}}
                @if($permissions->onFirstPage())
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 dark:text-gray-600 cursor-not-allowed">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </span>
                @else
                    <a href="{{ $permissions->previousPageUrl() }}"
                       class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers with Ellipsis --}}
                @php
                    $current = $permissions->currentPage();
                    $last = $permissions->lastPage();
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
                        <a href="{{ $permissions->url($page) }}"
                           class="flex items-center justify-center w-9 h-9 rounded-lg text-sm text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 transition-colors">
                            {{ $page }}
                        </a>
                    @endif

                    @php $prev = $page; @endphp
                @endforeach

                {{-- Next --}}
                @if($permissions->hasMorePages())
                    <a href="{{ $permissions->nextPageUrl() }}"
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
</div>
@endsection