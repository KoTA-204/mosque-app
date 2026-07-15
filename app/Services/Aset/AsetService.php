<?php

namespace App\Services\Aset;

use App\Models\Aset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AsetService
{
    // buat aset baru
    public function simpanAset(array $data, ?UploadedFile $dokumen): Aset
    {
        $dokumenPath = $this->simpanDokumen($dokumen);

        return Aset::create([
            'kode_aset'                => Aset::buatKode($data['tanggal_perolehan']),
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

    public function perbaruiAset(Aset $aset, array $data, ?UploadedFile $dokumen, bool $disusutkan): Aset
    {
        // Bila aset SUDAH memiliki jurnal penyesuaian, field keuangan dikunci
        // agar tidak mengubah angka yang sudah masuk jurnal.
        $keuanganTerkunci = $aset->jurnalPenyesuaian()->exists();

        // ganti dokumen kalau ada file baru
        $dokumenPath = $aset->dokumen_pendukung;
        if ($dokumen) {
            if ($dokumenPath) {
                Storage::delete($dokumenPath);
            }
            $dokumenPath = $this->simpanDokumen($dokumen);
        }

        if ($keuanganTerkunci) {
            $aset->fill([
                'nama_aset'         => $data['nama_aset'],
                'sumber_perolehan'  => $data['sumber_perolehan'],
                'kondisi_aset'      => $data['kondisi_aset'],
                'lokasi_aset'       => $data['lokasi_aset'],
                'nama_pemberi'      => $data['nama_pemberi'] ?? $aset->nama_pemberi,
                'jumlah_unit'       => $data['jumlah_unit'] ?? $aset->jumlah_unit,
                'dokumen_pendukung' => $dokumenPath,
                'keterangan'        => $data['keterangan'] ?? $aset->keterangan,
            ]);
            $aset->save();
            return $aset;
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
        $aset->nilai_buku           = $disusutkan ? $aset->hitungNilaiBukuRealTime() : (float) $data['nilai_tercatat'];
        $aset->akumulasi_penyusutan = $disusutkan ? $aset->hitungAkumulasiRealTime() : 0;

        // 3) simpan semua sekaligus
        $aset->save();

        return $aset;
    }

    /**
     * Aktif / nonaktifkan aset.
     *
     * Nonaktivasi WAJIB menyertakan alasan:
     *   - MENGANGGUR   : reversible; aset TETAP disusutkan (PSAK 16 par. 55) -> tidak dibekukan.
     *   - RUSAK_BERAT  : dibekukan; terkunci sampai kondisi diperbaiki lewat Edit.
     *   - AKAN_DILEPAS : dibekukan; terminal, tidak bisa diaktifkan kembali.
     *
     * Reaktivasi meneruskan nilai buku terakhir (tidak direset) agar tidak
     * terjadi lonjakan / penyusutan ganda.
     */
    public function ubahStatusAset(Aset $aset, ?string $alasan = null, ?string $catatan = null, ?string $jenisPerlepasan = null): string
    {
        // DRAFT tidak boleh di-toggle.
        if ($aset->status_aset === 'DRAFT') {
            throw new \InvalidArgumentException(
                'Aset berstatus draft belum dapat diaktifkan atau dinonaktifkan.'
            );
        }

        // ── Reaktivasi: TIDAK AKTIF -> AKTIF ────────────────────────────────
        if ($aset->status_aset === 'TIDAK AKTIF') {
            if (! $aset->bisaDiaktifkan()) {
                $pesan = $aset->alasan_nonaktif === Aset::ALASAN_AKAN_DILEPAS
                    ? 'Aset yang ditandai untuk dilepas/dibuang tidak dapat diaktifkan kembali.'
                    : 'Aset rusak berat baru dapat diaktifkan setelah kondisinya diperbaiki melalui menu Edit.';
                throw new \InvalidArgumentException($pesan);
            }

            $aset->update([
                'status_aset'      => 'AKTIF',
                'alasan_nonaktif'  => null,
                'catatan_nonaktif' => null,
                'tanggal_nonaktif' => null,
                // Akumulasi & nilai buku TIDAK direset (lanjut dari nilai terakhir).
            ]);

            return 'AKTIF';
        }

        // ── Nonaktivasi: AKTIF -> TIDAK AKTIF ───────────────────────────────
        $alasan = $alasan ?: Aset::ALASAN_MENGANGGUR;
        if (! array_key_exists($alasan, Aset::ALASAN_NONAKTIF_LABELS)) {
            throw new \InvalidArgumentException('Alasan penonaktifan aset tidak valid.');
        }

        // Validasi: AKAN_DILEPAS wajib menyertakan jenis pelepasan
        if ($alasan === Aset::ALASAN_AKAN_DILEPAS && empty($jenisPerlepasan)) {
            throw new \InvalidArgumentException('Pilih jenis pelepasan (Dijual/Dibuang/Donasi/Hilang) untuk menandai aset yang akan dilepas.');
        }

        $updateData = [
            'status_aset'      => 'TIDAK AKTIF',
            'alasan_nonaktif'  => $alasan,
            'catatan_nonaktif' => $catatan,
            'tanggal_nonaktif' => now()->toDateString(),
            'jenis_pelepasan'  => $alasan === Aset::ALASAN_AKAN_DILEPAS ? $jenisPerlepasan : null,
        ];

        // Menganggur sementara TETAP menyusut -> jangan bekukan.
        // Terminal (rusak berat / akan dilepas) -> bekukan nilai saat ini.
        if ($alasan !== Aset::ALASAN_MENGANGGUR && $aset->umur_manfaat) {
            $updateData['akumulasi_penyusutan'] = $aset->hitungAkumulasiRealTime();
            $updateData['nilai_buku']           = $aset->hitungNilaiBukuRealTime();
        }

        $aset->update($updateData);

        return 'TIDAK AKTIF';
    }

    // hapus aset (hanya jika memenuhi syarat)
    public function hapusAset(Aset $aset): void
    {
        // Aset tidak menyusut -> tidak bisa hapus
        if (is_null($aset->umur_manfaat)) {
            throw new \InvalidArgumentException(
                'Aset yang tidak menyusut tidak dapat dihapus. Gunakan toggle Tidak Aktif.'
            );
        }

        // Nilai buku masih ada -> belum bisa hapus
        if ($aset->hitungNilaiBukuRealTime() > 0) {
            throw new \InvalidArgumentException(
                'Aset belum dapat dihapus karena masih memiliki nilai buku. Gunakan toggle Tidak Aktif.'
            );
        }

        if ($aset->dokumen_pendukung) {
            Storage::delete($aset->dokumen_pendukung);
        }

        $aset->delete();
    }

    // simpan dokumen ke storage
    private function simpanDokumen(?UploadedFile $dokumen): ?string
    {
        if (! $dokumen) {
            return null;
        }
        return $dokumen->store('aset/dokumen');
    }
}
