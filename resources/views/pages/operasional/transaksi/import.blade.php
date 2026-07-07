<div id="imporState" data-state="upload">
    <div id="imporStateUpload">
        <form id="formImpor" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Bank <span class="text-red-500">*</span>
                    </label>
                    <select name="bank" required
                        class="w-full h-10 px-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                        <option value="">Pilih bank</option>
                        <option value="BSI">BSI – Bank Syariah Indonesia</option>
                        <option value="BRI">BRI – Bank Rakyat Indonesia</option>
                    </select>
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

            <div class="mb-4">
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

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Unggah File</label>
                <div id="dropzoneImpor"
                    class="border border-dashed border-gray-300 rounded-xl py-10 text-center cursor-pointer hover:border-green-400 hover:bg-green-50/30 transition-colors"
                    onclick="document.getElementById('inputFileImpor').click()"
                    ondragover="event.preventDefault()"
                    ondrop="handleDropImpor(event)">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <p class="text-sm text-gray-500">
                        <span class="font-semibold text-green-700 underline">Pilih File Excel/CSV/PDF</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">atau tarik file Excel/CSV/PDF mutasi bank untuk diunggah disini</p>
                    <p id="namaFileImpor" class="text-xs text-green-700 font-medium mt-2 hidden"></p>
                </div>
                <input type="file" id="inputFileImpor" name="file"
                    accept=".xlsx,.xls,.csv,.pdf" class="hidden"
                    onchange="onFileImpor(this)">
            </div>

            <div id="imporErrorBox" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p id="imporErrorMsg" class="text-sm text-red-600"></p>
            </div>

            <button type="button" onclick="submitImpor()"
                id="btnUnggah"
                class="w-full h-10 bg-green-700 text-white text-sm font-medium rounded-xl hover:bg-green-800 transition-colors flex items-center justify-center gap-2">
                <svg id="spinnerUnggah" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Unggah
            </button>
        </form>
    </div>

    <div id="imporStateSukses" class="hidden">
        <div class="bg-gray-50 border border-gray-200 rounded-xl py-8 mb-5 flex flex-col items-center gap-2 text-center">
            <div class="w-14 h-14 rounded-full border-2 border-green-600 flex items-center justify-center mb-1">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="font-medium text-gray-900">File berhasil diproses</p>
            <p class="text-sm text-gray-500">Silakan lanjut ke halaman review untuk mengklasifikasikan akun.</p>

            <div class="grid grid-cols-3 gap-3 w-full mt-4 px-4">
                <div class="text-center border border-gray-200 rounded-xl py-3 bg-white">
                    <p id="statTotal" class="text-xl font-semibold text-gray-900">–</p>
                    <p class="text-xs text-gray-500 mt-0.5">Total baris</p>
                </div>
                <div class="text-center border border-amber-200 rounded-xl py-3 bg-amber-50">
                    <p id="statDuplikat" class="text-xl font-semibold text-amber-600">–</p>
                    <p class="text-xs text-gray-500 mt-0.5">Duplikat</p>
                </div>
                <div class="text-center border border-green-200 rounded-xl py-3 bg-green-50">
                    <p id="statBersih" class="text-xl font-semibold text-green-700">–</p>
                    <p class="text-xs text-gray-500 mt-0.5">Perlu review</p>
                </div>
            </div>
        </div>
        <a id="linkReview" href="#"
            class="block w-full h-10 bg-green-700 text-white text-sm font-medium rounded-xl hover:bg-green-800 transition-colors text-center leading-10">
            Review Transaksi →
        </a>
    </div>

    <div id="imporStateGagal" class="hidden">
        <div class="bg-gray-50 border border-gray-200 rounded-xl py-8 mb-5 flex flex-col items-center gap-2 text-center">
            <div class="w-14 h-14 rounded-full bg-red-500 flex items-center justify-center mb-1">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <p class="font-medium text-gray-900">Impor Transaksi Gagal</p>
            <p id="pesanGagal" class="text-sm text-gray-500 px-4"></p>
        </div>
        <button type="button" onclick="resetImpor()"
            class="w-full h-10 bg-green-700 text-white text-sm font-medium rounded-xl hover:bg-green-800 transition-colors">
            Coba Lagi
        </button>
    </div>

</div>

<script>
// Flag hak akses — dipakai untuk menampilkan pesan yang sesuai saat pengguna
// hanya memiliki akses view (bisa membuka modal, tapi tidak bisa mengunggah).
const CAN_IMPOR_TRANSAKSI = @json(auth()->user()->hasPermission('CREATE_TRANSAKSI'));

// Reset seluruh isian form impor + state tampilan ke kondisi awal.
// Dipanggil saat modal dibuka dan saat tombol "Coba Lagi" ditekan,
// agar data dari percobaan impor sebelumnya tidak tertinggal.
function resetImpor() {
    const form = document.getElementById('formImpor');
    if (form) form.reset();

    const nama = document.getElementById('namaFileImpor');
    if (nama) { nama.textContent = ''; nama.classList.add('hidden'); }

    const errBox = document.getElementById('imporErrorBox');
    if (errBox) errBox.classList.add('hidden');
    const errMsg = document.getElementById('imporErrorMsg');
    if (errMsg) errMsg.textContent = '';

    const fileInput = document.getElementById('inputFileImpor');
    if (fileInput) fileInput.value = '';

    const pesanGagal = document.getElementById('pesanGagal');
    if (pesanGagal) pesanGagal.textContent = '';

    imporSetState('upload');
}

function onFileImpor(input) {
    if (!input.files.length) return;
    const p = document.getElementById('namaFileImpor');
    p.textContent = input.files[0].name;
    p.classList.remove('hidden');
}

function handleDropImpor(e) {
    e.preventDefault();
    const dt = new DataTransfer();
    [...e.dataTransfer.files].forEach(f => dt.items.add(f));
    document.getElementById('inputFileImpor').files = dt.files;
    onFileImpor(document.getElementById('inputFileImpor'));
}

function imporSetState(state) {
    ['Upload', 'Sukses', 'Gagal'].forEach(s => {
        document.getElementById('imporState' + s)?.classList.add('hidden');
    });
    document.getElementById('imporState' + state.charAt(0).toUpperCase() + state.slice(1))
        ?.classList.remove('hidden');
}

async function submitImpor() {
    const form = document.getElementById('formImpor');
    const fd   = new FormData(form);

    if (!CAN_IMPOR_TRANSAKSI) {
        document.getElementById('imporErrorMsg').textContent = 'Anda tidak memiliki hak akses untuk mengimpor transaksi.';
        document.getElementById('imporErrorBox').classList.remove('hidden');
        return;
    }

    if (!fd.get('bank') || !fd.get('jenis_transaksi') || !fd.get('file')?.name) {
        document.getElementById('imporErrorMsg').textContent = 'Lengkapi semua field sebelum mengunggah.';
        document.getElementById('imporErrorBox').classList.remove('hidden');
        return;
    }

    document.getElementById('imporErrorBox').classList.add('hidden');
    document.getElementById('btnUnggah').disabled = true;
    document.getElementById('spinnerUnggah').classList.remove('hidden');

    try {
        const res  = await fetch('{{ route("dashboard.transaksi.import") }}', {
            method: 'POST',
            body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
        });
        if (res.status === 403) {
            document.getElementById('pesanGagal').textContent =
                'Anda tidak memiliki hak akses untuk mengimpor transaksi.';
            imporSetState('gagal');
            return;
        }

        const data = await res.json();

        if (data.success && data.type === 'parse_success') {
            document.getElementById('statTotal').textContent    = data.stats.total;
            document.getElementById('statDuplikat').textContent = data.stats.duplikat;
            document.getElementById('statBersih').textContent   = data.stats.bersih;
            document.getElementById('linkReview').href          = data.redirect;
            imporSetState('sukses');
        } else {
            document.getElementById('pesanGagal').textContent =
                data.message ?? 'Format file tidak dikenali.';
            imporSetState('gagal');
        }
    } catch {
        document.getElementById('pesanGagal').textContent = 'Gagal menghubungi server.';
        imporSetState('gagal');
    } finally {
        document.getElementById('btnUnggah').disabled = false;
        document.getElementById('spinnerUnggah').classList.add('hidden');
    }
}
</script>