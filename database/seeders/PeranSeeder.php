<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Peran;

class PeranSeeder extends Seeder
{
    public function run(): void
    {
        $peran = [
            ['nama_peran' => 'Administrator',           'deskripsi' => 'Administrator sistem, akses penuh ke seluruh fitur'],
            ['nama_peran' => 'Ketua DKM',               'deskripsi' => 'Ketua Dewan Kemakmuran Masjid, akses dashboard dan laporan keuangan'],
            ['nama_peran' => 'Bendahara 1',             'deskripsi' => 'Bendahara utama, pencatatan pemasukan/pengeluaran, persetujuan, dan laporan keuangan'],
            ['nama_peran' => 'Bendahara 2',             'deskripsi' => 'Bendahara pembantu, pencatatan transaksi operasional'],
            ['nama_peran' => 'Pengurus Harian Masjid',  'deskripsi' => 'Pengurus Harian Masjid, mencatat setoran kencleng'],
            ['nama_peran' => 'Panitia Kegiatan Khusus', 'deskripsi' => 'Panitia kegiatan khusus, mencatat transaksi kegiatan yang ditugaskan'],
            ['nama_peran' => 'Sekretaris',              'deskripsi' => 'Sekretaris DKM, pengelolaan aset masjid'],
        ];

        foreach ($peran as $peran) {
            Peran::firstOrCreate(
                ['nama_peran' => $peran['nama_peran']],
                ['deskripsi' => $peran['deskripsi']]
            );
        }
    }
}
