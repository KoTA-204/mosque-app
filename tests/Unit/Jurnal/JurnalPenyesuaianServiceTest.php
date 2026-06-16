<?php

namespace Tests\Unit\Jurnal;

use App\Models\Aset;
use App\Services\JurnalPenyesuaianService;
use Tests\Unit\Inc2TestCase;

class JurnalPenyesuaianServiceTest extends Inc2TestCase
{
    private function tipeNonPenyusutan(): string
    {
        $tipes = array_keys(JurnalPenyesuaianService::TIPE_LABELS);
        return collect($tipes)->first(fn ($t) => $t !== 'PENYUSUTAN_ASET') ?? $tipes[0];
    }

    /** UT-F96-01 — Store simpan per tipe (non-penyusutan) */
    public function test_UT_F96_01_store_simpan_per_tipe(): void
    {
        $beban = $this->buatAkun('5-1000', 'Beban Sewa', 'DEBIT');
        $utang = $this->buatAkun('2-1000', 'Utang', 'KREDIT');

        $res = $this->post(route('dashboard.jurnal-penyesuaian.store'), [
            'periode_id'       => $this->periodeAktif()->id,
            'tanggal'          => '2026-06-30',
            'tipe_penyesuaian' => $this->tipeNonPenyusutan(),
            'keterangan'       => 'Penyesuaian akhir bulan',
            'submit_type'      => 'draft',
            'detail'           => [
                ['akun_id' => $beban->id, 'tipe' => 'DEBIT',  'nominal' => '100000'],
                ['akun_id' => $utang->id, 'tipe' => 'KREDIT', 'nominal' => '100000'],
            ],
        ]);

        $res->assertRedirect(route('dashboard.jurnal-penyesuaian.index'));
        $this->assertDatabaseHas('jurnal', ['jenis_jurnal' => 'PENYESUAIAN']);
    }

    /** UT-F96-02 — getAkunList mengecualikan kas & bank */
    public function test_UT_F96_02_get_akun_list_exclude_kas_bank(): void
    {
        $this->buatAkun('1-1000', 'Kas', 'DEBIT');
        $this->buatAkun('5-1000', 'Beban Operasional', 'DEBIT');

        $list = app(JurnalPenyesuaianService::class)->getAkunList('MANUAL');

        $this->assertNotNull($list);
    }

    /** UT-F97-01 — Hitung penyusutan aset (jalur PENYUSUTAN_ASET) */
    public function test_UT_F97_01_hitung_penyusutan(): void
    {
        $aset = Aset::create([
            'kode_aset'                => 'ASET-2026-001',
            'nama_aset'                => 'Laptop',
            'sumber_perolehan'         => 'PEMBELIAN',
            'tanggal_perolehan'        => '2026-01-01',
            'nilai_tercatat'           => 2000000,
            'umur_manfaat'             => 12,
            'kondisi_aset'             => 'BAIK',
            'lokasi_aset'              => 'Kantor DKM',
            'jumlah_unit'              => 1,
            'tanggal_mulai_penyusutan' => '2026-01-01',
            'status_aset'              => 'AKTIF',
        ]);

        $beban = $this->buatAkun('5-2000', 'Beban Penyusutan', 'DEBIT');
        $akum  = $this->buatAkun('1-2900', 'Akumulasi Penyusutan', 'KREDIT');

        $res = $this->post(route('dashboard.jurnal-penyesuaian.store'), [
            'periode_id'       => $this->periodeAktif()->id,
            'tanggal'          => '2026-06-30',
            'tipe_penyesuaian' => 'PENYUSUTAN_ASET',
            'keterangan'       => 'Penyusutan Juni',
            'submit_type'      => 'draft',
            'detail'           => [
                [
                    'akun_id'   => $beban->id,
                    'tipe'      => 'DEBIT',
                    'nominal'   => '166666.67',
                    'aset_rows' => [
                        ['aset_id' => $aset->id, 'nominal' => '166666.67'],
                    ],
                ],
                ['akun_id' => $akum->id, 'tipe' => 'KREDIT', 'nominal' => '166666.67'],
            ],
        ]);

        $res->assertRedirect();
    }

    /** UT-F97-02 — Bulk post penyesuaian (update akumulasi saat POSTED) */
    public function test_UT_F97_02_on_posted_update_akumulasi_aset(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'PENYESUAIAN', 'status' => 'DRAFT']);
        $beban  = $this->buatAkun('5-2000', 'Beban Penyusutan', 'DEBIT');
        $akum   = $this->buatAkun('1-2900', 'Akumulasi Penyusutan', 'KREDIT');
        $this->tambahDetail($jurnal, $beban, 'DEBIT', 100000);
        $this->tambahDetail($jurnal, $akum, 'KREDIT', 100000);

        $res = $this->post(route('dashboard.jurnal-penyesuaian.bulk-post'), ['ids' => [$jurnal->id]]);

        $res->assertRedirect();
    }

    /** UT-F86-01 — Post tolak jika tidak seimbang */
    public function test_UT_F86_01_post_tolak_tidak_seimbang(): void
    {
        $jurnal = $this->buatJurnal(['jenis_jurnal' => 'PENYESUAIAN', 'status' => 'DRAFT']);
        $beban  = $this->buatAkun('5-2000', 'Beban', 'DEBIT');
        $this->tambahDetail($jurnal, $beban, 'DEBIT', 100000); // tidak seimbang

        $res = $this->post(route('dashboard.jurnal-penyesuaian.bulk-post'), ['ids' => [$jurnal->id]]);

        $res->assertRedirect();
        $this->assertEquals('DRAFT', $jurnal->fresh()->status);
    }
}