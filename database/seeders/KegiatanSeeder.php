<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kegiatan;
use App\Models\User;

class KegiatanSeeder extends Seeder
{
    /**
     * Kegiatan khusus masjid. panitia_id mengarah ke user ber-role Panitia Khusus.
     */
    public function run(): void
    {
        $panitia = User::where('email', 'panitia@masjid.id')->value('id');

        $data = [
            [
                'nama_kegiatan'  => 'Qurban Idul Adha 1447H',
                'jenis_kegiatan' => 'QURBAN',
                'tanggal_mulai'  => '2026-05-01',
                'tanggal_selesai'=> '2026-06-20',
                'anggaran'       => 50000000,
                'status'         => 'AKTIF',
                'panitia_id'     => $panitia,
            ],
            [
                'nama_kegiatan'  => 'Kajian Rutin & PHBI 2026',
                'jenis_kegiatan' => 'KAJIAN',
                'tanggal_mulai'  => '2026-01-01',
                'tanggal_selesai'=> null,
                'anggaran'       => 20000000,
                'status'         => 'AKTIF',
                'panitia_id'     => $panitia,
            ],
            [
                'nama_kegiatan'  => 'Bakti Sosial Ramadhan 2026',
                'jenis_kegiatan' => 'SOSIAL',
                'tanggal_mulai'  => '2026-03-01',
                'tanggal_selesai'=> '2026-03-31',
                'anggaran'       => 15000000,
                'status'         => 'DITUTUP',
                'panitia_id'     => $panitia,
            ],
        ];

        foreach ($data as $row) {
            Kegiatan::create($row);
        }
    }
}
