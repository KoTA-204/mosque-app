@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between rounded-xl border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Edit Kencleng</h2>
    </div>

    {{-- Catatan Revisi --}}
    @if($kencleng->transaksi->catatan_revisi)
    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-900/20">
        <p class="mb-1 text-sm font-medium text-blue-800 dark:text-blue-200">Catatan dari Bendahara:</p>
        <p class="text-sm text-blue-700 dark:text-blue-300">{{ $kencleng->transaksi->catatan_revisi }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">
        <ul class="list-inside list-disc">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('dashboard.kencleng.update', $kencleng) }}" method="POST"
          enctype="multipart/form-data" id="kenclengForm">
        @csrf
        @method('PUT')

        {{-- Informasi Periode --}}
        <div class="mb-4 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h3 class="font-semibold text-black dark:text-white">Informasi Periode</h3>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Tanggal Hitung <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tanggal_hitung"
                           value="{{ old('tanggal_hitung', $kencleng->transaksi->tanggal_transaksi->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Dompet <span class="text-red-500">*</span>
                    </label>
                    <select name="dompet_id"
                            class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                        @foreach($dompetList as $dompet)
                            <option value="{{ $dompet->id }}"
                                {{ old('dompet_id', $kencleng->transaksi->dompet_id) == $dompet->id ? 'selected' : '' }}>
                                {{ $dompet->nama_dompet }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Rincian Pecahan --}}
        <div class="mb-4 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mb-2 flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                <h3 class="font-semibold text-black dark:text-white">Rincian Pecahan Uang</h3>
            </div>
            <p class="mb-4 text-sm text-body dark:text-bodydark">
                Isi jumlah lembar/keping untuk setiap pecahan yang ditemukan dalam kencleng.
            </p>

            <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-9">
                @foreach($pecahan as $p)
                @php
                    $label  = $p >= 1000 ? 'Rp ' . number_format($p/1000, 0, ',', '.') . 'K' : 'Rp ' . $p;
                    $oldVal = old("pecahan.$p", $detailMap[$p] ?? 0);
                @endphp
                <div class="rounded-lg border border-stroke p-3 dark:border-strokedark">
                    <p class="mb-2 text-xs font-medium text-black dark:text-white">{{ $label }}</p>
                    <div class="flex items-center justify-between gap-1">
                        <button type="button" onclick="changeCount({{ $p }}, -1)"
                                class="flex h-7 w-7 items-center justify-center rounded border border-stroke text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">−</button>
                        <input type="number" name="pecahan[{{ $p }}]" id="pecahan_{{ $p }}"
                               value="{{ $oldVal }}" min="0"
                               onchange="recalcTotal()"
                               class="w-12 rounded border border-stroke px-1 py-1 text-center text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                        <button type="button" onclick="changeCount({{ $p }}, 1)"
                                class="flex h-7 w-7 items-center justify-center rounded border border-stroke text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">+</button>
                    </div>
                    <p id="subtotal_{{ $p }}" class="mt-1 text-center text-xs text-body dark:text-bodydark">
                        Rp {{ number_format($p * $oldVal, 0, ',', '.') }}
                    </p>
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between rounded-lg bg-green-50 px-5 py-4 dark:bg-meta-4">
                <p class="text-sm font-medium text-green-700 dark:text-white">Total fisik terhitung</p>
                <p id="totalFisik" class="text-lg font-bold text-green-700 dark:text-white">Rp 0</p>
            </div>

            <div class="mt-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Jumlah disetor ke kas <span class="text-red-500">*</span>
                </label>
                <input type="text" name="jumlah_disetor" id="jumlahDisetor"
                       value="{{ old('jumlah_disetor', number_format($kencleng->transaksi->jumlah, 0, ',', '.')) }}"
                       class="w-64 rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                <p class="mt-1 text-xs text-body dark:text-bodydark">
                    Boleh berbeda dari total fisik — sisanya dicatat sebagai transfer ke rekening
                </p>
            </div>
        </div>

        {{-- Berita Acara --}}
        <div class="mb-4 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <h3 class="font-semibold text-black dark:text-white">Berita Acara</h3>
            </div>

            {{-- File existing --}}
            @if($kencleng->berita_acara)
            @php $ext = strtoupper(pathinfo($kencleng->berita_acara, PATHINFO_EXTENSION)); @endphp
            <div class="mb-3 flex items-center gap-3 rounded-lg border border-stroke px-4 py-3 dark:border-strokedark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <a href="{{ Storage::url($kencleng->berita_acara) }}" target="_blank"
                   class="text-sm text-black hover:text-primary dark:text-white">
                    {{ basename($kencleng->berita_acara) }}
                </a>
                <span class="ml-auto rounded px-2 py-0.5 text-xs font-medium
                    {{ $ext === 'PDF' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $ext }}
                </span>
            </div>
            <p class="mb-3 text-xs text-body dark:text-bodydark">Upload file baru untuk mengganti</p>
            @endif

            <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-stroke px-6 py-8 hover:border-primary dark:border-strokedark transition-colors">
                <input type="file" name="berita_acara" accept=".jpg,.jpeg,.png,.pdf"
                       class="sr-only" onchange="showBAName(this)">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="1.5" class="mb-2">
                    <polyline points="16 16 12 12 8 16"/>
                    <line x1="12" y1="12" x2="12" y2="21"/>
                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                </svg>
                <p id="baLabel" class="text-sm text-body dark:text-bodydark">
                    <span class="text-primary underline">Klik untuk unggah</span> atau seret file ke sini
                </p>
                <p class="mt-1 text-xs text-body dark:text-bodydark">JPG, PNG, atau PDF — maks. 5 MB</p>
            </label>
        </div>

        {{-- Keterangan Tambahan --}}
        <div class="mb-6 rounded-xl border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                <h3 class="font-semibold text-black dark:text-white">Keterangan Tambahan</h3>
                <span class="text-xs text-body dark:text-bodydark">Opsional</span>
            </div>
            <textarea name="keterangan" rows="3"
                      placeholder="cth: Kondisi semua kotak normal, tidak ada yang rusak"
                      class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">{{ old('keterangan', $kencleng->transaksi->deskripsi) }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end gap-3 rounded-xl border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark">

            {{-- Batal --}}
            <a href="{{ route('dashboard.kencleng.index') }}"
            class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                Batal
            </a>

            {{-- Simpan Draf --}}
            <button type="submit"
                    name="submit_type"
                    value="draf"
                    class="rounded-lg border border-green-600 bg-white px-6 py-2.5 text-sm font-medium text-green-600 transition hover:bg-green-600 hover:text-white">
                Simpan Draf
            </button>

            {{-- Simpan & Ajukan --}}
            <button type="submit"
                    name="submit_type"
                    value="ajukan"
                    class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-green-700">
                Simpan & Ajukan
            </button>

        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
const pecahan = @json($pecahan);

function changeCount(p, delta) {
    const input = document.getElementById('pecahan_' + p);
    const val   = Math.max(0, (parseInt(input.value) || 0) + delta);
    input.value = val;
    updateSubtotal(p, val);
    recalcTotal();
}

function updateSubtotal(p, count) {
    document.getElementById('subtotal_' + p).textContent =
        'Rp ' + (p * count).toLocaleString('id-ID');
}

function recalcTotal() {
    let total = 0;
    pecahan.forEach(p => {
        const val = parseInt(document.getElementById('pecahan_' + p)?.value) || 0;
        total += p * val;
        updateSubtotal(p, val);
    });
    document.getElementById('totalFisik').textContent =
        'Rp ' + total.toLocaleString('id-ID');
}

function showBAName(input) {
    if (input.files.length > 0) {
        document.getElementById('baLabel').innerHTML =
            `<span class="font-medium text-black dark:text-white">${input.files[0].name}</span>`;
    }
}

document.getElementById('jumlahDisetor').addEventListener('input', function () {
    let val = this.value.replace(/\D/g, '');
    this.value = parseInt(val || 0).toLocaleString('id-ID');
});

recalcTotal();
</script>
@endpush