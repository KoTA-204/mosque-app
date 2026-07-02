<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<x-modal id="createKegiatanModal" title="Tambah Kegiatan">

    <form id="createKegiatanForm">
        @csrf

        <div class="px-6 py-5 space-y-4">

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Nama Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kegiatan" placeholder="Masukkan nama kegiatan"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-nama_kegiatan" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="jenis_kegiatan"
                        class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        <option value="">Pilih Jenis</option>
                        <option value="QURBAN">Qurban</option>
                        <option value="ZAKAT">Zakat</option>
                        <option value="KAJIAN">Kajian</option>
                        <option value="SOSIAL">Sosial</option>
                        <option value="LAINNYA">Lainnya</option>
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <p id="err-jenis_kegiatan" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="text" id="create-fp-daterange" placeholder="Pilih rentang tanggal" readonly
                        class="w-full border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors cursor-pointer">
                </div>
                <input type="hidden" name="tanggal_mulai"   id="create-tanggal_mulai">
                <input type="hidden" name="tanggal_selesai" id="create-tanggal_selesai">
                <p id="err-tanggal_mulai" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Anggaran (Rp) <span class="text-red-500">*</span></label>
                <input type="hidden" name="anggaran" id="create-anggaran-hidden">
                <input type="text" id="create-anggaran-display"
                    placeholder="0" inputmode="numeric"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-anggaran" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Panitia <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="panitia_id"
                        class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        <option value="">Pilih Panitia</option>
                        @foreach($panitias as $panitia)
                        <option value="{{ $panitia->id }}">{{ $panitia->name }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <p id="err-panitia_id" class="text-xs text-red-500 mt-1"></p>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <button type="button" onclick="closeModal('createKegiatanModal')"
                class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <button type="button"
                onclick="submitKegiatanForm('createKegiatanForm', 'POST', '{{ route('dashboard.kegiatan.store') }}')"
                class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors">
                Simpan
            </button>
        </div>
    </form>

</x-modal>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.js"></script>
<script>
(function () {
    flatpickr('#create-fp-daterange', {
        mode:       'range',
        dateFormat: 'Y-m-d',
        locale:     'id',
        onChange(dates) {
            document.getElementById('create-tanggal_mulai').value   = dates[0] ? flatpickr.formatDate(dates[0], 'Y-m-d') : '';
            document.getElementById('create-tanggal_selesai').value = dates[1] ? flatpickr.formatDate(dates[1], 'Y-m-d') : '';
        }
    });

    // Format ribuan anggaran
    const displayEl = document.getElementById('create-anggaran-display');
    const hiddenEl  = document.getElementById('create-anggaran-hidden');

    displayEl.addEventListener('input', function () {
        const raw = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
        const num = parseInt(raw, 10) || 0;
        const pos = this.selectionStart;
        const prevLen = this.value.length;
        this.value    = num > 0 ? num.toLocaleString('id-ID') : '';
        hiddenEl.value = num > 0 ? num : '';
        const diff = this.value.length - prevLen;
        try { this.setSelectionRange(pos + diff, pos + diff); } catch(e) {}
    });
})();

</script>