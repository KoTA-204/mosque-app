@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Edit Role</h2>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Role Name --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Nama Role <span class="text-red-500">*</span>
                </label>
                <input type="text" name="role_name" value="{{ old('role_name', $role->role_name) }}"
                       class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('role_name') border-red-500 @enderror">
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
                          class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">{{ old('description', $role->description) }}</textarea>
            </div>

            {{-- Is Active --}}
            <div class="mb-6">
                <label class="flex items-center gap-2 text-sm font-medium text-black dark:text-white">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-stroke">
                    Aktif
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90">
                    Update
                </button>
                <a href="{{ route('roles.index') }}"
                   class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:text-white">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection