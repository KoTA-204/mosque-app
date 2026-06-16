@props([
    'id',
    'title' => ''
])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    style="display: none;"
>

    <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
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

        {{-- Content --}}
        <div class="p-6">
            {!! $slot !!}
        </div>

    </div>
</div>