<form id="formTambah"
      action="{{ route('dashboard.transaksi.store') }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="force" id="forceSubmit" value="0">

    <div id="tambahDraftNotice" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700 flex items-center justify-between">
        <span>Draft sebelumnya berhasil dipulihkan. Bukti transaksi perlu diunggah ulang.</span>
        <button type="button" onclick="document.getElementById('tambahDraftNotice').classList.add('hidden')" class="text-blue-400 hover:text-blue-600">&times;</button>
    </div>
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="text" id="inputTanggalTambah" name="tanggal_transaksi" required
                    placeholder="Pilih tanggal"
                    readonly
                    class="w-full h-10 px-3 pr-9 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 cursor-pointer bg-white">
                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Dompet <span class="text-red-500">*</span>
            </label>
            <select name="dompet_id" required
                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                <option value="">Pilih dompet</option>
                @foreach ($dompets as $d)
                    <option value="{{ $d->id }}">{{ $d->nama_dompet }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Jenis Transaksi <span class="text-red-500">*</span>
        </label>
        <select name="jenis_transaksi" required
            class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
            <option value="">Pilih jenis transaksi</option>
            <option value="PEMASUKAN">Pemasukan</option>
            <option value="PENGELUARAN">Pengeluaran</option>
        </select>
    </div>

    {{-- Detail Jurnal --}}
    <div class="mb-1 flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">
            Detail Jurnal <span class="text-red-500">*</span>
        </label>
       <button type="button" onclick="buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah)"
            class="text-xs font-medium text-green-700 hover:underline">
            + Tambah Baris
        </button>
    </div>
    <div class="border border-gray-200 rounded-xl overflow-hidden mb-2">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-xs font-medium text-gray-500 px-3 py-2">Akun</th>
                    <th class="text-left text-xs font-medium text-gray-500 px-3 py-2 w-24">Tipe</th>
                    <th class="text-left text-xs font-medium text-gray-500 px-3 py-2 w-32">Nominal</th>
                    <th class="w-8"></th>
                </tr>
            </thead>
            <tbody id="jurnalTambahBody" class="divide-y divide-gray-100"></tbody>
        </table>
    </div>
    <div class="flex items-center justify-between text-xs px-1 mb-4">
        <div class="flex items-center gap-4">
            <span class="text-gray-500">Total Debit: <span id="jurnalTambahTotalDebit" class="font-semibold text-red-600">Rp 0</span></span>
            <span class="text-gray-500">Total Kredit: <span id="jurnalTambahTotalKredit" class="font-semibold text-green-700">Rp 0</span></span>
        </div>
        <span id="jurnalTambahStatus" class="font-medium text-gray-400">Belum diisi</span>
    </div>

    <div id="jurnalTambahZakatWarning" class="hidden mb-3 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.28 11.18c.75 1.334-.213 2.98-1.742 2.98H3.72c-1.53 0-2.493-1.646-1.743-2.98l6.28-11.18zM11 14a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V7a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span>Transaksi ini melibatkan akun <strong>Zakat</strong>. Mohon isi <strong>Keterangan</strong> secara detail (jenis zakat, jumlah muzakki/mustahik, atau tujuan penyaluran) agar sesuai ketentuan pencatatan zakat.</span>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <textarea name="deskripsi" rows="2"
            placeholder="Masukan keterangan transaksi"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"></textarea>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Bukti Transaksi</label>
        <div id="dropzoneBukti"
            class="border border-dashed border-gray-300 rounded-xl p-5 text-center cursor-pointer hover:border-green-400 hover:bg-green-50/30 transition-colors"
            onclick="document.getElementById('inputBukti').click()"
            ondragover="event.preventDefault()"
            ondrop="handleDropBukti(event)">
            <svg class="w-6 h-6 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <p class="text-sm text-gray-500">
                <span class="font-medium text-green-700 underline">Pilih File</span>
                atau tarik file bukti transaksi untuk diunggah disini
            </p>
            <p class="text-xs text-gray-400 mt-1">.PNG, .JPG, .PDF &middot; maks. {{ config('transaksi.bukti_max_files', 5) }} file, masing-masing maks. {{ config('transaksi.bukti_max_size_mb', 5) }} MB</p>
        </div>
        <p id="buktiError" class="hidden mt-1.5 text-xs text-red-500"></p>
        <input type="file" id="inputBukti" name="bukti_transaksi[]"
            accept=".png,.jpg,.jpeg,.pdf" multiple class="hidden"
            onchange="previewBukti(this.files)">
        <div id="listBukti" class="mt-2 space-y-1.5"></div>
    </div>

    <div class="border-t border-gray-100 pt-4 mb-4">
        <div class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 cursor-pointer select-none"
            onclick="tambahToggleAset()">
            <input type="hidden" name="is_aset" id="tambahIsAset" value="0">
            <div id="tambahTrack"
                class="relative w-10 h-5 rounded-full bg-gray-300 flex-shrink-0 transition-colors duration-200">
                <span id="tambahThumb"
                    class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">Transaksi ini merupakan perolehan aset</p>
                <p class="text-xs text-gray-500">Aktifkan untuk mencatat detail aset terkait transaksi</p>
            </div>
            <span id="tambahBadgeAset"
                class="hidden items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-lg">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Aset aktif
            </span>
        </div>
    </div>

    <div id="sectionAset" class="hidden space-y-4 mb-4">

        <div>
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Identitas Aset</h4>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Nama Aset <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_aset"
                            placeholder="Masukkan nama aset"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Tanggal Perolehan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_perolehan"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Kondisi Aset <span class="text-red-500">*</span>
                    </label>
                    <select name="kondisi_aset"
                        class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        <option value="">Pilih kondisi aset</option>
                        <option value="BARU">Baru</option>
                        <option value="BAIK">Baik</option>
                        <option value="RUSAK_RINGAN">Rusak Ringan</option>
                        <option value="RUSAK_BERAT">Rusak Berat</option>
                    </select>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Perolehan Aset</h4>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Sumber Perolehan <span class="text-red-500">*</span>
                        </label>
                        <select name="sumber_perolehan"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                            <option value="">Pilih sumber</option>
                            <option value="PEMBELIAN">Pembelian</option>
                            <option value="DONASI">Donasi</option>
                            <option value="WAKAF">Wakaf</option>
                            <option value="HIBAH">Hibah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Lokasi Aset <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="lokasi_aset"
                            placeholder="Masukkan lokasi"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Jumlah Unit <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah_unit" value="1" min="1"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dokumen Pendukung</label>
                        <div class="relative">
                            <input type="file" name="dokumen_aset" id="inputDokumenAset"
                                accept=".jpg,.jpeg,.png,.pdf" class="hidden"
                                onchange="previewDokumen(this)">
                            <button type="button"
                                onclick="document.getElementById('inputDokumenAset').click()"
                                class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white text-left flex items-center gap-2 hover:border-green-400 transition-colors">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span id="labelDokumenAset" class="text-xs text-gray-400 truncate">Unggah dokumen</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
                <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Penyusutan</h4>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Tanggal Mulai Penyusutan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai_penyusutan"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Umur Manfaat (Tahun) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="umur_manfaat"
                            placeholder="Contoh: 20" min="1"
                            class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan / Catatan</label>
                    <textarea name="keterangan_penyusutan" rows="2"
                        placeholder="Masukkan catatan tambahan yang perlu diungkapkan dalam laporan keuangan"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white resize-none focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"></textarea>
                </div>
            </div>
        </div>

    </div>

    <div id="tambahErrors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
        <ul id="tambahErrorList" class="text-sm text-red-600 space-y-0.5 list-disc list-inside"></ul>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" onclick="hapusDraftTambah(); closeModal('modalTambah')"
            class="h-9 px-4 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
            Batal
        </button>
        <button type="button" id="btnTambahSubmit" onclick="submitTambah()"
            class="h-9 px-5 text-sm bg-green-700 text-white rounded-xl font-medium hover:bg-green-800 transition-colors flex items-center gap-2">
            <svg id="iconSpinnerTambah" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Simpan
        </button>
    </div>
</form>

<script>
// Daftar akun untuk dropdown jurnal (dipakai juga oleh editTransaksi() di index.blade.php)
const akunListTambah = {!! json_encode($akuns->map(fn($a) => [
    'id'       => $a->id,
    'label'    => $a->kode_akun . ' – ' . $a->nama_akun,
    'is_zakat' => str_contains(strtolower($a->nama_akun), 'zakat'),
])) !!};

// ── Draft Auto-save ────────────────────────────────
const DRAFT_KEY_TAMBAH = 'draft_transaksi_tambah';

// true = draft baru saja sengaja dihapus (submit sukses atau user membatalkan
// lewat X/Batal). Selama flag ini true, autosave (termasuk yang dipicu oleh
// 'beforeunload' saat reload/keluar halaman) tidak boleh menuliskan ulang
// draft yang sudah sengaja dibuang. Flag direset begitu pengguna benar-benar
// mengetik/memilih sesuatu lagi di form.
let tambahDraftDibatalkan = false;

function debounce(fn, delay) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

function kumpulkanDraftTambah() {
    const jurnalRows = [];
    document.querySelectorAll('#jurnalTambahBody tr').forEach(tr => {
        jurnalRows.push({
            akun_id: tr.querySelector('.jurnalAkun')?.value ?? '',
            tipe:    tr.querySelector('.jurnalTipe')?.value ?? 'DEBIT',
            nominal: tr.querySelector('.jurnalNominal')?.value ?? '',
        });
    });

    return {
        tanggal_transaksi: document.getElementById('inputTanggalTambah')?.value ?? '',
        dompet_id:         document.querySelector('[name="dompet_id"]')?.value ?? '',
        jenis_transaksi:   document.querySelector('[name="jenis_transaksi"]')?.value ?? '',
        deskripsi:         document.querySelector('[name="deskripsi"]')?.value ?? '',
        is_aset:           document.getElementById('tambahIsAset')?.value ?? '0',
        jurnal:            jurnalRows,
        savedAt:           Date.now(),
    };
}

function simpanDraftTambah() {
    if (tambahDraftDibatalkan) return; // draft sengaja dihapus, jangan dihidupkan lagi
    try {
        sessionStorage.setItem(DRAFT_KEY_TAMBAH, JSON.stringify(kumpulkanDraftTambah()));
    } catch (e) {
        console.warn('Gagal menyimpan draft:', e);
    }
}
const simpanDraftTambahDebounced = debounce(simpanDraftTambah, 500);

function hapusDraftTambah() {
    sessionStorage.removeItem(DRAFT_KEY_TAMBAH);
    tambahDraftDibatalkan = true;
    resetBuktiTambah();
    resetFormTambahVisual();
    tambahDraftDibatalkan = true; // pastikan tetap true walau reset di atas memicu event input/change sintetis
}

// Kembalikan form ke kondisi kosong/awal: dipakai saat draft dibuang lewat
// tombol X/Batal (atau setelah submit sukses) supaya membuka form Tambah
// lagi tidak menampilkan sisa isian yang sudah sengaja dibatalkan/tersimpan.
function resetFormTambahVisual() {
    const form = document.getElementById('formTambah');
    if (!form) return;

    form.reset();
    fpTambahTanggal?.clear();

    if (tambahAsetOn) tambahToggleAset();

    document.getElementById('jurnalTambahBody').innerHTML = '';
    buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, 'DEBIT');
    buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, 'KREDIT');
    checkZakatWarning('jurnalTambahBody', 'jurnalTambah');
    hitungTotalJurnal('jurnalTambahBody', 'jurnalTambah');

    document.getElementById('tambahDraftNotice')?.classList.add('hidden');
}

