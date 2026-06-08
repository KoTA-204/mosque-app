<form id="formEdit"
      action=""
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Tanggal & Dompet --}}
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

    {{-- Jumlah & Jenis Transaksi --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Jumlah <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 select-none">Rp</span>
                <input type="number" name="jumlah" required min="1"
                    class="w-full h-10 pl-9 pr-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
            </div>
        </div>
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
    </div>

    {{-- Akun Debit & Kredit --}}
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

    {{-- Kategori & Kegiatan --}}
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Kategori <span class="text-red-500">*</span>
            </label>
            <select name="kategori_transaksi_id" required
                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                <option value="">Pilih kategori</option>
                @foreach ($kategoris as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kegiatan</label>
            <select name="kegiatan_id"
                class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                <option value="">Pilih kegiatan (opsional)</option>
                @foreach ($kegiatans as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kegiatan }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Keterangan --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <textarea name="deskripsi" rows="2"
            placeholder="Masukan keterangan transaksi"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"></textarea>
    </div>

    {{-- Catatan --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
        <textarea name="catatan" rows="2"
            placeholder="Catatan tambahan"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"></textarea>
    </div>

    {{-- Tambah bukti baru --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tambah Bukti Baru</label>
        <div class="border border-dashed border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:border-green-400 hover:bg-green-50/30 transition-colors"
            onclick="document.getElementById('inputBuktiEdit').click()">
            <svg class="w-5 h-5 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <p class="text-sm text-gray-500">
                <span class="font-medium text-green-700 underline">Pilih File</span>
                atau tarik file bukti transaksi
            </p>
            <p class="text-xs text-gray-400 mt-0.5">.PNG, .JPG, .PDF</p>
        </div>
        <input type="file" id="inputBuktiEdit" name="bukti_transaksi[]"
            accept=".png,.jpg,.jpeg,.pdf" multiple class="hidden"
            onchange="previewBuktiEdit(this.files)">
        <div id="listBuktiEdit" class="mt-2 space-y-1.5"></div>
    </div>

    {{-- Error --}}
    <div id="editErrors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
        <ul id="editErrorList" class="text-sm text-red-600 space-y-0.5 list-disc list-inside"></ul>
    </div>

    {{-- Tombol --}}
    <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal('modalEdit')"
            class="h-9 px-4 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
            Batal
        </button>
        <button type="submit" id="btnEditSubmit"
            class="h-9 px-5 text-sm bg-green-700 text-white rounded-xl font-medium hover:bg-green-800 transition-colors flex items-center gap-2">
            <svg id="iconSpinnerEdit" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Simpan Perubahan
        </button>
    </div>
</form>

<script>
function previewBuktiEdit(files) {
    const list = document.getElementById('listBuktiEdit');
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

document.getElementById('formEdit').addEventListener('submit', async function (e) {
    e.preventDefault();

    const btn     = document.getElementById('btnEditSubmit');
    const spinner = document.getElementById('iconSpinnerEdit');
    const errBox  = document.getElementById('editErrors');
    const errList = document.getElementById('editErrorList');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    errBox.classList.add('hidden');
    errList.innerHTML = '';

    try {
        const fd = new FormData(this);
        // Laravel PUT via _method
        fd.set('_method', 'PUT');

        const res  = await fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.success) {
            closeModal('modalEdit');
            window.location.reload();
        } else if (res.status === 422 && data.errors) {
            Object.values(data.errors).flat().forEach(msg => {
                errList.insertAdjacentHTML('beforeend', `<li>${msg}</li>`);
            });
            errBox.classList.remove('hidden');
        } else {
            alert(data.message ?? 'Terjadi kesalahan.');
        }
    } catch {
        alert('Gagal menghubungi server.');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
});
</script>