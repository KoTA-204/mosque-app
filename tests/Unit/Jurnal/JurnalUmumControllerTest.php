<?php

namespace Tests\Unit\Jurnal;

use Tests\Unit\Inc2TestCase;

class JurnalUmumControllerTest extends Inc2TestCase
{
    private function detailSeimbang(): array
    {
        $debit  = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $kredit = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');

        return [
            ['akun_id' => $debit->id,  'tipe' => 'DEBIT',  'nominal' => '100000'],
            ['akun_id' => $kredit->id, 'tipe' => 'KREDIT', 'nominal' => '100000'],
        ];
    }

    /** UT-F85-01 — Store sebagai draft */
    public function test_UT_F85_01_store_draft(): void
    {
        $res = $this->post(route('dashboard.jurnal-umum.store'), [
            'periode_id' => $this->periodeAktif()->id,
            'tanggal'    => '2026-06-15',
            'keterangan' => 'Jurnal umum uji',
            'detail'     => $this->detailSeimbang(),
        ]);

        $res->assertRedirect(route('dashboard.jurnal-umum.index'));
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'UMUM', 'status' => 'DRAFT']);
    }

    /** UT-F85-02 — Store langsung posted (action=post) */
    public function test_UT_F85_02_store_langsung_posted(): void
    {
        $res = $this->post(route('dashboard.jurnal-umum.store'), [
            'periode_id' => $this->periodeAktif()->id,
            'tanggal'    => '2026-06-15',
            'keterangan' => 'Jurnal umum posted',
            'detail'     => $this->detailSeimbang(),
            'action'     => 'post',
        ]);

        $res->assertRedirect(route('dashboard.jurnal-umum.index'));
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);
    }

    /** UT-F86-02 — Post tolak jika tidak seimbang */
    public function test_UT_F86_02_post_tolak_tidak_seimbang(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'UMUM', 'status' => 'DRAFT']);
        $kas    = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $this->tambahDetail($jurnal, $kas, 'DEBIT', 100000); // tidak seimbang

        $res = $this->post(route('dashboard.jurnal-umum.post', $jurnal->id));

        $res->assertRedirect();
        $res->assertSessionHas('error');
        $this->assertEquals('DRAFT', $jurnal->fresh()->status);
    }

    /** UT-F87-01 — Update posted ditolak */
    public function test_UT_F87_01_update_posted_ditolak(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);

        $res = $this->put(route('dashboard.jurnal-umum.update', $jurnal->id), [
            'periode_id' => $jurnal->periode_id,
            'tanggal'    => '2026-06-16',
            'detail'     => $this->detailSeimbang(),
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('error');
    }

    /** UT-F87-02 — Destroy posted ditolak */
    public function test_UT_F87_02_destroy_posted_ditolak(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'UMUM', 'status' => 'POSTED']);

        $res = $this->delete(route('dashboard.jurnal-umum.destroy', $jurnal->id));

        $res->assertRedirect();
        $res->assertSessionHas('error');
        $this->assertDatabaseHas('jurnal', ['id' => $jurnal->id]);
    }

    /** UT-F88-01 — Bulk post draft seimbang */
    public function test_UT_F88_01_bulk_post_draft_seimbang(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'UMUM', 'status' => 'DRAFT']);
        $kas    = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $pend   = $this->buatAkun('4-1000', 'Pendapatan', 'KREDIT');
        $this->tambahDetail($jurnal, $kas, 'DEBIT', 100000);
        $this->tambahDetail($jurnal, $pend, 'KREDIT', 100000);

        $res = $this->post(route('dashboard.jurnal-umum.bulk-post'), ['ids' => [$jurnal->id]]);

        $res->assertRedirect();
        $this->assertEquals('POSTED', $jurnal->fresh()->status);
    }
}