function pulihkanDraftTambah() {
    const raw = sessionStorage.getItem(DRAFT_KEY_TAMBAH);
    if (!raw) return false;

    let draft;
    try { draft = JSON.parse(raw); } catch { hapusDraftTambah(); return false; }

    // Draft basi (lebih dari 24 jam) tidak dipulihkan
    if (!draft.savedAt || (Date.now() - draft.savedAt) > 24 * 60 * 60 * 1000) {
        hapusDraftTambah();
        return false;
    }

    const adaIsi = draft.tanggal_transaksi || draft.dompet_id || draft.deskripsi ||
        draft.jurnal?.some(r => r.akun_id || r.nominal);
    if (!adaIsi) return false;

    if (draft.tanggal_transaksi) fpTambahTanggal.setDate(draft.tanggal_transaksi, true);
    if (draft.dompet_id) document.querySelector('[name="dompet_id"]').value = draft.dompet_id;
    if (draft.jenis_transaksi) document.querySelector('[name="jenis_transaksi"]').value = draft.jenis_transaksi;
    if (draft.deskripsi) document.querySelector('[name="deskripsi"]').value = draft.deskripsi;

    document.getElementById('jurnalTambahBody').innerHTML = '';
    if (Array.isArray(draft.jurnal) && draft.jurnal.length >= 2) {
        draft.jurnal.forEach(row => {
            buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, row.tipe, row.akun_id, row.nominal);
        });
    } else {
        buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, 'DEBIT');
        buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, 'KREDIT');
    }

    if (draft.is_aset === '1' && !tambahAsetOn) tambahToggleAset();

    checkZakatWarning('jurnalTambahBody', 'jurnalTambah');
    hitungTotalJurnal('jurnalTambahBody', 'jurnalTambah');

    document.getElementById('tambahDraftNotice')?.classList.remove('hidden');
    return true;
}

