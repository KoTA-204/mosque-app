<?php

namespace Tests\Feature\Integration;

use App\Models\Permission;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integrasi: pemberian & pencabutan permission pada role berdampak ke akses rute.
 */
class RolePermissionAksesIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /** IT-F12-01 (+): Menambahkan permission ke role membuka akses rute terproteksi. */
    public function test_it_f12_01_assign_permission_membuka_akses(): void
    {
        $role = $this->buatRole('Bendahara'); // belum punya VIEW_TRANSAKSI
        $user = $this->buatUser($role);

        // Sebelum diberi permission -> 403
        $this->actingAs($user)
            ->get(route('dashboard.transaksi.index'))
            ->assertForbidden();

        // Berikan permission (simulasi RoleController update via permission_ids -> pivot)
        $perm = $this->buatPermission('VIEW_TRANSAKSI');
        $role->permissions()->syncWithoutDetaching([$perm->id]);

        $this->assertTrue($user->fresh()->hasPermission('VIEW_TRANSAKSI'));

        // Sesudah diberi permission -> 200
        $this->actingAs($user->fresh())
            ->get(route('dashboard.transaksi.index'))
            ->assertOk();
    }

    /** IT-F14-01 (-): Mencabut permission menutup akses (403). */
    public function test_it_f14_01_cabut_permission_menutup_akses(): void
    {
        $role = $this->buatRole('Bendahara', ['VIEW_TRANSAKSI']);
        $user = $this->buatUser($role);

        $this->actingAs($user)
            ->get(route('dashboard.transaksi.index'))
            ->assertOk();

        // Cabut permission
        $permId = Permission::where('permission_code', 'VIEW_TRANSAKSI')->value('id');
        $role->permissions()->detach($permId);

        $this->actingAs($user->fresh())
            ->get(route('dashboard.transaksi.index'))
            ->assertForbidden();
    }
}
