@props([
    'transaksi',
    'confirmText'       => 'Yakin menyetujui transaksi ini?',
    'revisiTitle'       => 'Catatan revisi',
    'revisiPlaceholder' => 'Tuliskan catatan yang perlu diperbaiki...',
])

@if($transaksi->status_approval === 'PENDING')
    {{-- Action Bar --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl px-6 py-4 flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="font-medium text-gray-900 dark:text-white">Transaksi ini menunggu persetujuan kamu</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Periksa detail di bawah sebelum approve, revisi, atau reject</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="togglePanel('panel-revision')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                Minta Revisi
            </button>
            <button type="button" onclick="togglePanel('panel-reject')"
                    class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                Reject
            </button>
            <form action="{{ route('dashboard.approval.approve', $transaksi) }}" method="POST"
                  data-confirm="{{ $confirmText }}" data-confirm-title="Setujui Pengajuan" data-confirm-label="Setujui" data-confirm-class="bg-green-600 hover:bg-green-700">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-sm font-medium px-4 py-2 rounded-lg border border-green-600 text-green-700 dark:text-green-400 dark:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                    Approve
                </button>
            </form>
        </div>
    </div>

    {{-- Inline Panel: Reject --}}
    <div id="panel-reject"
         class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5">
        <p class="mb-3 font-medium text-red-700 dark:text-red-400">Alasan penolakan</p>
        <form action="{{ route('dashboard.approval.reject', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3"
                      placeholder="Tuliskan alasan reject transaksi ini..."
                      class="mb-3 w-full rounded-xl border border-red-200 dark:border-red-800 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:border-red-400 focus:outline-none placeholder-gray-400"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="togglePanel('panel-reject')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium border border-red-500 text-red-600 dark:text-red-400 dark:border-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    Konfirmasi Reject
                </button>
            </div>
        </form>
    </div>

    {{-- Inline Panel: Revisi --}}
    <div id="panel-revision"
         class="hidden bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
        <p class="mb-3 font-medium text-blue-700 dark:text-blue-400">{{ $revisiTitle }}</p>
        <form action="{{ route('dashboard.approval.revision', $transaksi) }}" method="POST">
            @csrf
            <textarea name="catatan" rows="3" required
                      placeholder="{{ $revisiPlaceholder }}"
                      class="mb-3 w-full rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 focus:border-blue-400 focus:outline-none placeholder-gray-400"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="togglePanel('panel-revision')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium border border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-500 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    Kirim Permintaan Revisi
                </button>
            </div>
        </form>
    </div>

    {{-- Script di-push sekali walau komponen dipakai berkali-kali --}}
    @once
        @push('scripts')
        <script>
            function togglePanel(id) {
                ['panel-reject', 'panel-revision'].forEach(p => {
                    if (p === id) {
                        document.getElementById(p).classList.toggle('hidden');
                    } else {
                        document.getElementById(p).classList.add('hidden');
                    }
                });
                const el = document.getElementById(id);
                if (!el.classList.contains('hidden')) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        </script>
        @endpush
    @endonce
@endif
