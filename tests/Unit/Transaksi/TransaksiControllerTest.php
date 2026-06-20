<?php

namespace Tests\Unit\Transaksi;

use App\Models\Transaksi;
use Tests\Unit\Inc2TestCase;

class TransaksiControllerTest extends Inc2TestCase
{
    private function payload(array $f, array $override = []): array
    {
        $base = array_merge([
            'jenis_transaksi'   => 'PEMASUKAN',
            'tanggal_transaksi' => '2026-06-15',
            'jumlah'            => 500000,
            'dompet_id'         => $f['dompet']->id,
            'deskripsi'         => 'Donasi Jumat',
        ], $override);

        // Jurnal seimbang; service menghitung `jumlah` dari total debit ini.
        $nominal = $base['jumlah'];
        $base['jurnal'] = [
            ['akun_id' => $f['debit']->id,  'tipe' => 'DEBIT',  'nominal' => $nominal],
            ['akun_id' => $f['kredit']->id, 'tipe' => 'KREDIT', 'nominal' => $nominal],
        ];

        return $base;
    }

    private function fixtures(bool $denganPeriode = true): array
    {
        if ($denganPeriode) {
            $this->periodeAktif();
        }
        return [
            'dompet' => $this->buatDompet(),
            'debit'  => $this->buatAkun('1-1000', 'Kas', 'DEBIT'),
            'kredit' => $this->buatAkun('4-1000', 'Pendapatan Donasi', 'KREDIT'),
        ];
    }

    /** UT-F22-01 — Store membuat transaksi dan jurnal */
    public function test_UT_F22_01_store_membuat_transaksi_dan_jurnal(): void
    {
        $f = $this->fixtures();

        $res = $this->postJson(route('dashboard.transaksi.store'), $this->payload($f));

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('transaksi', ['jumlah' => 500000, 'jenis_transaksi' => 'PEMASUKAN']);
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'UMUM']);
    }

    /** UT-F22-02 — Store menolak duplikat */
    public function test_UT_F22_02_store_menolak_duplikat(): void
    {
        $f = $this->fixtures();
        $payload = $this->payload($f);

        $this->postJson(route('dashboard.transaksi.store'), $payload)->assertOk();
        // ← TAMBAH INI:
        $this->assertEquals(1, \App\Models\Transaksi::count(), 'Transaksi pertama tidak tersimpan!');
        $res = $this->postJson(route('dashboard.transaksi.store'), $payload);

        $res = $this->postJson(route('dashboard.transaksi.store'), $payload);
        $res->assertStatus(409)->assertJson(['type' => 'duplikat_warning']);
    }

    /** UT-F23-01 — Store force melewati cek duplikat */
    public function test_UT_F23_01_store_force_melewati_cek_duplikat(): void
    {
        $f = $this->fixtures();
        $payload = $this->payload($f, ['force' => true]);

        $this->postJson(route('dashboard.transaksi.store'), $payload)->assertOk();
        $this->postJson(route('dashboard.transaksi.store'), $payload)->assertOk();

        $this->assertEquals(2, Transaksi::count());
    }

    /** UT-F24-01 — Update membuat ulang jurnal */
    public function test_UT_F24_01_update_membuat_ulang_jurnal(): void
    {
        $f = $this->fixtures();
        $this->postJson(route('dashboard.transaksi.store'), $this->payload($f))->assertOk();
        $trx = Transaksi::first();

        $res = $this->putJson(route('dashboard.transaksi.update', $trx->id), $this->payload($f, [
            'jumlah' => 750000,
        ]));

        $res->assertOk();
        $this->assertDatabaseHas('transaksi', ['id' => $trx->id, 'jumlah' => 750000]);
    }

    /** UT-F26-01 — Destroy menghapus transaksi */
    public function test_UT_F26_01_destroy_menghapus_transaksi(): void
    {
        $f = $this->fixtures();
        $this->postJson(route('dashboard.transaksi.store'), $this->payload($f))->assertOk();
        $trx = Transaksi::first();

        $res = $this->deleteJson(route('dashboard.transaksi.destroy', $trx->id));

        $this->assertContains($res->status(), [200, 302]);
        $this->assertDatabaseMissing('transaksi', ['id' => $trx->id]);
    }

    /** UT-F29-02 — Store gagal tanpa periode aktif */
    public function test_UT_F29_02_store_gagal_tanpa_periode_aktif(): void
    {
        $f = $this->fixtures(false); // TANPA periode aktif

        $res = $this->postJson(route('dashboard.transaksi.store'), $this->payload($f));

        // buatJurnalUmum melempar RuntimeException → transaksi di-rollback
        $this->assertGreaterThanOrEqual(400, $res->status());
        $this->assertDatabaseCount('transaksi', 0);
    }

    /** UT-F30-01 — Store dengan aset */
    public function test_UT_F30_01_store_dengan_aset(): void
    {
        $f = $this->fixtures();

        $res = $this->postJson(route('dashboard.transaksi.store'), $this->payload($f, [
            'jenis_transaksi'         => 'PENGELUARAN',
            'jumlah'                  => 2000000,
            'is_aset'                 => true,
            'nama_aset'               => 'Laptop Sekretariat',
            'tanggal_perolehan'       => '2026-06-15',
            'kondisi_aset'            => 'BAIK',
            'sumber_perolehan'        => 'PEMBELIAN',
            'lokasi_aset'             => 'Kantor DKM',
            'jumlah_unit'             => 1,
            'tanggal_mulai_penyusutan'=> '2026-06-15',
            'umur_manfaat'            => 48,
        ]));

       // GANTI:
        // $res->assertOk()->assertJson(['success' => true]);

        // MENJADI (sementara):
        $this->assertNotEquals(500, $res->status(), json_encode($res->json()));
        $res->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('aset', ['nama_aset' => 'Laptop Sekretariat']);
    }

    /** UT-F31-01 — Update ditolak jika jurnal posted */
    public function test_UT_F31_01_update_ditolak_jika_jurnal_posted(): void
    {
        $f = $this->fixtures();
        $this->postJson(route('dashboard.transaksi.store'), $this->payload($f))->assertOk();
        $trx = Transaksi::first();

        // POSTED + status_approval null (bukan APPROVED) → wajib ditolak
        $trx->jurnal()->update(['status' => 'POSTED']);

        $res = $this->putJson(route('dashboard.transaksi.update', $trx->id), $this->payload($f, [
            'jumlah' => 999000,
        ]));

        $res->assertStatus(403);
    }
}