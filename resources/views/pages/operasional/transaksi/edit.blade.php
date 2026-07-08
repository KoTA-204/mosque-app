<form id="formEdit"
      action=""
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="text" id="inputTanggalEdit" name="tanggal_transaksi" required
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

    {{-- Detail Jurnal — mendukung banyak akun debit & kredit --}}
    <div class="mb-1 flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700">
            Detail Jurnal <span class="text-red-500">*</span>
        </label>
        <button type="button" onclick="buatBarisJurnal('jurnalEditBody', 'jurnalEdit', akunListEdit)"
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
            <tbody id="jurnalEditBody" class="divide-y divide-gray-100"></tbody>
        </table>
    </div>
    <div class="flex items-center justify-between text-xs px-1 mb-4">
        <div class="flex items-center gap-4">
            <span class="text-gray-500">Total Debit: <span id="jurnalEditTotalDebit" class="font-semibold text-red-600">Rp 0</span></span>
            <span class="text-gray-500">Total Kredit: <span id="jurnalEditTotalKredit" class="font-semibold text-green-700">Rp 0</span></span>
        </div>
        <span id="jurnalEditStatus" class="font-medium text-gray-400">Belum diisi</span>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <textarea name="deskripsi" rows="2"
            placeholder="Masukan keterangan transaksi"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"></textarea>
    </div>

    <div class="mb-4">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Bukti Transaksi Saat Ini</label>
            <div id="existingBuktiList" class="space-y-1.5">
                <p class="text-xs text-gray-400">Memuat...</p>
            </div>
        </div>
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

    <div id="editErrors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
        <ul id="editErrorList" class="text-sm text-red-600 space-y-0.5 list-disc list-inside"></ul>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal('modalEdit')"
            class="h-9 px-4 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
            Batal
        </button>
        <button type="button" id="btnEditSubmit" onclick="submitEdit()"
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
const akunListEdit = @json($akuns->map(fn($a) => ['id' => $a->id, 'label' => $a->kode_akun . ' – ' . $a->nama_akun]));

let fpEditTanggal;

document.addEventListener('DOMContentLoaded', function () {
    fpEditTanggal = flatpickr('#inputTanggalEdit', {
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
});

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

function renderExistingBukti(items) {
    const container = document.getElementById('existingBuktiList');
    if (!container) return;

    if (!items || items.length === 0) {
        container.innerHTML = '<p class="text-xs text-gray-400">Belum ada bukti transaksi.</p>';
        return;
    }

    container.innerHTML = items.map(b => {
        const nama = b.nama_file ?? b.path_file.split('/').pop();
        const url  = `/storage/${b.path_file}`;
        return `
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700" data-bukti-id="${b.id}">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <a href="${url}" target="_blank" class="flex-1 truncate hover:underline">${nama}</a>
                <button type="button" onclick="hapusBuktiLama(${b.id}, this)" title="Hapus bukti"
                    class="text-gray-400 hover:text-red-500 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
    }).join('');
}

async function hapusBuktiLama(buktiId, btn) {
    if (!await confirmAsync('Hapus bukti transaksi ini? Tindakan ini tidak dapat dibatalkan.', { confirmLabel: 'Hapus' })) return;

    try {
        const res = await fetch(`/dashboard/transaksi/bukti/${buktiId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });

        if (res.redirected) {
            const errBox  = document.getElementById('editErrors');
            const errList = document.getElementById('editErrorList');
            errList.innerHTML = `<li>Anda tidak memiliki izin untuk melakukan aksi ini.</li>`;
            errBox.classList.remove('hidden');
            return;
        }

        const data = await res.json();
        if (data.success) {
            btn.closest('[data-bukti-id]').remove();
            const container = document.getElementById('existingBuktiList');
            if (container && container.children.length === 0) {
                container.innerHTML = '<p class="text-xs text-gray-400">Belum ada bukti transaksi.</p>';
            }
        } else {
            alert(data.message ?? 'Gagal menghapus bukti.');
        }
    } catch {
        alert('Gagal menghubungi server.');
    }
}

async function submitEdit() {
    const form    = document.getElementById('formEdit');
    const btn     = document.getElementById('btnEditSubmit');
    const spinner = document.getElementById('iconSpinnerEdit');
    const errBox  = document.getElementById('editErrors');
    const errList = document.getElementById('editErrorList');

    errBox.classList.add('hidden');
    errList.innerHTML = '';

    const { totalDebit, totalKredit } = hitungTotalJurnal('jurnalEditBody', 'jurnalEdit');
    if (totalDebit === 0 || totalDebit !== totalKredit) {
        errList.insertAdjacentHTML('beforeend', `<li>Total debit dan kredit harus sama dan tidak boleh kosong.</li>`);
        errBox.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    spinner.classList.remove('hidden');

    try {
        const fd  = new FormData(form);

        let idx = 0;
        document.querySelectorAll('#jurnalEditBody tr').forEach(tr => {
            fd.append(`jurnal[${idx}][akun_id]`, tr.querySelector('.jurnalAkun').value);
            fd.append(`jurnal[${idx}][tipe]`,    tr.querySelector('.jurnalTipe').value);
            fd.append(`jurnal[${idx}][nominal]`, tr.querySelector('.jurnalNominal').value);
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
            closeModal('modalEdit');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'error',
                message: 'Anda tidak memiliki izin untuk melakukan aksi ini.'
            }));
            window.location.reload();
            return;
        }
        const data = await res.json();
        if (data.success) {
            closeModal('modalEdit');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'success',
                message: 'Transaksi berhasil diperbarui.'
            }));
            window.location.reload();
        } else if (res.status === 422 && data.errors) {
            Object.values(data.errors).flat().forEach(msg => {
                errList.insertAdjacentHTML('beforeend', `<li>${msg}</li>`);
            });
            errBox.classList.remove('hidden');
        } else if (res.status === 403) {
            errList.insertAdjacentHTML('beforeend', `<li>Anda tidak memiliki hak akses untuk mengubah transaksi.</li>`);
            errBox.classList.remove('hidden');
            closeModal('modalEdit');
            sessionStorage.setItem('alert', JSON.stringify({
                type: 'error',
                message: 'Anda tidak memiliki izin untuk melakukan aksi ini.'
            }));
            window.location.reload();
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