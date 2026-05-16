<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori_transaksi')->insert([
            [
                'nama_kategori' => 'Infaq Jumat',
                'jenis_transaksi' => 'PEMASUKAN',
                'deskripsi' => 'Pemasukan infaq Jumat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Kencleng',
                'jenis_transaksi' => 'PEMASUKAN',
                'deskripsi' => 'Pemasukan kencleng masjid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Pembayaran Listrik',
                'jenis_transaksi' => 'PENGELUARAN',
                'deskripsi' => 'Pembayaran listrik bulanan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Pembelian Inventaris',
                'jenis_transaksi' => 'PENGELUARAN',
                'deskripsi' => 'Pembelian inventaris masjid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
