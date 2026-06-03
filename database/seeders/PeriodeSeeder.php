<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Periode;

class PeriodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Periode bulanan tahun 2026
        $bulan = [
            ['nama' => 'Januari 2026',   'awal' => '2026-01-01', 'akhir' => '2026-01-31'],
            ['nama' => 'Februari 2026',  'awal' => '2026-02-01', 'akhir' => '2026-02-28'],
            ['nama' => 'Maret 2026',     'awal' => '2026-03-01', 'akhir' => '2026-03-31'],
            ['nama' => 'April 2026',     'awal' => '2026-04-01', 'akhir' => '2026-04-30'],
            ['nama' => 'Mei 2026',       'awal' => '2026-05-01', 'akhir' => '2026-05-31'],
            ['nama' => 'Juni 2026',      'awal' => '2026-06-01', 'akhir' => '2026-06-30'],
            ['nama' => 'Juli 2026',      'awal' => '2026-07-01', 'akhir' => '2026-07-31'],
            ['nama' => 'Agustus 2026',   'awal' => '2026-08-01', 'akhir' => '2026-08-31'],
            ['nama' => 'September 2026', 'awal' => '2026-09-01', 'akhir' => '2026-09-30'],
            ['nama' => 'Oktober 2026',   'awal' => '2026-10-01', 'akhir' => '2026-10-31'],
            ['nama' => 'November 2026',  'awal' => '2026-11-01', 'akhir' => '2026-11-30'],
            ['nama' => 'Desember 2026',  'awal' => '2026-12-01', 'akhir' => '2026-12-31'],
        ];

        foreach ($bulan as $b) {
            Periode::create([
                'nama_periode'  => $b['nama'],
                'tanggal_awal'  => $b['awal'],
                'tanggal_akhir' => $b['akhir'],
                'tipe'          => 'bulanan',
                'status'        => now()->between($b['awal'], $b['akhir']), // aktif kalau bulan sekarang
            ]);
        }

        // Periode tahunan 2026
        Periode::create([
            'nama_periode'  => 'Tahun 2026',
            'tanggal_awal'  => '2026-01-01',
            'tanggal_akhir' => '2026-12-31',
            'tipe'          => 'tahunan',
            'status'        => true,
        ]);
    }
}
