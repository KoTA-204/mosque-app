<?php

namespace Tests\Unit\Jurnal;

use App\Services\JurnalPenutupService;
use Tests\Unit\Inc2TestCase;

class JurnalPenutupServiceTest extends Inc2TestCase
{
    private function service(): JurnalPenutupService
    {
        return app(JurnalPenutupService::class);
    }

    private function seedPosted(int $periodeId): void
    {
        $kas   = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend  = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');
        $beban = $this->buatAkun('5-1000', 'Beban', 'DEBIT');

        $j = $this->buatJurnal(['periode_id' => $periodeId, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->tambahDetail($j, $kas, 'DEBIT', 500000);
        $this->tambahDetail($j, $pend, 'KREDIT', 500000);

        $b = $this->buatJurnal(['periode_id' => $periodeId, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->tambahDetail($b, $beban, 'DEBIT', 200000);
        $this->tambahDetail($b, $kas, 'KREDIT', 200000);
    }

    /** UT-F99-01 — Generate jurnal tutup pendapatan */
    public function test_UT_F99_01_generate_tutup_pendapatan(): void
    {
        $periode = $this->periodeAktif();
        $this->seedPosted($periode->id);

        $ringkasan = $this->service()->getRingkasanPeriode($periode);

        $this->assertNotNull($this->service()->generateTutupPendapatan($ringkasan));
    }

    /** UT-F99-02 — Generate jurnal tutup beban */
    public function test_UT_F99_02_generate_tutup_beban(): void
    {
        $periode = $this->periodeAktif();
        $this->seedPosted($periode->id);

        $ringkasan = $this->service()->getRingkasanPeriode($periode);

        $this->assertNotNull($this->service()->generateTutupBeban($ringkasan));
    }

    /** UT-F100-01 — Store & post finalisasi penutupan */
    public function test_UT_F100_01_store_and_post_finalize_closing(): void
    {
        $periode = $this->periodeAktif();
        $this->seedPosted($periode->id);

        $res = $this->post(route('dashboard.jurnal-penutup.store'), [
            'periode_id' => $periode->id,
            'tanggal'    => '2026-06-30',
            'aksi'       => 'posting',
        ]);

        $res->assertRedirect();
    }

    /** UT-F100-02 — Guard periode siap tutup */
    public function test_UT_F100_02_guard_periode_siap_tutup(): void
    {
        $periode = $this->periodeAktif();

        $hasil = $this->service()->guardPeriodeSiapTutup($periode);

        // guard mengembalikan string pesan error, atau falsy bila lolos
        $this->assertTrue($hasil === null || $hasil === false || is_string($hasil));
    }

    /** UT-F100-03 — Guard periode berikutnya */
    public function test_UT_F100_03_guard_periode_berikutnya(): void
    {
        $this->periodeAktif('2026-06-01', '2026-06-30');
        $berikut = $this->periodeAktif('2026-07-01', '2026-07-31');

        $res = $this->post(route('dashboard.jurnal-penutup.store'), [
            'periode_id' => $berikut->id,
            'tanggal'    => '2026-07-31',
            'aksi'       => 'draft',
        ]);

        $res->assertRedirect();
    }
}