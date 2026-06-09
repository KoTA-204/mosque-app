<x-confirm-modal
    id="deleteKegiatanModal"
    title="Hapus Kegiatan"
    message="{{ $hasTransaksi
        ? 'Kegiatan ini memiliki ' . $transaksiCount . ' transaksi dan tidak dapat dihapus. Ubah statusnya menjadi Ditutup untuk menonaktifkannya.'
        : 'Yakin ingin menghapus kegiatan ' . $kegiatan->nama_kegiatan . '? Tindakan ini tidak dapat dibatalkan.' }}"
>
    <x-slot name="actions">
        @if($hasTransaksi)
            {{-- Tidak bisa hapus — hanya bisa tutup kegiatan --}}
            <button type="button" onclick="closeModal('deleteKegiatanModal')"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Batal
            </button>
            <button type="button"
                onclick="tutupKegiatan({{ $kegiatan->id }})"
                class="rounded-lg bg-yellow-500 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-600">
                Tutup Kegiatan
            </button>
        @else
            {{-- Bisa dihapus --}}
            <button type="button" onclick="closeModal('deleteKegiatanModal')"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Batal
            </button>
            <button type="button"
                onclick="submitDeleteKegiatan('{{ route('dashboard.kegiatan.destroy', $kegiatan->id) }}')"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Hapus
            </button>
        @endif
    </x-slot>
</x-confirm-modal>

<script>
function tutupKegiatan(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch(`/dashboard/kegiatan/${id}/tutup`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':     csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type':     'application/x-www-form-urlencoded',
        },
        body: '_method=PATCH',
    })
    .then(r => r.json())
    .then(res => {
        closeModal('deleteKegiatanModal');
        showToast(res.message, res.success ? 'success' : 'error');
        if (res.success) applyFilters();
    })
    .catch(() => showToast('Terjadi kesalahan.', 'error'));
}
</script>