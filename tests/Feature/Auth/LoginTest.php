<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * TC-01 & TC-02 — Login & Autentikasi
 *
 * REQ yang dicakup:
 *   REQ-F-01 : Sistem harus dapat mengidentifikasi dan mengkonfirmasi pengguna
 *   REQ-F-03 : Sistem harus dapat memvalidasi format alamat email
 *   REQ-F-04 : Sistem harus menolak penyimpanan / aksi apabila format email tidak valid
 *   REQ-F-06 : Sistem menolak login dengan kredensial salah / akun tidak terdaftar
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TC-01 — Login dengan kredensial valid & validasi format email
    // REQ-F-01, REQ-F-03, REQ-F-04
    // =========================================================================

    /**
     * TC-01 Skenario 1
     * Login berhasil dengan email dan kata sandi yang valid.
     * REQ-F-01
     */
    public function test_login_berhasil_dengan_kredensial_valid(): void
    {
        $user = User::factory()->create([
            'email'    => 'bendahara1@luqmanul.ac.id',
            'password' => Hash::make('bendahara123'),
            'status'   => 'active',
        ]);

        $response = $this->post(route('auth.login.post'), [
            'email'    => 'bendahara1@luqmanul.ac.id',
            'password' => 'bendahara123',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * TC-01 Skenario 2
     * Login ditolak apabila email tidak mengandung karakter @ (format tidak valid).
     * REQ-F-03, REQ-F-04
     */
    public function test_login_ditolak_jika_format_email_tanpa_at(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'bendahara1luqmanul',
            'password' => 'bendahara123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TC-01 Skenario 3
     * Login ditolak apabila email dan kata sandi keduanya kosong (null).
     * REQ-F-03, REQ-F-04
     */
    public function test_login_ditolak_jika_email_dan_password_kosong(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * TC-01 Tambahan
     * Login ditolak apabila email tidak memiliki domain (format tidak valid).
     * REQ-F-03, REQ-F-04
     */
    public function test_login_ditolak_jika_format_email_tanpa_domain(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'bendahara1@',
            'password' => 'bendahara123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // =========================================================================
    // TC-02 — Login dengan kredensial salah / akun tidak terdaftar
    // REQ-F-01, REQ-F-06
    // =========================================================================

    /**
     * TC-02 Skenario 1
     * Login ditolak apabila kata sandi tidak sesuai dengan yang terdaftar.
     * REQ-F-01, REQ-F-06
     */
    public function test_login_ditolak_jika_password_salah(): void
    {
        User::factory()->create([
            'email'    => 'bendahara1@luqmanul.ac.id',
            'password' => Hash::make('bendahara123'),
            'status'   => 'active',
        ]);

        $response = $this->post(route('auth.login.post'), [
            'email'    => 'bendahara1@luqmanul.ac.id',
            'password' => 'salah123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TC-02 Skenario 2
     * Login ditolak apabila email tidak terdaftar di sistem.
     * REQ-F-01, REQ-F-06
     */
    public function test_login_ditolak_jika_email_tidak_terdaftar(): void
    {
        $response = $this->post(route('auth.login.post'), [
            'email'    => 'tidakada@mail.com',
            'password' => 'abc123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * TC-02 Tambahan
     * Pengguna yang sudah login diarahkan ke dashboard, bukan ke halaman login.
     * REQ-F-01
     */
    public function test_pengguna_sudah_login_diarahkan_ke_dashboard(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('auth.login'));

        $response->assertRedirect(route('dashboard.index'));
    }

    /**
     * TC-02 Tambahan
     * Pengguna yang belum login tidak dapat mengakses dashboard.
     * REQ-F-01
     */
    public function test_tamu_tidak_dapat_akses_dashboard(): void
    {
        $response = $this->get(route('dashboard.index'));

        $response->assertRedirect(route('auth.login'));
    }
}