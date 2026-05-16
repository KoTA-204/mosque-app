@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-4 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Catat Transaksi</h2>
    </div>

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

        {{-- Info Bar --}}
        <div class="mb-6 flex items-center gap-10 rounded-lg bg-green-50 px-6 py-4 dark:bg-meta-4">
            <div>
                <p class="text-xs text-gray-500 dark:text-bodydark">Kegiatan</p>
                <p class="text-sm font-semibold text-black dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-bodydark">Dicatat oleh</p>
                <p class="text-sm font-semibold text-black dark:text-white">{{ auth()->user()->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-bodydark">Kode transaksi</p>
                <p class="text-sm font-semibold text-black dark:text-white">
                    {{ $kodeTransaksi }} <span class="text-xs font-normal text-gray-400">(otomatis)</span>
                </p>
            </div>
        </div>

        <form action="{{ route('dashboard.kegiatan.transaksi.store', $kegiatan) }}" method="POST"
              enctype="multipart/form-data">
            @csrf

            {{-- Toggle Jenis Transaksi --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Jenis transaksi <span class="text-red-500">*</span>
                </label>
                <div class="flex rounded-lg border border-stroke overflow-hidden dark:border-strokedark">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="jenis_transaksi" value="PEMASUKAN"
                               class="sr-only" {{ old('jenis_transaksi', 'PEMASUKAN') === 'PEMASUKAN' ? 'checked' : '' }}
                               onchange="updateKategori('PEMASUKAN'); updateToggleStyle('PEMASUKAN')">
                        <span class="jenis-btn block py-3 text-center text-sm font-medium transition
                                     {{ old('jenis_transaksi', 'PEMASUKAN') === 'PEMASUKAN'
                                        ? 'bg-[#3a6b4a] text-white'
                                        : 'text-gray-500 hover:bg-gray-50' }}"
                              id="btn-pemasukan">
                            Pemasukan
                        </span>
                    </label>
                    <label class="flex-1 cursor-pointer border-l border-stroke dark:border-strokedark">
                        <input type="radio" name="jenis_transaksi" value="PENGELUARAN"
                               class="sr-only" {{ old('jenis_transaksi') === 'PENGELUARAN' ? 'checked' : '' }}
                               onchange="updateKategori('PENGELUARAN'); updateToggleStyle('PENGELUARAN')">
                        <span class="jenis-btn block py-3 text-center text-sm font-medium transition
                                     {{ old('jenis_transaksi') === 'PENGELUARAN'
                                        ? 'bg-[#3a6b4a] text-white'
                                        : 'text-gray-500 hover:bg-gray-50' }}"
                              id="btn-pengeluaran">
                            Pengeluaran
                        </span>
                    </label>
                </div>
                @error('jenis_transaksi')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal + Jumlah --}}
            <div class="mb-5 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_transaksi"
                           value="{{ old('tanggal_transaksi', now()->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-stroke px-4 py-3 text-sm text-black focus:border-[#3a6b4a] focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('tanggal_transaksi') border-red-500 @enderror">
                    @error('tanggal_transaksi')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah" value="{{ old('jumlah', 0) }}" min="1"
                           class="w-full rounded-lg border border-stroke px-4 py-3 text-sm text-black focus:border-[#3a6b4a] focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('jumlah') border-red-500 @enderror">
                    @error('jumlah')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Dompet --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Dompet <span class="text-red-500">*</span>
                </label>
                <select name="dompet_id"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm text-black focus:border-[#3a6b4a] focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('dompet_id') border-red-500 @enderror">
                    <option value="">-- Pilih Dompet --</option>
                    @foreach($dompetList as $dompet)
                        <option value="{{ $dompet->id }}"
                            {{ old('dompet_id') == $dompet->id ? 'selected' : '' }}>
                            {{ $dompet->nama_dompet }}
                        </option>
                    @endforeach
                </select>
                @error('dompet_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select name="kategori_transaksi_id" id="kategori_select"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm text-black focus:border-[#3a6b4a] focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('kategori_transaksi_id') border-red-500 @enderror">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}"
                                data-jenis="{{ $kategori->jenis_transaksi }}"
                            {{ old('kategori_transaksi_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                @error('kategori_transaksi_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">Deskripsi</label>
                <textarea name="deskripsi" rows="4"
                          class="w-full rounded-lg border border-stroke px-4 py-3 text-sm text-black focus:border-[#3a6b4a] focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white"
                          placeholder="Keterangan transaksi...">{{ old('deskripsi') }}</textarea>
            </div>

            {{-- Bukti Transaksi --}}
            <div class="mb-8">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">Bukti transaksi</label>
                <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 px-4 py-10 hover:border-[#3a6b4a] transition dark:border-strokedark">
                    <input type="file" name="bukti_transaksi[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                           class="sr-only" id="buktiInput" onchange="showFileNames(this)">
                    <div id="fileLabel" class="text-center">
                        <p class="text-sm text-gray-500 dark:text-bodydark">Klik untuk upload foto atau PDF</p>
                        <p class="text-xs text-gray-400 dark:text-bodydark mt-1">Maks. 5MB · JPG, PNG, PDF</p>
                    </div>
                </label>
                @error('bukti_transaksi.*')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('dashboard.kegiatan.show', $kegiatan) }}"
                   class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-50 dark:border-strokedark dark:text-white">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-[#3a6b4a] px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90 transition">
                    Simpan & kirim
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="jenis_transaksi"]');
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            updateKategori(this.value);
            updateToggleStyle(this.value);
        });
    });

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
        btnPemasukan.classList.add('bg-[#3a6b4a]', 'text-white');
        btnPemasukan.classList.remove('text-gray-500');
        btnPengeluaran.classList.remove('bg-[#3a6b4a]', 'text-white');
        btnPengeluaran.classList.add('text-gray-500');
    } else {
        btnPengeluaran.classList.add('bg-[#3a6b4a]', 'text-white');
        btnPengeluaran.classList.remove('text-gray-500');
        btnPemasukan.classList.remove('bg-[#3a6b4a]', 'text-white');
        btnPemasukan.classList.add('text-gray-500');
    }
}

function showFileNames(input) {
    const label = document.getElementById('fileLabel');
    if (input.files.length > 0) {
        const names = Array.from(input.files).map(f => f.name).join(', ');
        label.innerHTML = `<p class="text-sm font-medium text-black dark:text-white">${names}</p>`;
    }
}
</script>
@endpush