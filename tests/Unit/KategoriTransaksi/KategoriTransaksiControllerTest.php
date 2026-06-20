<?php

namespace Tests\Unit\KategoriTransaksi;

use App\Models\KategoriTransaksi;
use App\Models\Role;
use App\Models\User;
use App\Models\Dompet;
use App\Models\Transaksi;
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

    /** UT-F63-09 — Hapus kategori yang dipakai transaksi → diblokir */
    public function test_UT_F63_09_delete_kategori_dengan_transaksi_blocked(): void
    {
        $kategori = KategoriTransaksi::create(['nama_kategori' => 'Dipakai', 'status' => 'aktif']);

        $dompet = Dompet::factory()->create();
        Transaksi::create([
            'dompet_id'             => $dompet->id,
            'user_id'               => $this->admin->id,
            'kategori_transaksi_id' => $kategori->id,
            'tanggal_transaksi'     => now()->toDateString(),
            'jenis_transaksi'       => 'PEMASUKAN',
            'jumlah'                => 100000,
            'status_approval'       => 'PENDING',
            'status_jurnal'         => 'UNMAPPED',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.kategori-transaksi.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategori_transaksi', ['id' => $kategori->id]);
    }

    /** UT-F63-10 — Update tolak nama duplikat milik kategori lain */
    public function test_UT_F63_10_update_tolak_nama_duplikat(): void
    {
        KategoriTransaksi::create(['nama_kategori' => 'Infaq', 'status' => 'aktif']);
        $kategori = KategoriTransaksi::create(['nama_kategori' => 'Sedekah', 'status' => 'aktif']);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.kategori-transaksi.update', $kategori), [
                'nama_kategori' => 'Infaq', // bentrok
                'status'        => 'aktif',
            ]);

        $response->assertSessionHasErrors('nama_kategori');
    }

    /** UT-F63-11 — Update boleh pakai nama sendiri (unique abaikan diri sendiri) */
    public function test_UT_F63_11_update_nama_sendiri_lolos(): void
    {
        $kategori = KategoriTransaksi::create(['nama_kategori' => 'Wakaf', 'status' => 'aktif']);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.kategori-transaksi.update', $kategori), [
                'nama_kategori' => 'Wakaf',
                'status'        => 'tidak_aktif',
                'deskripsi'     => 'Update status',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kategori_transaksi', ['id' => $kategori->id, 'status' => 'tidak_aktif']);
    }

    /** UT-F63-12 — Store tolak status tidak valid */
    public function test_UT_F63_12_store_tolak_status_invalid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.kategori-transaksi.store'), [
                'nama_kategori' => 'Kategori X',
                'status'        => 'nonaktif', // di luar in:aktif,tidak_aktif
            ]);

        $response->assertSessionHasErrors('status');
    }
}