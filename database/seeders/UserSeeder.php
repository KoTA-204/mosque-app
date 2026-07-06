<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userMap = [
            ['role' => 'Administrator',           'name' => 'Administrator',      'email' => 'admin@masjid.id'],
            ['role' => 'Ketua DKM',               'name' => 'H. Rahmat Hidayat',  'email' => 'ketua@masjid.id'],
            ['role' => 'Bendahara 1',             'name' => 'Ahmad Fauzi',         'email' => 'bendahara1@masjid.id'],
            ['role' => 'Bendahara 2',             'name' => 'Siti Rahayu',         'email' => 'bendahara2@masjid.id'],
            ['role' => 'Pengurus Harian Masjid', 'name' => 'Budi Santoso',         'email' => 'phm@masjid.id'],
            ['role' => 'Panitia Kegiatan Khusus', 'name' => 'Farhan Akbar',        'email' => 'panitia@masjid.id'],
            ['role' => 'Sekretaris',              'name' => 'Dewi Lestari',        'email' => 'sekretaris@masjid.id'],
        ];

        foreach ($userMap as $item) {
            $role = Role::where('role_name', $item['role'])->firstOrFail();
            User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'role_id'  => $role->id,
                    'name'     => $item['name'],
                    'password' => Hash::make('password'),
                    'status'   => 'active',
                ]
            );
        }
    }
}
