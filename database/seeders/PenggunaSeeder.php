<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $penggunaMap = [
            ['peran' => 'Administrator',           'nama' => 'Administrator',      'email' => 'admin@masjid.id'],
            ['peran' => 'Ketua DKM',               'nama' => 'H. Rahmat Hidayat',  'email' => 'ketua@masjid.id'],
            ['peran' => 'Bendahara 1',             'nama' => 'Ahmad Fauzi',         'email' => 'bendahara1@masjid.id'],
            ['peran' => 'Bendahara 2',             'nama' => 'Siti Rahayu',         'email' => 'bendahara2@masjid.id'],
            ['peran' => 'Pengurus Harian Masjid', 'nama' => 'Budi Santoso',         'email' => 'phm@masjid.id'],
            ['peran' => 'Panitia Kegiatan Khusus', 'nama' => 'Farhan Akbar',        'email' => 'panitia@masjid.id'],
            ['peran' => 'Sekretaris',              'nama' => 'Dewi Lestari',        'email' => 'sekretaris@masjid.id'],
        ];

        foreach ($penggunaMap as $item) {
            $peran = Peran::where('nama_peran', $item['peran'])->firstOrFail();
            Pengguna::firstOrCreate(
                ['email' => $item['email']],
                [
                    'peran_id'  => $peran->id,
                    'nama'     => $item['nama'],
                    'password' => Hash::make('password'),
                    'status'   => 'active',
                ]
            );
        }
    }
}
