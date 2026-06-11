<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            MenuSeeder::class,
            UserSeeder::class,
            PermissionRoleSeeder::class,
            KategoriTransaksiSeeder::class,
            DompetSeeder::class,
            KegiatanSeeder::class,
            TransaksiSeeder::class,
            KenclengSeeder::class,
            KenclengDetailSeeder::class,
            BuktiTransaksiSeeder::class,
            KategoriAkunSeeder::class,
            AkunSeeder::class,
            AsetSeeder::class,
            PeriodeSeeder::class,
            TransaksiKegiatanSeeder::class,
        ]);
    }
}
