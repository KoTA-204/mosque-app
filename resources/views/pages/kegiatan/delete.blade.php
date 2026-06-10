<x-confirm-modal
    id="deleteKegiatanModal"
    title="{{ $hasTransaksi ? 'Tidak Dapat Dihapus' : 'Hapus Kegiatan' }}"
    message="{{ $hasTransaksi
        ? 'Kegiatan ' . $kegiatan->nama_kegiatan . ' memiliki ' . $transaksiCount . ' transaksi sehingga tidak dapat dihapus. Status kegiatan akan otomatis berubah menjadi Ditutup setelah semua transaksi disetujui.'
        : 'Yakin ingin menghapus kegiatan ' . $kegiatan->nama_kegiatan . '? Tindakan ini tidak dapat dibatalkan.' }}"
>
    <x-slot name="actions">
        <button type="button" onclick="closeModal('deleteKegiatanModal')"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
            {{ $hasTransaksi ? 'Tutup' : 'Batal' }}
        </button>
        @if(!$hasTransaksi)
        <button type="button"
            onclick="submitDeleteKegiatan('{{ route('dashboard.kegiatan.destroy', $kegiatan->id) }}')"
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
            Hapus
        </button>
        @endif
    </x-slot>
</x-confirm-modal>