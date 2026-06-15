<?php

namespace Tests\Browser;

use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsFullApp;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Transaksi Kegiatan (dashboard.transaksi-kegiatan.show).
 * Catat transaksi = modal #modal-catat-transaksi (tombol "Catat Transaksi", hanya saat status AKTIF).
 * Radio jenis_transaksi bersifat sr-only -> diset via script. Submit "Simpan & Kirim".
 * Peringatan over-budget di #create-over-warning ("Melebihi anggaran") via cekAnggaranCreate().
 * Bergantung pada data seeder penuh (panitia@masjid.id pemilik semua kegiatan).
 */
class TransaksiKegiatanSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsFullApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedFullApp();
    }

    private function panitia()
    {
        return $this->userByEmail('panitia@masjid.id');
    }

    private function kegiatanId(string $nama): int
    {
        return (int) Kegiatan::where('nama_kegiatan', $nama)->value('id');
    }

    /** ST-F64-01 (+) Input Transaksi pada Kegiatan AKTIF */
    public function test_st_f64_01_input_transaksi_aktif(): void
    {
        $id = $this->kegiatanId('Qurban 1447 H'); // AKTIF, tgl belum selesai
        $dompetId = (string) Dompet::first()->id;
        $kategoriId = (string) KategoriTransaksi::first()->id;

        $this->browse(function (Browser $b) use ($id, $dompetId, $kategoriId) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->assertSee('Catat Transaksi')
                ->press('Catat Transaksi')
                ->waitFor('#modal-catat-transaksi');
            // pilih jenis PEMASUKAN (radio sr-only)
            $b->script('var r=document.querySelector(\'#form-create-transaksi input[value="PEMASUKAN"]\'); if(r){r.checked=true;} if(typeof updateToggleStyle==="function"){updateToggleStyle("PEMASUKAN");}');
            $b->within('#form-create-transaksi', function (Browser $m) use ($dompetId, $kategoriId) {
                $m->type('jumlah', '513000')
                    ->select('dompet_id', $dompetId)
                    ->select('kategori_transaksi_id', $kategoriId)
                    ->type('deskripsi', 'Uji input transaksi kegiatan');
            })
                ->press('Simpan & Kirim')
                ->waitForReload()
                ->assertSee('513.000');
        });
    }

    /** ST-F64-02 (-) Tombol "Catat Transaksi" hilang saat kegiatan DITUTUP */
    public function test_st_f64_02_tombol_hilang_saat_ditutup(): void
    {
        // 'Maulid Nabi 1446 H' = tgl lewat + semua APPROVED -> ditutup oleh command
        $this->artisan('kegiatan:tutup-otomatis');
        $id = $this->kegiatanId('Maulid Nabi 1446 H');

        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->assertSee('Detail Kegiatan')
                ->assertDontSee('Catat Transaksi');
        });
    }

    /** ST-F64-03 (-) Edit transaksi APPROVED ditolak (tombol Edit tidak tampil) */
    public function test_st_f64_03_edit_approved_ditolak(): void
    {
        $this->markTestIncomplete('Perlu menargetkan baris transaksi APPROVED tertentu lalu memverifikasi tombol Edit ($bisaUbah=false) tidak dirender.');
    }

    /** ST-F65-01 (+) Pengeluaran dalam anggaran: TIDAK ada peringatan over-budget */
    public function test_st_f65_01_dalam_anggaran(): void
    {
        $id = $this->kegiatanId('Qurban 1447 H'); // anggaran 50jt
        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->press('Catat Transaksi')
                ->waitFor('#modal-catat-transaksi')
                ->within('#form-create-transaksi', function (Browser $m) {
                    $m->type('jumlah', '100000');
                });
            $b->script('var r=document.querySelector(\'#form-create-transaksi input[value="PENGELUARAN"]\'); if(r){r.checked=true;} if(typeof updateToggleStyle==="function"){updateToggleStyle("PENGELUARAN");}');
            $b->assertDontSee('Melebihi anggaran');
        });
    }

    /** ST-F66-01 (+) Notifikasi Pengeluaran Melebihi Anggaran */
    public function test_st_f66_01_over_budget_warning(): void
    {
        $id = $this->kegiatanId('Bakti Sosial Idul Adha 1447 H'); // anggaran 10jt
        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->press('Catat Transaksi')
                ->waitFor('#modal-catat-transaksi')
                ->within('#form-create-transaksi', function (Browser $m) {
                    $m->type('jumlah', '999999999'); // jauh melebihi sisa anggaran
                });
            $b->script('var r=document.querySelector(\'#form-create-transaksi input[value="PENGELUARAN"]\'); if(r){r.checked=true;} if(typeof updateToggleStyle==="function"){updateToggleStyle("PENGELUARAN");}');
            $b->waitForTextIn('#create-over-warning', 'Melebihi anggaran')
                ->assertSeeIn('#create-over-warning', 'Melebihi anggaran');
        });
    }
}
