<div id="deleteModal" class="hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl w-full max-w-sm" onclick="event.stopPropagation()">
    <div class="px-6 py-5 text-center space-y-3">
        <div class="mx-auto w-12 h-12 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Hapus Kegiatan</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Yakin ingin menghapus kegiatan <span id="delete_nama" class="font-medium text-gray-800 dark:text-gray-200"></span>? Tindakan ini tidak dapat dibatalkan.
        </p>
    </div>
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex gap-3">
        <button onclick="closeAllModals()"
            class="flex-1 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            Batal
        </button>
        <form id="deleteForm" method="POST" class="flex-1">
            @csrf @method('DELETE')
            <button type="submit"
                class="w-full px-4 py-2.5 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">
                Hapus
            </button>
        </form>
    </div>
</div>