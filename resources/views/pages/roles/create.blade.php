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
                    <tbody>
                        @foreach($menus as $menu)
                            {{-- Parent Menu Row --}}
                            <tr class="border-t border-stroke bg-gray-50 dark:border-strokedark dark:bg-meta-4">
                                <td class="px-4 py-3 text-sm font-semibold text-black dark:text-white" colspan="{{ count($actions) + 1 }}">
                                    {{ $menu->menu_name }}
                                </td>
                            </tr>

                            {{-- Child Menu Rows --}}
                            @if($menu->children->count() > 0)
                                @foreach($menu->children as $child)
                                    <tr class="border-t border-stroke dark:border-strokedark">
                                        <td class="px-4 py-3 text-sm text-black dark:text-white pl-8">
                                            └ {{ $child->menu_name }}
                                        </td>
                                        @foreach($actions as $action)
                                            @php
                                                $permission = $child->permissions
                                                    ->where('action', $action)
                                                    ->first();
                                            @endphp
                                            <td class="px-4 py-3 text-center">
                                                @if($permission)
                                                    <input type="checkbox"
                                                           name="permission_ids[]"
                                                           value="{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permission_ids', [])) ? 'checked' : '' }}
                                                           class="h-4 w-4 rounded border-stroke">
                                                @else
                                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                {{-- Parent tanpa child (misal Dashboard) --}}
                                <tr class="border-t border-stroke dark:border-strokedark">
                                    <td class="px-4 py-3 text-sm text-black dark:text-white pl-8">
                                        └ {{ $menu->menu_name }}
                                    </td>
                                    @foreach($actions as $action)
                                        @php
                                            $permission = $menu->permissions
                                                ->where('action', $action)
                                                ->first();
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if($permission)
                                                <input type="checkbox"
                                                       name="permission_ids[]"
                                                       value="{{ $permission->id }}"
                                                       {{ in_array($permission->id, old('permission_ids', [])) ? 'checked' : '' }}
                                                       class="h-4 w-4 rounded border-stroke">
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90">
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