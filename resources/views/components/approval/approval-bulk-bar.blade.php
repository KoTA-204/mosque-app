<div id="bulk-action-bar"
     class="hidden items-center justify-between gap-3 px-5 py-3
            bg-green-50 dark:bg-green-900/20
            border-b border-green-200 dark:border-green-800">

    {{-- Kiri: jumlah terpilih --}}
    <div class="flex items-center gap-2">
        <span id="selected-count"
              class="inline-flex items-center justify-center w-5 h-5 rounded-full
                     bg-green-600 text-white text-xs font-bold">0</span>
        <span class="text-sm font-medium text-green-700 dark:text-green-400">transaksi dipilih</span>
    </div>

    {{-- Kanan: aksi --}}
    <div class="flex items-center gap-2">
        <button type="button" onclick="clearSelection()"
                class="text-sm text-gray-500 hover:text-gray-700
                       dark:text-gray-400 dark:hover:text-gray-200
                       px-3 py-1.5 rounded-lg
                       hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            Batal pilih
        </button>

        <button type="button" onclick="openBulkApproveModal()"
                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700
                       text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Approve Semua
        </button>

        <button type="button" onclick="openBulkRevisiModal()"
                class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-1.5 rounded-lg
                       border border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-500
                       hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Revisi Semua
        </button>

        <button type="button" onclick="openBulkRejectModal()"
                class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-1.5 rounded-lg
                       border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500
                       hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reject Semua
        </button>
    </div>
</div>