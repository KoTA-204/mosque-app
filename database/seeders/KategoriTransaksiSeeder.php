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
            ['nama_kategori' => 'Penerimaan Zakat Maal', 'deskripsi' => 'Penerimaan dana zakat maal dari muzakki'],
            ['nama_kategori' => 'Penerimaan Zakat Fitrah', 'deskripsi' => 'Penerimaan zakat fitrah berupa beras maupun uang'],
            ['nama_kategori' => 'Penyaluran Zakat', 'deskripsi' => 'Penyaluran zakat kepada 8 asnaf (mustahik)'],
            ['nama_kategori' => 'Penerimaan Dana Qurban', 'deskripsi' => 'Penerimaan dana/hewan qurban dari shohibul qurban'],
            ['nama_kategori' => 'Operasional Qurban', 'deskripsi' => 'Pengadaan hewan, pemotongan, dan distribusi daging qurban'],
            ['nama_kategori' => 'Penerimaan Wakaf', 'deskripsi' => 'Penerimaan dana atau aset wakaf'],
            ['nama_kategori' => 'Penyaluran Wakaf', 'deskripsi' => 'Realisasi penggunaan dana/aset wakaf'],
            ['nama_kategori' => 'Penerimaan Dana Pembangunan', 'deskripsi' => 'Donasi khusus untuk pembangunan masjid'],
            ['nama_kategori' => 'Beban Pembangunan', 'deskripsi' => 'Pengeluaran untuk pembangunan/renovasi masjid'],
            ['nama_kategori' => 'Donasi Program', 'deskripsi' => 'Donasi dengan tujuan program tertentu'],
            ['nama_kategori' => 'Beban Kajian & PHBI', 'deskripsi' => 'Pelaksanaan kajian dan peringatan hari besar Islam'],
            ['nama_kategori' => 'Beban Kegiatan Sosial', 'deskripsi' => 'Bakti sosial, santunan, dan kegiatan kemasyarakatan'],
            ['nama_kategori' => 'Konsumsi Kegiatan', 'deskripsi' => 'Konsumsi panitia dan peserta kegiatan'],
            ['nama_kategori' => 'Honorarium Narasumber', 'deskripsi' => 'Honor pemateri/ustadz pada kegiatan'],
            ['nama_kategori' => 'Perlengkapan Kegiatan', 'deskripsi' => 'Pengadaan perlengkapan penunjang kegiatan khusus'],
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
