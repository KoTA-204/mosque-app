<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsSystemTestData;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Kategori Transaksi
 * Halaman: dashboard.kategori-transaksi.index.
 * Create/Edit = MODAL (di-include di halaman, dibuka via openModal()).
 * Field create: nama_kategori, status (select aktif/tidak_aktif), deskripsi. Tombol "Tambah Kategori" -> "Simpan".
 * Catatan: kategori_transaksi MEMILIKI kolom status (form + filter membuktikannya).
 */
class KategoriTransaksiSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsSystemTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPeranDasar();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@masjid.id')->first();
    }

    /** ST-F16-01 (+) Tambah Kategori Transaksi (status Aktif) */
    public function test_st_f16_01_tambah_kategori(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kategori-transaksi')
                ->press('Tambah Kategori')
                ->waitFor('#createKategoriModal')
                ->within('#createKategoriModal', function (Browser $m) {
                    $m->type('nama_kategori', 'Infaq Jumat')
                        ->select('status', 'aktif')
                        ->type('deskripsi', 'Pemasukan infaq salat Jumat')
                        ->press('Simpan');
                })
                ->waitForText('Infaq Jumat')
                ->assertSee('Infaq Jumat')
                ->assertSee('Aktif');
        });
    }

    /** ST-F16-02 (+) Tampilkan Daftar Kategori */
    public function test_st_f16_02_daftar_kategori(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kategori-transaksi')
                ->assertSee('Kategori Transaksi');
        });
    }

    /** ST-F18-01 (-) Validasi Nama Kategori Kosong */
    public function test_st_f18_01_nama_kosong(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kategori-transaksi')
                ->press('Tambah Kategori')
                ->waitFor('#createKategoriModal')
                ->within('#createKategoriModal', function (Browser $m) {
                    $m->select('status', 'aktif')
                        ->press('Simpan'); // nama dikosongkan
                })
                // server kembali dengan error validasi nama_kategori
                ->waitForText('Tambah Kategori')
                ->assertPathBeginsWith('/dashboard/kategori-transaksi');
        });
    }

    /** ST-F20-01 (+) Nonaktifkan Kategori (via edit modal) */
    public function test_st_f20_01_nonaktifkan_kategori(): void
    {
        $this->browse(function (Browser $b) {
            // buat dulu kategori aktif
            $b->loginAs($this->admin())
                ->visit('/dashboard/kategori-transaksi')
                ->press('Tambah Kategori')
                ->waitFor('#createKategoriModal')
                ->within('#createKategoriModal', function (Browser $m) {
                    $m->type('nama_kategori', 'Kategori Sementara')
                        ->select('status', 'aktif')
                        ->press('Simpan');
                })
                ->waitForText('Kategori Sementara');

            // ambil id kategori untuk membuka edit modal
            $id = \App\Models\KategoriTransaksi::where('nama_kategori', 'Kategori Sementara')->value('id');
            $b->script("openModal('editKategoriModal$id');");
            $b->waitFor("#editKategoriModal$id")
                ->within("#editKategoriModal$id", function (Browser $m) {
                    $m->select('status', 'tidak_aktif')
                        ->press('Simpan Perubahan');
                })
                ->waitForText('Tidak Aktif')
                ->assertSee('Tidak Aktif');
        });
    }

    /**
     * ST-F21-01 (-) Kategori Terpakai Tidak Bisa Dihapus
     * UI menyembunyikan tombol hapus saat transaksi_count > 0 (lihat @if($item->transaksi_count == 0)).
     * Butuh kategori yang sudah dipakai transaksi (pola data lihat ITD F21-01).
     */
    public function test_st_f21_01_kategori_terpakai_tak_bisa_hapus(): void
    {
        $this->markTestIncomplete('TODO data: butuh kategori dengan transaksi; verifikasi tombol hapus tidak muncul.');
    }
}
