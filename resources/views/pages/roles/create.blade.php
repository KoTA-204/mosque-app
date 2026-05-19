@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Tambah Role</h2>
    </div>

    <form action="{{ route('dashboard.roles.store') }}" method="POST">
        @csrf

        {{-- Info Role --}}
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
            <h3 class="mb-4 text-lg font-medium text-black dark:text-white">Informasi Role</h3>

            {{-- Role Name --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Nama Role <span class="text-red-500">*</span>
                </label>
                <input type="text" name="role_name" value="{{ old('role_name') }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('role_name') border-red-500 @enderror"
                       placeholder="Contoh: Bendahara 1">
                @error('role_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Deskripsi
                </label>
                <textarea name="description" rows="3"
                          class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white"
                          placeholder="Deskripsi role (opsional)">{{ old('description') }}</textarea>
            </div>

            {{-- Is Active --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-black dark:text-white">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-stroke">
                    Aktif
                </label>
            </div>
        </div>

        {{-- Permission Matrix --}}
        <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark mb-6">
            <h3 class="mb-4 text-lg font-medium text-black dark:text-white">Permissions</h3>

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

                                            $permission = $module
                                                ? ($permissions[$module] ?? collect())
                                                    ->where('action', $action)
                                                    ->first()
                                                : null;
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if($permission)
                                                <input type="checkbox"
                                                    name="permission_ids[]"
                                                    value="{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permission_ids', [])) ? 'checked' : '' }}
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

                                        $permission = $module
                                            ? ($permissions[$module] ?? collect())
                                                ->where('action', $action)
                                                ->first()
                                            : null;
                                    @endphp
                                    <td class="px-4 py-3 text-center">
                                        @if($permission)
                                            <input type="checkbox"
                                                name="permission_ids[]"
                                                value="{{ $permission->id }}"
                                                {{ in_array($permission->id, old('permission_ids', [])) ? 'checked' : '' }}
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
            <a href="{{ route('dashboard.roles.index') }}"
               class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:text-white">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection