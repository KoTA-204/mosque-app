<?php
namespace Tests\Unit\Transaksi;

use App\Services\TransaksiService;
use Tests\Unit\Inc2TestCase;

class TransaksiServiceTest extends Inc2TestCase
{
    private function service(): TransaksiService
    {
        return app(TransaksiService::class);
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

    /** Helper: bangun 1 klasifikasi berisi entries debit+kredit seimbang. */
    private function klas(string $ref, array $k, int $nominal = 100000): array
    {
        return [
            'no_referensi' => $ref,
            'entries' => [
                ['akun_id' => $k['debit']->id,  'tipe' => 'DEBIT',  'nominal' => $nominal],
                ['akun_id' => $k['kredit']->id, 'tipe' => 'KREDIT', 'nominal' => $nominal],
            ],
        ];
    }

    /** UT-F91-01 — simpanImport baris bersih */
    public function test_UT_F91_01_simpan_import_baris_bersih(): void
    {
        $this->periodeAktif();
        $k = $this->konteks();

        $hasil = $this->service()->simpanImport([
            'dompet_id'       => $k['dompet']->id,
            'jenis_transaksi' => 'PEMASUKAN',
            'rows'            => [$this->baris(), $this->baris(['no_referensi' => 'REF-002'])],
        ], [
            $this->klas('REF-001', $k),
            $this->klas('REF-002', $k),
        ]);

        $this->assertEmpty($hasil['gagalPeriode'] ?? []);
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
            'rows'            => [
                $this->baris(['is_duplikat' => true]),
                $this->baris(['no_referensi' => 'REF-002']),
            ],
        ], [
            $this->klas('REF-001', $k),
            $this->klas('REF-002', $k),
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
            'rows'            => [$this->baris()],
        ], [
            $this->klas('REF-001', $k),
        ]);

        $this->assertTrue(!empty($hasil['gagalPeriode']));
        $this->assertEquals(0, \App\Models\Transaksi::count());
    }
}