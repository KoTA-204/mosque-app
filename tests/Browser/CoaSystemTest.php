<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsSystemTestData;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Chart of Account (CoA)
 * Halaman: dashboard.coa.index (3 level: Kategori > Sub Kategori > Akun).
 * Modal (openModal()): createKategoriModal / createSubKategoriModal / createAkunModal.
 * Tombol pembuka: "Tambah Kategori" / "Tambah Sub Kategori" / "Tambah Akun". Submit "Simpan".
 * Karena option select (parent/kategori) di-render server, antar-langkah perlu reload halaman.
 */
class CoaSystemTest extends DuskTestCase
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

    /** ST-F71-01 (+) Tambah Kategori Akun (Level 1) */
    public function test_st_f71_01_tambah_kategori_akun(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/chart-of-account')
                ->press('Tambah Kategori')
                ->waitFor('#createKategoriModal')
                ->within('#createKategoriModal', function (Browser $m) {
                    $m->type('kode_kategori', '1')
                        ->type('nama_kategori', 'Aset')
                        ->press('Simpan');
                })
                ->waitForText('Aset')
                ->assertSee('Aset');
        });
    }

    /** ST-F72-01 (+) Tambah Sub Kategori (Level 2) */
    public function test_st_f72_01_tambah_sub_kategori(): void
    {
        $this->browse(function (Browser $b) {
            // prasyarat: kategori level 1
            $b->loginAs($this->admin())
                ->visit('/dashboard/chart-of-account')
                ->press('Tambah Kategori')
                ->waitFor('#createKategoriModal')
                ->within('#createKategoriModal', function (Browser $m) {
                    $m->type('kode_kategori', '1')->type('nama_kategori', 'Aset')->press('Simpan');
                })
                ->waitForText('Aset');

            $b->visit('/dashboard/chart-of-account')
                ->press('Tambah Sub Kategori')
                ->waitFor('#createSubKategoriModal')
                ->within('#createSubKategoriModal', function (Browser $m) {
                    // pilih kategori pertama yang tersedia
                    $m->script("document.querySelector('#createSubKategoriModal select[name=kategori_akun_id]').selectedIndex = 1;");
                    $m->type('kode_akun', '1.1')
                        ->type('nama_akun', 'Aset Lancar')
                        ->select('saldo_normal', 'DEBIT')
                        ->press('Simpan');
                })
                ->waitForText('Aset Lancar')
                ->assertSee('Aset Lancar');
        });
    }

    /** ST-F73-01 (+) Tambah Akun (Level 3) */
    public function test_st_f73_01_tambah_akun(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())->visit('/dashboard/chart-of-account');
            // kategori
            $b->press('Tambah Kategori')->waitFor('#createKategoriModal')
                ->within('#createKategoriModal', fn (Browser $m) => $m->type('kode_kategori', '1')->type('nama_kategori', 'Aset')->press('Simpan'))
                ->waitForText('Aset');
            // sub kategori
            $b->visit('/dashboard/chart-of-account')->press('Tambah Sub Kategori')->waitFor('#createSubKategoriModal')
                ->within('#createSubKategoriModal', function (Browser $m) {
                    $m->script("document.querySelector('#createSubKategoriModal select[name=kategori_akun_id]').selectedIndex = 1;");
                    $m->type('kode_akun', '1.1')->type('nama_akun', 'Aset Lancar')->select('saldo_normal', 'DEBIT')->press('Simpan');
                })
                ->waitForText('Aset Lancar');
            // akun
            $b->visit('/dashboard/chart-of-account')->press('Tambah Akun')->waitFor('#createAkunModal')
                ->within('#createAkunModal', function (Browser $m) {
                    $m->script("document.querySelector('#createAkunModal select[name=parent_id]').selectedIndex = 1;");
                    $m->type('kode_akun', '1.1.01')->type('nama_akun', 'Kas Masjid')->select('saldo_normal', 'DEBIT')->press('Simpan');
                })
                ->waitForText('Kas Masjid')
                ->assertSee('Kas Masjid');
        });
    }

    /** ST-F74-01 (+) Tampilkan Struktur CoA */
    public function test_st_f74_01_tampilkan_struktur(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/chart-of-account')
                ->assertSee('Chart of Account')
                ->assertSee('Kategori')
                ->assertSee('Akun');
        });
    }

    /**
     * ST-F81-01 (-) Kode Akun Duplikat Ditolak
     * Buat akun, lalu coba buat akun lain dengan kode sama -> error validasi inline (kode_akun unik).
     */
    public function test_st_f81_01_kode_duplikat(): void
    {
        $this->markTestIncomplete('TODO: butuh kategori+sub lebih dulu; verifikasi pesan validasi kode_akun duplikat.');
    }

    /**
     * ST-F76-01 (-) Hapus Kategori Bersub Ditolak
     * UI: tombol hapus -> openDeleteModal() -> x-confirm-modal #confirmModal.
     */
    public function test_st_f76_01_hapus_kategori_bersub(): void
    {
        $this->markTestIncomplete('TODO data: butuh kategori yang memiliki sub/akun untuk menguji penolakan hapus.');
    }
}
