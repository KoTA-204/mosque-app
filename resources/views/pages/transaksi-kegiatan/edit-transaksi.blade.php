<x-modal id="modal-edit-transaksi" title="Edit Transaksi">

    {{-- Info Bar --}}
    <div class="mb-6 flex flex-wrap items-center gap-6 rounded-xl bg-green-50 dark:bg-green-900/20 px-5 py-4">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kegiatan</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $kegiatan->nama_kegiatan }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Dicatat oleh</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white" id="edit-pencatat">-</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kode transaksi</p>
            <p class="text-sm font-semibold font-mono text-gray-900 dark:text-white" id="edit-kode">-</p>
        </div>
    </div>

    <form id="form-edit-transaksi" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Toggle Jenis Transaksi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Jenis transaksi <span class="text-red-500">*</span>
            </label>
            <div class="flex rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="jenis_transaksi" value="PEMASUKAN"
                           class="sr-only" onchange="updateEditToggleStyle('PEMASUKAN')">
                    <span id="edit-btn-pemasukan"
                          class="block py-2.5 text-center text-sm font-medium transition-colors text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Pemasukan
                    </span>
                </label>
                <label class="flex-1 cursor-pointer border-l border-gray-200 dark:border-gray-700">
                    <input type="radio" name="jenis_transaksi" value="PENGELUARAN"
                           class="sr-only" onchange="updateEditToggleStyle('PENGELUARAN')">
                    <span id="edit-btn-pengeluaran"
                          class="block py-2.5 text-center text-sm font-medium transition-colors text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Pengeluaran
                    </span>
                </label>
            </div>
        </div>

        {{-- Tanggal + Jumlah --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_transaksi" id="edit-tanggal"
                       class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none transition-colors bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Jumlah (Rp) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="jumlah" id="edit-jumlah" min="1"
                       class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none transition-colors bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            </div>
        </div>

        {{-- Dompet --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Dompet <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select name="dompet_id" id="edit-dompet"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none appearance-none transition-colors bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">-- Pilih Dompet --</option>
                    @foreach($dompetList as $dompet)
                        <option value="{{ $dompet->id }}">{{ $dompet->nama_dompet }}</option>
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
                <select name="kategori_transaksi_id" id="edit-kategori"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none appearance-none transition-colors bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" data-jenis="{{ $kategori->jenis_transaksi }}">
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
            <textarea name="deskripsi" id="edit-deskripsi" rows="3"
                      placeholder="Keterangan transaksi..."
                      class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 focus:border-green-400 rounded-xl outline-none resize-none transition-colors bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400"></textarea>
        </div>

        {{-- Bukti Transaksi --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bukti transaksi</label>
            <div id="edit-bukti-list" class="mb-3 flex flex-wrap gap-2"></div>
            <p id="edit-bukti-hint" class="hidden text-xs text-gray-400 dark:text-gray-500 mb-2">
                Centang ✕ untuk menghapus file lama, lalu upload yang baru di bawah.
            </p>
            <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 px-4 py-8 hover:border-green-400 transition-colors">
                <input type="file" name="bukti_transaksi[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                       class="sr-only" id="editBuktiInput" onchange="showEditFileNames(this)">
                <div id="editFileLabel" class="text-center">
                    <svg class="mx-auto mb-2 w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Klik untuk upload foto atau PDF baru</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Maks. 5MB · JPG, PNG, PDF</p>
                </div>
            </label>
        </div>

        {{-- Buttons --}}
        <div class="pt-1 flex items-center gap-3">
            <button type="submit"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Simpan Perubahan
            </button>
            <button type="button" onclick="closeModal('modal-edit-transaksi')"
                    class="flex-1 text-center border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium px-6 py-2.5 rounded-xl transition-colors">
                Batal
            </button>
        </div>
    </form>

</x-modal>