<?php

namespace Tests\Unit\User;

use App\Models\Role;
use App\Models\User;
use App\Notifications\AkunDibuatNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        // PENTING: hanya bypass guard kustom — JANGAN bypass semua middleware
        // (SubstituteBindings & StartSession harus tetap aktif).
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $this->role  = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $this->role->id,
            'status'  => 'active',
        ]);
    }

    /** Buat user valid → tersimpan + notifikasi akun dikirim. */
    public function test_store_user_with_valid_data(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.users.store'), [
                'name'    => 'Budi Santoso',
                'email'   => 'budi@masjid.id',
                'role_id' => $this->role->id,
                'status'  => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'budi@masjid.id']);

        $created = User::where('email', 'budi@masjid.id')->first();
        Notification::assertSentTo($created, AkunDibuatNotification::class);
    }

    /** Email duplikat → ditolak. */
    public function test_store_user_with_duplicate_email(): void
    {
        Notification::fake();

        User::factory()->create(['email' => 'duplikat@masjid.id', 'role_id' => $this->role->id]);

        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.users.store'), [
                'name'    => 'User Baru',
                'email'   => 'duplikat@masjid.id',
                'role_id' => $this->role->id,
                'status'  => 'active',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** Validasi field wajib. */
    public function test_store_user_validation_rules(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'role_id', 'status']);
    }

    /** Update data user → tersimpan. */
    public function test_update_user_data(): void
    {
        $userToUpdate = User::factory()->create([
            'name'    => 'Lama',
            'email'   => 'lama@masjid.id',
            'role_id' => $this->role->id,
            'status'  => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.users.update', $userToUpdate), [
                'name'    => 'Nama Baru',
                'email'   => 'lama@masjid.id',
                'role_id' => $this->role->id,
                'status'  => 'inactive',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'     => $userToUpdate->id,
            'name'   => 'Nama Baru',
            'status' => 'inactive',
        ]);
    }

    /** Hapus user tanpa transaksi → terhapus. */
    public function test_delete_user_success(): void
    {
        $userToDelete = User::factory()->create([
            'role_id' => $this->role->id,
            'status'  => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.users.destroy', $userToDelete));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }
}