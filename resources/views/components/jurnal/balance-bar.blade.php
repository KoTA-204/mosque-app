@props(['prefix' => ''])

@php
    $idDebit   = $prefix . 'TotalDebit';
    $idKredit  = $prefix . 'TotalKredit';
    $idStatus  = $prefix . 'BalanceStatus';
@endphp

<div class="mt-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 p-4 flex items-center justify-between">
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</span>
    <div class="flex items-center gap-6">
        <div class="text-right">
            <p class="text-xs text-gray-400">Debit</p>
            <p id="{{ $idDebit }}" class="text-sm font-bold text-red-500">Rp 0</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-400">Kredit</p>
            <p id="{{ $idKredit }}" class="text-sm font-bold text-green-600">Rp 0</p>
        </div>
        <div id="{{ $idStatus }}" class="flex items-center gap-1.5 text-xs font-medium text-yellow-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Belum seimbang
        </div>
    </div>
</div>