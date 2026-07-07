@extends('layouts.app')

@section('title', 'Tambah Permission')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.permissions.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Permission</h1>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Informasi Permission</h2>

            <form method="POST" action="{{ route('dashboard.permissions.store') }}" class="space-y-5">
                @csrf

                @error('permission')
                <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    $message 
                </div>
                @enderror

                {{-- Permission Code --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Kode Permission <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="permission_code" value="{{ old('permission_code') }}"
                        placeholder="Contoh: PEMASUKAN_CREATE"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors font-mono
                            {{ $errors->has('permission_code') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('permission_code')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Permission Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Permission <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="permission_name" value="{{ old('permission_name') }}"
                        placeholder="Contoh: Buat Pemasukan"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                            {{ $errors->has('permission_name') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('permission_name')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Module + Action --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Module <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="module" value="{{ old('module') }}"
                            placeholder="Contoh: pemasukan"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                                {{ $errors->has('module') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                                bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                        @error('module')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Action <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="action"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('action') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">-- Pilih Action --</option>
                                @foreach(['view', 'create', 'update', 'delete', 'manage'] as $act)
                                <option value="{{ $act }}" {{ old('action') == $act ? 'selected' : '' }}>
                                    {{ ucfirst($act) }}
                                </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('action')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3"
                        placeholder="Deskripsi permission (opsional)"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-blue-400 rounded-xl outline-none resize-none transition-colors
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('description') }}</textarea>
                </div>

                {{-- Is Active --}}
                <div>
                    <label class="flex items-center gap-2.5 cursor-pointer w-fit">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span>
                    </label>
                </div>

                {{-- Submit --}}
                <div class="pt-1">
                    <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection