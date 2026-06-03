<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\KategoriTransaksi;

class KategoriTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // ── Pemasukan ──────────────────────────────────────────
            ['nama_kategori' => 'Infak Jumat',          'deskripsi' => 'Infak yang dikumpulkan setiap sholat Jumat'],
            ['nama_kategori' => 'Infak Harian',         'deskripsi' => 'Infak harian dari kotak amal masjid'],
            ['nama_kategori' => 'Sedekah',              'deskripsi' => 'Sedekah dari jamaah atau donatur'],
            ['nama_kategori' => 'Wakaf',                'deskripsi' => 'Penerimaan aset atau dana wakaf'],
            ['nama_kategori' => 'Zakat',                'deskripsi' => 'Penerimaan zakat mal maupun zakat fitrah'],
            ['nama_kategori' => 'Donasi Kegiatan',      'deskripsi' => 'Donasi khusus untuk mendukung kegiatan masjid'],
            ['nama_kategori' => 'Kencleng',             'deskripsi' => 'Hasil pengumpulan kencleng jamaah'],

            // ── Pengeluaran ────────────────────────────────────────
            ['nama_kategori' => 'Operasional Masjid',  'deskripsi' => 'Biaya operasional rutin masjid (listrik, air, kebersihan)'],
            ['nama_kategori' => 'Pembelian Aset',       'deskripsi' => 'Pengeluaran untuk pembelian aset masjid'],
            ['nama_kategori' => 'Perawatan & Renovasi', 'deskripsi' => 'Biaya perawatan dan renovasi fasilitas masjid'],
            ['nama_kategori' => 'Honorarium',           'deskripsi' => 'Honor imam, khatib, marbot, dan pengajar TPA'],
            ['nama_kategori' => 'Konsumsi',             'deskripsi' => 'Biaya konsumsi kegiatan atau tamu masjid'],
            ['nama_kategori' => 'Perlengkapan Ibadah',  'deskripsi' => 'Pembelian perlengkapan ibadah (sajadah, mukena, Al-Quran)'],
            ['nama_kategori' => 'Sosial & Santunan',    'deskripsi' => 'Pengeluaran untuk kegiatan sosial dan santunan'],
            ['nama_kategori' => 'Kegiatan',             'deskripsi' => 'Biaya penyelenggaraan kegiatan masjid'],
            ['nama_kategori' => 'Lainnya',              'deskripsi' => 'Pengeluaran lain-lain yang tidak termasuk kategori di atas'],
        ];

        foreach ($data as $item) {
            KategoriTransaksi::create($item);
        }
    }
}
