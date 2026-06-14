<?php

namespace Tests\Unit\Permission;

use Tests\TestCase;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PermissionService();
    }

    /**
     * UT-F65-01
     * Deskripsi : Buat permission baru dengan kode unik
     * Expected  : Permission tersimpan di DB
     */
    public function test_UT_F65_01_create_permission_with_unique_code(): void
    {
        $data = [
            'permission_code' => 'VIEW_USERS',
            'permission_name' => 'View Users',
            'module'          => 'users',
            'action'          => 'view',
            'is_active'       => true,
        ];

        $permission = $this->service->create($data);

        $this->assertDatabaseHas('permissions', [
            'permission_code' => 'VIEW_USERS',
        ]);
    }

    /**
     * UT-F65-02
     * Deskripsi : Update permission yang sudah ada
     * Expected  : Data terupdate di DB
     */
    public function test_UT_F65_02_update_permission_data(): void
    {
        $permission = Permission::create([
            'permission_code' => 'VIEW_USERS',
            'permission_name' => 'Old Name',
            'module'          => 'users',
            'action'          => 'view',
        ]);

        $this->service->update($permission, [
            'permission_name' => 'View Users Updated',
            'module'          => 'users',
            'action'          => 'view',
        ]);

        $this->assertDatabaseHas('permissions', [
            'id'              => $permission->id,
            'permission_name' => 'View Users Updated',
        ]);
    }

    /**
     * UT-F65-03
     * Deskripsi : Hapus permission yang tidak dipakai role
     * Expected  : Permission terhapus, return true
     */
    public function test_UT_F65_03_delete_permission_not_assigned_to_role(): void
    {
        $permission = Permission::create([
            'permission_code' => 'DELETE_USERS',
            'permission_name' => 'Delete Users',
            'module'          => 'users',
            'action'          => 'delete',
        ]);

        $result = $this->service->delete($permission);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    /**
     * UT-F65-04
     * Deskripsi : Hapus permission yang masih dipakai oleh role
     * Expected  : Gagal, return string error
     */
    public function test_UT_F65_04_delete_permission_assigned_to_role_returns_error(): void
    {
        $permission = Permission::create([
            'permission_code' => 'VIEW_USERS',
            'permission_name' => 'View Users',
            'module'          => 'users',
            'action'          => 'view',
        ]);

        $role = Role::create(['role_name' => 'Test Role', 'description' => '-']);
        $role->permissions()->sync([$permission->id]);

        $result = $this->service->delete($permission);

        $this->assertIsString($result);
        $this->assertStringContainsString('dipakai', $result);
    }
}