function buatOpsiAkunHTML(akunList, selected = '') {
    let html = '<option value="">Pilih akun</option>';
    akunList.forEach(a => {
        html += `<option value="${a.id}" data-zakat="${a.is_zakat ? 1 : 0}" ${String(a.id) === String(selected) ? 'selected' : ''}>${a.label}</option>`;
    });
    return html;
}

document.addEventListener('DOMContentLoaded', function () {
    fpTambahTanggal = flatpickr('#inputTanggalTambah', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: false,
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
                longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
            },
            months: {
                shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
            },
        },
    });

    const dipulihkan = pulihkanDraftTambah();

    if (!dipulihkan) {
        buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, 'DEBIT');
        buatBarisJurnal('jurnalTambahBody', 'jurnalTambah', akunListTambah, 'KREDIT');
        checkZakatWarning('jurnalTambahBody', 'jurnalTambah');
    }

    // Auto-save setiap ada perubahan input di form (delegasi di level form
    // supaya baris jurnal yang dibuat secara dinamis ikut tercakup tanpa
    // perlu didaftarkan satu-satu). Sebelumnya draft hanya tersimpan saat
    // event 'offline' terpicu, sehingga refresh halaman biasa membuat isian
    // form yang belum disimpan ke database ikut hilang.
    const formTambahEl = document.getElementById('formTambah');
    formTambahEl?.addEventListener('input',  () => { tambahDraftDibatalkan = false; simpanDraftTambahDebounced(); });
    formTambahEl?.addEventListener('change', () => { tambahDraftDibatalkan = false; simpanDraftTambahDebounced(); });

    // Simpan segera (tanpa debounce) sesaat sebelum halaman ditinggalkan/
    // di-refresh, supaya perubahan dalam jendela debounce terakhir tidak hilang.
    window.addEventListener('beforeunload', () => {
        simpanDraftTambah();
    });
    window.addEventListener('offline', () => {
        simpanDraftTambah();
    });
});

