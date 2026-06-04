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
            <div class="grid grid-cols-2 gap-4">
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
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="edit_status"
                        class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                        <option value="DRAFT">Draft</option>
                        <option value="BERJALAN">Berjalan</option>
                        <option value="SELESAI">Selesai</option>
                        <option value="DIBATALKAN">Dibatalkan</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="edit_tgl_mulai"
                        class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="edit_tgl_selesai"
                        class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                </div>
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