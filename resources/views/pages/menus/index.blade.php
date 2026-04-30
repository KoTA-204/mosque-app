@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Manajemen Menu</h2>
        <a href="{{ route('menus.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-opacity-90">
            + Tambah Menu
        </a>
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
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Nama Menu</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Route</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Parent</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Sub Menu</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Urutan</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Status</th>
                        <th class="px-4 py-4 font-medium text-black dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr class="border-t border-stroke dark:border-strokedark">
                        <td class="px-4 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-4 py-4 text-sm font-medium text-black dark:text-white">
                            {{ $menu->menu_name }}
                        </td>
                        <td class="px-4 py-4 text-sm font-mono text-body dark:text-bodydark">
                            {{ $menu->route_name ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-sm text-body dark:text-bodydark">
                            {{ $menu->parent?->menu_name ?? '-' }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if($menu->children->count() > 0)
                                <div class="flex flex-col gap-1">
                                    @foreach($menu->children as $child)
                                        <span class="text-xs text-body dark:text-bodydark">
                                            └ {{ $child->menu_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-body dark:text-bodydark">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-body dark:text-bodydark">
                            {{ $menu->sort_order ?? 0 }}
                        </td>
                        <td class="px-4 py-4 text-sm">
                            @if($menu->is_active)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('menus.show', $menu) }}"
                                   class="rounded bg-blue-100 px-3 py-1 text-xs text-blue-700 hover:bg-blue-200">
                                    Detail
                                </a>
                                <a href="{{ route('menus.edit', $menu) }}"
                                   class="rounded bg-yellow-100 px-3 py-1 text-xs text-yellow-700 hover:bg-yellow-200">
                                    Edit
                                </a>
                                <form action="{{ route('menus.destroy', $menu) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus menu ini?')">
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
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-body dark:text-bodydark">
                            Belum ada menu
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection