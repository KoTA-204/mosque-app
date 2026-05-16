<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transaksi')->insert([
            [
                'dompet_id' => 1,
                'kegiatan_id' => null,
                'user_id' => 1,
                'kategori_transaksi_id' => 1,
                'tanggal_transaksi' => '2026-05-01',
                'jumlah' => 5000000,
                'deskripsi' => 'Infaq Jumat minggu pertama',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dompet_id' => 1,
                'kegiatan_id' => null,
                'user_id' => 2,
                'kategori_transaksi_id' => 2,
                'tanggal_transaksi' => '2026-05-02',
                'jumlah' => 3200000,
                'deskripsi' => 'Pembukaan kencleng Jumat',
                'status_approval' => 'PENDING',
                'status_jurnal' => 'UNMAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dompet_id' => 2,
                'kegiatan_id' => null,
                'user_id' => 1,
                'kategori_transaksi_id' => 3,
                'tanggal_transaksi' => '2026-05-03',
                'jumlah' => 750000,
                'deskripsi' => 'Pembayaran listrik Mei',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dompet_id' => 2,
                'kegiatan_id' => 1,
                'user_id' => 2,
                'kategori_transaksi_id' => 1,
                'tanggal_transaksi' => '2026-06-01',
                'jumlah' => 25000000,
                'deskripsi' => 'Penerimaan dana qurban sapi',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dompet_id' => 2,
                'kegiatan_id' => 1,
                'user_id' => 2,
                'kategori_transaksi_id' => 1,
                'tanggal_transaksi' => '2026-06-02',
                'jumlah' => 18000000,
                'deskripsi' => 'Penerimaan dana qurban kambing',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 2,
                'kegiatan_id' => 1, // Pengadaan Hewan Qurban
                'user_id' => 1,
                'kategori_transaksi_id' => 4, // Pengeluaran,
                'tanggal_transaksi' => '2026-06-03',
                'jumlah' => 30000000,
                'deskripsi' => 'Pembelian 3 ekor sapi qurban',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 1,
                'kegiatan_id' => 1,
                'user_id' => 1,
                'kategori_transaksi_id' => 4,
                'tanggal_transaksi' => '2026-06-04',
                'jumlah' => 7500000,
                'deskripsi' => 'Pembelian perlengkapan penyembelihan',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'UNMAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 1,
                'kegiatan_id' => 1, // Distribusi Daging Qurban
                'user_id' => 2,
                'kategori_transaksi_id' => 4,
                'tanggal_transaksi' => '2026-06-07',
                'jumlah' => 2500000,
                'deskripsi' => 'Biaya distribusi daging qurban',
                'status_approval' => 'PENDING',
                'status_jurnal' => 'UNMAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =========================
            // ZAKAT
            // =========================

            [
                'dompet_id' => 2,
                'kegiatan_id' => 2, // Zakat Fitrah Ramadhan
                'user_id' => 3,
                'kategori_transaksi_id' => 1,
                'tanggal_transaksi' => '2026-03-10',
                'jumlah' => 15000000,
                'deskripsi' => 'Penerimaan zakat fitrah masyarakat',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 2,
                'kegiatan_id' => 2,
                'user_id' => 3,
                'kategori_transaksi_id' => 1,
                'tanggal_transaksi' => '2026-03-12',
                'jumlah' => 12000000,
                'deskripsi' => 'Penerimaan zakat maal',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 2,
                'kegiatan_id' => 2, // Penyaluran Zakat Mustahik
                'user_id' => 1,
                'kategori_transaksi_id' => 4,
                'tanggal_transaksi' => '2026-03-26',
                'jumlah' => 10000000,
                'deskripsi' => 'Penyaluran zakat kepada mustahik wilayah A',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'MAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 2,
                'kegiatan_id' => 2,
                'user_id' => 1,
                'kategori_transaksi_id' => 4,
                'tanggal_transaksi' => '2026-03-27',
                'jumlah' => 8500000,
                'deskripsi' => 'Penyaluran zakat kepada mustahik wilayah B',
                'status_approval' => 'APPROVED',
                'status_jurnal' => 'UNMAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'dompet_id' => 1,
                'kegiatan_id' => 2, 
                'user_id' => 3,
                'kategori_transaksi_id' => 4,
                'tanggal_transaksi' => '2026-02-25',
                'jumlah' => 1000000,
                'deskripsi' => 'Biaya operasional pendataan mustahik',
                'status_approval' => 'REVISION',
                'status_jurnal' => 'UNMAPPED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
