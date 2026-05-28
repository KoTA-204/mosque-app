@extends('layouts.app')

@section('title', 'Tambah Kategori Transaksi')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.kategori-transaksi.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Kategori Transaksi</h1>
        </div>
        <!-- <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
            </svg>
        </button> -->
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Tambah Kategori</h2>

            <form method="POST" action="{{ route('dashboard.kategori-transaksi.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" value="{{ old('nama_kategori') }}"
                        placeholder="Masukan nama kategori"
                        class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                            {{ $errors->has('nama_kategori') ? 'border-red-400 focus:border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                    @error('nama_kategori')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Jenis Transaksi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_transaksi"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('jenis_transaksi') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="PEMASUKAN"   {{ old('jenis_transaksi', 'PEMASUKAN') === 'PEMASUKAN'   ? 'selected' : '' }}>Pemasukan</option>
                                <option value="PENGELUARAN" {{ old('jenis_transaksi') === 'PENGELUARAN' ? 'selected' : '' }}>Pengeluaran</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('jenis_transaksi')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="status"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('status') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="aktif"       {{ old('status', 'aktif') === 'aktif'       ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status') === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('status')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="4"
                        placeholder="Masukan deskripsi kategori"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                </div>

                {{-- Actions --}}
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