<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Akun;
use App\Models\User;
use App\Models\Dompet;
use App\Models\Transaksi;
use App\Models\KategoriAkun;
use App\Models\KategoriTransaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KategoriDanCoATest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'email'  => 'admin@mosque.test',
            'status' => 'active',
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    // =========================================================================
    // TC-07 | REQ-F-16 s/d REQ-F-20
    // Kelola Kategori & CoA
    // =========================================================================

    /**
     * TC-07
     * Menguji penambahan kategori transaksi baru.
     */
    public function test_tc07_tambah_kategori_transaksi(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('dashboard.kategori-transaksi.store'), [
            'nama_kategori'   => 'Infak Pembangunan',
            'jenis_transaksi' => 'PEMASUKAN',
            'status'          => 'aktif',
            'deskripsi'       => 'Kategori pembangunan',
        ]);

        $response->assertRedirect(
            route('dashboard.kategori-transaksi.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_transaksi', [
            'nama_kategori' => 'Infak Pembangunan',
            'status'        => 'aktif',
        ]);
    }

    /**
     * TC-07
     * Menguji penambahan kategori akun baru.
     */
    public function test_tc07_tambah_kategori_akun(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('dashboard.coa.kategori.store'), [
            'kode_kategori' => 'AST',
            'nama_kategori' => 'Aset',
        ]);

        $response->assertRedirect(
            route('dashboard.coa.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_akun', [
            'kode_kategori' => 'AST',
            'nama_kategori' => 'Aset',
        ]);
    }

    /**
     * TC-07
     * Menguji penambahan akun baru pada CoA.
     */
    public function test_tc07_tambah_akun_baru(): void
    {
        $this->actingAsAdmin();

        $kategori = KategoriAkun::create([
            'kode_kategori' => 'AST',
            'nama_kategori' => 'Aset',
        ]);

        $subKategori = Akun::create([
            'kategori_akun_id' => $kategori->id,
            'parent_id'        => null,
            'kode_akun'        => '1100',
            'nama_akun'        => 'Kas',
            'saldo_normal'     => null,
        ]);

        $response = $this->post(route('dashboard.coa.akun.store'), [
            'parent_id'    => $subKategori->id,
            'kode_akun'    => '1101',
            'nama_akun'    => 'Kas Masjid',
            'saldo_normal' => 'debit',
        ]);

        $response->assertRedirect(
            route('dashboard.coa.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('akun', [
            'kode_akun'    => '1101',
            'nama_akun'    => 'Kas Masjid',
            'saldo_normal' => 'debit',
        ]);
    }

    /**
     * TC-07
     * Menguji penonaktifan kategori transaksi.
     */
    public function test_tc07_nonaktifkan_kategori(): void
    {
        $this->actingAsAdmin();

        $kategori = KategoriTransaksi::create([
            'nama_kategori'   => 'Infak Pembangunan',
            'jenis_transaksi' => 'PEMASUKAN',
            'status'          => 'aktif',
        ]);

        $response = $this->put(
            route('dashboard.kategori-transaksi.update', $kategori->id),
            [
                'nama_kategori'   => 'Infak Pembangunan',
                'jenis_transaksi' => 'PEMASUKAN',
                'status'          => 'tidak_aktif',
            ]
        );

        $response->assertRedirect(
            route('dashboard.kategori-transaksi.index')
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_transaksi', [
            'id'     => $kategori->id,
            'status' => 'tidak_aktif',
        ]);
    }

    // =========================================================================
    // TC-08 | REQ-F-21
    // Cegah Hapus Kategori
    // =========================================================================

    /**
     * TC-08
     * Menguji kategori transaksi yang sudah digunakan
     * tidak dapat dihapus.
     */
    public function test_tc08_kategori_yang_sudah_digunakan_tidak_boleh_dihapus(): void
    {
        $this->actingAsAdmin();

        $kategori = KategoriTransaksi::create([
            'nama_kategori'   => 'Infak Jumat',
            'jenis_transaksi' => 'PEMASUKAN',
            'status'          => 'aktif',
        ]);

        $user = User::factory()->create();

        $dompet = Dompet::create([
            'nama_dompet' => 'Kas Utama',
            'jenis_dompet' => 'CASH',
            'saldo'       => 0,
        ]);

        Transaksi::create([
            'kategori_transaksi_id' => $kategori->id,
            'dompet_id'             => $dompet->id,
            'user_id'               => $user->id,
            'tanggal_transaksi'     => now(),
            'jumlah'                => 100000,
        ]);

        $response = $this->delete(
            route('dashboard.kategori-transaksi.destroy', $kategori->id)
        );

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_transaksi', [
            'id' => $kategori->id,
        ]);
    }

    /**
     * TC-08
     * Menguji kategori transaksi yang belum digunakan
     * dapat dihapus.
     */
    public function test_tc08_kategori_yang_belum_digunakan_boleh_dihapus(): void
    {
        $this->actingAsAdmin();

        $kategori = KategoriTransaksi::create([
            'nama_kategori'   => 'Donasi Baru',
            'jenis_transaksi' => 'PEMASUKAN',
            'status'          => 'aktif',
        ]);

        $response = $this->delete(
            route('dashboard.kategori-transaksi.destroy', $kategori->id)
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('kategori_transaksi', [
            'id' => $kategori->id,
        ]);
    }
}
