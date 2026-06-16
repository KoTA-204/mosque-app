<?php

namespace Tests\Unit\Jurnal;

use Tests\Unit\Inc2TestCase;

class JurnalPembukaControllerTest extends Inc2TestCase
{
    private function detail(string $debit, string $kredit): array
    {
        $kas   = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $modal = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');

        return [
            ['akun_id' => $kas->id,   'tipe' => 'DEBIT',  'nominal' => $debit],
            ['akun_id' => $modal->id, 'tipe' => 'KREDIT', 'nominal' => $kredit],
        ];
    }

    /** UT-F95-01 — Store simpan saldo awal (seimbang) */
    public function test_UT_F95_01_store_simpan_saldo_awal(): void
    {
        $res = $this->post(route('dashboard.jurnal-pembuka.store'), [
            'tanggal_mulai' => '2026-06-01',
            'tanggal_akhir' => '2026-06-30',
            'keterangan'    => 'Saldo awal',
            'submit_type'   => 'draft',
            'detail'        => $this->detail('1000000', '1000000'),
        ]);

        $res->assertRedirect(route('dashboard.jurnal-pembuka.index'));
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'PEMBUKA']);
    }

    /** UT-F95-02 — Store tolak jika tidak seimbang */
    public function test_UT_F95_02_store_tolak_tidak_seimbang(): void
    {
        $res = $this->post(route('dashboard.jurnal-pembuka.store'), [
            'tanggal_mulai' => '2026-06-01',
            'tanggal_akhir' => '2026-06-30',
            'keterangan'    => 'Saldo awal timpang',
            'submit_type'   => 'draft',
            'detail'        => $this->detail('1000000', '500000'),
        ]);

        $res->assertSessionHasErrors('balance');
    }

    /** UT-F87-01 — Guard: jurnal POSTED tolak ubah & hapus */
    public function test_UT_F87_01_guard_posted_tolak_ubah_hapus(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'PEMBUKA', 'status' => 'POSTED']);

        // Update → redirect back + session error (cek POSTED dilakukan pertama)
        $update = $this->put(route('dashboard.jurnal-pembuka.update', $jurnal->id), []);
        $update->assertRedirect();
        $update->assertSessionHas('error');

        // Destroy → JSON 403
        $delete = $this->deleteJson(route('dashboard.jurnal-pembuka.destroy', $jurnal->id));
        $delete->assertStatus(403);
        $this->assertDatabaseHas('jurnal', ['id' => $jurnal->id]);
    }
}