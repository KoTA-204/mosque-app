<?php

namespace Tests\Unit\Transaksi;

use App\Services\MutasiBankParserService;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\Unit\Inc2TestCase;

class TransaksiImportTest extends Inc2TestCase
{
    /** UT-F92-01 — Import parsing sukses (parser di-mock) */
    public function test_UT_F92_01_import_parsing_sukses(): void
    {
        $this->mock(MutasiBankParserService::class, function ($m) {
            $m->shouldReceive('parse')->andReturn([
                'rows'   => [
                    ['no_referensi' => 'A1', 'jumlah' => 100000, 'deskripsi' => 'Masuk', 'is_duplikat' => false],
                ],
                'errors' => [],
                'meta'   => ['total' => 1],
            ]);
        });

        $dompet = $this->buatDompet();
        $file   = UploadedFile::fake()->create(
            'mutasi.xlsx', 50, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $res = $this->post(route('dashboard.transaksi.import'), [
            'bank'            => 'BSI',
            'dompet_id'       => $dompet->id,
            'jenis_transaksi' => 'PEMASUKAN',
            'file'            => $file,
        ]);

        $this->assertContains($res->status(), [200, 302]);
    }

    /** UT-F93-01 — Import simpan validasi akun wajib (gagal validasi) */
    public function test_UT_F93_01_import_simpan_validasi_akun_wajib(): void
    {
        $dompet = $this->buatDompet();

        $res = $this->postJson(route('dashboard.transaksi.import.simpan'), [
            'dompet_id'       => $dompet->id,
            'jenis_transaksi' => 'PEMASUKAN',
            // akun_debit_id / akun_kredit_id sengaja dikosongkan
            'rows'            => [['no_referensi' => 'A1', 'jumlah' => 100000]],
        ]);

        $res->assertStatus(422);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}