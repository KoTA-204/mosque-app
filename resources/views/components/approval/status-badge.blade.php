@props(['status'])
@php
    $map = [
        'PENDING'  => ['Menunggu',  'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400'],
        'APPROVED' => ['Disetujui', 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'],
        'REJECTED' => ['Ditolak',   'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400'],
        'REVISION' => ['Revisi',    'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'],
    ];
    [$label, $color] = $map[$status] ?? ['Tidak Diketahui', 'bg-gray-100 text-gray-500'];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
    {{ $label }}
</span>