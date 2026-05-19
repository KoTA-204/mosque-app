@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Perbaiki Transaksi</h2>
    </div>

    {{-- Catatan Revision --}}
    @if($transaksi->catatan_revision)
    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-900">
        <p class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200">Catatan dari Bendahara:</p>
        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $transaksi->catatan_revision }}</p>
    </div>
    @endif

    <div class="rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">

        {{-- Info Bar --}}
        <div class="mb-6 flex items-center gap-8 rounded-lg bg-blue-50 px-5 py-3 dark:bg-meta-4">
            <div>
                <p class="text-xs text-body dark:text-bodydark">Kegiatan</p>
                <p class="text-sm font-medium text-black dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
            </div>
            <div>
                <p class="text-xs text-body dark:text-bodydark">Kode transaksi</p>
                <p class="text-sm font-medium font-mono text-black dark:text-white">
                    TRX-{{ now()->year }}-{{ str_pad($transaksi->id, 3, '0', STR_PAD_LEFT) }}
                </p>
            </div>
        </div>

        <form action="{{ route('kegiatan.transaksi.update', [$kegiatan, $transaksi]) }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Tanggal + Jumlah --}}
            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_transaksi"
                           value="{{ old('tanggal_transaksi', $transaksi->tanggal_transaksi->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah"
                           value="{{ old('jumlah', $transaksi->jumlah) }}" min="1"
                           class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                </div>
            </div>

            {{-- Dompet --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Dompet <span class="text-red-500">*</span>
                </label>
                <select name="dompet_id"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                    @foreach($dompetList as $dompet)
                        <option value="{{ $dompet->id }}"
                            {{ old('dompet_id', $transaksi->dompet_id) == $dompet->id ? 'selected' : '' }}>
                            {{ $dompet->nama_dompet }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kategori --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select name="kategori_transaksi_id"
                        class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                    @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}"
                            {{ old('kategori_transaksi_id', $transaksi->kategori_transaksi_id) == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Deskripsi --}}
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">Deskripsi</label>
                <textarea name="deskripsi" rows="3"
                          class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">{{ old('deskripsi', $transaksi->deskripsi) }}</textarea>
            </div>

            {{-- Bukti existing --}}
            @if($transaksi->buktiTransaksi->count() > 0)
            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Bukti yang sudah ada
                </label>
                <div class="flex flex-col gap-2">
                    @foreach($transaksi->buktiTransaksi as $bukti)
                    @php $ext = strtoupper(pathinfo($bukti->nama_file, PATHINFO_EXTENSION)); @endphp
                    <div class="flex items-center justify-between rounded-lg border border-stroke px-4 py-3 dark:border-strokedark">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-black dark:text-white">{{ $bukti->nama_file }}</span>
                            <span class="rounded px-2 py-0.5 text-xs font-medium
                                {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $ext }}
                            </span>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-red-500">
                            <input type="checkbox" name="hapus_bukti[]" value="{{ $bukti->id }}"
                                   class="h-3 w-3">
                            Hapus
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Upload bukti baru --}}
            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Tambah bukti baru
                </label>
                <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-stroke px-4 py-6 hover:border-primary dark:border-strokedark">
                    <input type="file" name="bukti_transaksi[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                           class="sr-only" onchange="showFileNames(this)">
                    <div id="fileLabel" class="text-center">
                        <p class="text-sm text-body dark:text-bodydark">Klik untuk upload foto atau PDF</p>
                        <p class="text-xs text-body dark:text-bodydark">Maks. 5MB · JPG, PNG, PDF</p>
                    </div>
                </label>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('kegiatan.show', $kegiatan) }}"
                   class="rounded-lg border border-stroke px-6 py-2.5 text-sm font-medium text-black hover:bg-gray-100 dark:border-strokedark dark:text-white">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-white hover:bg-opacity-90">
                    Simpan & kirim ulang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showFileNames(input) {
    const label = document.getElementById('fileLabel');
    if (input.files.length > 0) {
        const names = Array.from(input.files).map(f => f.name).join(', ');
        label.innerHTML = `<p class="text-sm font-medium text-black">${names}</p>`;
    }
}
</script>
@endpush