// ── Jurnal dinamis (dipakai bersama oleh form Tambah & Edit) ───────────────

// Format angka menjadi "1.000.000" (pemisah ribuan ala Indonesia).
// Hanya digit yang dipertahankan, sehingga aman dipanggil berulang kali
// saat pengguna mengetik.
function formatRibuan(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    if (!digits) return '';
    return parseInt(digits, 10).toLocaleString('id-ID');
}

// Ambil kembali angka murni (tanpa titik pemisah ribuan) dari input nominal.
function parseRibuan(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    return digits ? parseInt(digits, 10) : 0;
}

// Diikat ke event "input" pada field nominal: memformat ulang nilai sambil
// mempertahankan posisi kursor secara wajar (kursor diletakkan di akhir).
function formatInputNominal(input) {
    input.value = formatRibuan(input.value);
}

function buatBarisJurnal(tbodyId, prefix, akunList, tipe = 'DEBIT', akunId = '', nominal = '') {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="px-3 py-2">
            <select class="jurnalAkun w-full h-9 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500"
                onchange="checkZakatWarning('${tbodyId}', '${prefix}')">
                ${buatOpsiAkunHTML(akunList, akunId)}
            </select>
        </td>
        <td class="px-3 py-2">
            <select class="jurnalTipe w-full h-9 px-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500"
                onchange="hitungTotalJurnal('${tbodyId}', '${prefix}')">
                <option value="DEBIT" ${tipe === 'DEBIT' ? 'selected' : ''}>Debit</option>
                <option value="KREDIT" ${tipe === 'KREDIT' ? 'selected' : ''}>Kredit</option>
            </select>
        </td>
        <td class="px-3 py-2">
            <div class="relative">
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                <input type="text" inputmode="numeric" value="${formatRibuan(nominal)}"
                    oninput="formatInputNominal(this); hitungTotalJurnal('${tbodyId}', '${prefix}')"
                    class="jurnalNominal w-full h-9 pl-7 pr-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-green-500">
            </div>
        </td>
        <td class="px-2 py-2 text-center">
            <button type="button" onclick="hapusBarisJurnal(this, '${tbodyId}', '${prefix}')" class="text-gray-400 hover:text-red-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    hitungTotalJurnal(tbodyId, prefix);
}

