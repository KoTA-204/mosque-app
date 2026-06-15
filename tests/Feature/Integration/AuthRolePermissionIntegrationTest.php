<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integrasi: Autentikasi + Middleware permission + status aktif.
 *
 * Rute asli: auth.login.post (POST /login), auth.logout (POST /logout),
 * grup dashboard ['auth','active'] + permission:CODE.
 *
 * CATATAN ARSITEKTUR: User model mendefinisikan roles() sebagai belongsTo
 * (FK role_id → roles), bukan belongsToMany. Middleware CheckPermission
 * memanggil $user->roles()->whereHas('permissions', ...) yang secara teknis
 * memanggil BelongsTo builder — ini berfungsi di PostgreSQL karena Eloquent
 * meneruskan query ke builder dasar, namun tidak ideal secara semantik.
 * Behavior yang benar tetap tercapai karena:
 *  - hasPermission() di User model menggunakan pola yang sama
 *  - Test IT-F01-02 pass karena Panitia Khusus memang tidak punya VIEW_USERS
 */
class AuthRolePermissionIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /** IT-F01-01 (+): Login valid -> redirect & bisa membuka dashboard. */
    public function test_it_f01_01_login_valid_dapat_mengakses_dashboard(): void
    {
        $user = $this->buatUser($this->buatRole('Bendahara'));

        $this->post(route('auth.login.post'), [
            'email'    => $user->email,
            'password' => 'password', // default UserFactory
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);

        // dashboard.index hanya butuh auth + active (tanpa permission)
        $this->get(route('dashboard.index'))->assertOk();
    }

    /** IT-F01-02 (-): User tanpa permission VIEW_USERS -> 403. */
    public function test_it_f01_02_tanpa_permission_ditolak_403(): void
    {
        // role panitia-khusus tidak punya VIEW_USERS
        $user = $this->buatUser($this->buatRole('Panitia Khusus'));

        $this->actingAs($user)
            ->get(route('dashboard.users.index'))
            ->assertForbidden(); // CheckPermission -> abort(403)
    }

    /** IT-F01-03 (-): User inactive tidak boleh masuk dashboard (middleware 'active'). */
    public function test_it_f01_03_user_inactive_diblokir(): void
    {
        $user = $this->buatUser($this->buatRole('Bendahara'), ['status' => 'inactive']);

        $response = $this->actingAs($user)->get(route('dashboard.index'));

        // Middleware 'active' menolak (redirect/logout). Yang pasti: BUKAN 200.
        // CATATAN: routes/web.php yang aktif harus menggunakan ['auth', 'active'],
        // bukan hanya ['auth']. Verifikasi bahwa middleware group dashboard
        // menyertakan middleware 'active'.
        $this->assertNotSame(200, $response->getStatusCode());
    }

    /**
     * IT-F85-01 (+): Logout -> sesi berakhir (guest).
     *
     * CATATAN: Test ini memverifikasi dua hal yang dapat diobservasi:
     * (1) Response adalah redirect setelah POST /logout.
     * (2) User menjadi guest setelah logout (assertGuest).
     *
     * Session invalidate dan CSRF token regeneration adalah perilaku internal
     * Laravel Auth::logout() + session()->invalidate() + regenerateToken()
     * yang dilakukan di LoginController::destroy(). Kedua operasi ini tidak
     * diverifikasi secara eksplisit di level test integrasi karena sudah
     * dicakup oleh Laravel framework testing. Verifikasi assertGuest sudah
     * cukup membuktikan sesi berakhir.
     */
    public function test_it_f85_01_logout_mengakhiri_sesi(): void
    {
        $user = $this->buatUser($this->buatRole('Bendahara'));

        $this->actingAs($user)
            ->post(route('auth.logout'))
            ->assertRedirect();

        $this->assertGuest();
    }
}