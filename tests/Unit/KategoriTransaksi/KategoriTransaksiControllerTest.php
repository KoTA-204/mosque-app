<?php

namespace Tests\Unit\KategoriTransaksi;

use App\Models\KategoriTransaksi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriTransaksiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Hanya bypass permission & active — SubstituteBindings & Session tetap aktif
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role        = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
    }

    /**
     * UT-F63-01
     * Menyimpan kategori transaksi dengan data valid
     */
    public function test_UT_F63_01_store_kategori_valid(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.kategori-transaksi.store'), [
                             'nama_kategori' => 'Infaq',
                             'status'        => 'aktif',
                             'deskripsi'     => 'Kategori infaq masjid',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kategori_transaksi', ['nama_kategori' => 'Infaq']);
    }

    /**
     * UT-F63-02
     * Menyimpan kategori dengan nama duplikat ditolak
     */
    public function test_UT_F63_02_store_duplicate_nama(): void
    {
        KategoriTransaksi::create([
            'nama_kategori' => 'Zakat',
            'status'        => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.kategori-transaksi.store'), [
                             'nama_kategori' => 'Zakat',
                             'status'        => 'aktif',
                         ]);

        $response->assertSessionHasErrors('nama_kategori');
    }

    /**
     * UT-F63-03
     * Mengubah nama kategori transaksi
     */
    public function test_UT_F63_03_update_kategori_nama(): void
    {
        $kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Nama Lama',
            'status'        => 'aktif',
            'deskripsi'     => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($this->admin)
                         ->put(route('dashboard.kategori-transaksi.update', $kategori), [
                             'nama_kategori' => 'Nama Baru',
                             'status'        => 'aktif',
                             'deskripsi'     => 'Deskripsi baru',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kategori_transaksi', [
            'id'            => $kategori->id,
            'nama_kategori' => 'Nama Baru',
        ]);
    }

    /**
     * UT-F63-04
     * Mengubah deskripsi kategori transaksi
     */
    public function test_UT_F63_04_update_kategori_deskripsi(): void
    {
        $kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Sodaqoh',
            'status'        => 'aktif',
            'deskripsi'     => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($this->admin)
                         ->put(route('dashboard.kategori-transaksi.update', $kategori), [
                             'nama_kategori' => 'Sodaqoh',
                             'status'        => 'aktif',
                             'deskripsi'     => 'Deskripsi diperbarui',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kategori_transaksi', [
            'id'        => $kategori->id,
            'deskripsi' => 'Deskripsi diperbarui',
        ]);
    }

    /**
     * UT-F63-05
     * Menghapus kategori transaksi yang tidak digunakan
     */
    public function test_UT_F63_05_delete_kategori(): void
    {
        $kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Kategori Hapus',
            'status'        => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.kategori-transaksi.destroy', $kategori));

        $response->assertRedirect();
        $this->assertDatabaseMissing('kategori_transaksi', ['id' => $kategori->id]);
    }

    /**
     * UT-F63-06
     * Menyimpan kategori dengan nama kosong ditolak
     */
    public function test_UT_F63_06_store_kategori_nama_kosong(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.kategori-transaksi.store'), [
                             'nama_kategori' => '',
                             'status'        => 'aktif',
                         ]);

        $response->assertSessionHasErrors('nama_kategori');
    }

    /**
     * UT-F63-07
     * Halaman index kategori transaksi dapat diakses
     */
    public function test_UT_F63_07_index_returns_view(): void
    {
        $response = $this->actingAs($this->admin)
                         ->get(route('dashboard.kategori-transaksi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.kategori-transaksi.index');
    }
}