function hapusBarisJurnal(btn, tbodyId, prefix) {
    const tbody = document.getElementById(tbodyId);
    if (tbody.querySelectorAll('tr').length <= 2) {
        alert('Minimal harus ada 1 baris debit dan 1 baris kredit.');
        return;
    }
    btn.closest('tr').remove();
    hitungTotalJurnal(tbodyId, prefix);
    checkZakatWarning(tbodyId, prefix);
}

function hitungTotalJurnal(tbodyId, prefix) {
    const tbody = document.getElementById(tbodyId);
    let totalDebit = 0, totalKredit = 0;
    tbody.querySelectorAll('tr').forEach(tr => {
        const tipe    = tr.querySelector('.jurnalTipe').value;
        const nominal = parseRibuan(tr.querySelector('.jurnalNominal').value);
        if (tipe === 'DEBIT') totalDebit += nominal; else totalKredit += nominal;
    });

    document.getElementById(prefix + 'TotalDebit').textContent  = 'Rp ' + totalDebit.toLocaleString('id-ID');
    document.getElementById(prefix + 'TotalKredit').textContent = 'Rp ' + totalKredit.toLocaleString('id-ID');

    const statusEl = document.getElementById(prefix + 'Status');
    if (totalDebit === 0 && totalKredit === 0) {
        statusEl.textContent = 'Belum diisi';
        statusEl.className = 'font-medium text-gray-400';
    } else if (totalDebit === totalKredit) {
        statusEl.textContent = '✓ Balance';
        statusEl.className = 'font-medium text-green-600';
    } else {
        statusEl.textContent = '✗ Tidak balance';
        statusEl.className = 'font-medium text-red-500';
    }

    return { totalDebit, totalKredit };
}

function checkZakatWarning(tbodyId, prefix) {
    const tbody = document.getElementById(tbodyId);
    const warningEl = document.getElementById(prefix + 'ZakatWarning');
    if (!tbody || !warningEl) return;

    const adaZakat = [...tbody.querySelectorAll('.jurnalAkun')].some(sel => {
        const opt = sel.options[sel.selectedIndex];
        return opt && opt.dataset.zakat === '1';
    });

    warningEl.classList.toggle('hidden', !adaZakat);
}

// ── Aset toggle ──────────────────────────────────────────────────────────

let tambahAsetOn = false;

