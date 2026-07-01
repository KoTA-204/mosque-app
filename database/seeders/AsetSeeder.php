<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aset;

class AsetSeeder extends Seeder
{
    /**
     * Aset tetap awal masjid (dummy wajar).
     * DISESUAIKAN dengan skema migration 'aset' saat ini:
     * kolom kode_aset, jumlah_unit, dokumen_pendukung,
     * tanggal_mulai_penyusutan, dan keterangan TIDAK dipakai
     * karena belum ada di tabel.
     *
     * Nilai konsisten dengan Jurnal Pembuka:
     *   Tanah Masjid                1-201  800.000.000 (tidak disusutkan)
     *   Bangunan Masjid             1-202  1.200.000.000, akum 1-203 150.000.000
     *   Peralatan (Sound & Mimbar)  1-206  60.000.000,    akum 1-207 20.000.000
     *
     * Penyusutan garis lurus (bulanan):
     *   Bangunan : 1.200.000.000 / 240 bln = 5.000.000/bln (sudah jalan 30 bln -> 150.000.000)
     *   Peralatan:    60.000.000 / 60 bln  = 1.000.000/bln (sudah jalan 20 bln -> 20.000.000)
     */
    public function run(): void
    {
        $aset = [
            [
                'nama_aset'            => 'Tanah Masjid',
                'sumber_perolehan'     => 'Wakaf',
                'tanggal_perolehan'    => '2015-01-10',
                'nilai_tercatat'       => 800000000,
                'umur_manfaat'         => null,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Area Masjid',
                'nama_pemberi'         => 'Wakif',
                'status_aset'          => 'AKTIF',
                'akumulasi_penyusutan' => 0,
                'nilai_buku'           => 800000000,
            ],
            [
                'nama_aset'            => 'Bangunan Masjid',
                'sumber_perolehan'     => 'Pembangunan Swadaya',
                'tanggal_perolehan'    => '2023-06-20',
                'nilai_tercatat'       => 1200000000,
                'umur_manfaat'         => 240,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Bangunan Utama',
                'nama_pemberi'         => null,
                'status_aset'          => 'AKTIF',
                'akumulasi_penyusutan' => 150000000,
                'nilai_buku'           => 1050000000,
            ],
            [
                'nama_aset'            => 'Sound System & Mimbar',
                'sumber_perolehan'     => 'Pembelian',
                'tanggal_perolehan'    => '2024-04-25',
                'nilai_tercatat'       => 60000000,
                'umur_manfaat'         => 60,
                'kondisi_aset'         => 'BAIK',
                'lokasi_aset'          => 'Ruang Utama Masjid',
                'nama_pemberi'         => null,
                'status_aset'          => 'AKTIF',
                'akumulasi_penyusutan' => 20000000,
                'nilai_buku'           => 40000000,
            ],
        ];

        foreach ($aset as $a) {
            Aset::create(array_merge($a, [
                'transaksi_id' => null,
            ]));
        }
    }
}
