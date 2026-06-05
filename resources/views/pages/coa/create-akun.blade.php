@extends('layouts.app')

@section('title', 'Tambah Akun')

@section('content')
<div class="p-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.coa.index') }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Chart of Account</h1>
            </div>
        </div>
    </div><br>

    {{-- Form Card --}}
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-6">Tambah Akun</h2>

            <form method="POST" action="{{ route('dashboard.coa.akun.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nomor Akun <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kode_akun" value="{{ old('kode_akun') }}"
                            placeholder="Masukkan nomor akun"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                                {{ $errors->has('kode_akun') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                        @error('kode_akun')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Nama Akun <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_akun" value="{{ old('nama_akun') }}"
                            placeholder="Masukkan nama akun"
                            class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                                {{ $errors->has('nama_akun') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">
                        @error('nama_akun')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Sub Kategori <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="parent_id"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('parent_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="">Pilih sub kategori</option>
                                @foreach($subKategoriList as $sub)
                                <option value="{{ $sub->id }}" {{ old('parent_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->kode_akun }} – {{ $sub->nama_akun }}
                                </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('parent_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Saldo Normal <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="saldo_normal"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('saldo_normal') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                <option value="debit"  {{ old('saldo_normal','debit') === 'debit'  ? 'selected' : '' }}>Debit</option>
                                <option value="kredit" {{ old('saldo_normal') === 'kredit' ? 'selected' : '' }}>Kredit</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        @error('saldo_normal')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="4"
                        placeholder="Masukan deskripsi akun"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection