@php
    // Normalkan ke 1-based array
    $steps = array_values($steps);
    $total = count($steps);
@endphp

<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-4">
    <div class="flex items-center">
        @foreach($steps as $i => $label)
        @php $n = $i + 1; @endphp
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-colors
                {{ $n === 1 ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}"
                 id="step-circle-{{ $n }}">{{ $n }}</div>
            <div>
                <p class="text-xs text-gray-400">Langkah {{ $n }}</p>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</p>
            </div>
        </div>
        @if($n < $total)
        <div class="mx-4 flex-1 border-t-2 border-gray-100 dark:border-gray-800 transition-colors"
             id="step-line-{{ $n }}"></div>
        @endif
        @endforeach
    </div>
</div>