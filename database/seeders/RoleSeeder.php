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
        Role::insert([
            ['role_name' => 'Super Admin', 'description' => 'Full access'],
            ['role_name' => 'Bendahara 1', 'description' => 'Mencatat Pemasukan dan Pengeluaran'],
            ['role_name' => 'Bendahara 2', 'description' => 'Mencatat Pengeluaran'],
            ['role_name' => 'PHM', 'description' => 'Melaporkan Kencleng Harian'],
            ['role_name' => 'Panitia Khusus', 'description' => 'Melaporkan Kegiatan Khusus'],
        ]);
    }
}
