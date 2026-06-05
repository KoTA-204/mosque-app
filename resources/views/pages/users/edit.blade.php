<div id="editModal" class="hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl w-full max-w-lg" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Edit User</h3>
        <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <form id="editForm" method="POST">
        @csrf
        @method('PUT')
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="edit_name"
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Username</label>
                <input type="text" id="edit_username" readonly
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 px-3 text-sm text-gray-400 dark:text-gray-500 outline-none cursor-not-allowed">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="edit_email"
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" id="edit_role_id"
                        class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" id="edit_status"
                        class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-sm text-gray-800 dark:text-gray-200 outline-none focus:border-green-400 transition-colors">
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex gap-3">
            <button type="button" onclick="closeAllModals()"
                class="flex-1 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Batal
            </button>
            <button type="submit"
                class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>