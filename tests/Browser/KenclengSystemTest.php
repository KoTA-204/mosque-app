<?php

namespace Tests\Browser;

use App\Models\Dompet;
use App\Models\Kencleng;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsFullApp;
use Tests\DuskTestCase;

/**
 * SYSTEM TEST (Black Box) — Modul Kencleng.
 * List: dashboard.kencleng.index. Create = halaman penuh dashboard.kencleng.create.
 * Field create: tanggal_hitung (default hari ini), dompet_id, pecahan[<nominal>],
 *   berita_acara (file WAJIB), keterangan. Tombol "Simpan & Ajukan" -> status PENDING.
 * Approve via dashboard.approval (tab PENDING -> .row-check -> openBulkApproveModal() -> "Ya, Approve Semua").
 * Bergantung pada data seeder penuh + fixture tests/Browser/fixtures/berita-acara.pdf.
 */
class KenclengSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsFullApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedFullApp();
    }

    private function phm()
    {
        return $this->userByEmail('phm@masjid.id'); // punya CREATE_KENCLENG
    }

    private function beritaAcaraFixture(): string
    {
        return base_path('tests/Browser/fixtures/berita-acara.pdf');
    }

    private function isiFormKencleng(Browser $b): void
    {
        $dompetId = (string) Dompet::first()->id;
        $b->select('dompet_id', $dompetId)
            ->attach('berita_acara', $this->beritaAcaraFixture())
            ->type('keterangan', 'Pengujian sistem kencleng');
        // isi pecahan pertama = 10 lembar/keping lalu hitung total (sinkron ke jumlah_disetor)
        $b->script('var i=document.querySelector(\'input[name^="pecahan["]\'); if(i){i.value=10; if(typeof recalcTotal==="function"){recalcTotal();}}');
    }

    /** ST-F58-01 (+) Input Kencleng (status awal PENDING) */
    public function test_st_f58_01_input_kencleng(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())
                ->visit('/dashboard/kencleng/create')
                ->assertSee('Catat Kencleng Baru');
            $this->isiFormKencleng($b);
            $b->press('Simpan & Ajukan')
                ->waitUntilMissing('#kenclengForm', 10); // form hilang = submit sukses (redirect ke index/show)
            $b->visit('/dashboard/kencleng?status=PENDING&sort=terbaru')
                ->assertSee('Menunggu');
        });
    }

    /** ST-F60-01 (+) Status awal kencleng = PENDING (badge "Menunggu") */
    public function test_st_f60_01_status_awal_pending(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())->visit('/dashboard/kencleng/create');
            $this->isiFormKencleng($b);
            $b->press('Simpan & Ajukan')
                ->waitUntilMissing('#kenclengForm', 10); // form hilang = submit sukses (redirect ke index/show)
            $b->visit('/dashboard/kencleng?status=PENDING&sort=terbaru')
                ->assertSee('Menunggu');
        });
    }

    /** ST-F59-01 (+) Upload Berita Acara PDF (<=5MB) diterima */
    public function test_st_f59_01_upload_pdf(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())->visit('/dashboard/kencleng/create');
            $this->isiFormKencleng($b); // melampirkan berita-acara.pdf
            $b->press('Simpan & Ajukan')
                ->waitUntilMissing('#kenclengForm', 10); // form hilang = submit sukses (redirect ke index/show)
            $b->visit('/dashboard/kencleng?status=PENDING&sort=terbaru')
                ->assertSee('Menunggu');
        });
    }

    /** ST-F59-02 (-) Berita Acara > 5MB ditolak */
    public function test_st_f59_02_file_terlalu_besar(): void
    {
        // PDF valid (header %PDF) namun ~6MB -> lolos mime, gagal aturan max:5120
        $tmp = tempnam(sys_get_temp_dir(), 'ba_') . '.pdf';
        file_put_contents($tmp, "%PDF-1.4\n" . str_repeat('0', 6 * 1024 * 1024));

        $this->browse(function (Browser $b) use ($tmp) {
            $dompetId = (string) Dompet::first()->id;
            $b->loginAs($this->phm())
                ->visit('/dashboard/kencleng/create')
                ->select('dompet_id', $dompetId)
                ->attach('berita_acara', $tmp);
            $b->script('var i=document.querySelector(\'input[name^="pecahan["]\'); if(i){i.value=5; if(typeof recalcTotal==="function"){recalcTotal();}}');
            $b->press('Simpan & Ajukan')
                ->waitForLocation('/dashboard/kencleng/create')
                ->assertVisible('.text-red-800'); // blok daftar error validasi
        });

        @unlink($tmp);
    }

    /** ST-F58-02 (-) Akses kencleng milik orang lain -> 403 */
    public function test_st_f58_02_owner_only(): void
    {
        $kencleng = Kencleng::first(); // dimiliki Administrator (dari seeder)
        $this->browse(function (Browser $b) use ($kencleng) {
            $b->loginAs($this->phm()) // bukan pemilik
                ->visit('/dashboard/kencleng/' . $kencleng->id)
                ->assertSee('403');
        });
    }

    /** ST-F60-02 (+) Approve transaksi PENDING -> APPROVED (halaman Approval) */
    public function test_st_f60_02_approve_pending(): void
    {
        // Bendahara 1 punya VIEW/MANAGE_APPROVAL; seeder menyediakan transaksi PENDING.
        $this->browse(function (Browser $b) {
            $b->loginAs($this->userByEmail('bendahara1@masjid.id'))
                ->visit('/dashboard/approval/transaksi?tab=PENDING')
                ->waitFor('.row-check');
            $b->script('var c=document.querySelector(".row-check"); if(c){c.checked=true; if(typeof updateBulkBar==="function"){updateBulkBar();}}');
            $b->script('if(typeof openBulkApproveModal==="function"){openBulkApproveModal();}');
            $b->waitFor('#modal-approve')
                ->within('#modal-approve', function (Browser $m) {
                    $m->press('Ya, Approve Semua');
                })
                ->waitForLocation('/dashboard/approval/transaksi');
        });
    }

    /** ST-F60-03 (-) Approve transaksi non-PENDING ditolak */
    public function test_st_f60_03_approve_non_pending_ditolak(): void
    {
        $this->markTestIncomplete('Verifikasi negatif: transaksi APPROVED tidak muncul di tab PENDING. Tandai 1 transaksi APPROVED lalu assertDontSee kodenya di tab PENDING.');
    }

    /** ST-F62-01 (+) Edit kencleng PENDING milik sendiri */
    public function test_st_f62_01_edit_pending(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())->visit('/dashboard/kencleng/create');
            $this->isiFormKencleng($b);
            $b->press('Simpan & Ajukan')->waitUntilMissing('#kenclengForm', 10);

            $id = Kencleng::latest('id')->first()->id;
            $b->visit('/dashboard/kencleng/' . $id . '/edit')
                ->assertPathIs('/dashboard/kencleng/' . $id . '/edit') // tidak diblok/redirect: PENDING + pemilik
                ->assertSee('Dompet');
        });
    }

    /** ST-F62-02 (-) Edit kencleng non-editable (status bukan PENDING/REVISION/DRAFT) ditolak */
    public function test_st_f62_02_edit_non_editable_ditolak(): void
    {
        $kencleng = Kencleng::first(); // transaksinya berstatus non-editable
        $this->browse(function (Browser $b) use ($kencleng) {
            // login sebagai pemilik (Administrator) agar lolos cek owner, lalu cek blokir status
            $b->loginAs($this->userByEmail('admin@masjid.id'))
                ->visit('/dashboard/kencleng/' . $kencleng->id . '/edit')
                ->assertDontSee('Simpan & Ajukan'); // form edit tidak tampil; diblok controller
        });
    }
}
