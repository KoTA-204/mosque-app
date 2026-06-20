<?php

namespace Tests\Unit\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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

    /** Kirim reset link ke email terdaftar → redirect check-email + session reset_email. */
    public function test_send_reset_link_to_registered_email(): void
    {
        Notification::fake();

        $response = $this->post(route('auth.forgot-password.post'), [
            'email' => 'admin@masjid.id',
        ]);

        $response->assertRedirect(route('auth.check-email'));
        $this->assertEquals('admin@masjid.id', session('reset_email'));
        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    /** Email tidak terdaftar → redirect back + error email. */
    public function test_send_reset_link_to_unregistered_email_returns_error(): void
    {
        $response = $this->post(route('auth.forgot-password.post'), [
            'email' => 'tidakada@masjid.id',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }

    /** Validasi email wajib. */
    public function test_send_reset_link_requires_email(): void
    {
        $response = $this->post(route('auth.forgot-password.post'), []);
        $response->assertSessionHasErrors('email');
    }

    /** Halaman check-email tampil saat ada session reset_email. */
    public function test_check_email_page_displayed_when_session_exists(): void
    {
        $response = $this->withSession(['reset_email' => 'admin@masjid.id'])
            ->get(route('auth.check-email'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.auth.check-email');
    }

    /** Akses check-email tanpa session → redirect forgot-password. */
    public function test_check_email_redirects_if_no_session(): void
    {
        $response = $this->get(route('auth.check-email'));
        $response->assertRedirect(route('auth.forgot-password'));
    }

    /** Resend reset email: dengan session reset_email → POST ke API & flash success. */
    public function test_resend_email_with_session_mengirim_ulang_permintaan(): void
    {
        // Pastikan URL deterministik (di testing app.api_url bisa null).
        config(['app.api_url' => 'https://api.test']);
        Http::fake();

        $response = $this->withSession(['reset_email' => 'user@example.com'])
            ->post(route('auth.forgot-password.resend'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.test/auth/forgot-password'
                && $request['email'] === 'user@example.com';
        });
    }

    /** Resend tanpa session → redirect forgot-password. */
    public function test_resend_email_without_session_redirects(): void
    {
        $response = $this->post(route('auth.forgot-password.resend'));
        $response->assertRedirect(route('auth.forgot-password'));
    }

    /** Reset password dengan token valid → password berubah + redirect success. */
    public function test_reset_password_with_valid_token_changes_password(): void
    {
        $token = Password::broker()->createToken($this->user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'admin@masjid.id',
            'password'              => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]);

        $response->assertRedirect(route('auth.reset-success'));

        $this->user->refresh();
        $this->assertTrue(Hash::check('passwordbaru', $this->user->password));
    }

    /** Reset password dengan token invalid → back + error email. */
    public function test_reset_password_with_invalid_token_returns_error(): void
    {
        $response = $this->post(route('password.update'), [
            'token'                 => 'token-palsu',
            'email'                 => 'admin@masjid.id',
            'password'              => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** Validasi reset: password < 8 / konfirmasi tidak cocok → error password. */
    public function test_reset_password_validation_rules(): void
    {
        $response = $this->post(route('password.update'), [
            'token'                 => 'apa-saja',
            'email'                 => 'admin@masjid.id',
            'password'              => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors('password');
    }
}