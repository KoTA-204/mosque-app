{{-- resources/views/pages/aset/edit.blade.php --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <style>
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
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Edit Aset</h2>
            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $aset->kode_aset }}</p>
        </div>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    @php $isDisusutkan = !is_null($aset->umur_manfaat); @endphp

    <form id="editAsetForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-6">

            {{-- Identitas Aset --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Identitas Aset</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Nama Aset <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_aset" value="{{ $aset->nama_aset }}"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        <p id="err-nama_aset" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Lokasi <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="lokasi_aset"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                @foreach(['Ruang Utama Masjid','Ruang Wanita Masjid','Menara Masjid','Ruang Utilitas','Garasi Masjid','Gudang','Lainnya'] as $lok)
                                    <option value="{{ $lok }}" {{ $aset->lokasi_aset == $lok ? 'selected' : '' }}>{{ $lok }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Kondisi Aset <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="kondisi_aset"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                @foreach(['BAIK' => 'Baik','RUSAK RINGAN' => 'Rusak Ringan','RUSAK BERAT' => 'Rusak Berat'] as $val => $label)
                                    <option value="{{ $val }}" {{ $aset->kondisi_aset == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Status Aset</label>
                        <div class="relative">
                            <select name="status_aset"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                <option value="AKTIF"       {{ $aset->status_aset == 'AKTIF'       ? 'selected' : '' }}>Aktif</option>
                                <option value="TIDAK AKTIF" {{ $aset->status_aset == 'TIDAK AKTIF' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
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
                            <select name="sumber_perolehan" id="edit-sumberPerolehan"
                                class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                                @foreach(['Wakaf','Hibah/Donasi','Pembelian','Infak Jamaah','Lainnya'] as $src)
                                    <option value="{{ $src }}" {{ $aset->sumber_perolehan == $src ? 'selected' : '' }}>{{ $src }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Perolehan <span class="text-red-500">*</span></label>
                        <input type="hidden" name="tanggal_perolehan" id="edit-tanggal_perolehan"
                            value="{{ $aset->tanggal_perolehan?->format('Y-m-d') }}">
                        <input type="text" id="edit-fp-tanggal_perolehan" readonly
                            value="{{ $aset->tanggal_perolehan?->translatedFormat('d F Y') }}"
                            placeholder="Pilih tanggal"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors cursor-pointer">
                    </div>

                    {{-- Nilai --}}
                    <div>
                        <label id="edit-labelNilai" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            {{ $aset->label_nilai }} (IDR) <span class="text-red-500">*</span>
                        </label>
                        <input type="hidden" name="nilai_tercatat" id="edit-nilai_tercatat"
                            value="{{ $aset->nilai_tercatat }}">
                        <input type="text" id="edit-display-nilai"
                            value="{{ number_format((float)$aset->nilai_tercatat, 0, ',', '.') }}"
                            inputmode="numeric"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        <p id="err-nilai_tercatat" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    {{-- Nama Pemberi --}}
                    <div id="edit-fieldNamaPemberi" class="{{ in_array($aset->sumber_perolehan, ['Wakaf','Hibah/Donasi','Infak Jamaah']) ? '' : 'hidden' }}">
                        <label id="edit-labelNamaPemberi" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            {{ $aset->label_pemberi }}
                        </label>
                        <input type="text" name="nama_pemberi" value="{{ $aset->nama_pemberi }}"
                            placeholder="Masukkan nama"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Jumlah Unit</label>
                        <input type="number" name="jumlah_unit" value="{{ $aset->jumlah_unit ?? 1 }}" min="1"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Dokumen Pendukung</label>
                        @if($aset->dokumen_pendukung)
                            <p class="text-xs text-gray-500 mb-1.5">
                                Saat ini: <a href="{{ Storage::url($aset->dokumen_pendukung) }}" target="_blank" class="text-green-600 underline">Lihat Dokumen</a>
                            </p>
                        @endif
                        <input type="file" name="dokumen_pendukung" accept=".png,.jpg,.jpeg,.pdf"
                            class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border file:border-green-600 file:text-green-700 file:bg-transparent file:text-sm file:font-medium hover:file:bg-green-50 cursor-pointer">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Keterangan / Catatan</label>
                        <textarea name="keterangan" rows="3"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors resize-none">{{ $aset->keterangan }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Checkbox Penyusutan --}}
            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <input type="checkbox" id="edit-cbDisusutkan"
                    {{ $isDisusutkan ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                <div>
                    <label for="edit-cbDisusutkan" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                        Aset ini disusutkan
                    </label>
                    <p class="text-xs text-gray-400 mt-0.5">Centang jika aset memiliki umur manfaat dan perlu dihitung penyusutannya</p>
                </div>
            </div>

            {{-- Section Penyusutan --}}
            <div id="edit-sectionPenyusutan" class="{{ $isDisusutkan ? '' : 'hidden' }} space-y-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Penyusutan</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Mulai Penyusutan <span class="text-red-500">*</span></label>
                        <input type="hidden" name="tanggal_mulai_penyusutan" id="edit-tanggal_mulai_penyusutan"
                            value="{{ $aset->tanggal_mulai_penyusutan?->format('Y-m-d') }}">
                        <input type="text" id="edit-fp-tanggal_mulai" readonly
                            value="{{ $aset->tanggal_mulai_penyusutan?->translatedFormat('d F Y') }}"
                            placeholder="Pilih tanggal"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Umur Manfaat (Tahun) <span class="text-red-500">*</span></label>
                        <input type="number" name="umur_manfaat" id="edit-umurManfaat"
                            value="{{ $aset->umur_manfaat }}" placeholder="Contoh: 20" min="1"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    </div>
                </div>

                {{-- Preview --}}
                <div id="edit-previewPenyusutan" class="{{ $isDisusutkan && $aset->umur_manfaat ? '' : 'hidden' }} grid grid-cols-3 gap-3">
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900">
                        <p class="text-xs text-gray-400 mb-1">Penyusutan / Tahun</p>
                        <p id="edit-prev-tahunan" class="text-sm font-semibold text-gray-800 dark:text-white">
                            Rp {{ number_format($aset->penyusutan_per_tahun, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900">
                        <p class="text-xs text-gray-400 mb-1">Penyusutan / Bulan</p>
                        <p id="edit-prev-bulanan" class="text-sm font-semibold text-gray-800 dark:text-white">
                            Rp {{ number_format($aset->penyusutan_per_bulan, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/10 rounded-lg border border-green-100 dark:border-green-900">
                        <p class="text-xs text-gray-400 mb-1">Selesai Tahun</p>
                        <p id="edit-prev-selesai" class="text-sm font-semibold text-gray-800 dark:text-white">
                            @if($aset->tanggal_mulai_penyusutan && $aset->umur_manfaat)
                                {{ $aset->tanggal_mulai_penyusutan->year + $aset->umur_manfaat - 1 }}
                            @else –
                            @endif
                        </p>
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
                onclick="submitAsetForm('editAsetForm', 'PUT', '{{ route('dashboard.aset.update', $aset) }}')"
                class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
