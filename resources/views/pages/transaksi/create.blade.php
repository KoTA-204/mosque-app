<form id="formTambah"
      action="{{ route('dashboard.transaksi.store') }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal <span class="text-red-500">*</span>
            </label>
            <input type="date" name="tanggal_transaksi" required
                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Jumlah <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 select-none">Rp</span>
                <input type="number" name="jumlah" required min="1"
                    placeholder="0"
                    class="w-full h-10 pl-9 pr-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Akun Debit <span class="text-red-500">*</span>
            </label>
            <select name="akun_debit_id" required
                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                <option value="">Pilih akun debit</option>
                @foreach ($akuns as $a)
                    <option value="{{ $a->id }}">{{ $a->kode_akun }} – {{ $a->nama_akun }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Akun Kredit <span class="text-red-500">*</span>
            </label>
            <select name="akun_kredit_id" required
                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                <option value="">Pilih akun kredit</option>
                @foreach ($akuns as $a)
                    <option value="{{ $a->id }}">{{ $a->kode_akun }} – {{ $a->nama_akun }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
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
            <p class="text-xs text-gray-400 mt-1">.PNG, .JPG, .PDF</p>
        </div>
        <input type="file" id="inputBukti" name="bukti_transaksi[]"
            accept=".png,.jpg,.jpeg,.pdf" multiple class="hidden"
            onchange="previewBukti(this.files)">
        <div id="listBukti" class="mt-2 space-y-1.5"></div>
    </div>

    <div class="border-t border-gray-100 pt-4 mb-4">
        <div class="flex items-center gap-3 px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 cursor-pointer select-none"
            onclick="tambahToggleAset()">
            <input type="hidden" name="is_aset" id="tambahIsAset" value="0">
            {{-- Track --}}
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

        {{-- Identitas Aset --}}
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

        {{-- Perolehan Aset --}}
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

        {{-- Penyusutan --}}
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

    {{-- Error container --}}
    <div id="tambahErrors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
        <ul id="tambahErrorList" class="text-sm text-red-600 space-y-0.5 list-disc list-inside"></ul>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal('modalTambah')"
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

function previewBukti(files) {
    const list = document.getElementById('listBukti');
    list.innerHTML = '';
    [...files].forEach(f => {
        list.insertAdjacentHTML('beforeend', `
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="flex-1 truncate">${f.name}</span>
                <span class="text-xs text-gray-400">${(f.size/1024).toFixed(0)} KB</span>
            </div>
        `);
    });
}

function handleDropBukti(e) {
    e.preventDefault();
    const dt = new DataTransfer();
    [...e.dataTransfer.files].forEach(f => dt.items.add(f));
    document.getElementById('inputBukti').files = dt.files;
    previewBukti(dt.files);
}

function previewDokumen(input) {
    const lbl = document.getElementById('labelDokumenAset');
    lbl.textContent = input.files[0]?.name ?? 'Unggah dokumen';
    lbl.classList.toggle('text-gray-400', !input.files[0]);
    lbl.classList.toggle('text-gray-700', !!input.files[0]);
}

async function submitTambah() {
    const form    = document.getElementById('formTambah');
    const btn     = document.getElementById('btnTambahSubmit');
    const spinner = document.getElementById('iconSpinnerTambah');
    const errBox  = document.getElementById('tambahErrors');
    const errList = document.getElementById('tambahErrorList');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    errBox.classList.add('hidden');
    errList.innerHTML = '';

    try {
        const fd  = new FormData(form);
        const res = await fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        if (data.success) {
            closeModal('modalTambah');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'success',
                message: 'Transaksi berhasil disimpan.'
            }));
            window.location.reload();
        } else if (res.status === 422 && data.errors) {
            Object.values(data.errors).flat().forEach(msg => {
                errList.insertAdjacentHTML('beforeend', `<li>${msg}</li>`);
            });
            errBox.classList.remove('hidden');
        } else {
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'error',
                message: data.message ?? 'Terjadi kesalahan.'
            }));
            window.location.reload();
        }
    } catch {
        alert('Gagal menghubungi server.');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
}
</script>