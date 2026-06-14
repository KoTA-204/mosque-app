<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['role_name' => 'Super Admin', 'description' => '-']);

        $this->user = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@masjid.id',
            'password' => bcrypt('password'),
            'role_id'  => $role->id,
            'status'   => 'active',
        ]);
    }

    /**
     * UT-F06-01
     * Deskripsi : Request kirim reset link ke email terdaftar
     * Expected  : Redirect ke halaman check-email,
     *             session reset_email tersimpan
     */
    public function test_UT_F06_01_send_reset_link_to_registered_email(): void
    {
        $response = $this->post(route('auth.forgot-password.post'), [
            'email' => 'admin@masjid.id',
        ]);

        $response->assertRedirect(route('auth.check-email'));
        $this->assertEquals('admin@masjid.id', session('reset_email'));
    }

    /**
     * UT-F06-02
     * Deskripsi : Request reset link dengan email yang tidak terdaftar
     * Expected  : Redirect back dengan error pada field email
     */
    public function test_UT_F06_02_send_reset_link_to_unregistered_email_returns_error(): void
    {
        $response = $this->post(route('auth.forgot-password.post'), [
            'email' => 'tidakada@masjid.id',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    /**
     * UT-F06-03
     * Deskripsi : Halaman check-email ditampilkan saat ada session reset_email
     * Expected  : Response 200, view check-email ditampilkan
     */
    public function test_UT_F06_03_check_email_page_displayed_when_session_exists(): void
    {
        $response = $this->withSession(['reset_email' => 'admin@masjid.id'])
                         ->get(route('auth.check-email'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.auth.check-email');
    }

    /**
     * UT-F06-04
     * Deskripsi : Akses halaman check-email tanpa session reset_email
     * Expected  : Redirect ke halaman forgot-password
     */
    public function test_UT_F06_04_check_email_redirects_if_no_session(): void
    {
        $response = $this->get(route('auth.check-email'));

        $response->assertRedirect(route('auth.forgot-password'));
    }
}