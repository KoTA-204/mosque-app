<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<x-modal id="editKegiatanModal" title="Edit Kegiatan">

    <form id="editKegiatanForm">
        @csrf

        <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-4">

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Nama Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kegiatan" value="{{ $kegiatan->nama_kegiatan }}"
                    placeholder="Masukkan nama kegiatan"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-nama_kegiatan" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="jenis_kegiatan"
                        class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        @foreach(['QURBAN','ZAKAT','KAJIAN','SOSIAL','LAINNYA'] as $j)
                        <option value="{{ $j }}" {{ $kegiatan->jenis_kegiatan === $j ? 'selected' : '' }}>
                            {{ ucfirst(strtolower($j)) }}
                        </option>
                        @endforeach
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
                    <input type="text" id="edit-fp-daterange" placeholder="Pilih rentang tanggal" readonly
                        class="w-full border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors cursor-pointer">
                </div>
                <input type="hidden" name="tanggal_mulai"   id="edit-tanggal_mulai"   value="{{ $kegiatan->tanggal_mulai?->format('Y-m-d') }}">
                <input type="hidden" name="tanggal_selesai" id="edit-tanggal_selesai" value="{{ $kegiatan->tanggal_selesai?->format('Y-m-d') }}">
                <p id="err-tanggal_mulai" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Anggaran (Rp) <span class="text-red-500">*</span></label>
                <input type="hidden" name="anggaran" id="edit-anggaran-hidden" value="{{ $kegiatan->anggaran }}">
                <input type="text" id="edit-anggaran-display"
                    value="{{ number_format($kegiatan->anggaran, 0, ',', '.') }}"
                    inputmode="numeric"
                    class="w-full border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 outline-none focus:border-green-400 transition-colors">
                <p id="err-anggaran" class="text-xs text-red-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">Panitia <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="panitia_id"
                        class="w-full appearance-none border border-gray-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:border-green-400 transition-colors">
                        @foreach($panitias as $panitia)
                        <option value="{{ $panitia->id }}" {{ $kegiatan->panitia_id == $panitia->id ? 'selected' : '' }}>
                            {{ $panitia->name }}
                        </option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <p id="err-panitia_id" class="text-xs text-red-500 mt-1"></p>
            </div>

            {{-- Status info — readonly, tidak bisa diedit --}}
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-3">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Status kegiatan dikelola otomatis berdasarkan approval transaksi.
                    Saat ini:
                    @if($kegiatan->status === 'AKTIF')
                        <span class="font-medium text-green-600">Aktif</span>
                    @else
                        <span class="font-medium text-gray-500">Ditutup</span>
                    @endif
                </p>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <button type="button" onclick="closeModal('editKegiatanModal')"
                class="px-5 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <button type="button"
                onclick="submitKegiatanForm('editKegiatanForm', 'PUT', '{{ route('dashboard.kegiatan.update', $kegiatan->id) }}')"
                class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>

</x-modal>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.js"></script>
<script>
(function () {
    const mulai   = document.getElementById('edit-tanggal_mulai').value;
    const selesai = document.getElementById('edit-tanggal_selesai').value;

    flatpickr('#edit-fp-daterange', {
        mode:        'range',
        dateFormat:  'Y-m-d',
        locale:      'id',
        defaultDate: selesai ? [mulai, selesai] : (mulai ? [mulai] : []),
        onChange(dates) {
            document.getElementById('edit-tanggal_mulai').value   = dates[0] ? flatpickr.formatDate(dates[0], 'Y-m-d') : '';
            document.getElementById('edit-tanggal_selesai').value = dates[1] ? flatpickr.formatDate(dates[1], 'Y-m-d') : '';
        }
    });

    const displayEl = document.getElementById('edit-anggaran-display');
    const hiddenEl  = document.getElementById('edit-anggaran-hidden');
    displayEl.addEventListener('input', function () {
        const raw = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
        const num = parseInt(raw, 10) || 0;
        const prevLen = this.value.length;
        const pos     = this.selectionStart;
        this.value    = num > 0 ? num.toLocaleString('id-ID') : '';
        hiddenEl.value = num > 0 ? num : '';
        const diff = this.value.length - prevLen;
        try { this.setSelectionRange(pos + diff, pos + diff); } catch(e) {}
    });
})();
</script>