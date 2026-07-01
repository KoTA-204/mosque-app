<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dompet;

class DompetSeeder extends Seeder
{
    /**
     * 3 kas/dompet masjid (semua tunai).
     * saldo_awal konsisten dengan akun Kas pada Jurnal Pembuka:
     *   Kas Kecil  -> 1-101
     *   Kas Infak  -> 1-102
     *   Kas Zakat  -> 1-103
     */
    public function run(): void
    {
        $dompet = [
            ['nama_dompet' => 'Kas Kecil', 'saldo_awal' => 5000000],
            ['nama_dompet' => 'Kas Infak', 'saldo_awal' => 25000000],
            ['nama_dompet' => 'Kas Zakat', 'saldo_awal' => 15000000],
        ];

        foreach ($dompet as $d) {
            Dompet::create([
                'nama_dompet'    => $d['nama_dompet'],
                'jenis_dompet'   => 'CASH',
                'nomor_rekening' => null,
                'nama_bank'      => null,
                'saldo_awal'     => $d['saldo_awal'],
            ]);
        }
    }
}
