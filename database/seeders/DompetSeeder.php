<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DompetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('dompet')->insert([
            [
                'nama_dompet' => 'Kas Masjid',
                'jenis_dompet' => 'CASH',
                'nomor_rekening' => null,
                'nama_bank' => null,
                'saldo_awal' => 10000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nama_dompet' => 'Bank BSI Operasional',
                'jenis_dompet' => 'BANK',
                'nomor_rekening' => '7123456789',
                'nama_bank' => 'BSI',
                'saldo_awal' => 50000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nama_dompet' => 'Bank BRI Operasional',
                'jenis_dompet' => 'BANK',
                'nomor_rekening' => '0234567890123',
                'nama_bank' => 'BRI',
                'saldo_awal' => 30000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
