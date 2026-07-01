<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kencleng;
use App\Models\KenclengDetail;
use App\Models\Transaksi;

class KenclengDetailSeeder extends Seeder
{
    /**
     * Rincian pecahan uang per kencleng.
     * Total (pecahan * jumlah_pecahan) DISENGAJA sama dengan transaksi.jumlah.
     */
    public function run(): void
    {
        // no_referensi => [ [pecahan, jumlah_pecahan], ... ]
        $rincian = [
            // total = 2.700.000
            'INF-KCL-2026-03-001' => [
                [100000, 7],
                [50000, 12],
                [20000, 25],
                [10000, 40],
                [5000, 60],
                [2000, 50],
                [1000, 100],
            ],
            // total = 1.850.000
            'INF-KCL-2026-06-002' => [
                [100000, 5],
                [50000, 10],
                [20000, 15],
                [10000, 25],
                [5000, 40],
                [2000, 25],
                [1000, 50],
            ],
        ];

        foreach ($rincian as $noReferensi => $detail) {
            $transaksi = Transaksi::where('no_referensi', $noReferensi)->first();
            if (! $transaksi) {
                continue;
            }

            $kencleng = Kencleng::where('transaksi_id', $transaksi->id)->first();
            if (! $kencleng) {
                continue;
            }

            foreach ($detail as [$pecahan, $jumlahPecahan]) {
                KenclengDetail::create([
                    'kencleng_id'    => $kencleng->id,
                    'pecahan'        => $pecahan,
                    'jumlah_pecahan' => $jumlahPecahan,
                ]);
            }
        }
    }
}
