<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriTransaksi;

class KategoriTransaksiSeeder extends Seeder
{
    /**
     * Kategori transaksi khusus untuk KEGIATAN KHUSUS.
     * Tujuannya membantu panitia khusus mengelompokkan transaksi agar
     * memudahkan Bendahara 1 saat mapping akun debit & kredit.
     */
    public function run(): void
    {
        $kategori = [
            // ── UMUM (dibutuhkan TransaksiSeeder / BuktiTransaksiSeeder / JurnalUmumSeeder) ──
            ['nama_kategori' => 'Infak Jumat',          'deskripsi' => 'Infak yang dikumpulkan setiap sholat Jumat'],
            ['nama_kategori' => 'Infak Harian',         'deskripsi' => 'Infak harian dari kotak amal masjid'],
            ['nama_kategori' => 'Sedekah',              'deskripsi' => 'Sedekah dari jamaah atau donatur'],
            ['nama_kategori' => 'Wakaf',                'deskripsi' => 'Penerimaan aset atau dana wakaf'],
            ['nama_kategori' => 'Zakat',                'deskripsi' => 'Penerimaan zakat mal maupun zakat fitrah'],
            ['nama_kategori' => 'Donasi Kegiatan',      'deskripsi' => 'Donasi khusus untuk mendukung kegiatan masjid'],
            ['nama_kategori' => 'Kencleng',             'deskripsi' => 'Hasil pengumpulan kencleng jamaah'],
            ['nama_kategori' => 'Operasional Masjid',   'deskripsi' => 'Biaya operasional rutin masjid (listrik, air, kebersihan)'],
            ['nama_kategori' => 'Pembelian Aset',       'deskripsi' => 'Pengeluaran untuk pembelian aset masjid'],
            ['nama_kategori' => 'Perawatan & Renovasi', 'deskripsi' => 'Biaya perawatan dan renovasi fasilitas masjid'],
            ['nama_kategori' => 'Honorarium',           'deskripsi' => 'Honor imam, khatib, marbot, dan pengajar TPA'],
            ['nama_kategori' => 'Konsumsi',             'deskripsi' => 'Biaya konsumsi kegiatan atau tamu masjid'],
            ['nama_kategori' => 'Perlengkapan Ibadah',  'deskripsi' => 'Pembelian perlengkapan ibadah (sajadah, mukena, Al-Quran)'],
            ['nama_kategori' => 'Sosial & Santunan',    'deskripsi' => 'Pengeluaran untuk kegiatan sosial dan santunan'],
            ['nama_kategori' => 'Kegiatan',             'deskripsi' => 'Biaya penyelenggaraan kegiatan masjid'],
            ['nama_kategori' => 'Lainnya',              'deskripsi' => 'Pengeluaran lain-lain yang tidak termasuk kategori di atas'],

            // ── KHUSUS KEGIATAN (bagianmu — dipertahankan) ──
            ['nama_kategori' => 'Penerimaan Zakat Maal',    'deskripsi' => 'Penerimaan dana zakat maal dari muzakki'],
            ['nama_kategori' => 'Penerimaan Zakat Fitrah',  'deskripsi' => 'Penerimaan zakat fitrah berupa beras maupun uang'],
            ['nama_kategori' => 'Penyaluran Zakat',         'deskripsi' => 'Penyaluran zakat kepada 8 asnaf (mustahik)'],
            ['nama_kategori' => 'Penerimaan Dana Qurban',   'deskripsi' => 'Penerimaan dana/hewan qurban dari shohibul qurban'],
            ['nama_kategori' => 'Operasional Qurban',       'deskripsi' => 'Pengadaan hewan, pemotongan, dan distribusi daging qurban'],
            ['nama_kategori' => 'Penerimaan Wakaf',         'deskripsi' => 'Penerimaan dana atau aset wakaf'],
            ['nama_kategori' => 'Penyaluran Wakaf',         'deskripsi' => 'Realisasi penggunaan dana/aset wakaf'],
            ['nama_kategori' => 'Penerimaan Dana Pembangunan', 'deskripsi' => 'Donasi khusus untuk pembangunan masjid'],
            ['nama_kategori' => 'Beban Pembangunan',        'deskripsi' => 'Pengeluaran untuk pembangunan/renovasi masjid'],
            ['nama_kategori' => 'Donasi Program',           'deskripsi' => 'Donasi dengan tujuan program tertentu'],
            ['nama_kategori' => 'Beban Kajian & PHBI',      'deskripsi' => 'Pelaksanaan kajian dan peringatan hari besar Islam'],
            ['nama_kategori' => 'Beban Kegiatan Sosial',    'deskripsi' => 'Bakti sosial, santunan, dan kegiatan kemasyarakatan'],
            ['nama_kategori' => 'Konsumsi Kegiatan',        'deskripsi' => 'Konsumsi panitia dan peserta kegiatan'],
            ['nama_kategori' => 'Honorarium Narasumber',    'deskripsi' => 'Honor pemateri/ustadz pada kegiatan'],
            ['nama_kategori' => 'Perlengkapan Kegiatan',    'deskripsi' => 'Pengadaan perlengkapan penunjang kegiatan khusus'],
        ];

        foreach ($kategori as $k) {
            KategoriTransaksi::create([
                'nama_kategori' => $k['nama_kategori'],
                'deskripsi'     => $k['deskripsi'],
                'status'        => 'aktif',
            ]);
        }
    }
}
