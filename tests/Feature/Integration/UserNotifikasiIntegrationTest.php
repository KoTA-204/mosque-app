<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use App\Notifications\AkunDibuatNotification;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

/**
 * Integrasi: pembuatan user -> notifikasi AkunDibuat, dan reset password -> login.
 */
class UserNotifikasiIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /**
     * IT-F02-01 (+): Admin membuat user baru -> AkunDibuatNotification terkirim.
     *
     * CATATAN: field request mengikuti UserController::store. Bila StoreUserRequest
     * mewajibkan field lain (mis. role berupa nama), sesuaikan payload di bawah.
     */
    public function test_it_f02_01_buat_user_mengirim_notifikasi_akun_dibuat(): void
    {
        Notification::fake();

        $admin      = $this->buatUser($this->buatRole('Super Admin', ['VIEW_USERS']));
        $roleTarget = $this->buatRole('Bendahara');

        $this->actingAs($admin)->post(route('dashboard.users.store'), [
            'name'    => 'Pengguna Baru',
            'email'   => 'penggunabaru@masjid.id',
            'role_id' => $roleTarget->id,
            'status'  => 'active',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'penggunabaru@masjid.id']);

        $userBaru = User::where('email', 'penggunabaru@masjid.id')->first();
        Notification::assertSentTo($userBaru, AkunDibuatNotification::class);
    }

    /** IT-F06-01 (+): Reset password dengan token valid -> bisa login dgn password baru. */
    public function test_it_f06_01_reset_password_lalu_login(): void
    {
        $user  = $this->buatUser($this->buatRole('Bendahara'));
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect();

        // Login memakai password baru
        $this->post(route('auth.login.post'), [
            'email'    => $user->email,
            'password' => 'PasswordBaru123!',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user->fresh());
    }
}
