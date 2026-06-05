<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin    = Role::where('role_name', 'Super Admin')->first();
        $bendahara1    = Role::where('role_name', 'Bendahara 1')->first();
        $bendahara2    = Role::where('role_name', 'Bendahara 2')->first();
        $phm           = Role::where('role_name', 'PHM')->first();
        $panitiaKhusus = Role::where('role_name', 'Panitia Khusus')->first();

        $users = [
            [
                'role_id'  => $superAdmin->id,
                'name'     => 'Administrator',
                'email'    => 'admin@masjid.id',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ],
            [
                'role_id'  => $bendahara1->id,
                'name'     => 'Ahmad Fauzi',
                'email'    => 'bendahara1@masjid.id',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ],
            [
                'role_id'  => $bendahara2->id,
                'name'     => 'Siti Rahayu',
                'email'    => 'bendahara2@masjid.id',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ],
            [
                'role_id'  => $phm->id,
                'name'     => 'Budi Santoso',
                'email'    => 'phm@masjid.id',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ],
            [
                'role_id'  => $panitiaKhusus->id,
                'name'     => 'Farhan Akbar',
                'email'    => 'panitia@masjid.id',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
