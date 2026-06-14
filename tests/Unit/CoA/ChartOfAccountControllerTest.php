<?php

namespace Tests\Unit\CoA;

use App\Models\Akun;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChartOfAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected int  $kategoriAkunId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role        = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        // Buat kategori_akun (FK wajib untuk tabel akun)
        $this->kategoriAkunId = DB::table('kategori_akun')->insertGetId([
            'kode_kategori' => '1',
            'nama_kategori' => 'Aset',
            'status'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * UT-F62-01
     * Menyimpan sub-kategori akun baru (Akun tanpa parent_id)
     */
    public function test_UT_F62_01_store_sub_kategori_valid(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.coa.sub-kategori.store'), [
                             'kode_akun'        => '1-1000',
                             'nama_akun'        => 'Aset Lancar',
                             'kategori_akun_id' => $this->kategoriAkunId,
                             'saldo_normal'     => 'DEBIT',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', ['kode_akun' => '1-1000']);
    }

    /**
     * UT-F62-02
     * Menyimpan akun baru dengan parent sub-kategori
     * StoreAkunRequest wajib: parent_id, kode_akun, nama_akun, saldo_normal, status
     */
    public function test_UT_F62_02_store_akun_with_parent(): void
    {
        // Buat sub-kategori (parent) terlebih dahulu
        $subKategori = Akun::create([
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Aset Lancar',
            'kategori_akun_id' => $this->kategoriAkunId,
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.coa.akun.store'), [
                             'parent_id'    => $subKategori->id,
                             'kode_akun'    => '1-1100',
                             'nama_akun'    => 'Kas',
                             'saldo_normal' => 'DEBIT',
                             'status'       => 'aktif', // ← wajib oleh StoreAkunRequest
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', [
            'kode_akun' => '1-1100',
            'parent_id' => $subKategori->id,
        ]);
    }

    /**
     * UT-F62-03
     * Menghapus sub-kategori yang masih punya child akun diblokir
     */
    public function test_UT_F62_03_delete_sub_kategori_with_children_blocked(): void
    {
        $subKategori = Akun::create([
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Aset Lancar',
            'kategori_akun_id' => $this->kategoriAkunId,
            'saldo_normal'     => 'DEBIT',
        ]);

        // Buat child akun
        Akun::create([
            'kode_akun'        => '1-1100',
            'nama_akun'        => 'Kas',
            'parent_id'        => $subKategori->id,
            'kategori_akun_id' => $this->kategoriAkunId,
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.coa.sub-kategori.destroy', $subKategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('akun', ['id' => $subKategori->id]);
    }
}