<?php

namespace Tests\Unit\BukuBesar;

use Tests\Unit\Inc2TestCase;

class BukuBesarControllerTest extends Inc2TestCase
{
    private function ajax(array $params)
    {
        return $this->get(
            route('dashboard.buku-besar.index', $params),
            ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']
        );
    }

    /** UT-F28-01 — Hanya jurnal POSTED yang dihitung */
    public function test_UT_F28_01_hanya_posted(): void
    {
        $periode = $this->periodeAktif();
        $kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend    = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $posted = $this->buatJurnal(['periode_id' => $periode->id, 'status' => 'POSTED']);
        $this->tambahDetail($posted, $kas, 'DEBIT', 100000);
        $this->tambahDetail($posted, $pend, 'KREDIT', 100000);

        $draft = $this->buatJurnal(['periode_id' => $periode->id, 'status' => 'DRAFT']);
        $this->tambahDetail($draft, $kas, 'DEBIT', 999000);

        $res = $this->ajax(['akun_id' => $kas->id, 'periode_id' => $periode->id]);

        $res->assertOk();
        $this->assertStringNotContainsString('999000', $res->getContent());
    }

    /** UT-F79-01 — Saldo akhir = saldo awal + debit - kredit */
    public function test_UT_F79_01_saldo_akhir(): void
    {
        $periode = $this->periodeAktif();
        $kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend    = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        // Saldo awal 200rb dari jurnal PEMBUKA
        $pembuka = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'PEMBUKA', 'status' => 'POSTED']);
        $this->tambahDetail($pembuka, $kas, 'DEBIT', 200000);

        // Transaksi berjalan: +500rb debit
        $umum = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->tambahDetail($umum, $kas, 'DEBIT', 500000);
        $this->tambahDetail($umum, $pend, 'KREDIT', 500000);

        $res = $this->ajax(['akun_id' => $kas->id, 'periode_id' => $periode->id]);

        $res->assertOk(); // saldo akhir 700.000
        $this->assertStringContainsString('700', $res->getContent());
    }

    /** UT-F89-01 — Saldo awal diambil dari jurnal PEMBUKA */
    public function test_UT_F89_01_saldo_awal_dari_pembuka(): void
    {
        $periode = $this->periodeAktif();
        $kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');

        $pembuka = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'PEMBUKA', 'status' => 'POSTED']);
        $this->tambahDetail($pembuka, $kas, 'DEBIT', 300000);

        $res = $this->ajax(['akun_id' => $kas->id, 'periode_id' => $periode->id]);

        $res->assertOk();
    }

    /** UT-F80-01 — Filter berdasarkan periode */
    public function test_UT_F80_01_filter_periode(): void
    {
        $periode = $this->periodeAktif();
        $kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');

        $res = $this->ajax(['akun_id' => $kas->id, 'periode_id' => $periode->id]);

        $res->assertOk();
    }
}