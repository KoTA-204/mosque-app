<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat role dulu (diperlukan untuk User)
        $role = Role::create([
            'role_name'   => 'Super Admin',
            'description' => 'Admin',
        ]);

        // Buat user aktif
        $this->activeUser = User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@masjid.id',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);
    }

    /**
     * UT-F01-01
     * Deskripsi : Login dengan email dan password yang valid
     * Expected  : Redirect ke dashboard.index, user terauthentikasi
     */
    public function test_UT_F01_01_login_valid_credentials_redirects_to_dashboard(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'admin@masjid.id',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticated();
    }

    /**
     * UT-F01-02
     * Deskripsi : Login dengan password yang salah
     * Expected  : Redirect back, ada error pada field email,
     *             user tidak terauthentikasi
     */
    public function test_UT_F01_02_login_wrong_password_returns_error(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'admin@masjid.id',
            'password' => 'salah123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * UT-F01-03
     * Deskripsi : Login dengan email yang tidak terdaftar
     * Expected  : Redirect back dengan error field email
     */
    public function test_UT_F01_03_login_unregistered_email_returns_error(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'tidakada@masjid.id',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * UT-F85-01
     * Deskripsi : User yang login melakukan logout
     * Expected  : Session di-invalidate, token di-regenerate,
     *             redirect ke halaman login, user tidak terauthentikasi
     */
    public function test_UT_F85_01_logout_invalidates_session_and_redirects(): void
    {
        // Login dulu
        $this->actingAs($this->activeUser);
        $this->assertAuthenticated();

        // Logout
        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('auth.login'));
        $this->assertGuest();
    }

    public function test_UT_F07_01_session_lifetime_120_menit(): void
    {
        $this->assertEquals(120, config('session.lifetime'));
    }
}