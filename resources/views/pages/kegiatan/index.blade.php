@extends('layouts.app')

@section('content')
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-5 dark:border-gray-800">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Manajemen Kegiatan Khusus</h4>
            <a href="{{ route('dashboard.kegiatan.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#c8d300] px-5 py-2.5 text-sm font-medium text-gray-900 transition hover:bg-[#b3bd00]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kegiatan
            </a>
        </div>

        <!-- Filters -->
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <!-- Search -->
                <form action="{{ route('dashboard.kegiatan.index') }}" method="GET">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kegiatan..."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        <button type="submit"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Jenis Filter -->
                <form action="{{ route('dashboard.kegiatan.index') }}" method="GET">
                    <select name="jenis" onchange="this.form.submit()"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Semua Jenis</option>
                        <option value="QURBAN" {{ request('jenis') == 'QURBAN' ? 'selected' : '' }}>Qurban</option>
                        <option value="ZAKAT" {{ request('jenis') == 'ZAKAT' ? 'selected' : '' }}>Zakat</option>
                        <option value="KAJIAN" {{ request('jenis') == 'KAJIAN' ? 'selected' : '' }}>Kajian</option>
                        <option value="SOSIAL" {{ request('jenis') == 'SOSIAL' ? 'selected' : '' }}>Sosial</option>
                        <option value="LAINNYA" {{ request('jenis') == 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </form>

                <!-- Status Filter -->
                <form action="{{ route('dashboard.kegiatan.index') }}" method="GET">
                    <select name="status" onchange="this.form.submit()"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Semua Status</option>
                        <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="BERJALAN" {{ request('status') == 'BERJALAN' ? 'selected' : '' }}>Berjalan</option>
                        <option value="SELESAI" {{ request('status') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                        <option value="DIBATALKAN" {{ request('status') == 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/5">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">No</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Nama
                            Kegiatan</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Jenis</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Tanggal
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Anggaran
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Panitia
                        </th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($kegiatan as $index => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white/90">
                                {{ $kegiatan->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white/90">{{ $item->nama_kegiatan }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $item->jenis_kegiatan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white/90">
                                {{ $item->tanggal_mulai->format('d/m/Y') }}
                                @if ($item->tanggal_selesai)
                                    - {{ $item->tanggal_selesai->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white/90">
                                Rp {{ number_format($item->anggaran, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white/90">
                                {{ $item->panitia->name }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($item->status == 'DRAFT')
                                    <span
                                        class="inline-flex rounded-md bg-gray-500 px-3 py-1 text-xs font-medium text-white">
                                        Draft
                                    </span>
                                @elseif($item->status == 'BERJALAN')
                                    <span
                                        class="inline-flex rounded-md bg-green-500 px-3 py-1 text-xs font-medium text-white">
                                        Berjalan
                                    </span>
                                @elseif($item->status == 'SELESAI')
                                    <span
                                        class="inline-flex rounded-md bg-blue-500 px-3 py-1 text-xs font-medium text-white">
                                        Selesai
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-md bg-red-500 px-3 py-1 text-xs font-medium text-white">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('dashboard.kegiatan.show', $item) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500 text-white transition hover:bg-blue-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('dashboard.kegiatan.edit', $item) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#c8d300] text-gray-900 transition hover:bg-[#b3bd00]">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('dashboard.kegiatan.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-500 text-white transition hover:bg-red-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                Tidak ada data kegiatan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($kegiatan->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $kegiatan->links() }}
            </div>
        @endif
    </div>
@endsection