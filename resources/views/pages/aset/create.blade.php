{{-- resources/views/pages/aset/create.blade.php --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl overflow-hidden">

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

    {{-- Tabs --}}
    <div class="flex gap-6 border-b border-gray-200 dark:border-gray-800 px-6">
        <button type="button" onclick="switchCreateTab('details')" id="create-tab-details"
            class="pb-3 pt-1 text-sm font-medium border-b-2 border-gray-900 dark:border-white text-gray-900 dark:text-white -mb-px transition-colors">
            Details
        </button>
        <button type="button" onclick="switchCreateTab('moreinfo')" id="create-tab-moreinfo"
            class="pb-3 pt-1 text-sm font-medium border-b-2 border-transparent text-gray-400 dark:text-gray-500 -mb-px transition-colors">
            More Info
        </button>
    </div>

    <form id="createAsetForm" enctype="multipart/form-data">
        @csrf

        <div class="px-6 py-5 max-h-[68vh] overflow-y-auto">

            {{-- ── Panel Details ──────────────────────────────── --}}
            <div id="create-panel-details">

                {{-- Identitas Aset --}}
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Identitas Aset</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Nama Aset <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_aset" placeholder="Masukkan Nama Aset"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                        <p id="err-nama_aset" class="text-xs text-red-500 mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Lokasi <span class="text-red-500">*</span>
                        </label>
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
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Kondisi Aset <span class="text-red-500">*</span>
                        </label>
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

                {{-- Perolehan Aset --}}
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Perolehan Aset</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Sumber Perolehan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="sumber_perolehan" id="create-sumberPerolehan" onchange="onCreateSumberChange()"
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
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Tanggal Perolehan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_perolehan"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        <p id="err-tanggal_perolehan" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    {{-- Field nilai — label berubah sesuai sumber --}}
                    <div>
                        <label id="create-labelNilai" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Nilai Perolehan (IDR) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="nilai_tercatat" placeholder="0" min="0"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                        <p id="err-nilai_tercatat" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    {{-- Nama Pemberi — tampil kondisional --}}
                    <div id="create-fieldNamaPemberi" class="hidden">
                        <label id="create-labelNamaPemberi" class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Nama Pemberi
                        </label>
                        <input type="text" name="nama_pemberi" placeholder="Masukkan nama"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    </div>

                    {{-- Nomor Kuitansi — tampil hanya saat Pembelian --}}
                    <div id="create-fieldNoKuitansi" class="hidden">
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
                            Nomor Kuitansi / Faktur
                        </label>
                        <input type="text" name="nomor_kuitansi" placeholder="Masukkan nomor dokumen"
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
                            <span class="text-xs text-gray-400">atau tarik file untuk diunggah (.PNG, .JPG, .PDF)</span>
                            <input type="file" name="dokumen_pendukung" class="hidden" accept=".png,.jpg,.jpeg,.pdf"
                                onchange="document.getElementById('create-dokumenNama').textContent = this.files[0]?.name ?? 'Pilih File'">
                        </label>
                    </div>
                </div>

                {{-- Penyusutan --}}
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">Penyusutan
                    <span class="ml-2 text-xs text-gray-400 normal-case font-normal">(Opsional — kosongkan jika tidak disusutkan)</span>
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Mulai Penyusutan</label>
                        <input type="date" name="tanggal_mulai_penyusutan"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Umur Manfaat (Tahun)</label>
                        <input type="number" name="umur_manfaat" placeholder="Contoh: 20" min="1"
                            class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Keterangan / Catatan</label>
                    <textarea name="keterangan" rows="3"
                        placeholder="Masukkan catatan tambahan yang perlu diungkapkan dalam laporan keuangan"
                        class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors resize-none"></textarea>
                </div>
            </div>

            {{-- ── Panel More Info ────────────────────────────── --}}
            <div id="create-panel-moreinfo" class="hidden">
                <div class="mb-5">
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Pemilik Aset</label>
                    <input type="text" value="Masjid Lukmanul Hakim" disabled
                        class="w-full border border-gray-100 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-500 cursor-not-allowed">
                </div>
                <div class="p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900 rounded-xl">
                    <p class="text-xs text-blue-700 dark:text-blue-400">
                        Akun COA dan kategori aset akan terisi otomatis saat aset dihubungkan dengan transaksi atau jurnal pembuka.
                    </p>
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

<script>
function switchCreateTab(tab) {
    ['details','moreinfo'].forEach(t => {
        document.getElementById(`create-panel-${t}`).classList.add('hidden');
        const btn = document.getElementById(`create-tab-${t}`);
        btn.className = 'pb-3 pt-1 text-sm font-medium border-b-2 border-transparent text-gray-400 dark:text-gray-500 -mb-px transition-colors';
    });
    document.getElementById(`create-panel-${tab}`).classList.remove('hidden');
    document.getElementById(`create-tab-${tab}`).className =
        'pb-3 pt-1 text-sm font-medium border-b-2 border-gray-900 dark:border-white text-gray-900 dark:text-white -mb-px transition-colors';
}

const createLabelNilaiMap = {
    'Wakaf':        'Nilai Wajar Aset (IDR)',
    'Hibah/Donasi': 'Nilai Wajar Aset (IDR)',
    'Infak Jamaah': 'Nilai Wajar Aset (IDR)',
    'Pembelian':    'Nilai Perolehan / Harga Beli (IDR)',
    'Lainnya':      'Nilai Perolehan (IDR)',
    '':             'Nilai Perolehan (IDR)',
};
const createLabelPemberiMap = {
    'Wakaf':        'Nama Wakif (Pemberi Wakaf)',
    'Hibah/Donasi': 'Nama Donatur / Pemberi Hibah',
    'Infak Jamaah': 'Nama Pemberi Infak',
};
const createShowPemberi  = ['Wakaf','Hibah/Donasi','Infak Jamaah'];
const createShowKuitansi = ['Pembelian'];

function onCreateSumberChange() {
    const val = document.getElementById('create-sumberPerolehan').value;

    // Label nilai
    document.getElementById('create-labelNilai').innerHTML =
        `${createLabelNilaiMap[val] ?? 'Nilai Perolehan (IDR)'} <span class="text-red-500">*</span>`;

    // Field nama pemberi
    const fp = document.getElementById('create-fieldNamaPemberi');
    if (createShowPemberi.includes(val)) {
        fp.classList.remove('hidden');
        document.getElementById('create-labelNamaPemberi').textContent =
            createLabelPemberiMap[val] ?? 'Nama Pemberi';
    } else {
        fp.classList.add('hidden');
    }

    // Field nomor kuitansi (hanya Pembelian)
    const fk = document.getElementById('create-fieldNoKuitansi');
    createShowKuitansi.includes(val) ? fk.classList.remove('hidden') : fk.classList.add('hidden');
}
</script>