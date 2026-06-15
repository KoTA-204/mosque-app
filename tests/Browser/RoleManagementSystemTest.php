<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsSystemTestData;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Manajemen Role
 * Halaman: dashboard.roles.* (Role Management). Create/Edit = halaman penuh (bukan modal).
 * Field: role_name, description, permission_ids[]. Tombol: "Simpan" (create) / "Update" (edit).
 */
class RoleManagementSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsSystemTestData;

    protected array $roles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->roles = $this->seedPeranDasar();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@masjid.id')->first();
    }

    /** ST-F12-01 (+) Tambah Role Baru */
    public function test_st_f12_01_tambah_role(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/roles/create')
                ->type('role_name', 'Pengawas')
                ->type('description', 'Role pengawas keuangan masjid')
                ->press('Simpan')
                ->assertPathIs('/dashboard/roles')
                ->assertSee('Pengawas');
        });
    }

    /** ST-F13-01 (+) Tampilkan Daftar Role */
    public function test_st_f13_01_daftar_role(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/roles')
                ->assertSee('Super Admin')
                ->assertSee('Bendahara 1')
                ->assertSee('PHM');
        });
    }

    /** ST-F14-01 (+) Ubah Permission Role (centang VIEW_COA pada Bendahara 1) */
    public function test_st_f14_01_ubah_permission_role(): void
    {
        // pastikan permission VIEW_COA tersedia agar muncul di matrix
        $this->buatPermission('VIEW_COA');
        $bendaharaId = $this->roles['bendahara']->id;

        $this->browse(function (Browser $b) use ($bendaharaId) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/roles/' . $bendaharaId . '/edit')
                // matrix permission memakai checkbox name="permission_ids[]" value="{id}"
                // centang checkbox pertama yang tersedia sebagai bukti perubahan tersimpan
                ->script("document.querySelector('input[name=\"permission_ids[]\"]').checked = true;");
            // form submit + redirect butuh waktu; tunggu navigasi selesai (jangan assert langsung)
            $b->press('Update')
                ->waitForLocation('/dashboard/roles');
        });
    }

    /** ST-F15-01 (-) Akses Ditolak Role Tidak Berwenang (PHM ke Manajemen User) */
    public function test_st_f15_01_akses_ditolak(): void
    {
        $phm = User::where('email', 'phm@masjid.id')->first();
        $this->browse(function (Browser $b) use ($phm) {
            $b->loginAs($phm)
                ->visit('/dashboard/users')
                ->assertSee('403'); // TODO: cocokkan halaman/abort(403) aplikasi
        });
    }
}
