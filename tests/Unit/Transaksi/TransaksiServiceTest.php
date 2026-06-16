<?php

namespace Tests\Unit\Transaksi;

use App\Services\TransaksiService;
use Tests\Unit\Inc2TestCase;

class TransaksiServiceTest extends Inc2TestCase
{
    private function service(): TransaksiService
    {
        return app(TransaksiService::class); // hormati binding singleton + dependency parser
    }

    private function konteks(): array
    {
        return [
            'dompet' => $this->buatDompet(),
            'debit'  => $this->buatAkun('1-1000', 'Kas', 'DEBIT'),
            'kredit' => $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT'),
        ];
    }

    private function baris(array $override = []): array
    {
        return array_merge([
            'tanggal_transaksi' => '2026-06-15',
            'jumlah'            => 100000,
            'deskripsi'         => 'Transfer masuk',
            'no_referensi'      => 'REF-001',
            'is_duplikat'       => false,
        ], $override);
    }

    /** UT-F91-01 — simpanImport baris bersih */
    public function test_UT_F91_01_simpan_import_baris_bersih(): void
    {
        $this->periodeAktif();
        $k = $this->konteks();

        $hasil = $this->service()->simpanImport([
            'dompet_id'       => $k['dompet']->id,
            'jenis_transaksi' => 'PEMASUKAN',
            'akun_debit_id'   => $k['debit']->id,
            'akun_kredit_id'  => $k['kredit']->id,
            'rows'            => [$this->baris(), $this->baris(['no_referensi' => 'REF-002'])],
        ]);

        $this->assertFalse($hasil['gagalPeriode'] ?? false);
        $this->assertEquals(2, $hasil['tersimpan']);
        $this->assertEquals(2, \App\Models\Transaksi::count());
    }

    /** UT-F91-02 — simpanImport melewati duplikat */
    public function test_UT_F91_02_simpan_import_melewati_duplikat(): void
    {
        $this->periodeAktif();
        $k = $this->konteks();

        $hasil = $this->service()->simpanImport([
            'dompet_id'       => $k['dompet']->id,
            'jenis_transaksi' => 'PEMASUKAN',
            'akun_debit_id'   => $k['debit']->id,
            'akun_kredit_id'  => $k['kredit']->id,
            'rows'            => [
                $this->baris(['is_duplikat' => true]),
                $this->baris(['no_referensi' => 'REF-002']),
            ],
        ]);

        $this->assertGreaterThanOrEqual(1, ($hasil['dilewati'] ?? 0) + ($hasil['duplikat'] ?? 0));
        $this->assertEquals(1, $hasil['tersimpan']);
    }

    /** UT-F94-01 — simpanImport gagal tanpa periode aktif */
    public function test_UT_F94_01_simpan_import_gagal_periode(): void
    {
        $k = $this->konteks(); // TANPA periodeAktif()

        $hasil = $this->service()->simpanImport([
            'dompet_id'       => $k['dompet']->id,
            'jenis_transaksi' => 'PEMASUKAN',
            'akun_debit_id'   => $k['debit']->id,
            'akun_kredit_id'  => $k['kredit']->id,
            'rows'            => [$this->baris()],
        ]);

        $this->assertTrue($hasil['gagalPeriode']);
        $this->assertEquals(0, \App\Models\Transaksi::count());
    }
}