<?php

namespace Tests\Browser;

use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsFullApp;
use Tests\DuskTestCase;

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
        $id         = $this->kegiatanId('Qurban 1447 H');
        $dompetId   = (string) Dompet::first()->id;
        $kategoriId = (string) KategoriTransaksi::first()->id;

        $this->browse(function (Browser $b) use ($id, $dompetId, $kategoriId) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->assertSee('Catat Transaksi')
                ->press('Catat Transaksi')
                ->waitFor('#modal-catat-transaksi', 10);

            // Set radio PEMASUKAN
            $b->script('
                var r = document.querySelector(\'#form-create-transaksi input[value="PEMASUKAN"]\');
                if (r) { r.checked = true; }
                if (typeof updateToggleStyle === "function") { updateToggleStyle("PEMASUKAN"); }
            ');

            $b->within('#form-create-transaksi', function (Browser $m) use ($dompetId, $kategoriId) {
                $m->type('jumlah', '513000')
                    ->select('dompet_id', $dompetId)
                    ->select('kategori_transaksi_id', $kategoriId)
                    ->type('deskripsi', 'Uji input transaksi kegiatan');
            });

            $b->press('Simpan & Kirim');

            // Tunggu redirect ke halaman show kegiatan (bukan index)
            $b->waitForLocation('/dashboard/transaksi-kegiatan/' . $id, 15);

            // Verifikasi transaksi muncul di tabel — cari angka atau kode transaksi
            $b->waitForText('513', 10);
        });
    }

    /** ST-F64-02 (-) Tombol "Catat Transaksi" hilang saat kegiatan DITUTUP */
    public function test_st_f64_02_tombol_hilang_saat_ditutup(): void
    {
        $this->artisan('kegiatan:tutup-otomatis');
        $id = $this->kegiatanId('Maulid Nabi 1446 H');

        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->assertSee('Detail Kegiatan')
                ->assertDontSee('Catat Transaksi');
        });
    }

    /** ST-F64-03 (-) Edit transaksi APPROVED ditolak */
    public function test_st_f64_03_edit_approved_ditolak(): void
    {
        $this->markTestIncomplete('Perlu menargetkan baris transaksi APPROVED tertentu lalu memverifikasi tombol Edit tidak dirender.');
    }

    /** ST-F65-01 (+) Pengeluaran dalam anggaran: TIDAK ada peringatan over-budget */
    public function test_st_f65_01_dalam_anggaran(): void
    {
        $id = $this->kegiatanId('Qurban 1447 H');
        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->press('Catat Transaksi')
                ->waitFor('#modal-catat-transaksi', 10)
                ->within('#form-create-transaksi', function (Browser $m) {
                    $m->type('jumlah', '100000');
                });
            $b->script('
                var r = document.querySelector(\'#form-create-transaksi input[value="PENGELUARAN"]\');
                if (r) { r.checked = true; }
                if (typeof updateToggleStyle === "function") { updateToggleStyle("PENGELUARAN"); }
                if (typeof cekAnggaranCreate === "function") { cekAnggaranCreate(); }
            ');
            $b->pause(800)->assertDontSee('Melebihi anggaran');
        });
    }

    /** ST-F66-01 (+) Notifikasi Pengeluaran Melebihi Anggaran */
    public function test_st_f66_01_over_budget_warning(): void
    {
        $id = $this->kegiatanId('Bakti Sosial Idul Adha 1447 H');
        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->panitia())
                ->visit('/dashboard/transaksi-kegiatan/' . $id)
                ->press('Catat Transaksi')
                ->waitFor('#modal-catat-transaksi', 10)
                ->within('#form-create-transaksi', function (Browser $m) {
                    $m->type('jumlah', '999999999');
                });
            $b->script('
                var r = document.querySelector(\'#form-create-transaksi input[value="PENGELUARAN"]\');
                if (r) { r.checked = true; }
                if (typeof updateToggleStyle === "function") { updateToggleStyle("PENGELUARAN"); }
                if (typeof cekAnggaranCreate === "function") { cekAnggaranCreate(); }
            ');
            $b->waitForTextIn('#create-over-warning', 'Melebihi anggaran', 5)
                ->assertSeeIn('#create-over-warning', 'Melebihi anggaran');
        });
    }
}