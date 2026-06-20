<?php

namespace Tests\Unit\BukuBesar;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Periode;
use Tests\Unit\Inc2TestCase;

class BukuBesarControllerTest extends Inc2TestCase
{
    private Periode $periode;
    private Akun $kas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->periode = $this->periodeAktif();          // Juni 2026 (2026-06-01..06-30, status true)
        $this->kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
    }

    /** Buat jurnal langsung (hindari buatJurnal yg memicu periode phantom). */
    private function jurnal(string $jenis, Periode $periode, string $tanggal = '2026-06-15', string $status = 'POSTED'): Jurnal
    {
        return Jurnal::create([
            'periode_id'   => $periode->id,
            'tanggal'      => $tanggal,
            'jenis_jurnal' => $jenis,
            'status'       => $status,
            'keterangan'   => 'Test ' . $jenis,
        ]);
    }

    /** Request AJAX ke Buku Besar dan kembalikan response JSON. */
    private function requestBukuBesar(array $params = [])
    {
        return $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('dashboard.buku-besar.index', $params));
    }

    public function test_UT_F28_01_hanya_jurnal_posted_yang_dihitung(): void
    {
        $pendapatan = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        // POSTED → dihitung
        $posted = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($posted, $this->kas, 'DEBIT', 500_000);
        $this->tambahDetail($posted, $pendapatan, 'KREDIT', 500_000);

        // DRAFT → diabaikan
        $draft = $this->jurnal('UMUM', $this->periode, '2026-06-16', 'DRAFT');
        $this->tambahDetail($draft, $this->kas, 'DEBIT', 999_000);
        $this->tambahDetail($draft, $pendapatan, 'KREDIT', 999_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        $this->assertEquals(500_000, (float) $res->json('totalDebit')); // draft 999.000 tidak masuk
    }

    public function test_UT_F28_02_menghitung_total_debit_dan_kredit_akun(): void
    {
        $pendapatan = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $j = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($j, $this->kas, 'DEBIT', 500_000);
        $this->tambahDetail($j, $pendapatan, 'KREDIT', 500_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        $this->assertEquals(500_000, (float) $res->json('totalDebit'));
        $this->assertEquals(0, (float) $res->json('totalKredit'));
    }

    public function test_UT_F28_03_saldo_akhir_adalah_saldo_awal_tambah_debit_kurang_kredit(): void
    {
        $asetNeto = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');
        $pembuka  = $this->jurnal('PEMBUKA', $this->periode);
        $this->tambahDetail($pembuka, $this->kas, 'DEBIT', 1_000_000);
        $this->tambahDetail($pembuka, $asetNeto, 'KREDIT', 1_000_000);

        $beban = $this->buatAkun('5-1000', 'Beban', 'DEBIT');
        $j = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($j, $beban, 'DEBIT', 200_000);
        $this->tambahDetail($j, $this->kas, 'KREDIT', 200_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        $saldoAwal   = (float) $res->json('saldoAwal');
        $totalDebit  = (float) $res->json('totalDebit');
        $totalKredit = (float) $res->json('totalKredit');
        $saldoAkhir  = (float) $res->json('saldoAkhir');

        // verifikasi rumus controller: saldoAkhir = saldoAwal + debit - kredit
        $this->assertEquals($saldoAwal + $totalDebit - $totalKredit, $saldoAkhir);
    }

    public function test_UT_F79_01_saldo_akhir_gabungan_saldo_awal_dan_mutasi(): void
    {
        $periodeLalu = Periode::create([
            'nama_periode'  => 'Mei 2026',
            'tipe'          => 'bulanan',
            'tanggal_awal'  => '2026-05-01',
            'tanggal_akhir' => '2026-05-31',
            'status'        => false,
        ]);
        $asetNeto = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');

        // saldo awal dari PEMBUKA periode lalu → 1.000.000
        $pembuka = $this->jurnal('PEMBUKA', $periodeLalu, '2026-05-01');
        $this->tambahDetail($pembuka, $this->kas, 'DEBIT', 1_000_000);
        $this->tambahDetail($pembuka, $asetNeto, 'KREDIT', 1_000_000);

        // mutasi periode berjalan: +300.000 lalu -100.000
        $pendapatan = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');
        $beban      = $this->buatAkun('5-1000', 'Beban', 'DEBIT');

        $jMasuk = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($jMasuk, $this->kas, 'DEBIT', 300_000);
        $this->tambahDetail($jMasuk, $pendapatan, 'KREDIT', 300_000);

        $jKeluar = $this->jurnal('UMUM', $this->periode, '2026-06-20');
        $this->tambahDetail($jKeluar, $beban, 'DEBIT', 100_000);
        $this->tambahDetail($jKeluar, $this->kas, 'KREDIT', 100_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        $this->assertEquals(1_000_000, (float) $res->json('saldoAwal'));
        $this->assertEquals(300_000, (float) $res->json('totalDebit'));
        $this->assertEquals(100_000, (float) $res->json('totalKredit'));
        $this->assertEquals(1_200_000, (float) $res->json('saldoAkhir')); // 1jt + 300rb - 100rb
    }

    public function test_UT_F79_03_saldo_awal_dari_jurnal_pembuka_periode_sebelumnya(): void
    {
        $periodeLalu = Periode::create([
            'nama_periode'  => 'Mei 2026',
            'tipe'          => 'bulanan',
            'tanggal_awal'  => '2026-05-01',
            'tanggal_akhir' => '2026-05-31',
            'status'        => false,
        ]);

        $asetNeto = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');
        $pembuka  = $this->jurnal('PEMBUKA', $periodeLalu, '2026-05-01');
        $this->tambahDetail($pembuka, $this->kas, 'DEBIT', 1_000_000);
        $this->tambahDetail($pembuka, $asetNeto, 'KREDIT', 1_000_000);

        // pilih periode SEKARANG → mutasi kosong, tapi saldoAwal tetap dari PEMBUKA
        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        $this->assertEquals(1_000_000, (float) $res->json('saldoAwal'));
        $this->assertEquals(0, (float) $res->json('totalDebit'));
        $this->assertEquals(1_000_000, (float) $res->json('saldoAkhir'));
    }

    public function test_UT_F80_01_filter_periode_membatasi_mutasi(): void
    {
        $periodeLalu = Periode::create([
            'nama_periode'  => 'Mei 2026',
            'tipe'          => 'bulanan',
            'tanggal_awal'  => '2026-05-01',
            'tanggal_akhir' => '2026-05-31',
            'status'        => false,
        ]);
        $pendapatan = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        // mutasi periode lalu (Mei) 400.000
        $jLalu = $this->jurnal('UMUM', $periodeLalu, '2026-05-10');
        $this->tambahDetail($jLalu, $this->kas, 'DEBIT', 400_000);
        $this->tambahDetail($jLalu, $pendapatan, 'KREDIT', 400_000);

        // mutasi periode ini (Juni) 500.000
        $jIni = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($jIni, $this->kas, 'DEBIT', 500_000);
        $this->tambahDetail($jIni, $pendapatan, 'KREDIT', 500_000);

        // filter periode SEKARANG → hanya 500.000 yang dihitung
        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        $this->assertEquals(500_000, (float) $res->json('totalDebit')); // mutasi Mei tidak masuk
    }

    public function test_UT_F89_01_badge_akun_sesuai_saldo_normal_kredit(): void
    {
        $utang = $this->buatAkun('2-1000', 'Utang Usaha', 'KREDIT');

        $j = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($j, $this->kas, 'DEBIT', 300_000);
        $this->tambahDetail($j, $utang, 'KREDIT', 300_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $utang->id,
        ]);

        $res->assertOk();
        // Utang saldo_normal KREDIT, net -300.000 (<=0) → badge KREDIT
        $this->assertEquals('KREDIT', $res->json('badgeAkun'));
    }

    public function test_UT_F89_02_badge_akun_sesuai_saldo_normal_debit(): void
    {
        $pendapatan = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $j = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($j, $this->kas, 'DEBIT', 700_000);
        $this->tambahDetail($j, $pendapatan, 'KREDIT', 700_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();
        // Kas saldo_normal DEBIT, net 700.000 (>=0) → badge DEBIT
        $this->assertEquals('DEBIT', $res->json('badgeAkun'));
    }

    public function test_UT_F79_04_pembuka_di_periode_terpilih_tidak_double_count(): void
    {
        $asetNeto   = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');
        $pendapatan = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        // PEMBUKA berada di periode yang DIPILIH (Juni) → saldo awal Kas 1.000.000
        $pembuka = $this->jurnal('PEMBUKA', $this->periode);
        $this->tambahDetail($pembuka, $this->kas, 'DEBIT', 1_000_000);
        $this->tambahDetail($pembuka, $asetNeto, 'KREDIT', 1_000_000);

        // Mutasi berjalan di periode yang sama: Kas masuk 500.000
        $mutasi = $this->jurnal('UMUM', $this->periode);
        $this->tambahDetail($mutasi, $this->kas, 'DEBIT', 500_000);
        $this->tambahDetail($mutasi, $pendapatan, 'KREDIT', 500_000);

        $res = $this->requestBukuBesar([
            'periode_id' => $this->periode->id,
            'akun_id'    => $this->kas->id,
        ]);

        $res->assertOk();

        // Saldo awal HANYA dari PEMBUKA = 1.000.000
        $this->assertEquals(1_000_000, (float) $res->json('saldoAwal'));

        // PEMBUKA TIDAK boleh ikut mutasi → totalDebit hanya 500.000 (bukan 1.500.000)
        $this->assertEquals(500_000, (float) $res->json('totalDebit'));
        $this->assertEquals(0, (float) $res->json('totalKredit'));

        // Inti regresi: saldoAkhir TIDAK double-count.
        //  - Tanpa fix: PEMBUKA ikut totalDebit (1,5jt) → saldoAkhir = 1jt + 1,5jt = 2,5jt ❌
        //  - Dengan fix: saldoAkhir = 1jt + 500rb - 0 = 1,5jt ✅
        $this->assertEquals(1_500_000, (float) $res->json('saldoAkhir'));
    }
}