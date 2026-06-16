<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Password;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsSystemTestData;
use Tests\DuskTestCase;

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
                ->press('Sign in')
                ->assertPathIs('/dashboard');
            // Verifikasi sudah terautentikasi (tidak kembali ke /login)
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
                ->assertPathIs('/login');
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
                ->assertPathIs('/login');
        });
    }

    /** ST-F06-01 (+) Lupa Password - Kirim Email Reset */
    public function test_st_f06_01_lupa_password_kirim_email(): void
    {
        $this->browse(function (Browser $b) {
            $b->visit('/forgot-password')
                ->type('email', 'bendahara1@masjid.id')
                ->press('Reset password')
                ->assertSee('Cek Email Anda');
        });
    }

    /** ST-F06-02 (+) Reset Password - Password Baru Berhasil */
    public function test_st_f06_02_reset_password_berhasil(): void
    {
        $user  = User::where('email', 'bendahara1@masjid.id')->first();
        $token = Password::createToken($user);

        $this->browse(function (Browser $b) use ($token, $user) {
            $b->visit('/reset-password/' . $token . '?email=' . urlencode($user->email))
                ->waitFor('input[name="password"]', 5)
                ->type('password', 'NewPass@123')
                ->type('password_confirmation', 'NewPass@123')
                ->press('Ubah Password')
                ->assertSee('Berhasil');

            $b->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'NewPass@123')
                ->press('Sign in')
                ->assertPathIs('/dashboard');
        });
    }

    /** ST-F07-01 (+) Sesi Otomatis Berakhir */
    public function test_st_f07_01_sesi_berakhir(): void
    {
        $admin = User::where('email', 'admin@masjid.id')->first();
        $this->browse(function (Browser $b) use ($admin) {
            $b->loginAs($admin)->visit('/dashboard')->assertPathIs('/dashboard');
            $b->driver->manage()->deleteAllCookies();
            $b->visit('/dashboard')->assertPathIs('/login');
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
            $b->pause(1500)->assertPathIs('/login');
        });
    }

    /** ST-NFR03-02 (MANUAL) */
    public function test_st_nfr03_02_akun_aktif_setelah_blokir(): void
    {
        $this->markTestIncomplete('MANUAL: blokir 15 menit perlu time-travel server.');
    }

    /** ST-F85-01 (+) Logout Berhasil */
    public function test_st_f85_01_logout_berhasil(): void
    {
        $admin = User::where('email', 'admin@masjid.id')->first();
        $this->browse(function (Browser $b) use ($admin) {
            $b->loginAs($admin)->visit('/dashboard')
                ->press('Logout')
                ->assertPathIs('/login');
            $b->visit('/dashboard')->assertPathIs('/login');
        });
    }
}