function tambahToggleAset() {
    tambahAsetOn = !tambahAsetOn;
    const track   = document.getElementById('tambahTrack');
    const thumb   = document.getElementById('tambahThumb');
    const section = document.getElementById('sectionAset');
    const badge   = document.getElementById('tambahBadgeAset');
    const input   = document.getElementById('tambahIsAset');
    const ASET_REQUIRED = ['nama_aset','tanggal_perolehan','kondisi_aset','sumber_perolehan','lokasi_aset','jumlah_unit','tanggal_mulai_penyusutan','umur_manfaat'];

    if (tambahAsetOn) {
        track.classList.replace('bg-gray-300', 'bg-green-600');
        thumb.classList.add('translate-x-5');
        section.classList.remove('hidden');
        badge.classList.replace('hidden', 'inline-flex');
        input.value = '1';
        ASET_REQUIRED.forEach(n => {
            const el = document.querySelector(`[name="${n}"]`);
            if (el) el.setAttribute('required', '');
        });
    } else {
        track.classList.replace('bg-green-600', 'bg-gray-300');
        thumb.classList.remove('translate-x-5');
        section.classList.add('hidden');
        badge.classList.replace('inline-flex', 'hidden');
        input.value = '0';
        ASET_REQUIRED.forEach(n => {
            const el = document.querySelector(`[name="${n}"]`);
            if (el) el.removeAttribute('required');
        });
    }
}

// Batas unggah bukti transaksi. Sesuaikan dengan aturan validasi di
// StoreTransaksiRequest/UpdateTransaksiRequest agar pesan di UI konsisten
// dengan yang benar-benar diterapkan di server.
const BUKTI_MAX_FILES   = {{ config('transaksi.bukti_max_files', 5) }};
const BUKTI_MAX_SIZE_MB = {{ config('transaksi.bukti_max_size_mb', 5) }};

// Validasi jumlah & ukuran file bukti transaksi di sisi klien.
// Mengembalikan pesan error (string) jika tidak valid, atau null jika valid.
function validasiBukti(files) {
    if (files.length > BUKTI_MAX_FILES) {
        return `Maksimal ${BUKTI_MAX_FILES} file bukti transaksi yang dapat diunggah.`;
    }
    const tooBig = [...files].find(f => f.size > BUKTI_MAX_SIZE_MB * 1024 * 1024);
    if (tooBig) {
        return `Ukuran file "${tooBig.name}" melebihi maksimal ${BUKTI_MAX_SIZE_MB} MB.`;
    }
    return null;
}

// Menyimpan seluruh file bukti transaksi yang sudah dipilih (akumulatif),
// karena memilih file baru lewat <input type=file> akan MENGGANTI seluruh
// FileList sebelumnya, bukan menambahkannya. Array inilah sumber kebenaran,
// lalu disinkronkan kembali ke input via DataTransfer sebelum submit.
let buktiFilesTambah = [];

function resetBuktiTambah() {
    buktiFilesTambah = [];
    document.getElementById('inputBukti').value = '';
    document.getElementById('listBukti').innerHTML = '';
    document.getElementById('buktiError').classList.add('hidden');
}

function sinkronInputBukti() {
    const dt = new DataTransfer();
    buktiFilesTambah.forEach(f => dt.items.add(f));
    document.getElementById('inputBukti').files = dt.files;
}

function renderListBukti() {
    const list = document.getElementById('listBukti');
    list.innerHTML = '';
    buktiFilesTambah.forEach((f, idx) => {
        list.insertAdjacentHTML('beforeend', `
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="flex-1 truncate">${f.name}</span>
                <span class="text-xs text-gray-400">${(f.size/1024).toFixed(0)} KB</span>
                <button type="button" onclick="hapusBuktiTambah(${idx})" title="Hapus file"
                    class="text-gray-400 hover:text-red-500 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `);
    });
}

function hapusBuktiTambah(idx) {
    buktiFilesTambah.splice(idx, 1);
    sinkronInputBukti();
    renderListBukti();
}

// Dipanggil saat file dipilih lewat dialog (input change) maupun drag & drop.
// File baru DITAMBAHKAN ke seleksi yang sudah ada, bukan menggantinya.
function tambahBuktiFiles(newFiles) {
    const errEl = document.getElementById('buktiError');

    const gabungan = [...buktiFilesTambah, ...newFiles];
    const pesanError = validasiBukti(gabungan);
    if (pesanError) {
        errEl.textContent = pesanError;
        errEl.classList.remove('hidden');
        // Kembalikan input ke seleksi lama yang masih valid (jangan hapus semua).
        sinkronInputBukti();
        return;
    }
    errEl.classList.add('hidden');

    buktiFilesTambah = gabungan;
    sinkronInputBukti();
    renderListBukti();
}

