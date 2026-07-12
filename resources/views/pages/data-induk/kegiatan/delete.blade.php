@php
    $destroyUrl  = route('dashboard.kegiatan.destroy', $kegiatan);
    $confirmJs   = "submitDeleteKegiatan('" . $destroyUrl . "')";
    $namaKegiatan = $kegiatan->nama_kegiatan;
@endphp

@if(!$hasTransaksi)
<x-confirm-modal
    id="deleteKegiatanModal"
    title="Hapus Kegiatan"
    :message="'Yakin menghapus kegiatan ' . $namaKegiatan . '? Tindakan ini tidak dapat dibatalkan.'"
    confirmLabel="Hapus"
    confirmClass="bg-red-600 hover:bg-red-700"
    :onConfirm="$confirmJs"
/>
@else
<x-confirm-modal
    id="deleteKegiatanModal"
    title="Tidak Dapat Dihapus"
    :message="'Kegiatan ini memiliki ' . $transaksiCount . ' transaksi dan tidak dapat dihapus.'"
    confirmLabel="Mengerti"
    confirmClass="bg-gray-500 hover:bg-gray-600"
    onConfirm="closeModal('deleteKegiatanModal')"
/>
@endif
