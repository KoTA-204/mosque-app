<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Dompet;

class DompetSeeder extends Seeder
{
    /**
     * Saldo awal dompet = posisi kas/bank pada AWAL periode (setara Jurnal Pembuka).
     * Angka dibuat cukup besar & realistis agar setiap dompet tetap positif
     * setelah transaksi operasional berjalan (lihat TransaksiSeeder).
     */
    public function run(): void
    {
        DB::table('dompet')->insert([
            [
                'nama_dompet'    => 'Kas Masjid',
                'jenis_dompet'   => 'CASH',
                'nomor_rekening' => null,
                'nama_bank'      => null,
                'saldo_awal'     => 15000000,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nama_dompet'    => 'Bank BSI Operasional',
                'jenis_dompet'   => 'BANK',
                'nomor_rekening' => '7123456789',
                'nama_bank'      => 'BSI',
                'saldo_awal'     => 100000000,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'nama_dompet'    => 'Bank BRI Operasional',
                'jenis_dompet'   => 'BANK',
                'nomor_rekening' => '0234567890123',
                'nama_bank'      => 'BRI',
                'saldo_awal'     => 50000000,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
