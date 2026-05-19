@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Edit Permission</h2>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <form action="{{ route('dashboard.permissions.update', $permission) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Permission Code --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Kode Permission <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permission_code"
                       value="{{ old('permission_code', $permission->permission_code) }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('permission_code') border-red-500 @enderror">
                @error('permission_code')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Permission Name --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Nama Permission <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permission_name"
                       value="{{ old('permission_name', $permission->permission_name) }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('permission_name') border-red-500 @enderror">
                @error('permission_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Module --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Module <span class="text-red-500">*</span>
                </label>
                <input type="text" name="module"
                       value="{{ old('module', $permission->module) }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('module') border-red-500 @enderror">
                @error('module')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Action <span class="text-red-500">*</span>
                </label>
                <select name="action"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('action') border-red-500 @enderror">
                    <option value="">-- Pilih Action --</option>
                    <option value="view"   {{ old('action', $permission->action) == 'view'   ? 'selected' : '' }}>View</option>
                    <option value="create" {{ old('action', $permission->action) == 'create' ? 'selected' : '' }}>Create</option>
                    <option value="update" {{ old('action', $permission->action) == 'update' ? 'selected' : '' }}>Update</option>
                    <option value="delete" {{ old('action', $permission->action) == 'delete' ? 'selected' : '' }}>Delete</option>
                    <option value="manage" {{ old('action', $permission->action) == 'manage' ? 'selected' : '' }}>Manage</option>
                </select>
                @error('action')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Deskripsi
                </label>
                <textarea name="description" rows="3"
                          class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">{{ old('description', $permission->description) }}</textarea>
            </div>

            {{-- Is Active --}}
            <div class="mb-6">
                <label class="flex items-center gap-2 text-sm font-medium text-black dark:text-white">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $permission->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-stroke">
                    Aktif
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors duration-150">
                    Update
                </button>
                <a href="{{ route('dashboard.permissions.index') }}"
                   class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:text-white">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection