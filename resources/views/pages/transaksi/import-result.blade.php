<div id="hasilState" data-state="sukses">
    <div id="hasilStateSukses">
        <div class="bg-gray-50 border border-gray-200 rounded-xl py-8 mb-5 flex flex-col items-center gap-2 text-center px-4">
            {{-- Ikon centang --}}
            <div class="w-16 h-16 rounded-full border-2 border-green-600 flex items-center justify-center mb-2">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <p class="text-lg font-semibold text-gray-900">
                <span id="hasilTersimpan">–</span> transaksi disimpan
            </p>
            <p id="hasilPeriode" class="text-sm text-gray-500"></p>

            {{-- Ringkasan angka --}}
            <div class="grid grid-cols-3 gap-3 w-full mt-4">
                <div class="text-center border border-gray-200 rounded-xl py-4 bg-white">
                    <p id="hasilTersimpanBox" class="text-2xl font-semibold text-green-700">–</p>
                    <p class="text-xs text-gray-500 mt-1">Tersimpan</p>
                </div>
                <div class="text-center border border-gray-200 rounded-xl py-4 bg-white">
                    <p id="hasilDuplikat" class="text-2xl font-semibold text-amber-500">–</p>
                    <p class="text-xs text-gray-500 mt-1">Duplikat</p>
                </div>
                <div class="text-center border border-gray-200 rounded-xl py-4 bg-white">
                    <p id="hasilTotal" class="text-2xl font-semibold text-gray-700">–</p>
                    <p class="text-xs text-gray-500 mt-1">Total baris file</p>
                </div>
            </div>
        </div>

        <a href="{{ route('dashboard.transaksi.index') }}"
            class="block w-full h-10 bg-green-700 text-white text-sm font-medium rounded-xl hover:bg-green-800 transition-colors text-center leading-10">
            Lihat daftar transaksi
        </a>
    </div>

    <div id="hasilStateGagal" class="hidden">
        <div class="bg-gray-50 border border-gray-200 rounded-xl py-8 mb-5 flex flex-col items-center gap-2 text-center px-4">
            <div class="w-16 h-16 rounded-full bg-red-500 flex items-center justify-center mb-2">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <p class="text-lg font-semibold text-gray-900">Impor Transaksi Gagal</p>
            <p id="hasilPesanGagal" class="text-sm text-gray-500 px-4"></p>
        </div>

        <button type="button"
            onclick="
                document.getElementById('hasilStateGagal').classList.add('hidden');
                document.getElementById('hasilStateSukses').classList.remove('hidden');
                closeModal('modalHasilImport');
            "
            class="w-full h-10 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
            Tutup
        </button>
    </div>

</div>

<script>
function tampilkanHasilImport(data) {
    document.getElementById('hasilTersimpan').textContent    = data.tersimpan ?? '–';
    document.getElementById('hasilTersimpanBox').textContent = data.tersimpan ?? '–';
    document.getElementById('hasilDuplikat').textContent     = data.duplikat  ?? '–';
    document.getElementById('hasilTotal').textContent        = data.total     ?? '–';
    document.getElementById('hasilPeriode').textContent      = data.periode
        ? `Mutasi ${data.periode}, diimpor oleh ${data.oleh ?? 'Anda'}`
        : '';

    const sukses = document.getElementById('hasilStateSukses');
    const gagal  = document.getElementById('hasilStateGagal');
    if (data.success) {
        sukses.classList.remove('hidden');
        gagal.classList.add('hidden');
    } else {
        sukses.classList.add('hidden');
        gagal.classList.remove('hidden');
        document.getElementById('hasilPesanGagal').textContent = data.message ?? 'Terjadi kesalahan.';
    }

    openModal('modalHasilImport');
}
</script>