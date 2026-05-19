@extends('layouts.app')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kegiatan</h4>
            <a href="{{ route('dashboard.kegiatan.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <div class="p-6">
            <div class="mx-auto max-w-2xl space-y-6">
                <!-- Nama Kegiatan -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Kegiatan</label>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
                </div>

                <!-- Jenis -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Kegiatan</label>
                    <span
                        class="inline-flex rounded-md bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                        {{ $kegiatan->jenis_kegiatan }}
                    </span>
                </div>

                <!-- Tanggal -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal
                            Mulai</label>
                        <p class="text-base text-gray-900 dark:text-white">
                            {{ $kegiatan->tanggal_mulai->format('d F Y') }}
                        </p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal
                            Selesai</label>
                        <p class="text-base text-gray-900 dark:text-white">
                            {{ $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->format('d F Y') : '-' }}
                        </p>
                    </div>
                </div>

                <!-- Anggaran -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Anggaran</label>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}
                    </p>
                </div>

                <!-- Panitia -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Penanggung Jawab</label>
                    <p class="text-base text-gray-900 dark:text-white">{{ $kegiatan->panitia->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $kegiatan->panitia->email }}</p>
                </div>

                <!-- Status -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                    @if ($kegiatan->status == 'DRAFT')
                        <span class="inline-flex rounded-md bg-gray-500 px-3 py-1 text-sm font-medium text-white">
                            Draft
                        </span>
                    @elseif($kegiatan->status == 'BERJALAN')
                        <span class="inline-flex rounded-md bg-green-500 px-3 py-1 text-sm font-medium text-white">
                            Berjalan
                        </span>
                    @elseif($kegiatan->status == 'SELESAI')
                        <span class="inline-flex rounded-md bg-blue-500 px-3 py-1 text-sm font-medium text-white">
                            Selesai
                        </span>
                    @else
                        <span class="inline-flex rounded-md bg-red-500 px-3 py-1 text-sm font-medium text-white">
                            Dibatalkan
                        </span>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                    <a href="{{ route('dashboard.kegiatan.edit', $kegiatan) }}"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#c8d300] px-4 py-3 text-sm font-medium text-gray-900 transition hover:bg-[#b3bd00]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('dashboard.kegiatan.destroy', $kegiatan) }}" method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-500 bg-white px-4 py-3 text-sm font-medium text-red-500 transition hover:bg-red-50 dark:bg-gray-900 dark:hover:bg-red-950">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection