@props([
    'id',
    'title' => ''
])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    style="display: none;"
>

    {{-- Kotak modal: flex-col agar header diam & hanya body yang scroll (satu scroll) --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">

        {{-- Header (tetap diam) --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800 shrink-0">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $title }}
            </h2>

            <button
                type="button"
                onclick="closeModal('{{ $id }}')"
                class="text-gray-400 hover:text-gray-600"
            >
                ✕
            </button>
        </div>

        {{-- Content: satu-satunya area yang discroll --}}
        <div class="flex-1 min-h-0 overflow-y-auto p-6">
            {!! $slot !!}
        </div>

    </div>
</div>
