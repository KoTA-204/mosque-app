<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KenclengSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kencleng')->insert([
            [
                'transaksi_id' => 2,
                'nomor_kwitansi' => 'BA-001',
                'berita_acara' => 'berita-acara/ba-001.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
