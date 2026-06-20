<?php

namespace Tests\Unit\Aset;

use App\Models\Aset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsetModelTest extends TestCase
{
    use RefreshDatabase;

    /** Helper: buat aset langsung lewat Model (semua kolom ada di $fillable) */
    private function buatAset(array $override = []): Aset
    {
        return Aset::create(array_merge([
            'nama_aset'                => 'Aset Uji',
            'sumber_perolehan'         => 'Pembelian',
            'tanggal_perolehan'        => '2026-06-16',
            'nilai_tercatat'           => 12_000_000,
            'umur_manfaat'             => 8,            // tahun
            'kondisi_aset'             => 'BAIK',
            'lokasi_aset'              => 'Ruang Utama Masjid',
            'jumlah_unit'              => 1,
            'tanggal_mulai_penyusutan' => '2026-06-16',
            'status_aset'              => 'AKTIF',
            'nilai_buku'               => 12_000_000,
            'akumulasi_penyusutan'     => 0,
        ], $override));
    }

    /**
     * UT-F52-01
     * Aset::generateKode() - Format & Urutan Kode
     * Expected: "ASET-2026-001"; pemanggilan berikutnya "ASET-2026-002".
     */
    public function test_UT_F52_01_generate_kode_format_dan_urutan(): void
    {
        // Belum ada aset 2026 → 001
        $kode1 = Aset::generateKode('2026-06-16');
        $this->assertEquals('ASET-2026-001', $kode1);

        // Simpan 1 aset 2026, lalu panggil ulang → 002
        $this->buatAset(['kode_aset' => $kode1]);
        $this->assertEquals('ASET-2026-002', Aset::generateKode('2026-06-16'));

        // Tahun diambil dari tanggal yang dikirim, bukan now()
        $this->assertEquals('ASET-2020-001', Aset::generateKode('2020-01-05'));
    }

    /** UT-F52-04 — accessor label_pemberi & label_nilai sesuai sumber */
    public function test_UT_F52_04_label_pemberi_dan_nilai(): void
    {
        $wakaf = $this->buatAset(['sumber_perolehan' => 'Wakaf']);
        $this->assertEquals('Nama Wakif', $wakaf->label_pemberi);
        $this->assertEquals('Nilai Wajar Aset', $wakaf->label_nilai);

        $beli = $this->buatAset(['sumber_perolehan' => 'Pembelian']);
        $this->assertEquals('Nama Pemberi', $beli->label_pemberi);
        $this->assertEquals('Nilai Perolehan', $beli->label_nilai);
    }

    /** UT-F53-04 — accessor progress_penyusutan (0..100) */
    public function test_UT_F53_04_progress_penyusutan(): void
    {
        $aset = $this->buatAset([
            'nilai_tercatat'           => 10_000_000,
            'umur_manfaat'             => 5,
            'tanggal_mulai_penyusutan' => now()->subYears(2)->toDateString(),
            'status_aset'              => 'AKTIF',
        ]);

        $expected = min(($aset->akumulasi_real_time / 10_000_000) * 100, 100);
        $this->assertEquals(round($expected, 2), round($aset->progress_penyusutan, 2));
        $this->assertGreaterThanOrEqual(0, $aset->progress_penyusutan);
        $this->assertLessThanOrEqual(100, $aset->progress_penyusutan);
    }

    /** UT-F53-05 — accessor penyusutan_per_tahun (garis lurus) */
    public function test_UT_F53_05_penyusutan_per_tahun(): void
    {
        $aset = $this->buatAset(['nilai_tercatat' => 12_000_000, 'umur_manfaat' => 8]);
        $this->assertEquals(1_500_000, round($aset->penyusutan_per_tahun, 2));

        $tanpaUmur = $this->buatAset(['umur_manfaat' => null]);
        $this->assertEquals(0, $tanpaUmur->penyusutan_per_tahun);
    }

    /** UT-F54-03 — scopeFilter by tahun (prefix kode ASET-{tahun}-) */
    public function test_UT_F54_03_filter_tahun(): void
    {
        $this->buatAset(['kode_aset' => 'ASET-2026-001']);
        $this->buatAset(['kode_aset' => 'ASET-2020-001']);

        $hasil = Aset::filter(['tahun' => '2026'])->get();

        $this->assertCount(1, $hasil);
        $this->assertEquals('ASET-2026-001', $hasil->first()->kode_aset);
    }

    /** UT-F54-04 — scopeAktif hanya status AKTIF */
    public function test_UT_F54_04_scope_aktif(): void
    {
        $this->buatAset(['status_aset' => 'AKTIF',       'kode_aset' => 'ASET-2026-010']);
        $this->buatAset(['status_aset' => 'TIDAK AKTIF', 'kode_aset' => 'ASET-2026-011']);

        $this->assertEquals(1, Aset::aktif()->count());
    }

    /**
     * UT-F53-01
     * Aset::penyusutan_per_bulan - Garis Lurus
     * Expected: 125.000 (= 12.000.000 / (8 x 12)).
     */
    public function test_UT_F53_01_penyusutan_per_bulan_garis_lurus(): void
    {
        $aset = $this->buatAset(['nilai_tercatat' => 12_000_000, 'umur_manfaat' => 8]);
        $this->assertEquals(125_000, round($aset->penyusutan_per_bulan, 2));

        // Tanpa umur manfaat → 0 (guard di accessor)
        $tanpaUmur = $this->buatAset(['umur_manfaat' => null]);
        $this->assertEquals(0, $tanpaUmur->penyusutan_per_bulan);
    }

    /**
     * UT-F53-02
     * Aset::akumulasi_real_time - Pembatasan (cap)
     * Expected: akumulasi di-cap = nilai_tercatat (tidak melebihi).
     */
    public function test_UT_F53_02_akumulasi_real_time_di_cap(): void
    {
        // Aktif + tanggal mulai jauh di masa lalu → penyusutan*bulan > nilai_tercatat
        $aset = $this->buatAset([
            'nilai_tercatat'           => 5_000_000,
            'umur_manfaat'             => 5,
            'tanggal_mulai_penyusutan' => '2015-01-01',
            'status_aset'              => 'AKTIF',
        ]);

        $this->assertEquals(
            5_000_000,
            round($aset->akumulasi_real_time, 2),
            'Akumulasi harus di-cap sebesar nilai_tercatat'
        );
    }

    /**
     * UT-F53-03
     * Aset::nilai_buku_real_time - Nilai Buku
     * Expected: nilai_buku = max(nilai_tercatat - akumulasi, 0).
     */
    public function test_UT_F53_03_nilai_buku_real_time(): void
    {
        $aset = $this->buatAset([
            'nilai_tercatat'           => 10_000_000,
            'umur_manfaat'             => 5,
            'tanggal_mulai_penyusutan' => now()->subYears(2)->toDateString(),
            'status_aset'              => 'AKTIF',
        ]);

        $expected = max(10_000_000 - $aset->akumulasi_real_time, 0);
        $this->assertEquals(round($expected, 2), round($aset->nilai_buku_real_time, 2));
        $this->assertGreaterThanOrEqual(0, $aset->nilai_buku_real_time);

        // Saat TIDAK AKTIF → pakai snapshot DB, bukan hitung ulang
        $nonaktif = $this->buatAset([
            'status_aset'          => 'TIDAK AKTIF',
            'nilai_buku'           => 6_000_000,
            'akumulasi_penyusutan' => 4_000_000,
        ]);
        $this->assertEquals(6_000_000, round($nonaktif->nilai_buku_real_time, 2));
        $this->assertEquals(4_000_000, round($nonaktif->akumulasi_real_time, 2));
    }
}