@props([
    'bodyId'        => 'reviewBody',
    'totalDebitId'  => 'review_total_debit',
    'totalKreditId' => 'review_total_kredit',
])

<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-gray-100 dark:border-gray-800">
            <th class="pb-2 text-left text-xs font-medium text-gray-400">Akun</th>
            <th class="pb-2 text-right text-xs font-medium text-gray-400">Debit</th>
            <th class="pb-2 text-right text-xs font-medium text-gray-400">Kredit</th>
        </tr>
    </thead>
    <tbody id="{{ $bodyId }}"></tbody>
</table>

<div class="mt-3 grid grid-cols-2 gap-3">
    <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3 text-center">
        <p class="text-xs text-gray-400">Total Debit</p>
        <p id="{{ $totalDebitId }}" class="text-base font-bold text-red-500">Rp 0</p>
    </div>
    <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3 text-center">
        <p class="text-xs text-gray-400">Total Kredit</p>
        <p id="{{ $totalKreditId }}" class="text-base font-bold text-green-600">Rp 0</p>
    </div>
</div>