@props([
    'id' => 'confirmModal',
    'title' => 'Konfirmasi',
    'message' => 'Apakah anda yakin?',
    'confirmLabel' => 'Hapus',
    'confirmClass' => 'bg-red-600 hover:bg-red-700',
    'onConfirm' => null, // nama function JS (contoh: 'confirmBulkPost()'). Kalau null, fallback ke form DELETE seperti semula.
])

<div id="{{ $id }}"
    style="display: none;"
    class="fixed inset-0 z-[9999] items-center justify-center bg-black/50 px-4">

    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl">

        {{-- Header --}}
        <div class="flex items-start justify-between">
            <div>
                <h3 id="{{ $id }}Title" class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $title }}
                </h3>

                <p id="{{ $id }}Message" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ $message }}
                </p>
            </div>

            <button type="button"
                    onclick="closeModal('{{ $id }}')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                ✕
            </button>
        </div>

        {{-- Actions --}}
        <div class="mt-6 flex items-center justify-end gap-3">

            <button type="button"
                    onclick="closeModal('{{ $id }}')"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Batal
            </button>

            @if($onConfirm)
                {{-- Mode callback JS — dipakai untuk aksi non-form, misal bulk action via fetch/AJAX --}}
                <button type="button"
                        onclick="closeModal('{{ $id }}'); {{ $onConfirm }}"
                        class="rounded-lg {{ $confirmClass }} px-4 py-2 text-sm font-medium text-white">
                    {{ $confirmLabel }}
                </button>
            @else
                {{-- Mode default — form DELETE, action diset dinamis lewat JS sebelum modal dibuka --}}
                <form id="{{ $id }}Form" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="rounded-lg {{ $confirmClass }} px-4 py-2 text-sm font-medium text-white">
                        {{ $confirmLabel }}
                    </button>
                </form>
            @endif

        </div>
    </div>
</div>