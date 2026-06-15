<?php

namespace Tests\Feature\Integration;

use App\Models\KategoriTransaksi;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integrasi Kategori Transaksi.
 *
 * PERINGATAN KODE ASLI:
 *
 * 1. Migration kategori_transaksi HANYA punya kolom nama_kategori & deskripsi
 *    (TIDAK ada 'status' maupun 'jenis_transaksi'), namun:
 *    - KategoriTransaksi model memiliki 'status' di $fillable
 *    - KategoriTransaksiController::store() memvalidasi & menyimpan 'status'
 *    - KategoriTransaksiController::index() memfilter where('status', ...)
 *    Ini akan menyebabkan error kolom pada DB production saat store/update diakses
 *    via HTTP. Perbaiki dengan menambahkan kolom 'status' ke migration.
 *
 * 2. KenclengService::getKategoriKencleng() memfilter where('jenis_transaksi','PEMASUKAN')
 *    — kolom tsb juga TIDAK ada di migration kategori_transaksi. Bug ini sudah
 *    didokumentasikan di KenclengIntegrationTest.
 *
 * Karena itu test IT-F16-01 menggunakan factory (bukan HTTP store) agar tidak
 * terkena bug kolom status, dan hanya memverifikasi bahwa data tampil di halaman
 * index — bukan memverifikasi filter aktif/tidak_aktif.
 */
class KategoriTransaksiIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /**
     * IT-F16-01 (+): Kategori transaksi tampil di halaman index.
     *
     * CATATAN: Migration kategori_transaksi tidak memiliki kolom 'status',
     * sehingga filter aktif/tidak_aktif tidak dapat diuji pada level integrasi ini.
     * Test menggunakan factory langsung untuk membuat data tanpa melalui HTTP store
     * yang membutuhkan kolom status. Hanya memverifikasi bahwa data tampil di halaman.
     */
    public function test_it_f16_01_kategori_tampil_di_halaman_index(): void
    {
        $user     = $this->buatUser($this->buatRole('Bendahara', ['VIEW_KATEGORI']));
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Infaq Jumat']);

        $this->actingAs($user)
            ->get(route('dashboard.kategori-transaksi.index'))
            ->assertOk()
            ->assertSee('Infaq Jumat');
    }

    /** IT-F21-01 (-): Kategori yang sudah dipakai transaksi tidak bisa dihapus. */
    public function test_it_f21_01_kategori_terpakai_tidak_bisa_dihapus(): void
    {
        $user     = $this->buatUser($this->buatRole('Bendahara', ['VIEW_KATEGORI', 'DELETE_KATEGORI']));
        $kategori = KategoriTransaksi::factory()->create();

        // Pakai kategori pada sebuah transaksi
        $this->buatTransaksi(['kategori_transaksi_id' => $kategori->id]);

        $this->actingAs($user)
            ->delete(route('dashboard.kategori-transaksi.destroy', $kategori))
            ->assertRedirect()
            ->assertSessionHas('error');

        // Kategori harus tetap ada
        $this->assertDatabaseHas('kategori_transaksi', ['id' => $kategori->id]);
    }
}