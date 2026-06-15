@props(['url', 'name'])

@php $ext = strtoupper(pathinfo($name, PATHINFO_EXTENSION)); @endphp

<a href="{{ $url }}" target="_blank"
   class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
    <svg class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
    </svg>
    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $name }}</span>
    <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ext === 'PDF' ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' }}">
        {{ $ext }}
    </span>
</a>
