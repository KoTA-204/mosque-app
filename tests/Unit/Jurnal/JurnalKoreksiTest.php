<?php

namespace Tests\Unit\Jurnal;

use App\Models\Jurnal;
use App\Services\JurnalKoreksiService;
use Tests\Unit\Inc2TestCase;

class JurnalKoreksiTest extends Inc2TestCase
{
    /** UT-F98-01 — Store jurnal koreksi dengan referensi */
    public function test_UT_F98_01_store_dengan_referensi(): void
    {
        $periode = $this->periodeAktif();
        $ref     = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $debit   = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $kredit  = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $res = $this->post(route('dashboard.jurnal-koreksi.store'), [
            'periode_id'    => $periode->id,
            'tanggal'       => '2026-06-20',
            'jurnal_ref_id' => $ref->id,
            'keterangan'    => 'Koreksi salah catat',
            'submit_type'   => 'draft',
            'detail'        => [
                ['akun_id' => $debit->id,  'tipe' => 'DEBIT',  'nominal' => '50000'],
                ['akun_id' => $kredit->id, 'tipe' => 'KREDIT', 'nominal' => '50000'],
            ],
        ]);

        $res->assertRedirect(route('dashboard.jurnal-koreksi.index'));
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'KOREKSI']);
    }

    /** UT-F98-02 — getJurnalData hanya mengembalikan jurnal yang bisa dikoreksi (POSTED) */
    public function test_UT_F98_02_get_jurnal_data_hanya_posted(): void
    {
        $periode = $this->periodeAktif();
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'DRAFT']);

        $data = app(JurnalKoreksiService::class)->getJurnalData();

        $this->assertNotNull($data);
    }

    /** UT-F98-03 — getJurnalData hanya mengembalikan jurnal POSTED (jumlah benar) */
    public function test_UT_F98_03_get_jurnal_data_count_posted(): void
    {
        $periode = $this->periodeAktif();
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'DRAFT']);

        $this->assertCount(2, app(JurnalKoreksiService::class)->getJurnalData());
    }

    /** UT-F98-04 — store posting (balanced) → jurnal KOREKSI POSTED */
    public function test_UT_F98_04_store_posting_seimbang(): void
    {
        $periode = $this->periodeAktif();
        $ref     = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $debit   = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $kredit  = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $res = $this->post(route('dashboard.jurnal-koreksi.store'), [
            'periode_id' => $periode->id, 'tanggal' => '2026-06-20',
            'jurnal_ref_id' => $ref->id, 'keterangan' => 'Koreksi posting',
            'submit_type' => 'posting',
            'detail' => [
                ['akun_id' => $debit->id,  'tipe' => 'DEBIT',  'nominal' => '75000'],
                ['akun_id' => $kredit->id, 'tipe' => 'KREDIT', 'nominal' => '75000'],
            ],
        ]);

        $res->assertRedirect(route('dashboard.jurnal-koreksi.index'));
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'KOREKSI', 'status' => 'POSTED']);
    }

    /** UT-F98-05 — store posting tidak seimbang → error balance, tidak tersimpan */
    public function test_UT_F98_05_store_posting_tidak_seimbang_ditolak(): void
    {
        $periode = $this->periodeAktif();
        $ref     = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
        $debit   = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $kredit  = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        $res = $this->from(route('dashboard.jurnal-koreksi.index'))
            ->post(route('dashboard.jurnal-koreksi.store'), [
                'periode_id' => $periode->id, 'tanggal' => '2026-06-20',
                'jurnal_ref_id' => $ref->id, 'keterangan' => 'Koreksi timpang',
                'submit_type' => 'posting',
                'detail' => [
                    ['akun_id' => $debit->id,  'tipe' => 'DEBIT',  'nominal' => '75000'],
                    ['akun_id' => $kredit->id, 'tipe' => 'KREDIT', 'nominal' => '50000'],
                ],
            ]);

        $res->assertSessionHasErrors('balance');
        $this->assertDatabaseMissing('jurnal', ['jenis_jurnal' => 'KOREKSI']);
    }

    /** UT-F98-06 — destroy jurnal koreksi DRAFT → terhapus */
    public function test_UT_F98_06_destroy_draft_berhasil(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'KOREKSI', 'status' => 'DRAFT']);
        $this->delete(route('dashboard.jurnal-koreksi.destroy', $jurnal->id))
            ->assertRedirect(route('dashboard.jurnal-koreksi.index'));
        $this->assertDatabaseMissing('jurnal', ['id' => $jurnal->id]);
    }

    /** UT-F88-01 — Bulk post jurnal koreksi seimbang */
    public function test_UT_F88_01_bulk_post(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'KOREKSI', 'status' => 'DRAFT']);
        $debit  = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $kredit = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');
        $this->tambahDetail($jurnal, $debit, 'DEBIT', 50000);
        $this->tambahDetail($jurnal, $kredit, 'KREDIT', 50000);

        $res = $this->post(route('dashboard.jurnal-koreksi.bulk-post'), ['ids' => [$jurnal->id]]);

        $res->assertRedirect(route('dashboard.jurnal-koreksi.index'));
        $this->assertEquals('POSTED', $jurnal->fresh()->status);
    }
}