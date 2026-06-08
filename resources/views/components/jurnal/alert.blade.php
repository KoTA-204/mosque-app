{{--
    Component: <x-alert />

    Props:
      - type    : 'success' | 'error'   (default: 'success')
      - message : string
--}}
@props(['type' => 'success', 'message'])

@php
$config = [
    'success' => [
        'wrapper' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400',
        'icon'    => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
    ],
    'error' => [
        'wrapper' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400',
        'icon'    => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
    ],
];
$c = $config[$type] ?? $config['success'];
@endphp

<div class="flex items-center gap-3 {{ $c['wrapper'] }} border rounded-xl px-4 py-3 text-sm">
    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        {!! $c['icon'] !!}
    </svg>
    {{ $message }}
</div>