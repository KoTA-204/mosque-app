@extends('layouts.app')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Kegiatan Khusus</h4>
        </div>

        <div class="p-6">
            <div class="mx-auto max-w-2xl">
                <form action="{{ route('dashboard.kegiatan.update', $kegiatan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <!-- Nama Kegiatan -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Nama Kegiatan<span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_kegiatan"
                                value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}"
                                placeholder="Masukkan nama kegiatan"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('nama_kegiatan') border-red-500 @enderror" />
                            @error('nama_kegiatan')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Kegiatan -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Jenis Kegiatan<span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_kegiatan"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('jenis_kegiatan') border-red-500 @enderror">
                                <option value="">Pilih Jenis</option>
                                <option value="QURBAN"
                                    {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) == 'QURBAN' ? 'selected' : '' }}>
                                    Qurban</option>
                                <option value="ZAKAT"
                                    {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) == 'ZAKAT' ? 'selected' : '' }}>Zakat
                                </option>
                                <option value="KAJIAN"
                                    {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) == 'KAJIAN' ? 'selected' : '' }}>
                                    Kajian</option>
                                <option value="SOSIAL"
                                    {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) == 'SOSIAL' ? 'selected' : '' }}>
                                    Sosial</option>
                                <option value="LAINNYA"
                                    {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) == 'LAINNYA' ? 'selected' : '' }}>
                                    Lainnya</option>
                            </select>
                            @error('jenis_kegiatan')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Tanggal Mulai<span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tanggal_mulai"
                                    value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai->format('Y-m-d')) }}"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('tanggal_mulai') border-red-500 @enderror" />
                                @error('tanggal_mulai')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Tanggal Selesai
                                </label>
                                <input type="date" name="tanggal_selesai"
                                    value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai?->format('Y-m-d')) }}"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('tanggal_selesai') border-red-500 @enderror" />
                                @error('tanggal_selesai')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Anggaran -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Anggaran (Rp)<span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="anggaran" value="{{ old('anggaran', $kegiatan->anggaran) }}"
                                min="0" step="0.01" placeholder="0"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('anggaran') border-red-500 @enderror" />
                            @error('anggaran')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Panitia & Status -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Panitia<span class="text-red-500">*</span>
                                </label>
                                <select name="panitia_id"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('panitia_id') border-red-500 @enderror">
                                    <option value="">Pilih Panitia</option>
                                    @foreach ($panitias as $panitia)
                                        <option value="{{ $panitia->id }}"
                                            {{ old('panitia_id', $kegiatan->panitia_id) == $panitia->id ? 'selected' : '' }}>
                                            {{ $panitia->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('panitia_id')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status<span class="text-red-500">*</span>
                                </label>
                                <select name="status"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('status') border-red-500 @enderror">
                                    <option value="DRAFT" {{ old('status', $kegiatan->status) == 'DRAFT' ? 'selected' : '' }}>
                                        Draft</option>
                                    <option value="BERJALAN"
                                        {{ old('status', $kegiatan->status) == 'BERJALAN' ? 'selected' : '' }}>Berjalan
                                    </option>
                                    <option value="SELESAI"
                                        {{ old('status', $kegiatan->status) == 'SELESAI' ? 'selected' : '' }}>Selesai
                                    </option>
                                    <option value="DIBATALKAN"
                                        {{ old('status', $kegiatan->status) == 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan
                                    </option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3 pt-3">
                            <a href="{{ route('dashboard.kegiatan.index') }}"
                                class="inline-flex flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#c8d300] px-4 py-3 text-sm font-medium text-gray-900 transition hover:bg-[#b3bd00]">
                                Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection