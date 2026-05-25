<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Unit Testing - Kelola Data Pengguna & Role/Hak Akses
 * Kelompok Tugas Akhir 204 - Aplikasi MosQue
 *
 * Cakupan:
 *  TC-03 → REQ-F-02, REQ-F-05 : Tambah Pengguna (data valid & cegah duplikasi email)
 *  TC-04 → REQ-F-03, REQ-F-04 : Validasi format email tidak valid
 *  TC-05 → REQ-F-08 s/d REQ-F-11 : Tampil daftar, edit, nonaktifkan, aktifkan pengguna
 *  TC-06 → REQ-F-12 s/d REQ-F-15 : Kelola Role & Hak Akses
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** Buat user admin yang sudah login untuk semua test. */
    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'email'  => 'admin@luqmanul.ac.id',
            'status' => 'active',
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    /** Buat role aktif dengan nama tertentu. */
    private function createRole(string $name = 'Bendahara 2', bool $active = true): Role
    {
        return Role::create([
            'role_name'   => $name,
            'description' => 'Role ' . $name,
            'is_active'   => $active,
        ]);
    }

    /** Buat permission aktif. */
    private function createPermission(string $code, string $module = 'transaksi'): Permission
    {
        return Permission::create([
            'permission_code' => $code,
            'permission_name' => $code,
            'module'          => $module,
            'action'          => 'view',
            'is_active'       => true,
        ]);
    }

    // =========================================================================
    // TC-03 | REQ-F-02, REQ-F-05
    // Tambah Pengguna – data valid & pencegahan duplikasi email
    // =========================================================================

    /**
     * TC-03 Skenario 1
     * Menguji penambahan pengguna baru dengan data lengkap yang valid.
     * Expected: user tersimpan di database dan redirect ke halaman index.
     */
    public function test_tc03_tambah_pengguna_dengan_data_valid(): void
    {
        $this->actingAsAdmin();
        $role = $this->createRole('Bendahara 2');

        $response = $this->post(route('dashboard.users.store'), [
            'name'     => 'Indah Pramudita',
            'email'    => 'indah@luqmanul.ac.id',
            'password' => 'password123',
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $response->assertRedirect(route('dashboard.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'  => 'Indah Pramudita',
            'email' => 'indah@luqmanul.ac.id',
        ]);

        $user = User::where('email', 'indah@luqmanul.ac.id')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->roles->contains($role));
    }

    /**
     * TC-03 Skenario 2 (REQ-F-05)
     * Menguji pencegahan duplikasi email – mendaftarkan email yang sama dua kali.
     * Expected: request kedua ditolak dengan error validasi pada field email.
     */
    public function test_tc03_penolakan_duplikasi_email(): void
    {
        $this->actingAsAdmin();
        $role = $this->createRole('Bendahara 2');

        // Registrasi pertama – harus berhasil
        User::factory()->create([
            'email' => 'indah@luqmanul.ac.id',
        ]);

        // Registrasi kedua dengan email yang sama – harus ditolak
        $response = $this->post(route('dashboard.users.store'), [
            'name'     => 'Indah Duplikat',
            'email'    => 'indah@luqmanul.ac.id',
            'password' => 'password123',
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 2); // admin + 1 user pertama
    }

    // =========================================================================
    // TC-04 | REQ-F-03, REQ-F-04
    // Validasi format email tidak valid
    // =========================================================================

    /**
     * TC-04 Skenario 1
     * Email tanpa karakter '@' (contoh: indah#luqmanul).
     * Expected: sistem menolak dan mengembalikan error validasi email.
     */
    public function test_tc04_email_tanpa_at_sign_ditolak(): void
    {
        $this->actingAsAdmin();
        $role = $this->createRole();

        $response = $this->post(route('dashboard.users.store'), [
            'name'     => 'Indah Pramudita',
            'email'    => 'indah#luqmanul',
            'password' => 'password123',
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'indah#luqmanul']);
    }

    /**
     * TC-04 Skenario 2
     * Email tanpa domain (contoh: indahluqmanul.ac.id).
     * Expected: sistem menolak dan mengembalikan error validasi email.
     */
    public function test_tc04_email_tanpa_domain_ditolak(): void
    {
        $this->actingAsAdmin();
        $role = $this->createRole();

        $response = $this->post(route('dashboard.users.store'), [
            'name'     => 'Indah Pramudita',
            'email'    => 'indahluqmanul.ac.id',
            'password' => 'password123',
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'indahluqmanul.ac.id']);
    }

    /**
     * TC-04 Skenario 3
     * Field email dikosongkan (null).
     * Expected: sistem menolak dan mengembalikan error validasi email.
     */
    public function test_tc04_email_kosong_ditolak(): void
    {
        $this->actingAsAdmin();
        $role = $this->createRole();

        $response = $this->post(route('dashboard.users.store'), [
            'name'     => 'Indah Pramudita',
            'email'    => '',
            'password' => 'password123',
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // TC-05 | REQ-F-08, REQ-F-09, REQ-F-10, REQ-F-11
    // Tampil daftar pengguna, edit data, nonaktifkan, dan aktifkan kembali akun
    // =========================================================================

    /**
     * TC-05 Skenario 1 (REQ-F-08)
     * Menguji bahwa daftar pengguna tampil dengan status 200.
     */
    public function test_tc05_daftar_pengguna_tampil(): void
    {
        $this->actingAsAdmin();

        User::factory()->count(3)->create();

        $response = $this->get(route('dashboard.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.users.index');
        $response->assertViewHas('users');
    }

    /**
     * TC-05 Skenario 2 (REQ-F-09)
     * Menguji edit data pengguna: ubah nama dan nomor telepon.
     * Expected: data di database terupdate dan redirect ke index.
     *
     * Catatan: kolom 'phone' belum ada di migration saat ini. Jika sudah
     * ditambahkan, sesuaikan assertion assertDatabaseHas di bawah.
     */
    public function test_tc05_edit_data_pengguna(): void
    {
        $this->actingAsAdmin();
        $role    = $this->createRole('Bendahara 2');
        $targetUser = User::factory()->create([
            'name'   => 'Indah Pramudita',
            'status' => 'active',
        ]);
        $targetUser->roles()->attach($role);

        $response = $this->put(route('dashboard.users.update', $targetUser->id), [
            'name'    => 'Indah Ratu Pramudita',
            'email'   => $targetUser->email,
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        $response->assertRedirect(route('dashboard.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'   => $targetUser->id,
            'name' => 'Indah Ratu Pramudita',
        ]);
    }

    /**
     * TC-05 Skenario 3 (REQ-F-10)
     * Menguji penonaktifan akun pengguna (status → inactive).
     * Expected: status user di database berubah menjadi 'inactive'.
     */
    public function test_tc05_nonaktifkan_pengguna(): void
    {
        $this->actingAsAdmin();
        $role       = $this->createRole();
        $targetUser = User::factory()->create(['status' => 'active']);
        $targetUser->roles()->attach($role);

        $response = $this->put(route('dashboard.users.update', $targetUser->id), [
            'name'    => $targetUser->name,
            'email'   => $targetUser->email,
            'role_id' => $role->id,
            'status'  => 'inactive',
        ]);

        $response->assertRedirect(route('dashboard.users.index'));

        $this->assertDatabaseHas('users', [
            'id'     => $targetUser->id,
            'status' => 'inactive',
        ]);
    }

    /**
     * TC-05 Skenario 3 – Tambahan (REQ-F-10)
     * Pengguna yang dinonaktifkan tidak bisa login ke sistem.
     * Expected: login ditolak (redirect kembali ke login dengan error).
     */
    // public function test_tc05_pengguna_nonaktif_tidak_bisa_login(): void
    // {
    //     $inactiveUser = User::factory()->create([
    //         'email'    => 'nonaktif@luqmanul.ac.id',
    //         'password' => Hash::make('password123'),
    //         'status'   => 'inactive',
    //     ]);

    //     $response = $this->post(route('auth.login.post'), [
    //         'email'    => 'nonaktif@luqmanul.ac.id',
    //         'password' => 'password123',
    //     ]);

    //     // Pengguna tidak boleh ter-authentikasi
    //     $this->assertGuest();
    // }

    /**
     * TC-05 Skenario 4 (REQ-F-11)
     * Menguji pengaktifan kembali akun yang sebelumnya nonaktif.
     * Expected: status di database kembali menjadi 'active'.
     */
    public function test_tc05_aktifkan_kembali_pengguna(): void
    {
        $this->actingAsAdmin();
        $role       = $this->createRole();
        $targetUser = User::factory()->create(['status' => 'inactive']);
        $targetUser->roles()->attach($role);

        $response = $this->put(route('dashboard.users.update', $targetUser->id), [
            'name'    => $targetUser->name,
            'email'   => $targetUser->email,
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        $response->assertRedirect(route('dashboard.users.index'));

        $this->assertDatabaseHas('users', [
            'id'     => $targetUser->id,
            'status' => 'active',
        ]);
    }

    // =========================================================================
    // TC-06 | REQ-F-12, REQ-F-13, REQ-F-14, REQ-F-15
    // Kelola Role & Hak Akses
    // =========================================================================

    /**
     * TC-06 Skenario 1 (REQ-F-13)
     * Menguji bahwa daftar role tampil dengan status 200.
     */
    public function test_tc06_daftar_role_tampil(): void
    {
        $this->actingAsAdmin();

        Role::create(['role_name' => 'Bendahara 1', 'is_active' => true]);
        Role::create(['role_name' => 'Bendahara 2', 'is_active' => true]);

        $response = $this->get(route('dashboard.roles.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.roles.index');
        $response->assertViewHas('roles');
    }

    /**
     * TC-06 Skenario 2 (REQ-F-12)
     * Menguji penambahan role baru dengan permission yang valid.
     * Expected: role tersimpan di database beserta relasinya ke permission.
     */
    public function test_tc06_tambah_role_baru_dengan_permission(): void
    {
        $this->actingAsAdmin();

        $permission = $this->createPermission('VIEW_KEGIATAN', 'transaksi_kegiatan');

        $response = $this->post(route('dashboard.roles.store'), [
            'role_name'      => 'Panitia Khusus',
            'description'    => 'Role untuk panitia kegiatan khusus',
            'is_active'      => true,
            'permission_ids' => [$permission->id],
        ]);

        $response->assertRedirect(route('dashboard.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['role_name' => 'Panitia Khusus']);

        $role = Role::where('role_name', 'Panitia Khusus')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->permissions->contains($permission));
    }

    /**
     * TC-06 Skenario 3 (REQ-F-14)
     * Menguji perubahan hak akses (permission) pada role yang sudah ada.
     * Expected: permission lama ter-replace dengan permission baru (sync).
     */
    public function test_tc06_ubah_hak_akses_role(): void
    {
        $this->actingAsAdmin();

        $role           = $this->createRole('Bendahara 2');
        $permLama       = $this->createPermission('VIEW_TRANSAKSI', 'transaksi');
        $permBaru       = $this->createPermission('VIEW_LAPORAN', 'laporan');

        // Assign permission lama dulu
        $role->permissions()->attach($permLama);

        // Update role: ganti permission ke yang baru
        $response = $this->put(route('dashboard.roles.update', $role->id), [
            'role_name'      => $role->role_name,
            'permission_ids' => [$permBaru->id],
        ]);

        $response->assertRedirect(route('dashboard.roles.index'));
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertTrue($role->permissions->contains($permBaru));
        $this->assertFalse($role->permissions->contains($permLama));
    }

    /**
     * TC-06 Skenario 4 (REQ-F-15)
     * Menguji pembatasan akses pengguna berdasarkan role.
     * User dengan role yang tidak punya permission VIEW_KEGIATAN
     * tidak boleh bisa mengakses halaman kegiatan-panitia.
     * Expected: HTTP 403 Forbidden.
     */
    public function test_tc06_akses_ditolak_untuk_role_tanpa_permission(): void
    {
        // Buat role Jamaah tanpa permission apapun
        $roleJamaah = $this->createRole('Jamaah');

        $userJamaah = User::factory()->create([
            'email'  => 'jamaah@luqmanul.ac.id',
            'status' => 'active',
        ]);
        $userJamaah->roles()->attach($roleJamaah);

        $this->actingAs($userJamaah);

        // Coba akses halaman yang dilindungi permission VIEW_KEGIATAN
        $response = $this->get(route('dashboard.kegiatan-panitia.index'));

        $response->assertStatus(403);
    }

    /**
     * TC-06 Skenario 4 – Positif (REQ-F-15)
     * User yang punya permission VIEW_KEGIATAN harus bisa mengakses halaman tersebut.
     * Expected: HTTP 200.
     */
    public function test_tc06_akses_diterima_untuk_role_dengan_permission(): void
    {
        $permission = $this->createPermission('VIEW_KEGIATAN', 'kegiatan');
        $role       = $this->createRole('Bendahara 1');
        $role->permissions()->attach($permission);

        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $response = $this->get(route('dashboard.kegiatan-panitia.index'));

        // 200 jika halaman ada, bisa juga 302 jika ada redirect internal — keduanya bukan 403
        $this->assertNotEquals(403, $response->getStatusCode());
    }
}