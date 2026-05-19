{{-- dashboard/chart-of-account/edit-sub-kategori.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Sub Kategori Akun')
@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <a href="{{ route('dashboard.coa.index') }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Chart of Account</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Edit Sub Kategori Akun</p>
        </div>
    </div>
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Edit Sub Kategori Akun</h2>
            <form method="POST" action="{{ route('dashboard.coa.sub-kategori.update', $subKategori) }}" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="kategori_akun_id" class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors {{ $errors->has('kategori_akun_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">Pilih kategori</option>
                            @foreach($kategoriList as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_akun_id', $subKategori->kategori_akun_id) == $kat->id ? 'selected' : '' }}>({{ $kat->kode_kategori }}) {{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    @error('kategori_akun_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kode Sub Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_akun" value="{{ old('kode_akun', $subKategori->kode_akun) }}"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors {{ $errors->has('kode_akun') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('kode_akun')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Sub Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_akun" value="{{ old('nama_akun', $subKategori->nama_akun) }}"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors {{ $errors->has('nama_akun') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('nama_akun')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">Simpan Perubahan</button>
                    <a href="{{ route('dashboard.coa.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection