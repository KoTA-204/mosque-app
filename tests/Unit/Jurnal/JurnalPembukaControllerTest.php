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

    /** UT-F95-03 — stats() menghitung jurnal pembuka per status */
    public function test_UT_F95_03_stats_per_status(): void
    {
        $periode = $this->periodeAktif();
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'PEMBUKA', 'status' => 'POSTED']);
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'PEMBUKA', 'status' => 'DRAFT']);
        $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'UMUM', 'status' => 'POSTED']); // bukan pembuka

        $stats = app(\App\Services\JurnalPembukaService::class)->stats();
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['posted']);
        $this->assertEquals(1, $stats['draft']);
    }

    /** UT-F95-04 — simpan posting → periode dibuat (firstOrCreate) & jurnal POSTED */
    public function test_UT_F95_04_simpan_posting_buat_periode_dan_posted(): void
    {
        $kas   = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $modal = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');

        $jurnal = app(\App\Services\JurnalPembukaService::class)->simpan([
            'tanggal_mulai' => '2026-08-01', 'tanggal_akhir' => '2026-08-31',
            'keterangan' => 'Saldo awal Agustus', 'submit_type' => 'posting',
            'detail' => [
                ['akun_id' => $kas->id,   'tipe' => 'DEBIT',  'nominal' => '1000000'],
                ['akun_id' => $modal->id, 'tipe' => 'KREDIT', 'nominal' => '1000000'],
            ],
        ]);

        $this->assertEquals('POSTED', $jurnal->status);
        $this->assertEquals('PEMBUKA', $jurnal->jenis_jurnal);
        $this->assertEquals(2, $jurnal->detailJurnal()->count());
        $this->assertEquals('2026-08-01', $jurnal->periode->tanggal_awal->format('Y-m-d'));
        $this->assertEquals('2026-08-31', $jurnal->periode->tanggal_akhir->format('Y-m-d'));
    }

    /** UT-F95-05 — perbarui DRAFT mengganti detail & keterangan */
    public function test_UT_F95_05_perbarui_draft_ganti_detail(): void
    {
        $periode = $this->periodeAktif();
        $jurnal  = $this->buatJurnal(['periode_id' => $periode->id, 'jenis_jurnal' => 'PEMBUKA', 'status' => 'DRAFT']);
        $kas     = $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $modal   = $this->buatAkun('3-1000', 'Aset Neto', 'KREDIT');
        $this->tambahDetail($jurnal, $kas, 'DEBIT', 100000);

        app(\App\Services\JurnalPembukaService::class)->perbarui($jurnal, [
            'periode_id' => $periode->id, 'tanggal_mulai' => '2026-06-05',
            'keterangan' => 'Revisi saldo awal', 'submit_type' => 'draft',
            'detail' => [
                ['akun_id' => $kas->id,   'tipe' => 'DEBIT',  'nominal' => '250000'],
                ['akun_id' => $modal->id, 'tipe' => 'KREDIT', 'nominal' => '250000'],
            ],
        ]);

        $jurnal->refresh();
        $this->assertEquals('Revisi saldo awal', $jurnal->keterangan);
        $this->assertEquals(2, $jurnal->detailJurnal()->count());
        $this->assertEquals(250000, (float) $jurnal->detailJurnal()->where('tipe', 'DEBIT')->first()->nominal);
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