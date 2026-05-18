{{-- ================================================================ --}}
{{-- dashboard/chart-of-account/edit-kategori.blade.php            --}}
{{-- ================================================================ --}}
@extends('layouts.app')
@section('title', 'Edit Kategori Akun')
@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center gap-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <a href="{{ route('dashboard.coa.index') }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Chart of Account</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Edit Kategori Akun</p>
        </div>
    </div>
    <div class="max-w-2xl">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Edit Kategori Akun</h2>
            <form method="POST" action="{{ route('dashboard.coa.kategori.update', $kategori) }}" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kode Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_kategori" value="{{ old('kode_kategori', $kategori->kode_kategori) }}"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors {{ $errors->has('kode_kategori') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('kode_kategori')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors {{ $errors->has('nama_kategori') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('nama_kategori')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
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