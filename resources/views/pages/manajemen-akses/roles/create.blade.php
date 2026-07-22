@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.peran.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Peran</h1>
        </div>
    </div>

    <form action="{{ route('dashboard.peran.store') }}" method="POST">
        @csrf

        @error('hak_akses')
        <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            $message 
        </div>
        @enderror

        {{-- Info Peran --}}
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
            <h3 class="mb-4 text-lg font-medium text-black dark:text-white">Informasi Peran</h3>

            {{-- Peran Name --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Nama Peran <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_peran" value="{{ old('nama_peran') }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('nama_peran') border-red-500 @enderror"
                       placeholder="Contoh: Bendahara 1">
                @error('nama_peran')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Deskripsi
                </label>
                <textarea name="deskripsi" rows="3"
                          class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white"
                          placeholder="Deskripsi peran (opsional)">{{ old('deskripsi') }}</textarea>
            </div>
        </div>

        {{-- HakAkses Matrix --}}
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
            <h3 class="mb-4 text-lg font-medium text-black dark:text-white">Hak Akses</h3>

            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="px-4 py-3 font-medium text-black dark:text-white">Menu</th>
                            @foreach($actions as $action)
                                <th class="px-4 py-3 text-center font-medium text-black dark:text-white capitalize">
                                    {{ $action }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    @foreach($menus as $menu)
                    <tbody x-data="{ open: true }">
                        {{-- Parent Row --}}
                        <tr
                            @click="open = !open"
                            class="border-t border-stroke dark:border-strokedark bg-gray-50 dark:bg-meta-4 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="px-4 py-3 text-sm font-semibold text-black dark:text-white" colspan="{{ count($actions) + 1 }}">
                                <div class="flex items-center gap-2">
                                    <svg
                                        class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0"
                                        :class="{ 'rotate-90': open }"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    {{ $menu->menu_name }}
                                    @if($menu->children->count() > 0)
                                        <span class="text-xs font-normal text-gray-400 dark:text-gray-500">
                                            ({{ $menu->children->count() }} submenu)
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Child Rows --}}
                        @if($menu->children->count() > 0)
                            @foreach($menu->children as $child)
                                <tr
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    @click.stop
                                    class="border-t border-stroke dark:border-strokedark hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-black dark:text-white">
                                        <div class="flex items-center gap-2 pl-6">
                                            <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
                                            <span>{{ $child->menu_name }}</span>
                                        </div>
                                    </td>
                                    @foreach($actions as $action)
                                        @php
                                            $routeParts = $child->route_name
                                                ? explode('.', $child->route_name)
                                                : [];

                                            $module = count($routeParts) === 2
                                                ? 'dashboard'
                                                : ($routeParts[1] ?? null);

                                            $hak_akses = $module
                                                ? ($hak_akses[$module] ?? collect())
                                                    ->where('aksi', $action)
                                                    ->first()
                                                : null;
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if($hak_akses)
                                                <input type="checkbox"
                                                    name="hak_akses_ids[]"
                                                    value="{{ $hak_akses->id }}"
                                                    {{ in_array($hak_akses->id, old('hak_akses_ids', [])) ? 'checked' : '' }}
                                                    class="h-4 w-4 rounded border-stroke cursor-pointer">
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @else
                            {{-- Parent tanpa child --}}
                            <tr
                                x-show="open"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                @click.stop
                                class="border-t border-stroke dark:border-strokedark hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                <td class="px-4 py-3 text-sm text-black dark:text-white">
                                    <div class="flex items-center gap-2 pl-6">
                                        <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>
                                        <span>{{ $menu->menu_name }}</span>
                                    </div>
                                </td>
                                @foreach($actions as $action)
                                    @php
                                        $routeParts = $menu->route_name
                                            ? explode('.', $menu->route_name)
                                            : [];

                                        $module = count($routeParts) === 2
                                            ? 'dashboard'
                                            : ($routeParts[1] ?? null);

                                        $hak_akses = $module
                                            ? ($hak_akses[$module] ?? collect())
                                                ->where('aksi', $action)
                                                ->first()
                                            : null;
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        @if($hak_akses)
                                            <input type="checkbox"
                                                name="hak_akses_ids[]"
                                                value="{{ $hak_akses->id }}"
                                                {{ in_array($hak_akses->id, old('hak_akses_ids', [])) ? 'checked' : '' }}
                                                class="h-4 w-4 rounded border-stroke cursor-pointer">
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    </tbody>
                    @endforeach

                </table>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors duration-150">
                Simpan
            </button>
            <a href="{{ route('dashboard.peran.index') }}"
               class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:text-white">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection