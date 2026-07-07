<x-modal id="modal-catat-transaksi" title="Catat Transaksi">
    {{-- Info Bar --}}
    <div class="mb-6 flex flex-wrap items-center gap-6 rounded-xl bg-green-50 dark:bg-green-900/20 px-5 py-4">
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

    <form id="form-create-transaksi" action="{{ route('dashboard.transaksi-kegiatan.transaksi.store', $kegiatan) }}" method="POST"
          enctype="multipart/form-data" class="space-y-5" data-anggaran="<?php echo (int) $kegiatan->anggaran; ?>"
          data-pengeluaran="<?php echo (int) $kegiatan->totalPengeluaranBerjalan(); ?>">
        @csrf

        <x-jurnal.error-banner />

        {{-- Toggle Jenis Transaksi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis transaksi <span class="text-red-500">*</span></label>
            <div class="flex rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="jenis_transaksi" value="PEMASUKAN" class="sr-only"
                           {{ old('jenis_transaksi', 'PEMASUKAN') === 'PEMASUKAN' ? 'checked' : '' }}
                           onchange="updateToggleStyle('PEMASUKAN')">
                    <span id="btn-pemasukan" class="block py-2.5 text-center text-sm font-medium transition-colors">Pemasukan</span>
                </label>
                <label class="flex-1 cursor-pointer border-l border-gray-200 dark:border-gray-700">
                    <input type="radio" name="jenis_transaksi" value="PENGELUARAN" class="sr-only"
                           {{ old('jenis_transaksi') === 'PENGELUARAN' ? 'checked' : '' }}
                           onchange="updateToggleStyle('PENGELUARAN')">
                    <span id="btn-pengeluaran" class="block py-2.5 text-center text-sm font-medium transition-colors text-gray-500">Pengeluaran</span>
                </label>
            </div>
            @error('jenis_transaksi', 'createTransaksi')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Tanggal + Jumlah --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_transaksi" id="create-tanggal"
                       value="{{ old('tanggal_transaksi', now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors {{ $errors->has('tanggal_transaksi') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <p id="create-tanggal-error" class="hidden mt-1.5 text-xs text-red-500">Tanggal wajib diisi.</p>
                @error('tanggal_transaksi', 'createTransaksi')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="jumlah" value="{{ old('jumlah', 0) }}" min="1"
                       class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none transition-colors {{ $errors->has('jumlah') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                @error('jumlah', 'createTransaksi'))<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                <p id="create-over-warning" class="hidden mt-1.5 text-xs text-amber-600"></p>
            </div>
        </div>

        {{-- Dompet --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Dompet <span class="text-red-500">*</span></label>
            <select name="dompet_id" id="create-dompet"
                    class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors {{ $errors->has('dompet_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <option value="">-- Pilih Dompet --</option>
                @foreach($dompetList as $dompet)
                    <option value="{{ $dompet->id }}" {{ old('dompet_id') == $dompet->id ? 'selected' : '' }}>{{ $dompet->nama_dompet }}</option>
                @endforeach
            </select>
            <p id="create-dompet-error" class="hidden mt-1.5 text-xs text-red-500">Dompet wajib dipilih.</p>
            @error('dompet_id', 'createTransaksi')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Kategori --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori_transaksi_id" id="create-kategori"
                    class="w-full px-4 py-2.5 text-sm border rounded-xl outline-none appearance-none transition-colors {{ $errors->has('kategori_transaksi_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-700 focus:border-green-400' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoriList as $kategori)
                    <option value="{{ $kategori->id }}"
                        {{ old('kategori_transaksi_id') == $kategori->id ? 'selected' : '' }}>
                        {{ $kategori->nama_kategori }}
                    </option>
                @endforeach
            </select>
            <p id="create-kategori-error" class="hidden mt-1.5 text-xs text-red-500">Kategori wajib dipilih.</p>
            @error('kategori_transaksi_id', 'createTransaksi')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi</label>
            <textarea name="deskripsi" rows="3" placeholder="Keterangan transaksi..."
                      class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400">{{ old('deskripsi') }}</textarea>
        </div>

        {{-- Bukti Transaksi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bukti transaksi</label>
            <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 px-4 py-8 hover:border-green-400 transition-colors">
                <input type="file" name="bukti_transaksi[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="sr-only" id="buktiInput" onchange="showFileNames(this)">
                <div id="fileLabel" class="text-center">
                    <svg class="mx-auto mb-2 w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Klik untuk upload foto atau PDF</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Maks. 5MB · JPG, PNG, PDF</p>
                </div>
            </label>
            @error('bukti_transaksi.*', 'createTransaksi')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Buttons --}}
        <div class="pt-1 flex items-center gap-3">
            <button type="button" onclick="validateAndSubmitCreate()"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Simpan & Kirim
            </button>
            <button type="button" onclick="closeModal('modal-catat-transaksi')"
                    class="flex-1 text-center border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium px-6 py-2.5 rounded-xl transition-colors">
                Batal
            </button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checked = document.querySelector('#modal-catat-transaksi input[name="jenis_transaksi"]:checked');
        updateToggleStyle(checked ? checked.value : 'PEMASUKAN');

        flatpickr('#create-tanggal', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            defaultDate: '{{ now()->format('Y-m-d') }}',
        });

        @if($errors->createTransaksi->isNotEmpty() || $errors->has('permission'))
            openModal('modal-catat-transaksi');
        @endif
    });

    function updateToggleStyle(jenis) {
        const btnPemasukan   = document.getElementById('btn-pemasukan');
        const btnPengeluaran = document.getElementById('btn-pengeluaran');
        if (!btnPemasukan || !btnPengeluaran) return;
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

        cekAnggaranCreate();
    }

    function cekAnggaranCreate() {
        const form = document.getElementById('form-create-transaksi');
        const warn = document.getElementById('create-over-warning');
        if (!form || !warn) return;

        const anggaran = parseFloat(form.dataset.anggaran || '0');
        const terpakai = parseFloat(form.dataset.pengeluaran || '0');
        const jenisEl  = form.querySelector('input[name="jenis_transaksi"]:checked');
        const jumlahEl = form.querySelector('input[name="jumlah"]');
        const jumlah   = parseFloat(jumlahEl ? jumlahEl.value : '0') || 0;

        if (anggaran > 0 && jenisEl && jenisEl.value === 'PENGELUARAN' && (terpakai + jumlah) > anggaran) {
            const lebih = (terpakai + jumlah) - anggaran;
            warn.textContent = '⚠️ Melebihi anggaran sebesar Rp ' + lebih.toLocaleString('id-ID') + ' — transaksi tetap bisa disimpan.';
            warn.classList.remove('hidden');
        } else {
            warn.classList.add('hidden');
        }
    }

    function showFileNames(input) {
        const label = document.getElementById('fileLabel');
        if (input.files.length > 0) {
            const names = Array.from(input.files).map(f => f.name).join(', ');
            label.innerHTML = '<p class="text-sm font-medium text-gray-900 dark:text-white">' + names + '</p>';
        }
    }

    function validateAndSubmitCreate() {
        let valid = true;

        // Tanggal
        const tanggal    = document.getElementById('create-tanggal');
        const tanggalErr = document.getElementById('create-tanggal-error');
        if (!tanggal.value) {
            tanggal.classList.add('border-red-400');
            tanggal.classList.remove('border-gray-200');
            tanggalErr.classList.remove('hidden');
            valid = false;
        } else {
            tanggal.classList.remove('border-red-400');
            tanggalErr.classList.add('hidden');
        }

        // Dompet
        const dompet    = document.getElementById('create-dompet');
        const dompetErr = document.getElementById('create-dompet-error');
        if (!dompet.value) {
            dompet.classList.add('border-red-400');
            dompet.classList.remove('border-gray-200');
            dompetErr.classList.remove('hidden');
            valid = false;
        } else {
            dompet.classList.remove('border-red-400');
            dompetErr.classList.add('hidden');
        }

        // Kategori
        const kategori    = document.getElementById('create-kategori');
        const kategoriErr = document.getElementById('create-kategori-error');
        if (!kategori.value) {
            kategori.classList.add('border-red-400');
            kategori.classList.remove('border-gray-200');
            kategoriErr.classList.remove('hidden');
            valid = false;
        } else {
            kategori.classList.remove('border-red-400');
            kategoriErr.classList.add('hidden');
        }

        if (valid) {
            document.getElementById('form-create-transaksi').submit();
        }
    }
</script>
@endpush