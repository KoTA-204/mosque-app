<?php

namespace Tests\Unit\Permission;

use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PermissionService();
    }

    public function test_create_permission_with_unique_code(): void
    {
        $permission = $this->service->create([
            'permission_code' => 'VIEW_USERS',
            'permission_name' => 'View Users',
            'module'          => 'users',
            'action'          => 'view',
            'is_active'       => true,
        ]);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertDatabaseHas('permissions', ['permission_code' => 'VIEW_USERS']);
    }

    public function test_update_permission_data(): void
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

    public function test_delete_permission_not_assigned_to_role(): void
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

    public function test_delete_permission_assigned_to_role_returns_error(): void
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

    /** getAll filter module + action. */
    public function test_get_all_filters_by_module_and_action(): void
    {
        Permission::create(['permission_code' => 'VIEW_USERS', 'permission_name' => 'View Users', 'module' => 'users', 'action' => 'view']);
        Permission::create(['permission_code' => 'CREATE_USERS', 'permission_name' => 'Create Users', 'module' => 'users', 'action' => 'create']);
        Permission::create(['permission_code' => 'VIEW_ROLES', 'permission_name' => 'View Roles', 'module' => 'roles', 'action' => 'view']);

        $result = $this->service->getAll(null, 'users', 'view');

        $this->assertCount(1, $result);
        $this->assertEquals('VIEW_USERS', $result->first()->permission_code);
    }

    /** getDistinctModules → daftar modul unik. */
    public function test_get_distinct_modules(): void
    {
        Permission::create(['permission_code' => 'VIEW_USERS', 'permission_name' => 'View Users', 'module' => 'users', 'action' => 'view']);
        Permission::create(['permission_code' => 'CREATE_USERS', 'permission_name' => 'Create Users', 'module' => 'users', 'action' => 'create']);
        Permission::create(['permission_code' => 'VIEW_ROLES', 'permission_name' => 'View Roles', 'module' => 'roles', 'action' => 'view']);

        $modules = $this->service->getDistinctModules();

        $this->assertEqualsCanonicalizing(['users', 'roles'], $modules->all());
    }
}