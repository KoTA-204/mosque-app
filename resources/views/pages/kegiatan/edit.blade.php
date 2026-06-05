<div id="editModal" class="hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl w-full max-w-lg" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Edit Kegiatan</h3>
        <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <form id="editForm" method="POST">
        @csrf @method('PUT')
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Nama Kegiatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kegiatan" id="edit_nama"
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Jenis Kegiatan <span class="text-red-500">*</span></label>
                <select name="jenis_kegiatan" id="edit_jenis"
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                    <option value="QURBAN">Qurban</option>
                    <option value="ZAKAT">Zakat</option>
                    <option value="KAJIAN">Kajian</option>
                    <option value="SOSIAL">Sosial</option>
                    <option value="LAINNYA">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Tanggal Kegiatan <span class="text-red-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="text" id="edit_daterange" placeholder="Pilih rentang tanggal"
                        class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 pl-9 pr-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors placeholder-gray-400">
                </div>
                <input type="hidden" name="tanggal_mulai"   id="edit_tanggal_mulai">
                <input type="hidden" name="tanggal_selesai" id="edit_tanggal_selesai">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Anggaran (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="anggaran" id="edit_anggaran" min="0"
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Panitia <span class="text-red-500">*</span></label>
                <select name="panitia_id" id="edit_panitia"
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex gap-3">
            <button type="button" onclick="closeAllModals()"
                class="flex-1 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <button type="submit"
                class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                Simpan
            </button>
        </div>
    </form>
</div>