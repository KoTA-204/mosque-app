<?php

namespace Tests\Unit\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create([
            'role_name'   => 'Super Admin',
            'description' => 'Admin',
        ]);

        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@masjid.id',
            'password' => bcrypt('password'),
            'role_id'  => $this->role->id,
            'status'   => 'active',
        ]);
    }

    /** Login valid + status aktif → redirect dashboard, terautentikasi. */
    public function test_login_valid_credentials_redirects_to_dashboard(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'admin@masjid.id',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticated();
    }

    /** Password salah → redirect back + error email + guest. */
    public function test_login_wrong_password_returns_error(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'admin@masjid.id',
            'password' => 'salah123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** Email tidak terdaftar → error email + guest. */
    public function test_login_unregistered_email_returns_error(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'tidakada@masjid.id',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** BARU: kredensial benar TAPI status inactive → ditolak di level login + guest. */
    public function test_login_inactive_user_is_rejected(): void
    {
        User::create([
            'name'     => 'User Nonaktif',
            'email'    => 'nonaktif@masjid.id',
            'password' => bcrypt('password'),
            'role_id'  => $this->role->id,
            'status'   => 'inactive',
        ]);

        $response = $this->post(route('auth.login.post'), [
            'email'    => 'nonaktif@masjid.id',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** Validasi: email & password wajib. */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post(route('auth.login.post'), []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /** Logout → invalidate sesi + redirect login + guest. */
    public function test_logout_invalidates_session_and_redirects(): void
    {
        $user = User::where('email', 'admin@masjid.id')->first();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('auth.logout'));

        $response->assertRedirect(route('auth.login'));
        $this->assertGuest();
    }
}