<?php

namespace App\Services\Aset;

use App\Models\Aset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AsetService
{
    // buat aset baru
    public function create(array $data, ?UploadedFile $dokumen): Aset
    {
        $dokumenPath = $this->simpanDokumen($dokumen);

        return Aset::create([
            'kode_aset'                => Aset::generateKode($data['tanggal_perolehan']),
            'nama_aset'                => $data['nama_aset'],
            'sumber_perolehan'         => $data['sumber_perolehan'],
            'tanggal_perolehan'        => $data['tanggal_perolehan'],
            'nilai_tercatat'           => $data['nilai_tercatat'],
            'kondisi_aset'             => $data['kondisi_aset'],
            'lokasi_aset'              => $data['lokasi_aset'],
            'nama_pemberi'             => $data['nama_pemberi'] ?? null,
            'jumlah_unit'              => $data['jumlah_unit'] ?? 1,
            'dokumen_pendukung'        => $dokumenPath,
            'tanggal_mulai_penyusutan' => $data['tanggal_mulai_penyusutan'] ?? null,
            'umur_manfaat'             => $data['umur_manfaat'] ?? null,
            'keterangan'               => $data['keterangan'] ?? null,
            'status_aset'              => 'AKTIF',
            'nilai_buku'               => $data['nilai_tercatat'],
            'akumulasi_penyusutan'     => 0,
        ]);
    }

    public function update(Aset $aset, array $data, ?UploadedFile $dokumen, bool $disusutkan): Aset
    {
        // ganti dokumen kalau ada file baru
        $dokumenPath = $aset->dokumen_pendukung;
        if ($dokumen) {
            if ($dokumenPath) {
                Storage::disk('public')->delete($dokumenPath);
            }
            $dokumenPath = $this->simpanDokumen($dokumen);
        }

        // 1) isi dulu semua kolom KECUALI nilai_buku & akumulasi_penyusutan
        $aset->fill([
            'nama_aset'                => $data['nama_aset'],
            'sumber_perolehan'         => $data['sumber_perolehan'],
            'tanggal_perolehan'        => $data['tanggal_perolehan'],
            'nilai_tercatat'           => $data['nilai_tercatat'],
            'kondisi_aset'             => $data['kondisi_aset'],
            'lokasi_aset'              => $data['lokasi_aset'],
            'nama_pemberi'             => $data['nama_pemberi'] ?? null,
            'jumlah_unit'              => $data['jumlah_unit'] ?? 1,
            'dokumen_pendukung'        => $dokumenPath,
            'tanggal_mulai_penyusutan' => $disusutkan ? ($data['tanggal_mulai_penyusutan'] ?? null) : null,
            'umur_manfaat'             => $disusutkan ? ($data['umur_manfaat'] ?? null) : null,
            'keterangan'               => $data['keterangan'] ?? null,
        ]);

        // 2) accessor sekarang sudah memakai parameter BARU di atas
        $aset->nilai_buku           = $disusutkan ? $aset->nilai_buku_real_time : (float) $data['nilai_tercatat'];
        $aset->akumulasi_penyusutan = $disusutkan ? $aset->akumulasi_real_time : 0;

        // 3) simpan semua sekaligus
        $aset->save();

        return $aset;
    }

    // aktif / nonaktifkan aset
    public function toggleStatus(Aset $aset): string
    {
        $newStatus  = $aset->status_aset === 'AKTIF' ? 'TIDAK AKTIF' : 'AKTIF';
        $updateData = ['status_aset' => $newStatus];

        // bekukan nilai penyusutan saat dinonaktifkan
        if ($newStatus === 'TIDAK AKTIF' && $aset->umur_manfaat) {
            $updateData['akumulasi_penyusutan'] = $aset->akumulasi_real_time;
            $updateData['nilai_buku']           = $aset->nilai_buku_real_time;
        }

        // reset ke hitungan real-time saat diaktifkan
        if ($newStatus === 'AKTIF' && $aset->umur_manfaat) {
            $updateData['akumulasi_penyusutan'] = 0;
            $updateData['nilai_buku']           = $aset->nilai_buku_real_time;
        }

        $aset->update($updateData);

        return $newStatus;
    }

    // hapus aset (hanya jika memenuhi syarat)
    public function delete(Aset $aset): void
    {
        // Aset tidak menyusut → tidak bisa hapus
        if (is_null($aset->umur_manfaat)) {
            throw new \InvalidArgumentException(
                'Aset yang tidak menyusut tidak dapat dihapus. Gunakan toggle Tidak Aktif.'
            );
        }

        // Nilai buku masih ada → belum bisa hapus
        if ($aset->nilai_buku_real_time > 0) {
            throw new \InvalidArgumentException(
                'Aset belum dapat dihapus karena masih memiliki nilai buku. Gunakan toggle Tidak Aktif.'
            );
        }

        if ($aset->dokumen_pendukung) {
            Storage::disk('public')->delete($aset->dokumen_pendukung);
        }

        $aset->delete();
    }

    // simpan dokumen ke storage
    private function simpanDokumen(?UploadedFile $dokumen): ?string
    {
        if (! $dokumen) {
            return null;
        }
        return $dokumen->store('aset/dokumen', 'public');
    }
}