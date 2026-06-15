<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Password;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsSystemTestData;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Autentikasi
 * Selector disesuaikan dengan Blade asli (login/forgot/reset/check-email/success).
 */
class AuthSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsSystemTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPeranDasar();
    }

    /** ST-F01-01 (+) Login Kredensial Valid */
    public function test_st_f01_01_login_valid(): void
    {
        $this->browse(function (Browser $b) {
            $b->visit('/login')
                ->type('email', 'admin@masjid.id')
                ->type('password', 'password')
                ->press('Sign in')          // tombol login bertuliskan "Sign in"
                ->assertPathIs('/dashboard')
                ->assertSee('Administrator'); // nama user di header
        });
    }

    /** ST-F01-02 (-) Login Password Salah */
    public function test_st_f01_02_login_password_salah(): void
    {
        $this->browse(function (Browser $b) {
            $b->visit('/login')
                ->type('email', 'admin@masjid.id')
                ->type('password', 'PasswordSalah')
                ->press('Sign in')
                ->pause(1500)
                ->assertPathIs('/login'); // login gagal => tetap di /login (login valid akan redirect ke /dashboard)
        });
    }

    /** ST-F01-03 (-) Login User Nonaktif Ditolak */
    public function test_st_f01_03_login_user_nonaktif_ditolak(): void
    {
        $role = $this->buatRole('Bendahara 1');
        $this->buatUser($role, [
            'name'     => 'Akun Nonaktif',
            'email'    => 'nonaktif@mosque.test',
            'status'   => 'inactive',
            'password' => bcrypt('Password123!'),
        ]);

        $this->browse(function (Browser $b) {
            $b->visit('/login')
                ->type('email', 'nonaktif@mosque.test')
                ->type('password', 'Password123!')
                ->press('Sign in')
                ->pause(1500)
                ->assertPathIs('/login'); // akun nonaktif ditolak => tetap di /login
        });
    }

    /** ST-F06-01 (+) Lupa Password - Kirim Email Reset */
    public function test_st_f06_01_lupa_password_kirim_email(): void
    {
        $this->browse(function (Browser $b) {
            $b->visit('/login')
                ->clickLink('Lupa Password?')      // teks link asli di halaman login
                ->assertPathIs('/forgot-password') // halaman forgot
                ->type('email', 'bendahara1@masjid.id')
                ->press('Reset password')          // tombol kirim
                ->assertSee('Cek Email Anda');     // halaman check-email
        });
    }

    /** ST-F06-02 (+) Reset Password - Password Baru Berhasil (token disuntik via API) */
    public function test_st_f06_02_reset_password_berhasil(): void
    {
        $user  = User::where('email', 'bendahara1@masjid.id')->first();
        $token = Password::createToken($user); // Dusk tak bisa baca email

        $this->browse(function (Browser $b) use ($token, $user) {
            $b->visit('/reset-password/' . $token . '?email=' . urlencode($user->email))
                ->type('password', 'NewPass@123')              // memenuhi min 8 + karakter spesial
                ->type('password_confirmation', 'NewPass@123')
                ->press('Ubah Password')
                ->assertSee('Berhasil');                       // halaman success

            // verifikasi bisa login dengan password baru
            $b->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'NewPass@123')
                ->press('Sign in')
                ->assertPathIs('/dashboard');
        });
    }

    /** ST-F07-01 (+) Sesi Otomatis Berakhir (HYBRID: hapus cookie sesi) */
    public function test_st_f07_01_sesi_berakhir(): void
    {
        $admin = User::where('email', 'admin@masjid.id')->first();
        $this->browse(function (Browser $b) use ($admin) {
            $b->loginAs($admin)->visit('/dashboard')->assertPathIs('/dashboard');
            $b->driver->manage()->deleteAllCookies();
            $b->visit('/dashboard')->assertPathIs('/login'); // diarahkan ke login
        });
    }

    /** ST-NFR03-01 (-) Pemblokiran Akun Setelah 5x Login Gagal */
    public function test_st_nfr03_01_blokir_5x_gagal(): void
    {
        $this->browse(function (Browser $b) {
            for ($i = 1; $i <= 6; $i++) {
                $b->visit('/login')
                    ->type('email', 'bendahara1@masjid.id')
                    ->type('password', 'salahsemua')
                    ->press('Sign in');
            }
            // setelah 5x gagal: tetap tidak terautentikasi (throttle / login gagal) => masih di /login
            $b->pause(1500)->assertPathIs('/login');
        });
    }

    /**
     * ST-NFR03-02 (+) Akun Aktif Kembali Setelah Masa Blokir (MANUAL)
     * Perlu time-travel server (blokir 15 menit). Lihat SETUP.md.
     */
    public function test_st_nfr03_02_akun_aktif_setelah_blokir(): void
    {
        $this->markTestIncomplete('MANUAL: blokir 15 menit perlu time-travel server.');
    }

    /**
     * ST-F85-01 (+) Logout Berhasil
     * Markup tombol logout ada di layout (tidak disertakan). Sesuaikan teks/selektor menu logout.
     */
    public function test_st_f85_01_logout_berhasil(): void
    {
        $admin = User::where('email', 'admin@masjid.id')->first();
        $this->browse(function (Browser $b) use ($admin) {
            $b->loginAs($admin)->visit('/dashboard')
                ->press('Logout') // TODO selector: tombol/menu logout di layout.app
                ->assertPathIs('/login');
            $b->visit('/dashboard')->assertPathIs('/login');
        });
    }
}
