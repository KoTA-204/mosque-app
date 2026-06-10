@props([
    'step',
    'total',
    'backRoute'   => null,
    'backAction'  => null,
    'nextAction'  => null,
    'nextLabel'   => 'Lanjut ke Detail',
    'showSubmit'  => false,
    'showDraft'   => true,
    'postingId'   => 'btnPosting',
])

@php
    // Resolusi backAction default
    if ($backAction === null && $backRoute === null) {
        if ($step <= 1) {
            $backRoute = 'javascript:history.back()';
        } else {
            $backAction = 'goToStep(' . ($step - 1) . ')';
        }
    }

    // Resolusi nextAction default
    if ($nextAction === null && ! $showSubmit) {
        $nextAction = 'goToStep(' . ($step + 1) . ')';
    }
@endphp

<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4 flex items-center justify-between">
    <span class="text-xs text-gray-400">Langkah {{ $step }} dari {{ $total }}</span>

    <div class="flex gap-3">
        {{-- Tombol Kembali --}}
        @if($backRoute)
        <a href="{{ $backRoute }}"
           class="inline-flex items-center gap-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            @if($step > 1)
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            @endif
            Kembali
        </a>
        @elseif($backAction)
        <button type="button" onclick="{{ $backAction }}"
                class="inline-flex items-center gap-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </button>
        @endif

        {{-- Tombol Lanjut (step bukan terakhir) --}}
        @if($nextAction && ! $showSubmit)
        <button type="button" onclick="{{ $nextAction }}"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
            {{ $nextLabel }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        @endif

        {{-- Tombol Submit (step terakhir) --}}
        @if($showSubmit)
        @if($showDraft)
        <button type="submit" name="submit_type" value="draft"
                class="border border-green-600 text-green-700 ...">
            Simpan sebagai Draft
        </button>
        @endif
        <button type="submit" name="submit_type" value="posting"
                id="{{ $postingId }}"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Simpan & Posting
        </button>
        @endif
    </div>
</div>