<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kencleng;
use App\Models\Transaksi;

class KenclengSeeder extends Seeder
{
    /**
     * Detail kencleng untuk transaksi infak kotak amal.
     * Dihubungkan ke transaksi melalui no_referensi (unik).
     */
    public function run(): void
    {
        $data = [
            [
                'no_referensi'   => 'INF-KCL-2026-03-001',
                'nomor_kwitansi' => 'KWT-KCL-2026-03-001',
                'berita_acara'   => 'Penghitungan kencleng Jumat 13 Maret 2026 disaksikan oleh ketua DKM dan 2 pengurus harian.',
            ],
            [
                'no_referensi'   => 'INF-KCL-2026-06-002',
                'nomor_kwitansi' => 'KWT-KCL-2026-06-002',
                'berita_acara'   => 'Penghitungan kencleng Jumat 12 Juni 2026 disaksikan oleh PHM dan 2 pengurus harian.',
            ],
        ];

        foreach ($data as $row) {
            $transaksi = Transaksi::where('no_referensi', $row['no_referensi'])->first();
            if (! $transaksi) {
                continue;
            }

            Kencleng::create([
                'transaksi_id'   => $transaksi->id,
                'nomor_kwitansi' => $row['nomor_kwitansi'],
                'berita_acara'   => $row['berita_acara'],
            ]);
        }
    }
}
