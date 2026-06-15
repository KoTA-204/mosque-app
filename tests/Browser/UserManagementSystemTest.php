<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsSystemTestData;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Manajemen User
 * Create/Edit/Delete = MODAL AJAX (dimuat ke #modalContainer via fetch).
 * Form create: #createUserForm (name, email, role_id, status). Tombol "Tambah User" -> "Simpan & Kirim Email".
 * Tabel di #tableWrapper di-refresh via applyFilters().
 */
class UserManagementSystemTest extends DuskTestCase
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

    /** ST-F02-01 (+) Tambah User Baru oleh Admin */
    public function test_st_f02_01_tambah_user(): void
    {
        $bendaharaId = (string) $this->roles['bendahara']->id;
        $this->browse(function (Browser $b) use ($bendaharaId) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->press('Tambah User')          // memicu openCreateModal() (fetch)
                ->waitFor('#createUserForm')
                ->within('#createUserForm', function (Browser $m) use ($bendaharaId) {
                    $m->type('name', 'Siti Aisyah')
                        ->type('email', 'siti@mosque.test')
                        ->select('role_id', $bendaharaId)
                        ->select('status', 'active');
                })
                ->press('Simpan & Kirim Email')
                ->waitForText('Siti Aisyah')
                ->assertSee('Siti Aisyah');
        });
    }

    /** ST-F03-01 (-) Validasi Format Email Tidak Valid */
    public function test_st_f03_01_email_tidak_valid(): void
    {
        $bendaharaId = (string) $this->roles['bendahara']->id;
        $this->browse(function (Browser $b) use ($bendaharaId) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->press('Tambah User')
                ->waitFor('#createUserForm')
                ->within('#createUserForm', function (Browser $m) use ($bendaharaId) {
                    $m->type('name', 'Tes')
                        ->type('email', 'siti.bukan.email')
                        ->select('role_id', $bendaharaId)
                        ->select('status', 'active');
                })
                ->press('Simpan & Kirim Email')
                // submit AJAX -> error diisi via JS ke #err-email. Tunggu sampai elemen terisi teks.
                ->waitUntil('document.getElementById("err-email") && document.getElementById("err-email").textContent.trim().length > 0', 7)
                ->assertVisible('#err-email');
        });
    }

    /** ST-F05-01 (-) Email Duplikat Ditolak */
    public function test_st_f05_01_email_duplikat(): void
    {
        $bendaharaId = (string) $this->roles['bendahara']->id;
        $this->browse(function (Browser $b) use ($bendaharaId) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->press('Tambah User')
                ->waitFor('#createUserForm')
                ->within('#createUserForm', function (Browser $m) use ($bendaharaId) {
                    $m->type('name', 'Duplikat')
                        ->type('email', 'admin@masjid.id') // sudah terdaftar
                        ->select('role_id', $bendaharaId)
                        ->select('status', 'active');
                })
                ->press('Simpan & Kirim Email')
                ->waitFor('#err-email')
                ->assertVisible('#err-email'); // controller mengisi pesan "sudah digunakan"
        });
    }

    /** ST-F08-01 (+) Tampilkan Daftar User */
    public function test_st_f08_01_daftar_user(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->assertSee('admin@masjid.id')
                ->assertSee('bendahara1@masjid.id');
        });
    }

    /** ST-F09-01 (+) Edit Data User */
    public function test_st_f09_01_edit_user(): void
    {
        $target = $this->buatUser($this->roles['bendahara'], [
            'name' => 'Siti Aisyah', 'email' => 'siti2@mosque.test',
        ]);
        $this->browse(function (Browser $b) use ($target) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->script("openEditModal($target->id);");
            $b->waitFor('#editUserForm')
                ->within('#editUserForm', function (Browser $m) {
                    $m->clear('name')->type('name', 'Siti Aisyah Updated');
                })
                ->press('Simpan Perubahan')
                ->waitForText('Siti Aisyah Updated')
                ->assertSee('Siti Aisyah Updated');
        });
    }

    /** ST-F10-01 (+) Nonaktifkan User */
    public function test_st_f10_01_nonaktifkan_user(): void
    {
        $target = $this->buatUser($this->roles['bendahara'], [
            'name' => 'User Aktif', 'email' => 'aktif@mosque.test', 'status' => 'active',
        ]);
        $this->browse(function (Browser $b) use ($target) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->script("openEditModal($target->id);");
            $b->waitFor('#editUserForm')
                ->within('#editUserForm', function (Browser $m) {
                    $m->select('status', 'inactive');
                })
                ->press('Simpan Perubahan')
                ->waitForText('Tidak Aktif')
                ->assertSee('Tidak Aktif');
        });
    }

    /** ST-F11-01 (+) Aktifkan Kembali User */
    public function test_st_f11_01_aktifkan_user(): void
    {
        $target = $this->buatUser($this->roles['bendahara'], [
            'name' => 'User Nonaktif', 'email' => 'nonaktif2@mosque.test', 'status' => 'inactive',
        ]);
        $this->browse(function (Browser $b) use ($target) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/users')
                ->script("openEditModal($target->id);");
            $b->waitFor('#editUserForm')
                ->within('#editUserForm', function (Browser $m) {
                    $m->select('status', 'active');
                })
                ->press('Simpan Perubahan')
                ->waitForText('Aktif')
                ->assertSee('Aktif');
        });
    }

    /**
     * ST-F09-02 (-) Hapus User Bertransaksi Ditolak
     * Butuh user yang punya transaksi (FK restrictOnDelete). Pola data lihat ITD F09-01.
     * UI: openDeleteModal($id) -> tombol "Ya, Hapus" -> confirmDeleteUser() (fetch) -> showAlert error.
     */
    public function test_st_f09_02_hapus_user_bertransaksi(): void
    {
        $this->markTestIncomplete('TODO data: butuh user dengan riwayat transaksi (FK restrictOnDelete).');
    }
}
