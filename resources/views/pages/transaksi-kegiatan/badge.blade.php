{{-- Partial: _badge_status.blade.php --}}
{{-- Usage: @include('dashboard.kegiatan._badge_status', ['status' => $item->status]) --}}

@php
    $config = match($status) {
        'DRAFT'      => ['label' => 'Draft',      'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'],
        'BERJALAN'   => ['label' => 'Berjalan',   'class' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'],
        'SELESAI'    => ['label' => 'Selesai',    'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
        'DIBATALKAN' => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300'],
        default      => ['label' => $status,      'class' => 'bg-gray-100 text-gray-600'],
    };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $config['class'] }}">
    {{ $config['label'] }}
</span>