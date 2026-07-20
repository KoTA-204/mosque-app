@props([
    'postLabel'  => 'Post Terpilih',
    'permission' => '',
])

<div id="bulkActionBar"
     class="hidden items-center justify-between gap-3 px-5 py-3
            bg-green-50 dark:bg-green-900/20
            border-b border-green-200 dark:border-green-800">

    {{-- Kiri: jumlah terpilih --}}
    <div class="flex items-center gap-2">
        <span id="bulkCountBadge"
              class="inline-flex items-center justify-center w-5 h-5 rounded-full
                     bg-green-600 text-white text-xs font-bold">0</span>
        <span class="text-sm font-medium text-green-700 dark:text-green-400">jurnal dipilih</span>
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

        @php $canBulkPost = !$permission || auth()->user()->hasPermission($permission); @endphp
        <button type="button" <?php echo $canBulkPost ? 'onclick="submitBulkPost()"' : 'disabled aria-disabled="true" title="Anda tidak memiliki izin untuk posting jurnal"'; ?>
                class="inline-flex items-center gap-2
                       text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors <?php echo $canBulkPost ? 'bg-green-600 hover:bg-green-700' : 'bg-green-600/40 opacity-60 cursor-not-allowed'; ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $postLabel }}
        </button>
    </div>
</div>