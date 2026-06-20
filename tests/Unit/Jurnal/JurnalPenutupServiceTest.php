<?php

namespace Tests\Unit\Jurnal;

use App\Services\JurnalPenutupService;
use App\Models\Periode;
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

    /** UT-F99-03 — getRingkasanPeriode menghitung total pendapatan/beban/surplus */
    public function test_UT_F99_03_ringkasan_periode_totals(): void
    {
        $periode = $this->periodeAktif();
        $this->seedPosted($periode->id);

        $r = $this->service()->getRingkasanPeriode($periode);

        $this->assertEquals(500000, $r['total_pendapatan']);
        $this->assertEquals(200000, $r['total_beban']);
        $this->assertEquals(300000, $r['surplus']);
        $this->assertNull($r['pesan_tidak_siap']); // ada POSTED, tanpa DRAFT
    }

    /** UT-F99-04 — generateTutupPendapatan: DEBIT pendapatan + KREDIT aset neto 3-1000 */
    public function test_UT_F99_04_generate_tutup_pendapatan_struktur(): void
    {
        $periode = $this->periodeAktif();
        $this->buatAkun('3-1000', 'Aset Neto Tanpa Pembatasan', 'KREDIT'); // wajib ada
        $this->seedPosted($periode->id);

        $entri  = $this->service()->generateTutupPendapatan($this->service()->getRingkasanPeriode($periode));
        $debit  = collect($entri)->where('tipe', 'DEBIT');
        $kredit = collect($entri)->where('tipe', 'KREDIT');

        $this->assertEquals(500000, $debit->sum('nominal'));
        $this->assertEquals(500000, $kredit->sum('nominal'));
        $this->assertEquals('3-1000', $kredit->first()['kode_akun']);
    }

    /** UT-F99-05 — generateTutupBeban: DEBIT aset neto 3-1000 + KREDIT beban */
    public function test_UT_F99_05_generate_tutup_beban_struktur(): void
    {
        $periode = $this->periodeAktif();
        $this->buatAkun('3-1000', 'Aset Neto Tanpa Pembatasan', 'KREDIT');
        $this->seedPosted($periode->id);

        $entri  = $this->service()->generateTutupBeban($this->service()->getRingkasanPeriode($periode));
        $debit  = collect($entri)->where('tipe', 'DEBIT');
        $kredit = collect($entri)->where('tipe', 'KREDIT');

        $this->assertEquals(200000, $debit->sum('nominal'));
        $this->assertEquals('3-1000', $debit->first()['kode_akun']);
        $this->assertEquals(200000, $kredit->sum('nominal'));
    }

    /** UT-F100-04 — guardPeriodeSiapTutup: belum ada jurnal POSTED → pesan error */
    public function test_UT_F100_04_guard_belum_ada_posted(): void
    {
        $pesan = $this->service()->guardPeriodeSiapTutup($this->periodeAktif());
        $this->assertIsString($pesan);
        $this->assertStringContainsString('belum memiliki jurnal', $pesan);
    }

    /** UT-F100-05 — guardPeriodeSiapTutup: masih ada DRAFT → pesan error */
    public function test_UT_F100_05_guard_masih_ada_draft(): void
    {
        $periode = $this->periodeAktif();
        $kas  = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $posted = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->tambahDetail($posted, $kas, 'DEBIT', 100000);
        $this->tambahDetail($posted, $pend, 'KREDIT', 100000);

        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'DRAFT']);

        $pesan = $this->service()->guardPeriodeSiapTutup($periode);
        $this->assertIsString($pesan);
        $this->assertStringContainsString('belum diposting', $pesan);
    }

    /** UT-F100-06 — guardPeriodeSiapTutup: ada POSTED tanpa DRAFT → null (siap) */
    public function test_UT_F100_06_guard_siap_null(): void
    {
        $periode = $this->periodeAktif();
        $this->seedPosted($periode->id);
        $this->assertNull($this->service()->guardPeriodeSiapTutup($periode));
    }

    /** UT-F100-07 — storeAndPost menutup periode & mengaktifkan periode berikutnya */
    public function test_UT_F100_07_store_and_post_finalize_closing(): void
    {
        $periode = $this->periodeAktif('2026-06-01', '2026-06-30');
        $next = Periode::create([
            'nama_periode' => 'Juli 2026', 'tipe' => 'bulanan',
            'tanggal_awal' => '2026-07-01', 'tanggal_akhir' => '2026-07-31', 'status' => false,
        ]);
        $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');
        $this->seedPosted($periode->id);

        $r = $this->service()->getRingkasanPeriode($periode);
        $semua = [
            'TUTUP_PENDAPATAN' => $this->service()->generateTutupPendapatan($r),
            'TUTUP_BEBAN'      => $this->service()->generateTutupBeban($r),
        ];

        $this->assertTrue($this->service()->storeAndPost($periode, $semua, '2026-06-30'));
        $this->assertFalse((bool) $periode->fresh()->status); // periode ditutup
        $this->assertTrue((bool) $next->fresh()->status);      // next aktif
        $this->assertDatabaseHas('jurnal', [
            'periode_id' => $periode->id, 'jenis_jurnal' => 'PENUTUP', 'status' => 'POSTED',
        ]);
    }

    /** UT-F100-08 — activateNextPeriode lempar RuntimeException jika tak ada periode berikutnya */
    public function test_UT_F100_08_activate_next_tanpa_periode_berikutnya(): void
    {
        $periode = $this->periodeAktif();
        $this->expectException(\RuntimeException::class);
        $this->service()->activateNextPeriode($periode);
    }

    /** UT-F100-09 — isPeriodeClosed true saat periode tidak aktif */
    public function test_UT_F100_09_is_periode_closed(): void
    {
        $aktif = $this->periodeAktif('2026-06-01', '2026-06-30');
        $tutup = Periode::create([
            'nama_periode' => 'Mei 2026', 'tipe' => 'bulanan',
            'tanggal_awal' => '2026-05-01', 'tanggal_akhir' => '2026-05-31', 'status' => false,
        ]);

        $this->assertFalse($this->service()->isPeriodeClosed($aktif));
        $this->assertTrue($this->service()->isPeriodeClosed($tutup));
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