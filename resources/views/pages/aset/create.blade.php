{{-- resources/views/pages/aset/create.blade.php --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden">

    {{-- Flatpickr CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <style>
        .flatpickr-calendar { font-size: 13px; }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #16a34a; border-color: #16a34a; }
        .flatpickr-day:hover { background: #f0fdf4; }
        .dark .flatpickr-calendar { background: #1f2937; border-color: #374151; color: #f9fafb; }
        .dark .flatpickr-day { color: #d1d5db; }
        .dark .flatpickr-day:hover { background: #374151; }
        .dark .flatpickr-months, .dark .flatpickr-weekdays { background: #111827; }
        .dark .flatpickr-current-month, .dark .flatpickr-weekday { color: #f9fafb; }
    </style>

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Tambah Aset</h2>
            <p class="text-xs text-gray-400 mt-0.5">Isi informasi aset yang akan dicatat</p>
        </div>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <form id="createAsetForm" enctype="multipart/form-data">
        @csrf

        <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-6">

            {{-- Identitas Aset --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Identitas Aset</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Nama Aset <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_aset" placeholder="Masukkan Nama Aset"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                        <p id="err-nama_aset" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Lokasi <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="lokasi_aset"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                <option value="">Pilih lokasi</option>
                                @foreach(['Ruang Utama Masjid','Ruang Wanita Masjid','Menara Masjid','Ruang Utilitas','Garasi Masjid','Gudang','Lainnya'] as $lok)
                                    <option value="{{ $lok }}">{{ $lok }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <p id="err-lokasi_aset" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Kondisi Aset <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="kondisi_aset"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                <option value="">Pilih kondisi</option>
                                <option value="BAIK">Baik</option>
                                <option value="RUSAK RINGAN">Rusak Ringan</option>
                                <option value="RUSAK BERAT">Rusak Berat</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <p id="err-kondisi_aset" class="text-xs text-red-500 mt-1"></p>
                    </div>
                </div>
            </div>

            {{-- Perolehan Aset --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Perolehan Aset</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Sumber Perolehan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="sumber_perolehan" id="create-sumberPerolehan"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                <option value="">Pilih sumber</option>
                                <option value="Wakaf">Wakaf</option>
                                <option value="Hibah/Donasi">Hibah/Donasi</option>
                                <option value="Pembelian">Pembelian</option>
                                <option value="Infak Jamaah">Infak Jamaah</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <p id="err-sumber_perolehan" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Perolehan <span class="text-red-500">*</span></label>
                        {{-- hidden input untuk value aktual (Y-m-d) --}}
                        <input type="hidden" name="tanggal_perolehan" id="create-tanggal_perolehan">
                        {{-- input display untuk flatpickr --}}
                        <input type="text" id="create-fp-tanggal_perolehan" placeholder="Pilih tanggal" readonly
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors cursor-pointer">
                        <p id="err-tanggal_perolehan" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    {{-- Nilai --}}
                    <div>
                        <label id="create-labelNilai" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Nilai Perolehan (IDR) <span class="text-red-500">*</span>
                        </label>
                        {{-- hidden input untuk value aktual (angka) --}}
                        <input type="hidden" name="nilai_tercatat" id="create-nilai_tercatat">
                        {{-- input display dengan format ribuan --}}
                        <input type="text" id="create-display-nilai" placeholder="0"
                            inputmode="numeric"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                        <p id="err-nilai_tercatat" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    {{-- Nama Pemberi --}}
                    <div id="create-fieldNamaPemberi" class="hidden">
                        <label id="create-labelNamaPemberi" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Nama Pemberi</label>
                        <input type="text" name="nama_pemberi" placeholder="Masukkan nama"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Jumlah Unit</label>
                        <input type="number" name="jumlah_unit" value="1" min="1"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Dokumen Pendukung</label>
                        <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:border-green-400 transition-colors bg-gray-50 dark:bg-gray-800/50">
                            <svg class="w-5 h-5 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span id="create-dokumenNama" class="text-xs font-medium text-green-600 dark:text-green-400">Pilih File</span>
                            <span class="text-xs text-gray-400">PNG, JPG, PDF maks 5MB</span>
                            <input type="file" name="dokumen_pendukung" class="hidden" accept=".png,.jpg,.jpeg,.pdf"
                                onchange="document.getElementById('create-dokumenNama').textContent = this.files[0]?.name ?? 'Pilih File'">
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Keterangan / Catatan</label>
                        <textarea name="keterangan" rows="3" placeholder="Masukkan catatan tambahan..."
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Checkbox Penyusutan --}}
            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <input type="checkbox" id="create-cbDisusutkan"
                    class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                <div>
                    <label for="create-cbDisusutkan" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                        Aset ini disusutkan
                    </label>
                    <p class="text-xs text-gray-400 mt-0.5">Centang jika aset memiliki umur manfaat dan perlu dihitung penyusutannya</p>
                </div>
            </div>

            {{-- Section Penyusutan --}}
            <div id="create-sectionPenyusutan" class="hidden space-y-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Penyusutan</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Mulai Penyusutan <span class="text-red-500">*</span></label>
                        <input type="hidden" name="tanggal_mulai_penyusutan" id="create-tanggal_mulai_penyusutan">
                        <input type="text" id="create-fp-tanggal_mulai" placeholder="Pilih tanggal" readonly
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Umur Manfaat (Tahun) <span class="text-red-500">*</span></label>
                        <input type="number" name="umur_manfaat" id="create-umurManfaat"
                            placeholder="Contoh: 20" min="1"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    </div>
                </div>

                {{-- Preview --}}
                <div id="create-previewPenyusutan" class="hidden grid grid-cols-3 gap-3">
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900">
                        <p class="text-xs text-gray-400 mb-1">Penyusutan / Tahun</p>
                        <p id="create-prev-tahunan" class="text-sm font-semibold text-gray-800 dark:text-white">–</p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900">
                        <p class="text-xs text-gray-400 mb-1">Penyusutan / Bulan</p>
                        <p id="create-prev-bulanan" class="text-sm font-semibold text-gray-800 dark:text-white">–</p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900">
                        <p class="text-xs text-gray-400 mb-1">Selesai Tahun</p>
                        <p id="create-prev-selesai" class="text-sm font-semibold text-gray-800 dark:text-white">–</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <button type="button" onclick="closeModal()"
                class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <button type="button"
                onclick="submitAsetForm('createAsetForm', 'POST', '{{ route('dashboard.aset.store') }}')"
                class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors">
                Simpan
            </button>
        </div>
    </form>
</div>