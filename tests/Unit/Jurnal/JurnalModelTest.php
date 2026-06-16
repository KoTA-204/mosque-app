<?php

namespace Tests\Unit\Jurnal;

use Tests\Unit\Inc2TestCase;

class JurnalModelTest extends Inc2TestCase
{
    /** UT-F86-01 — Accessor is_balance */
    public function test_UT_F86_01_is_balance(): void
    {
        $jurnal = $this->buatJurnal();
        $kas    = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend   = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $this->tambahDetail($jurnal, $kas, 'DEBIT', 100000);
        $this->tambahDetail($jurnal, $pend, 'KREDIT', 100000);

        $this->assertTrue($jurnal->fresh()->is_balance);

        $this->tambahDetail($jurnal, $pend, 'KREDIT', 50000);
        $this->assertFalse($jurnal->fresh()->is_balance);
    }

    /** UT-F29-01 — Format kode jurnal UMUM */
    public function test_UT_F29_01_kode_jurnal_format_umum(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'UMUM', 'tanggal' => '2026-06-15']);

        $this->assertStringStartsWith('JU-2026-06-', $jurnal->kode_jurnal);
    }
}