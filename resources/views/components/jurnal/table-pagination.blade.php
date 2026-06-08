@props([
    'paginator',
    'queryParams' => [],
])

@if($paginator->hasPages())
@php $qs = http_build_query($queryParams); @endphp

<div class="flex items-center justify-between px-5 py-4
            border-t border-gray-100 dark:border-gray-800 flex-wrap gap-3">

    <div class="flex items-center gap-1">

        {{-- Previous --}}
        @if($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600
                         border border-gray-200 dark:border-gray-700 rounded-lg">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}&{{ $qs }}"
               class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400
                      border border-gray-200 dark:border-gray-700 rounded-lg
                      hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Previous</a>
        @endif

        {{-- Page numbers --}}
        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            <a href="{{ $url }}&{{ $qs }}"
               class="w-8 h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                      {{ $page === $paginator->currentPage()
                          ? 'bg-green-600 text-white font-medium'
                          : 'text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                {{ $page }}
            </a>
        @endforeach

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}&{{ $qs }}"
               class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400
                      border border-gray-200 dark:border-gray-700 rounded-lg
                      hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Next</a>
        @else
            <span class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600
                         border border-gray-200 dark:border-gray-700 rounded-lg">Next</span>
        @endif
    </div>

    {{-- Info: "Showing X to Y of Z entries" --}}
    <span class="text-xs text-gray-400 dark:text-gray-600">
        @if($paginator->total() > 0)
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }}
            of {{ $paginator->total() }} entries
        @else
            No entries
        @endif
    </span>
</div>
@endif