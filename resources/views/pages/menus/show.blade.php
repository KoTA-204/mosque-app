@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Detail Menu</h2>
        <a href="{{ route('dashboard.menus.index') }}"
           class="rounded-lg border border-stroke px-4 py-2 text-sm text-black hover:bg-gray-100 dark:text-white">
            ← Kembali
        </a>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Nama Menu</p>
            <p class="text-sm font-medium text-black dark:text-white">{{ $menu->menu_name }}</p>
        </div>

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Route Name</p>
            <p class="font-mono text-sm text-black dark:text-white">{{ $menu->route_name ?? '-' }}</p>
        </div>

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Icon</p>
            <p class="text-sm text-black dark:text-white">{{ $menu->icon ?? '-' }}</p>
        </div>

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Parent Menu</p>
            <p class="text-sm text-black dark:text-white">{{ $menu->parent?->menu_name ?? '-' }}</p>
        </div>

        <div class="mb-4">
            <p class="text-xs text-body dark:text-bodydark">Urutan</p>
            <p class="text-sm text-black dark:text-white">{{ $menu->sort_order ?? 0 }}</p>
        </div>

        <div class="mb-6">
            <p class="text-xs text-body dark:text-bodydark">Status</p>
            @if($menu->is_active)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">Aktif</span>
            @else
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800">Nonaktif</span>
            @endif
        </div>

        {{-- Sub Menu --}}
        <div class="mb-6">
            <p class="mb-2 text-xs text-body dark:text-bodydark">Sub Menu</p>
            <div class="flex flex-col gap-1">
                @forelse($menu->children as $child)
                    <span class="text-sm text-black dark:text-white">
                        └ {{ $child->menu_name }}
                    </span>
                @empty
                    <p class="text-sm text-body dark:text-bodydark">Tidak ada sub menu</p>
                @endforelse
            </div>
        </div>

        {{-- Permissions --}}
        <div>
            <p class="mb-2 text-xs text-body dark:text-bodydark">Permissions</p>
            <div class="flex flex-wrap gap-2">
                @forelse($menu->permissions as $permission)
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                        {{ $permission->permission_name }}
                    </span>
                @empty
                    <p class="text-sm text-body dark:text-bodydark">Belum ada permission</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection