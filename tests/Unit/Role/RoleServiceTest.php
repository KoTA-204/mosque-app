<?php

namespace Tests\Unit\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoleService();
    }

    /** Buat role baru → tersimpan. */
    public function test_create_role_with_unique_name(): void
    {
        $role = $this->service->create([
            'role_name'   => 'Bendahara 1',
            'description' => 'Bendahara utama',
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertDatabaseHas('roles', ['role_name' => 'Bendahara 1']);
    }

    /** Buat role + sinkron permission_ids → permission tertaut. */
    public function test_create_role_syncs_permissions(): void
    {
        $p1 = Permission::create(['permission_code' => 'VIEW_USERS', 'permission_name' => 'View Users', 'module' => 'users', 'action' => 'view']);
        $p2 = Permission::create(['permission_code' => 'CREATE_USERS', 'permission_name' => 'Create Users', 'module' => 'users', 'action' => 'create']);

        $role = $this->service->create([
            'role_name'      => 'Operator',
            'description'    => '-',
            'permission_ids' => [$p1->id, $p2->id],
        ]);

        $this->assertEqualsCanonicalizing(
            [$p1->id, $p2->id],
            $role->permissions()->pluck('permissions.id')->all()
        );
    }

    /** Update nama & deskripsi role. */
    public function test_update_role_name_and_description(): void
    {
        $role = Role::create(['role_name' => 'Nama Lama', 'description' => 'Deskripsi lama']);

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

    /** Hapus role tanpa user → true + terhapus. */
    public function test_delete_role_without_users(): void
    {
        $role = Role::create(['role_name' => 'Role Kosong', 'description' => '-']);

        $result = $this->service->delete($role);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    /** Hapus role yang masih dipakai user → string error + tidak terhapus. */
    public function test_delete_role_with_users_returns_error_string(): void
    {
        $role = Role::create(['role_name' => 'Role Terpakai', 'description' => '-']);

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

    /**
     * getAll tanpa search → paginator berisi seluruh role.
     * Catatan: cabang pencarian memakai `ilike` (Postgres-only) sehingga
     * tidak dapat diuji pada koneksi sqlite (lihat catatan dev).
     */
    public function test_get_all_tanpa_search_mengembalikan_seluruh_role(): void
    {
        Role::create(['role_name' => 'Bendahara']);
        Role::create(['role_name' => 'Ketua']);

        $hasil = $this->service->getAll(null);

        $this->assertGreaterThanOrEqual(2, $hasil->total());
    }
}