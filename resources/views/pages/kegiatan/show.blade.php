<div id="showModal" class="hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Detail Kegiatan</h3>
        <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="px-6 py-5 space-y-4">
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nama Kegiatan</p>
            <p id="show_nama" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jenis</p>
                <span id="show_jenis" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400"></span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Status</p>
                <span id="show_status_badge"></span>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal Mulai</p>
                <p id="show_tgl_mulai" class="text-sm text-gray-700 dark:text-gray-300"></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal Selesai</p>
                <p id="show_tgl_selesai" class="text-sm text-gray-700 dark:text-gray-300"></p>
            </div>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Anggaran</p>
            <p id="show_anggaran" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Penanggung Jawab</p>
            <p id="show_panitia_nama" class="text-sm text-gray-700 dark:text-gray-300"></p>
            <p id="show_panitia_email" class="text-xs text-gray-400 mt-0.5"></p>
        </div>
    </div>
    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
        <button onclick="closeAllModals()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            Tutup
        </button>
    </div>
</div>