@extends('layouts.app')

@section('title', 'Tambah Hak Akses')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.hak-akses.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Hak Akses</h1>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Informasi Hak Akses</h2>

            <form method="POST" action="{{ route('dashboard.hak-akses.store') }}" class="space-y-5">
                @csrf

                @error('hak_akses')
                <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl px-4 py-3 text-sm text-red-700 dark:text-red-400">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    $message 
                </div>
                @enderror

                {{-- HakAkses Code --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Kode Hak Akses <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode_hak_akses" value="{{ old('kode_hak_akses') }}"
                        placeholder="Contoh: PEMASUKAN_CREATE"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors font-mono
                            {{ $errors->has('kode_hak_akses') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('kode_hak_akses')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- HakAkses Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Hak Akses <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_hak_akses" value="{{ old('nama_hak_akses') }}"
                        placeholder="Contoh: Buat Pemasukan"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                            {{ $errors->has('nama_hak_akses') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('nama_hak_akses')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Module + Action --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Module <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="modul" value="{{ old('modul') }}"
                            placeholder="Contoh: pemasukan"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                                {{ $errors->has('modul') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                                bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                        @error('modul')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Action <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="aksi"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('aksi') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-blue-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">-- Pilih Action --</option>
                                @foreach(['view', 'create', 'update', 'delete'] as $act)
                                <option value="{{ $act }}" {{ old('aksi') == $act ? 'selected' : '' }}>
                                    {{ ucfirst($act) }}
                                </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('aksi')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        placeholder="Deskripsi hak_akses (opsional)"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-blue-400 rounded-xl outline-none resize-none transition-colors
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                </div>

                {{-- Is Active --}}
                <div>
                    <label class="flex items-center gap-2.5 cursor-pointer w-fit">
                        <input type="checkbox" name="aktif" value="1"
                               {{ old('aktif', true) ? 'checked' : '' }}
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