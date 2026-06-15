<?php

namespace Tests\Browser;

use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsFullApp;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Manajemen Kegiatan Khusus (dashboard.kegiatan.index).
 * Create/Edit = MODAL AJAX (#createKegiatanForm / #editKegiatanForm via openCreateModal()/openEditModal()).
 * Bergantung pada data seeder penuh.
 */
class KegiatanSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsFullApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedFullApp();
    }

    private function admin()
    {
        return $this->userByEmail('admin@masjid.id');
    }

    /** ST-F63-01 (+) Tambah Kegiatan Baru */
    public function test_st_f63_01_tambah_kegiatan(): void
    {
        $panitiaId = (string) $this->userByEmail('panitia@masjid.id')->id;

        $this->browse(function (Browser $b) use ($panitiaId) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kegiatan')
                ->press('Tambah Kegiatan')
                ->waitFor('#createKegiatanForm')
                ->within('#createKegiatanForm', function (Browser $m) use ($panitiaId) {
                    $m->type('nama_kegiatan', 'Bakti Sosial Uji Sistem')
                        ->select('jenis_kegiatan', 'SOSIAL')
                        ->select('panitia_id', $panitiaId);
                });
            // anggaran & tanggal disuntik ke input hidden (flatpickr / format ribuan via JS)
            $b->script('document.getElementById("create-anggaran-hidden").value=5000000;');
            $b->script('document.getElementById("create-tanggal_mulai").value="' . now()->format('Y-m-d') . '";');
            $b->script('document.getElementById("create-tanggal_selesai").value="' . now()->addDays(3)->format('Y-m-d') . '";');
            $b->press('Simpan')
                ->waitForText('Bakti Sosial Uji Sistem')
                ->assertSee('Bakti Sosial Uji Sistem');
        });
    }

    /** ST-F63-02 (+) Tampilkan Daftar Kegiatan */
    public function test_st_f63_02_daftar_kegiatan(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kegiatan')
                ->assertSee('Manajemen Kegiatan Khusus')
                ->assertSee('Qurban 1447 H'); // data seeder
        });
    }

    /** ST-F63-03 (+) Edit Kegiatan (modal AJAX) */
    public function test_st_f63_03_edit_kegiatan(): void
    {
        $id = (int) Kegiatan::where('nama_kegiatan', 'Renovasi Serambi Masjid')->value('id');

        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->admin())->visit('/dashboard/kegiatan');
            $b->script('openEditModal(' . $id . ');');
            $b->waitFor('#editKegiatanForm')
                ->within('#editKegiatanForm', function (Browser $m) {
                    $m->clear('nama_kegiatan')->type('nama_kegiatan', 'Renovasi Serambi (Diedit)');
                })
                ->press('Simpan Perubahan');
            // reload daftar agar tidak bergantung pada refresh tabel via AJAX
            $b->pause(2000)->visit('/dashboard/kegiatan')
                ->assertSee('Renovasi Serambi (Diedit)');
        });
    }

    /** ST-F63-04 (+) Hapus Kegiatan Tanpa Transaksi */
    public function test_st_f63_04_hapus_kegiatan(): void
    {
        // 'Renovasi Serambi Masjid' tidak punya transaksi -> dapat dihapus
        $id = (int) Kegiatan::where('nama_kegiatan', 'Renovasi Serambi Masjid')->value('id');

        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kegiatan')
                ->assertSee('Renovasi Serambi Masjid');
            $b->script('openDeleteModal(' . $id . ');');
            $b->waitFor('#deleteKegiatanModal');
            // submit form konfirmasi (action di-set oleh openDeleteModal)
            $b->script('document.getElementById("deleteKegiatanModalForm").submit();');
            $b->waitUntilMissingText('Renovasi Serambi Masjid')
                ->assertDontSee('Renovasi Serambi Masjid');
        });
    }
}
