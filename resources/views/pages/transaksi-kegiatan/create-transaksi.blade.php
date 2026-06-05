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
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Catat Transaksi</h1>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">

            {{-- Info Bar --}}
            <div class="mb-6 flex items-center gap-8 rounded-xl bg-green-50 dark:bg-green-900/20 px-5 py-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kegiatan</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dicatat oleh</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
                    <p class="text-sm font-semibold font-mono text-gray-900 dark:text-white">
                        {{ $kodeTransaksi }} <span class="text-xs font-normal text-gray-400">(otomatis)</span>
                    </p>
                </div>
            </div>

            <form action="{{ route('dashboard.kegiatan-panitia.transaksi.store', $kegiatan) }}" method="POST"
                  enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Toggle Jenis Transaksi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Jenis transaksi <span class="text-red-500">*</span>
                    </label>
                    <div class="flex rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="jenis_transaksi" value="PEMASUKAN"
                                   class="sr-only" {{ old('jenis_transaksi', 'PEMASUKAN') === 'PEMASUKAN' ? 'checked' : '' }}
                                   onchange="updateKategori('PEMASUKAN'); updateToggleStyle('PEMASUKAN')">
                            <span id="btn-pemasukan"
                                  class="block py-2.5 text-center text-sm font-medium transition-colors
                                         {{ old('jenis_transaksi', 'PEMASUKAN') === 'PEMASUKAN'
                                            ? 'bg-green-600 text-white'
                                            : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                Pemasukan
                            </span>
                        </label>
                        <label class="flex-1 cursor-pointer border-l border-gray-200 dark:border-gray-700">
                            <input type="radio" name="jenis_transaksi" value="PENGELUARAN"
                                   class="sr-only" {{ old('jenis_transaksi') === 'PENGELUARAN' ? 'checked' : '' }}
                                   onchange="updateKategori('PENGELUARAN'); updateToggleStyle('PENGELUARAN')">
                            <span id="btn-pengeluaran"
                                  class="block py-2.5 text-center text-sm font-medium transition-colors
                                         {{ old('jenis_transaksi') === 'PENGELUARAN'
                                            ? 'bg-green-600 text-white'
                                            : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                Pengeluaran
                            </span>
                        </label>
                    </div>
                    @error('jenis_transaksi')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal + Jumlah --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_transaksi"
                               value="{{ old('tanggal_transaksi', now()->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                                   {{ $errors->has('tanggal_transaksi') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        @error('tanggal_transaksi')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Jumlah (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah" value="{{ old('jumlah', 0) }}" min="1"
                               class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors
                                   {{ $errors->has('jumlah') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        @error('jumlah')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Dompet --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Dompet <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="dompet_id"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('dompet_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">-- Pilih Dompet --</option>
                            @foreach($dompetList as $dompet)
                                <option value="{{ $dompet->id }}"
                                    {{ old('dompet_id') == $dompet->id ? 'selected' : '' }}>
                                    {{ $dompet->nama_dompet }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    @error('dompet_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="kategori_transaksi_id" id="kategori_select"
                                class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors
                                    {{ $errors->has('kategori_transaksi_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }}
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriList as $kategori)
                                <option value="{{ $kategori->id }}"
                                        data-jenis="{{ $kategori->jenis_transaksi }}"
                                    {{ old('kategori_transaksi_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    @error('kategori_transaksi_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="4"
                              placeholder="Keterangan transaksi..."
                              class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi') }}</textarea>
                </div>

                {{-- Bukti Transaksi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bukti transaksi</label>
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 px-4 py-8 hover:border-green-400 transition-colors">
                        <input type="file" name="bukti_transaksi[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                               class="sr-only" id="buktiInput" onchange="showFileNames(this)">
                        <div id="fileLabel" class="text-center">
                            <svg class="mx-auto mb-2 w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Klik untuk upload foto atau PDF</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Maks. 5MB · JPG, PNG, PDF</p>
                        </div>
                    </label>
                    @error('bukti_transaksi.*')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="pt-1 flex items-center gap-3">
                    <button type="submit"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                        Simpan & Kirim
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
document.addEventListener('DOMContentLoaded', function () {
    const currentJenis = document.querySelector('input[name="jenis_transaksi"]:checked')?.value || 'PEMASUKAN';
    updateKategori(currentJenis);
    updateToggleStyle(currentJenis);
});

function updateKategori(jenis) {
    const select  = document.getElementById('kategori_select');
    const options = select.querySelectorAll('option');
    options.forEach(opt => {
        if (!opt.value) return;
        opt.hidden = opt.dataset.jenis !== jenis;
    });
    if (select.selectedOptions[0]?.dataset.jenis !== jenis) {
        select.value = '';
    }
}

function updateToggleStyle(jenis) {
    const btnPemasukan   = document.getElementById('btn-pemasukan');
    const btnPengeluaran = document.getElementById('btn-pengeluaran');
    if (jenis === 'PEMASUKAN') {
        btnPemasukan.classList.add('bg-green-600', 'text-white');
        btnPemasukan.classList.remove('text-gray-500');
        btnPengeluaran.classList.remove('bg-green-600', 'text-white');
        btnPengeluaran.classList.add('text-gray-500');
    } else {
        btnPengeluaran.classList.add('bg-green-600', 'text-white');
        btnPengeluaran.classList.remove('text-gray-500');
        btnPemasukan.classList.remove('bg-green-600', 'text-white');
        btnPemasukan.classList.add('text-gray-500');
    }
}

function showFileNames(input) {
    const label = document.getElementById('fileLabel');
    if (input.files.length > 0) {
        const names = Array.from(input.files).map(f => f.name).join(', ');
        label.innerHTML = `<p class="text-sm font-medium text-gray-900 dark:text-white">${names}</p>`;
    }
}
</script>
@endpush