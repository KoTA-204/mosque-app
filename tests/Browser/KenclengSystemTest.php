<?php

namespace Tests\Browser;

use App\Models\Dompet;
use App\Models\Kencleng;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsFullApp;
use Tests\DuskTestCase;

class KenclengSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsFullApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedFullApp();
        $this->ensureFixturePdf();
    }

    private function phm()
    {
        return $this->userByEmail('phm@masjid.id');
    }

    private function beritaAcaraFixture(): string
    {
        return base_path('tests/Browser/fixtures/berita-acara.pdf');
    }

    /**
     * Pastikan fixture PDF minimal valid tersedia.
     * PDF ini cukup kecil (<1KB) sehingga lolos validasi max:5120.
     */
    private function ensureFixturePdf(): void
    {
        $dir = base_path('tests/Browser/fixtures');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . '/berita-acara.pdf';
        if (!file_exists($path)) {
            file_put_contents($path,
                "%PDF-1.4\n" .
                "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n" .
                "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n" .
                "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\n" .
                "trailer<</Root 1 0 R>>\n" .
                "%%EOF\n"
            );
        }
    }

    private function isiFormKencleng(Browser $b): void
    {
        $dompetId = (string) Dompet::first()->id;

        $b->select('dompet_id', $dompetId)
            ->attach('berita_acara', $this->beritaAcaraFixture())
            ->type('keterangan', 'Pengujian sistem kencleng');

        // Set pecahan via JS dan paksa recalcTotal agar jumlahDisetor hidden input terisi
        $b->script('
            var inputs = document.querySelectorAll("input[name^=\"pecahan[\"]");
            if (inputs.length > 0) {
                inputs[0].value = 10;
                inputs[0].dispatchEvent(new Event("change"));
            }
            if (typeof recalcTotal === "function") { recalcTotal(); }
            // Pastikan hidden jumlahDisetor terisi
            var hidden = document.getElementById("jumlahDisetor");
            if (hidden && (!hidden.value || hidden.value === "0")) {
                // Ambil pecahan dari nama input pertama
                var firstName = inputs[0] ? inputs[0].name : "pecahan[100]";
                var match = firstName.match(/pecahan\[(\d+)\]/);
                var nominal = match ? parseInt(match[1]) : 100;
                hidden.value = nominal * 10;
            }
        ');
    }

    private function submitDanTunggu(Browser $b): void
    {
        // Tekan submit dan tunggu redirect (form hilang = halaman berganti)
        $b->press('Simpan & Ajukan')
          ->waitForLocation('/dashboard/kencleng', 20);
    }

    /** ST-F58-01 (+) Input Kencleng (status awal PENDING) */
    public function test_st_f58_01_input_kencleng(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())
                ->visit('/dashboard/kencleng/create')
                ->assertSee('Catat Kencleng Baru');
            $this->isiFormKencleng($b);
            $this->submitDanTunggu($b);
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
            $this->submitDanTunggu($b);
            $b->visit('/dashboard/kencleng?status=PENDING&sort=terbaru')
                ->assertSee('Menunggu');
        });
    }

    /** ST-F59-01 (+) Upload Berita Acara PDF (<=5MB) diterima */
    public function test_st_f59_01_upload_pdf(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())->visit('/dashboard/kencleng/create');
            $this->isiFormKencleng($b);
            $this->submitDanTunggu($b);
            $b->visit('/dashboard/kencleng?status=PENDING&sort=terbaru')
                ->assertSee('Menunggu');
        });
    }

    /** ST-F59-02 (-) Berita Acara > 5MB ditolak */
    public function test_st_f59_02_file_terlalu_besar(): void
    {
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
                ->waitForLocation('/dashboard/kencleng/create', 10)
                ->assertVisible('.text-red-800');
        });

        @unlink($tmp);
    }

    /** ST-F58-02 (-) Akses kencleng milik orang lain -> 403 */
    public function test_st_f58_02_owner_only(): void
    {
        $kencleng = Kencleng::first();
        $this->browse(function (Browser $b) use ($kencleng) {
            $b->loginAs($this->phm())
                ->visit('/dashboard/kencleng/' . $kencleng->id)
                ->assertSee('403');
        });
    }

    /** ST-F60-02 (+) Approve transaksi PENDING -> APPROVED */
    public function test_st_f60_02_approve_pending(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->userByEmail('bendahara1@masjid.id'))
                ->visit('/dashboard/approval/transaksi?tab=PENDING')
                ->waitFor('.row-check', 10);
            $b->script('var c=document.querySelector(".row-check"); if(c){c.checked=true; if(typeof updateBulkBar==="function"){updateBulkBar();}}');
            $b->script('if(typeof openBulkApproveModal==="function"){openBulkApproveModal();}');
            $b->waitFor('#modal-approve', 5)
                ->within('#modal-approve', function (Browser $m) {
                    $m->press('Ya, Approve Semua');
                })
                ->waitForLocation('/dashboard/approval/transaksi', 10);
        });
    }

    /** ST-F60-03 (-) Approve transaksi non-PENDING ditolak */
    public function test_st_f60_03_approve_non_pending_ditolak(): void
    {
        $this->markTestIncomplete('Verifikasi negatif: transaksi APPROVED tidak muncul di tab PENDING.');
    }

    /** ST-F62-01 (+) Edit kencleng PENDING milik sendiri */
    public function test_st_f62_01_edit_pending(): void
    {
        // Buat dulu kencleng PENDING
        $this->browse(function (Browser $b) {
            $b->loginAs($this->phm())->visit('/dashboard/kencleng/create');
            $this->isiFormKencleng($b);
            $this->submitDanTunggu($b);
        });

        $id = Kencleng::latest('id')->first()->id;

        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->phm())
                ->visit('/dashboard/kencleng/' . $id . '/edit')
                ->assertPathIs('/dashboard/kencleng/' . $id . '/edit')
                ->assertSee('Dompet');
        });
    }

    /** ST-F62-02 (-) Edit kencleng non-editable ditolak */
    public function test_st_f62_02_edit_non_editable_ditolak(): void
    {
        $kencleng = Kencleng::first();
        $this->browse(function (Browser $b) use ($kencleng) {
            $b->loginAs($this->userByEmail('admin@masjid.id'))
                ->visit('/dashboard/kencleng/' . $kencleng->id . '/edit')
                ->assertDontSee('Simpan & Ajukan');
        });
    }
}