<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_name'   => 'Super Admin',
                'description' => 'Memiliki akses penuh ke seluruh fitur sistem',
            ],
            [
                'role_name'   => 'Bendahara 1',
                'description' => 'Bendahara utama, bertanggung jawab atas pencatatan pemasukan, pengeluaran, approval transaksi, dan laporan keuangan',
            ],
            [
                'role_name'   => 'Bendahara 2',
                'description' => 'Bendahara pembantu, membantu pencatatan pengeluaran dan pelaporan keuangan',
            ],
            [
                'role_name'   => 'PHM',
                'description' => 'Pengurus Harian Masjid, bertanggung jawab mencatat setoran kencleng harian',
            ],
            [
                'role_name'   => 'Panitia Khusus',
                'description' => 'Panitia kegiatan khusus masjid, bertanggung jawab mencatat transaksi kegiatan yang ditugaskan',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
