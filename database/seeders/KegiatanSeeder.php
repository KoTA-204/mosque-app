<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Kegiatan;
use App\Models\User;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $panitia = User::where('email', 'panitia@masjid.id')->first();

        $kegiatan = [
            [
                'nama_kegiatan'   => 'Qurban 1447 H',
                'jenis_kegiatan'  => 'QURBAN',
                'tanggal_mulai'   => '2026-05-01',
                'tanggal_selesai' => '2026-06-30',
                'anggaran'        => 50000000,
                'status'          => 'BERJALAN',
                'panitia_id'      => $panitia->id,
            ],
            [
                'nama_kegiatan'   => 'Zakat Fitrah 1447 H',
                'jenis_kegiatan'  => 'ZAKAT',
                'tanggal_mulai'   => '2026-03-20',
                'tanggal_selesai' => '2026-04-10',
                'anggaran'        => 20000000,
                'status'          => 'SELESAI',
                'panitia_id'      => $panitia->id,
            ],
            [
                'nama_kegiatan'   => 'Kajian Ramadan 1447 H',
                'jenis_kegiatan'  => 'KAJIAN',
                'tanggal_mulai'   => '2026-02-28',
                'tanggal_selesai' => '2026-03-30',
                'anggaran'        => 5000000,
                'status'          => 'SELESAI',
                'panitia_id'      => $panitia->id,
            ],
            [
                'nama_kegiatan'   => 'Bakti Sosial Idul Adha 1447 H',
                'jenis_kegiatan'  => 'SOSIAL',
                'tanggal_mulai'   => '2026-06-01',
                'tanggal_selesai' => '2026-06-15',
                'anggaran'        => 10000000,
                'status'          => 'DRAFT',
                'panitia_id'      => $panitia->id,
            ],
        ];

        foreach ($kegiatan as $item) {
            Kegiatan::create($item);
        }
    }
}