function previewBukti(files) {
    tambahBuktiFiles([...files]);
}

function handleDropBukti(e) {
    e.preventDefault();
    tambahBuktiFiles([...e.dataTransfer.files]);
}

function previewDokumen(input) {
    const lbl = document.getElementById('labelDokumenAset');
    lbl.textContent = input.files[0]?.name ?? 'Unggah dokumen';
    lbl.classList.toggle('text-gray-400', !input.files[0]);
    lbl.classList.toggle('text-gray-700', !!input.files[0]);
}

async function submitTambah(force = false) {
    const form    = document.getElementById('formTambah');
    const btn     = document.getElementById('btnTambahSubmit');
    const spinner = document.getElementById('iconSpinnerTambah');
    const errBox  = document.getElementById('tambahErrors');
    const errList = document.getElementById('tambahErrorList');

    errBox.classList.add('hidden');
    errList.innerHTML = '';

    const { totalDebit, totalKredit } = hitungTotalJurnal('jurnalTambahBody', 'jurnalTambah');
    if (totalDebit === 0 || totalDebit !== totalKredit) {
        errList.insertAdjacentHTML('beforeend', `<li>Total debit dan kredit harus sama dan tidak boleh kosong.</li>`);
        errBox.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    spinner.classList.remove('hidden');

    document.getElementById('forceSubmit').value = force ? '1' : '0';

    try {
        const fd  = new FormData(form);

        let idx = 0;
        document.querySelectorAll('#jurnalTambahBody tr').forEach(tr => {
            fd.append(`jurnal[${idx}][akun_id]`, tr.querySelector('.jurnalAkun').value);
            fd.append(`jurnal[${idx}][tipe]`,    tr.querySelector('.jurnalTipe').value);
            fd.append(`jurnal[${idx}][nominal]`, parseRibuan(tr.querySelector('.jurnalNominal').value));
            idx++;
        });

        const res = await fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        if (res.redirected) {
            closeModal('modalTambah');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'error',
                message: 'Anda tidak memiliki izin untuk melakukan aksi ini.'
            }));
            window.location.reload();
            return;
        }
        const data = await res.json();

        if (data.success) {
            hapusDraftTambah();
            closeModal('modalTambah');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'success',
                message: 'Transaksi berhasil disimpan.'
            }));
            window.location.reload();

        } else if (res.status === 409 && data.type === 'duplikat_warning') {
            const d = data.detail;
            document.getElementById('dd_tanggal').textContent  = d.tanggal;
            document.getElementById('dd_jumlah').textContent   = 'Rp ' + d.jumlah;
            document.getElementById('dd_jenis').textContent    = d.jenis === 'PEMASUKAN' ? 'Pemasukan' : 'Pengeluaran';
            document.getElementById('dd_kategori').textContent = d.kategori;
            document.getElementById('dd_dompet').textContent   = d.dompet;
            document.getElementById('dd_deskripsi').textContent = d.deskripsi;
            openModal('modalDuplikat');

        } else if (res.status === 422 && data.errors) {
            Object.values(data.errors).flat().forEach(msg => {
                errList.insertAdjacentHTML('beforeend', `<li>${msg}</li>`);
            });
            errBox.classList.remove('hidden');

        } else if (res.status === 403) {
            errList.insertAdjacentHTML('beforeend', `<li>Anda tidak memiliki hak akses untuk mencatat transaksi.</li>`);
            errBox.classList.remove('hidden');
            hapusDraftTambah();
            closeModal('modalTambah');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'error',
                message: 'Anda tidak memiliki izin untuk melakukan aksi ini.'
            }));
            window.location.reload();

        } else {
            errList.insertAdjacentHTML('beforeend', `<li>${data.message ?? 'Terjadi kesalahan.'}</li>`);
            errBox.classList.remove('hidden');
        }
    } catch (error) {
        console.error('submitTambah error:', error);
        errList.insertAdjacentHTML('beforeend', `<li>Gagal menghubungi server. Silakan coba lagi.</li>`);
        errBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}

function konfirmasiDuplikat() {
    closeModal('modalDuplikat');
    submitTambah(true);
}
</script>