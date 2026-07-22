@extends('layouts.app')
@section('title', 'Detail Menu')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.menus.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Menu</h1>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 space-y-6">
        <div class="flex items-center gap-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 [&>svg]:w-6 [&>svg]:h-6">
                {!! \App\Helpers\MenuHelper::getIconSvg($menu->icon) !!}
            </span>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $menu->menu_name }}</h2>
                <p class="text-sm text-gray-400 font-mono">{{ $menu->route_name ?? '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs text-gray-400">Icon</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $menu->icon ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Parent Menu</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $menu->parent?->menu_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Urutan</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $menu->sort_order ?? 0 }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                @if($menu->is_active)
                    <span class="inline-flex rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400">Aktif</span>
                @else
                    <span class="inline-flex rounded-full bg-red-50 dark:bg-red-900/20 px-2.5 py-1 text-xs font-medium text-red-600 dark:text-red-400">Nonaktif</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-400">HakAkses</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $menu->hak_akses?->nama_hak_akses ?? '-' }}</p>
            </div>
        </div>

        {{-- Sub Menu --}}
        <div>
            <p class="text-xs text-gray-400 mb-2">Sub Menu</p>
            <div class="flex flex-col gap-1">
                @forelse($menu->children as $child)
                    <span class="text-sm text-gray-700 dark:text-gray-300">└ {{ $child->menu_name }}</span>
                @empty
                    <p class="text-sm text-gray-400">Tidak ada sub menu</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
