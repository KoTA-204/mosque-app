<?php

namespace Tests\Unit\Role;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleService();
    }

    /**
     * UT-F64-01
     * Deskripsi : Buat role baru dengan nama unik
     * Expected  : Role tersimpan di DB dengan data yang benar
     */
    public function test_UT_F64_01_create_role_with_unique_name(): void
    {
        $data = [
            'role_name'   => 'Bendahara 1',
            'description' => 'Bendahara utama',
        ];

        $role = $this->service->create($data);

        $this->assertDatabaseHas('roles', [
            'role_name' => 'Bendahara 1',
        ]);
        $this->assertInstanceOf(Role::class, $role);
    }

    /**
     * UT-F64-02
     * Deskripsi : Update nama dan deskripsi role yang sudah ada
     * Expected  : Data role terupdate di DB
     */
    public function test_UT_F64_02_update_role_name_and_description(): void
    {
        $role = Role::create([
            'role_name'   => 'Nama Lama',
            'description' => 'Deskripsi lama',
        ]);

        $this->service->update($role, [
            'role_name'   => 'Nama Baru',
            'description' => 'Deskripsi baru',
        ]);

        $this->assertDatabaseHas('roles', [
            'id'          => $role->id,
            'role_name'   => 'Nama Baru',
            'description' => 'Deskripsi baru',
        ]);
    }

    /**
     * UT-F64-03
     * Deskripsi : Hapus role yang tidak memiliki user terkait
     * Expected  : Role terhapus dari DB, return true
     */
    public function test_UT_F64_03_delete_role_without_users(): void
    {
        $role = Role::create([
            'role_name'   => 'Role Kosong',
            'description' => 'Tidak ada user',
        ]);

        $result = $this->service->delete($role);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /**
     * UT-F64-04
     * Deskripsi : Hapus role yang masih dipakai oleh user
     * Expected  : Gagal, return string error message
     */
    public function test_UT_F64_04_delete_role_with_users_returns_error_string(): void
    {
        $role = Role::create([
            'role_name'   => 'Role Terpakai',
            'description' => '-',
        ]);

        // Assign user ke role ini
        User::create([
            'name'     => 'Test User',
            'email'    => 'test@masjid.id',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $result = $this->service->delete($role);

        $this->assertIsString($result);
        $this->assertStringContainsString('dipakai', $result);
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }
}