@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.kegiatan-panitia.show', $kegiatan) }}"
               class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Perbaiki Transaksi</h1>
        </div>
    </div>

    {{-- Catatan Revision --}}
    @if($transaksi->catatan_revision)
    <div class="rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 px-5 py-4">
        <p class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200">Catatan dari Bendahara:</p>
        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $transaksi->catatan_revision }}</p>
    </div>
    @endif

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">

            {{-- Info Bar --}}
            <div class="mb-6 flex items-center gap-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 px-5 py-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kegiatan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
                    <p class="text-sm font-semibold font-mono text-gray-900 dark:text-white">
                        TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>

            <form action="{{ route('dashboard.kegiatan-panitia.transaksi.update', [$kegiatan, $transaksi]) }}" method="POST"
                  enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Tanggal + Jumlah --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_transaksi"
                               value="{{ old('tanggal_transaksi', $transaksi->tanggal_transaksi->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none transition-colors
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Jumlah (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah"
                               value="{{ old('jumlah', $transaksi->jumlah) }}" min="1"
                               class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none transition-colors
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    </div>
                </div>

                {{-- Dompet --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Dompet <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="dompet_id"
                                class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none appearance-none transition-colors
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            @foreach($dompetList as $dompet)
                                <option value="{{ $dompet->id }}"
                                    {{ old('dompet_id', $transaksi->dompet_id) == $dompet->id ? 'selected' : '' }}>
                                    {{ $dompet->nama_dompet }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="kategori_transaksi_id"
                                class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none appearance-none transition-colors
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            @foreach($kategoriList as $kategori)
                                <option value="{{ $kategori->id }}"
                                    {{ old('kategori_transaksi_id', $transaksi->kategori_transaksi_id) == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                              class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi', $transaksi->deskripsi) }}</textarea>
                </div>

                {{-- Bukti existing --}}
                @if($transaksi->buktiTransaksi->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Bukti yang sudah ada
                    </label>
                    <div class="flex flex-col gap-2">
                        @foreach($transaksi->buktiTransaksi as $bukti)
                        @php $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION)); @endphp
                        <div class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $bukti->nama_file }}</span>
                                <span class="rounded-lg px-2 py-0.5 text-xs font-medium
                                    {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $ext }}
                                </span>
                            </div>
                            <label class="flex items-center gap-1.5 text-xs text-red-500 cursor-pointer">
                                <input type="checkbox" name="hapus_bukti[]" value="{{ $bukti->id }}"
                                       class="h-3 w-3 rounded">
                                Hapus
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Upload bukti baru --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Tambah bukti baru
                    </label>
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 px-4 py-8 hover:border-green-400 transition-colors">
                        <input type="file" name="bukti_transaksi[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                               class="sr-only" onchange="showFileNames(this)">
                        <div id="fileLabel" class="text-center">
                            <svg class="mx-auto mb-2 w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Klik untuk upload foto atau PDF</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Maks. 5MB · JPG, PNG, PDF</p>
                        </div>
                    </label>
                </div>

                {{-- Buttons --}}
                <div class="pt-1 flex items-center gap-3">
                    <button type="submit"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                        Simpan & Kirim Ulang
                    </button>
                    <a href="{{ route('dashboard.kegiatan-panitia.show', $kegiatan) }}"
                       class="flex-1 text-center border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium px-6 py-2.5 rounded-xl transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function showFileNames(input) {
    const label = document.getElementById('fileLabel');
    if (input.files.length > 0) {
        const names = Array.from(input.files).map(f => f.name).join(', ');
        label.innerHTML = `<p class="text-sm font-medium text-gray-900 dark:text-white">${names}</p>`;
    }
}
</script>
@endpush