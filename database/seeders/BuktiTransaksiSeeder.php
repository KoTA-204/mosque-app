<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuktiTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bukti_transaksi')->insert([
            [
                'transaksi_id' => 1,
                'nama_file' => 'infaq-jumat.jpg',
                'path_file' => 'bukti/infaq-jumat.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaksi_id' => 3,
                'nama_file' => 'token-listrik.pdf',
                'path_file' => 'bukti/token-listrik.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
