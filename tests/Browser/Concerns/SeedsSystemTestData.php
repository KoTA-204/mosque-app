<?php

namespace Tests\Browser\Concerns;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

/**
 * Helper seeding data untuk System Test (Black Box) via Dusk.
 *
 * PENTING — kolom asli skema MosQue:
 *  - roles.role_name        (BUKAN "nama")
 *  - permissions.permission_code / permission_name / module / action
 *  - users.role_id / name / email / password / status (active|inactive)
 *
 * seedPeranDasar() menjalankan DatabaseSeeder asli agar role, permission,
 * menu, dan akun standar (admin@masjid.id dst, password "password") tersedia
 * lengkap — sehingga middleware permission tidak salah memblokir halaman.
 */
trait SeedsSystemTestData
{
    /** Cari/siapkan permission berdasarkan code (umumnya sudah dibuat PermissionSeeder). */
    protected function buatPermission(string $code): Permission
    {
        return Permission::firstOrCreate(
            ['permission_code' => $code],
            ['permission_name' => $code, 'module' => 'custom', 'action' => 'view']
        );
    }

    /** Cari/buat role berdasarkan role_name + assign permission codes. */
    protected function buatRole(string $nama, array $codes = []): Role
    {
        $role = Role::firstOrCreate(
            ['role_name' => $nama],
            ['description' => $nama]
        );
        foreach ($codes as $code) {
            $role->permissions()->syncWithoutDetaching([$this->buatPermission($code)->id]);
        }
        return $role->fresh();
    }

    /** Buat user baru dengan kolom yang benar (tanpa factory). */
    protected function buatUser(Role $role, array $attrs = []): User
    {
        return User::create(array_merge([
            'role_id'  => $role->id,
            'name'     => 'User ' . uniqid(),
            'email'    => 'user_' . uniqid() . '@mosque.test',
            'status'   => 'active',
            'password' => Hash::make('password'),
        ], $attrs));
    }

    /**
     * Jalankan DatabaseSeeder asli, lalu kembalikan role-role utama.
     * Akun standar yang tersedia (password "password"):
     *   admin@masjid.id (Super Admin), bendahara1@masjid.id (Bendahara 1),
     *   bendahara2@masjid.id (Bendahara 2), phm@masjid.id (PHM),
     *   panitia@masjid.id (Panitia Khusus).
     */
    protected function seedPeranDasar(): array
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        return [
            'admin'     => Role::where('role_name', 'Super Admin')->firstOrFail(),
            'bendahara' => Role::where('role_name', 'Bendahara 1')->firstOrFail(),
            'phm'       => Role::where('role_name', 'PHM')->firstOrFail(),
            'panitia'   => Role::where('role_name', 'Panitia Khusus')->firstOrFail(),
        ];
    }

    /** Ambil user seeded berdasarkan email. */
    protected function userByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }
}
