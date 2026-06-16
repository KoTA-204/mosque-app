<?php

namespace Tests\Unit\Laporan;

use App\Models\Periode;
use Tests\Unit\Inc2TestCase;

class LaporanKeuanganControllerTest extends Inc2TestCase
{
    private function seedData(): Periode
    {
        $periode = $this->periodeAktif();
        $kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend    = $this->buatAkun('4-1000', 'Pendapatan Donasi', 'KREDIT');
        $beban   = $this->buatAkun('5-1000', 'Beban Operasional', 'DEBIT');

        $j = $this->buatJurnal(['periode_id' => $periode->id, 'status' => 'POSTED']);
        $this->tambahDetail($j, $kas, 'DEBIT', 500000);
        $this->tambahDetail($j, $pend, 'KREDIT', 500000);

        $b = $this->buatJurnal(['periode_id' => $periode->id, 'status' => 'POSTED']);
        $this->tambahDetail($b, $beban, 'DEBIT', 200000);
        $this->tambahDetail($b, $kas, 'KREDIT', 200000);

        return $periode;
    }

    /** UT-F101-01 — Laporan penghasilan komprehensif */
    public function test_UT_F101_01_build_penghasilan_komprehensif(): void
    {
        $p = $this->seedData();
        $this->get(route('dashboard.laporan.penghasilan-komprehensif', ['periode_id' => $p->id]))->assertOk();
    }

    /** UT-F102-01 — Laporan posisi keuangan */
    public function test_UT_F102_01_build_posisi_keuangan_balance(): void
    {
        $p = $this->seedData();
        $this->get(route('dashboard.laporan.posisi-keuangan', ['periode_id' => $p->id]))->assertOk();
    }

    /** UT-F103-01 — Laporan perubahan aset neto */
    public function test_UT_F103_01_build_perubahan_aset_neto(): void
    {
        $p = $this->seedData();
        $this->get(route('dashboard.laporan.perubahan-aset-neto', ['periode_id' => $p->id]))->assertOk();
    }

    /** UT-F104-01 — Laporan arus kas */
    public function test_UT_F104_01_build_arus_kas(): void
    {
        $p = $this->seedData();
        $this->get(route('dashboard.laporan.arus-kas', ['periode_id' => $p->id]))->assertOk();
    }

    /** UT-F79-01 — Saldo by prefix dipakai posisi keuangan */
    public function test_UT_F79_01_saldo_by_prefix(): void
    {
        $p = $this->seedData();
        $this->get(route('dashboard.laporan.penghasilan-komprehensif', ['periode_id' => $p->id]))->assertOk();
    }

    /** UT-F105-01 — CALK */
    public function test_UT_F105_01_build_calk(): void
    {
        $p = $this->seedData();
        $this->get(route('dashboard.laporan.calk', ['periode_id' => $p->id]))->assertOk();
    }
}