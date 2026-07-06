<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_name' => 'Administrator',           'description' => 'Administrator sistem, akses penuh ke seluruh fitur'],
            ['role_name' => 'Ketua DKM',               'description' => 'Ketua Dewan Kemakmuran Masjid, akses dashboard dan laporan keuangan'],
            ['role_name' => 'Bendahara 1',             'description' => 'Bendahara utama, pencatatan pemasukan/pengeluaran, approval, dan laporan keuangan'],
            ['role_name' => 'Bendahara 2',             'description' => 'Bendahara pembantu, pencatatan transaksi operasional'],
            ['role_name' => 'Pengurus Harian Masjid', 'description' => 'Pengurus Harian Masjid, mencatat setoran kencleng'],
            ['role_name' => 'Panitia Kegiatan Khusus', 'description' => 'Panitia kegiatan khusus, mencatat transaksi kegiatan yang ditugaskan'],
            ['role_name' => 'Sekretaris',              'description' => 'Sekretaris DKM, pengelolaan aset masjid'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['role_name' => $role['role_name']],
                ['description' => $role['description']]
            );
        }
    }
}
