<?php

namespace Tests\Unit\Menu;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role        = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    /**
     * UT-F66-01
     * Menyimpan menu parent baru
     */
    public function test_UT_F66_01_store_parent_menu(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.menus.store'), [
                             'menu_name'  => 'Master Data',
                             'is_active'  => true,
                             'sort_order' => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menus', ['menu_name' => 'Master Data']);
    }

    /**
     * UT-F66-02
     * Menyimpan sub-menu dengan parent
     */
    public function test_UT_F66_02_store_submenu(): void
    {
        $parent = Menu::create([
            'menu_name'  => 'Master Data',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.menus.store'), [
                             'menu_name'  => 'Kategori',
                             'parent_id'  => $parent->id,
                             'route_name' => 'dashboard.kategori-transaksi.index',
                             'is_active'  => true,
                             'sort_order' => 1,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menus', [
            'menu_name' => 'Kategori',
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * UT-F66-03
     * Menghapus menu yang tidak memiliki child berhasil
     */
    public function test_UT_F66_03_delete_menu_no_children(): void
    {
        $menu = Menu::create([
            'menu_name'  => 'Menu Hapus',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.menus.destroy', $menu));

        $response->assertRedirect();
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    /**
     * UT-F66-04
     * Menghapus menu yang masih memiliki child diblokir
     */
    public function test_UT_F66_04_delete_menu_with_children_blocked(): void
    {
        $parent = Menu::create([
            'menu_name'  => 'Parent Menu',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        // Buat child menu
        Menu::create([
            'menu_name'  => 'Child Menu',
            'parent_id'  => $parent->id,
            'route_name' => 'dashboard.index',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.menus.destroy', $parent));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('menus', ['id' => $parent->id]);
    }

    /**
     * UT-F66-05
     * Mengupdate menu berhasil menyimpan data baru
     */
    public function test_UT_F66_05_update_menu(): void
    {
        $menu = Menu::create([
            'menu_name'  => 'Lama',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
                         ->put(route('dashboard.menus.update', $menu), [
                             'menu_name'  => 'Nama Baru',
                             'is_active'  => true,
                             'sort_order' => 2,
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('menus', [
            'id'        => $menu->id,
            'menu_name' => 'Nama Baru',
        ]);
    }
}