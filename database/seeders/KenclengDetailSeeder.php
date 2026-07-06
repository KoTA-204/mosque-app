<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kencleng;
use App\Models\KenclengDetail;

class KenclengDetailSeeder extends Seeder
{
    public function run(): void
    {
        $kenclengList = Kencleng::orderBy('id')->get();

        $templates = [
            [
                ['pecahan' => 100000, 'jumlah_pecahan' => 4],
                ['pecahan' => 50000,  'jumlah_pecahan' => 8],
                ['pecahan' => 20000,  'jumlah_pecahan' => 10],
                ['pecahan' => 10000,  'jumlah_pecahan' => 15],
                ['pecahan' => 5000,   'jumlah_pecahan' => 25],
                ['pecahan' => 2000,   'jumlah_pecahan' => 30],
                ['pecahan' => 1000,   'jumlah_pecahan' => 50],
            ],
            [
                ['pecahan' => 100000, 'jumlah_pecahan' => 3],
                ['pecahan' => 50000,  'jumlah_pecahan' => 10],
                ['pecahan' => 20000,  'jumlah_pecahan' => 12],
                ['pecahan' => 10000,  'jumlah_pecahan' => 18],
                ['pecahan' => 5000,   'jumlah_pecahan' => 20],
                ['pecahan' => 2000,   'jumlah_pecahan' => 25],
                ['pecahan' => 1000,   'jumlah_pecahan' => 40],
            ],
            [
                ['pecahan' => 100000, 'jumlah_pecahan' => 6],
                ['pecahan' => 50000,  'jumlah_pecahan' => 12],
                ['pecahan' => 20000,  'jumlah_pecahan' => 18],
                ['pecahan' => 10000,  'jumlah_pecahan' => 20],
                ['pecahan' => 5000,   'jumlah_pecahan' => 35],
                ['pecahan' => 2000,   'jumlah_pecahan' => 40],
                ['pecahan' => 1000,   'jumlah_pecahan' => 60],
            ],
            [
                ['pecahan' => 100000, 'jumlah_pecahan' => 3],
                ['pecahan' => 50000,  'jumlah_pecahan' => 7],
                ['pecahan' => 20000,  'jumlah_pecahan' => 9],
                ['pecahan' => 10000,  'jumlah_pecahan' => 12],
                ['pecahan' => 5000,   'jumlah_pecahan' => 20],
                ['pecahan' => 2000,   'jumlah_pecahan' => 18],
                ['pecahan' => 1000,   'jumlah_pecahan' => 30],
            ],
            [
                ['pecahan' => 100000, 'jumlah_pecahan' => 4],
                ['pecahan' => 50000,  'jumlah_pecahan' => 8],
                ['pecahan' => 20000,  'jumlah_pecahan' => 10],
                ['pecahan' => 10000,  'jumlah_pecahan' => 14],
                ['pecahan' => 5000,   'jumlah_pecahan' => 22],
                ['pecahan' => 2000,   'jumlah_pecahan' => 20],
                ['pecahan' => 1000,   'jumlah_pecahan' => 35],
            ],
        ];

        foreach ($kenclengList as $index => $kencleng) {
            $template = $templates[$index % count($templates)];
            foreach ($template as $row) {
                KenclengDetail::create([
                    'kencleng_id'    => $kencleng->id,
                    'pecahan'        => $row['pecahan'],
                    'jumlah_pecahan' => $row['jumlah_pecahan'],
                ]);
            }
        }
    }
}
