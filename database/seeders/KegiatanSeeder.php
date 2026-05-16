<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kegiatan')->insert([

            [
                'nama_kegiatan' => 'Qurban Idul Adha 1447H',
                'jenis_kegiatan' => 'QURBAN',
                'tanggal_mulai' => '2026-06-01',
                'tanggal_selesai' => '2026-06-10',
                'anggaran' => 75000000,
                'status' => 'BERJALAN',
                'panitia_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kegiatan' => 'Zakat Fitrah Ramadhan 1447H',
                'jenis_kegiatan' => 'ZAKAT',
                'tanggal_mulai' => '2026-03-01',
                'tanggal_selesai' => '2026-03-30',
                'anggaran' => 30000000,
                'status' => 'SELESAI',
                'panitia_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
