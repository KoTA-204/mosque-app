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