@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between rounded-xl border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark">
        <h2 class="text-2xl font-bold text-dark dark:text-white">Catat Kencleng Baru</h2>
    </div>

    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">
        <ul class="list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('dashboard.kencleng.store') }}" method="POST" enctype="multipart/form-data" id="kenclengForm">
        @csrf

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
                           value="{{ old('tanggal_hitung', now()->format('Y-m-d')) }}"
                           class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('tanggal_hitung') border-red-500 @enderror">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                        Dompet <span class="text-red-500">*</span>
                    </label>
                    <select name="dompet_id"
                            class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('dompet_id') border-red-500 @enderror">
                        <option value="">-- Pilih Dompet --</option>
                        @foreach($dompetList as $dompet)
                            <option value="{{ $dompet->id }}" {{ old('dompet_id') == $dompet->id ? 'selected' : '' }}>
                                {{ $dompet->nama_dompet }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Rincian Pecahan Uang --}}
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
                    $label = $p >= 1000 ? 'Rp ' . number_format($p/1000, 0, ',', '.') . 'K' : 'Rp ' . $p;
                    $oldVal = old("pecahan.$p", 0);
                @endphp
                <div class="rounded-lg border border-stroke p-3 dark:border-strokedark">
                    <p class="mb-2 text-xs font-medium text-black dark:text-white">{{ $label }}</p>
                    <div class="flex items-center justify-between gap-1">
                        <button type="button" onclick="changeCount({{ $p }}, -1)"
                                class="flex h-7 w-7 items-center justify-center rounded border border-stroke text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                            −
                        </button>
                        <input type="number" name="pecahan[{{ $p }}]" id="pecahan_{{ $p }}"
                               value="{{ $oldVal }}" min="0"
                               onchange="recalcTotal()"
                               class="w-12 rounded border border-stroke px-1 py-1 text-center text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">
                        <button type="button" onclick="changeCount({{ $p }}, 1)"
                                class="flex h-7 w-7 items-center justify-center rounded border border-stroke text-sm hover:bg-gray-100 dark:border-strokedark dark:hover:bg-meta-4">
                            +
                        </button>
                    </div>
                    <p id="subtotal_{{ $p }}" class="mt-1 text-center text-xs text-body dark:text-bodydark">
                        Rp {{ number_format($p * $oldVal, 0, ',', '.') }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- Total Fisik --}}
            <div class="mt-4 flex items-center justify-between rounded-lg bg-green-50 px-5 py-4 dark:bg-meta-4">
                <p class="text-sm font-medium text-green-700 dark:text-white">Total fisik terhitung</p>
                <p id="totalFisik" class="text-lg font-bold text-green-700 dark:text-white">Rp 0</p>
            </div>

            {{-- Jumlah Disetor --}}
            <div class="mt-4">
                <label class="mb-2 block text-sm font-medium text-black dark:text-white">
                    Jumlah disetor ke kas <span class="text-red-500">*</span>
                </label>
                <input type="text" name="jumlah_disetor" id="jumlahDisetor"
                       value="{{ old('jumlah_disetor') }}"
                       placeholder="cth: 500.000"
                       class="w-64 rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white @error('jumlah_disetor') border-red-500 @enderror">
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
                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Wajib</span>
            </div>

            <label id="dropzone"
                   class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-stroke px-6 py-10 hover:border-primary dark:border-strokedark transition-colors">
                <input type="file" name="berita_acara" accept=".jpg,.jpeg,.png,.pdf"
                       class="sr-only" id="baInput" onchange="showBAName(this)">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="1.5" class="mb-3">
                    <polyline points="16 16 12 12 8 16"/>
                    <line x1="12" y1="12" x2="12" y2="21"/>
                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                </svg>
                <p id="baLabel" class="text-sm text-body dark:text-bodydark">
                    <span class="text-primary underline">Klik untuk unggah</span> atau seret file ke sini
                </p>
                <p class="mt-1 text-xs text-body dark:text-bodydark">JPG, PNG, atau PDF — maks. 5 MB</p>
            </label>
            @error('berita_acara')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
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
                      class="w-full rounded-lg border border-stroke px-4 py-3 text-sm focus:border-primary focus:outline-none dark:border-strokedark dark:bg-boxdark dark:text-white">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end gap-3 rounded-xl border border-stroke bg-white px-6 py-4 shadow-default dark:border-strokedark dark:bg-boxdark">
            <a href="{{ route('dashboard.kencleng.index') }}"
            class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors dark:border-strokedark dark:bg-boxdark dark:text-white">
                Batal
            </a>

            <button type="submit" name="submit_type" value="draf"
                    class="rounded-lg border border-green-600 bg-white px-6 py-2.5 text-sm font-medium text-green-600 hover:bg-green-600 hover:text-white transition-colors">
                Simpan Draf
            </button>

            <button type="submit" name="submit_type" value="ajukan"
                    class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-colors">
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
    const subtotal = p * count;
    document.getElementById('subtotal_' + p).textContent =
        'Rp ' + subtotal.toLocaleString('id-ID');
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
    const label = document.getElementById('baLabel');
    if (input.files.length > 0) {
        label.innerHTML = `<span class="font-medium text-black dark:text-white">${input.files[0].name}</span>`;
    }
}

// Format jumlah disetor input
document.getElementById('jumlahDisetor').addEventListener('input', function () {
    let val = this.value.replace(/\D/g, '');
    this.value = parseInt(val || 0).toLocaleString('id-ID');
});

// Init
recalcTotal();
</script>
@endpush