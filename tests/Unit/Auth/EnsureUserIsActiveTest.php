<?php

namespace Tests\Unit\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * UT-F10-01
     * Deskripsi : User dengan status nonaktif mencoba mengakses halaman terproteksi (dashboard)
     * Expected  : Middleware 'active' (EnsureUserIsActive) memutus sesi —
     *             logout + invalidate session + redirect ke halaman login
     *             dengan error pada field email, dan user menjadi guest.
     */
    public function test_UT_F10_01_inactive_user_cannot_access_dashboard(): void
    {
        $role = Role::create(['role_name' => 'Super Admin']);

        // User dengan status NONAKTIF
        $inactiveUser = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'inactive',
        ]);

        // PENTING: JANGAN bypass middleware 'active' (EnsureUserIsActive),
        // karena justru middleware itulah yang sedang diuji di sini.
        $response = $this->actingAs($inactiveUser)
            ->get(route('dashboard.index'));

        // Sesuai logika middleware: redirect ke login + error 'email' + logout
        $response->assertRedirect(route('auth.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}