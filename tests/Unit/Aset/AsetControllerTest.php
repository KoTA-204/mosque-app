<?php

namespace Tests\Unit\Aset;

use App\Models\Aset;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AsetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Bypass permission & active check (auth tetap lewat actingAs).
        // SubstituteBindings (web group) tetap aktif → route-model binding {aset} jalan.
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role = Role::create(['role_name' => 'Super Admin']);
        $this->user = User::factory()->create(['role_id' => $role->id, 'status' => 'active']);
    }

    private function buatAset(array $override = []): Aset
    {
        return Aset::create(array_merge([
            'kode_aset'                => Aset::generateKode('2026-06-16'),
            'nama_aset'                => 'Karpet Sajadah Masjid',
            'sumber_perolehan'         => 'Wakaf',
            'tanggal_perolehan'        => '2026-06-16',
            'nilai_tercatat'           => 2_000_000,
            'umur_manfaat'             => null,
            'kondisi_aset'             => 'BAIK',
            'lokasi_aset'              => 'Ruang Utama Masjid',
            'jumlah_unit'              => 1,
            'status_aset'              => 'AKTIF',
            'nilai_buku'               => 2_000_000,
            'akumulasi_penyusutan'     => 0,
        ], $override));
    }

    /**
     * UT-F52-02
     * AsetController::store() - Simpan Aset Baru
     * Expected: tersimpan; status_aset=AKTIF; nilai_buku=nilai_tercatat; kode_aset terisi.
     */
    public function test_UT_F52_02_store_simpan_aset_baru(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('dashboard.aset.store'), [
                'nama_aset'         => 'Lemari Arsip',
                'kondisi_aset'      => 'BAIK',
                'lokasi_aset'       => 'Gudang',
                'sumber_perolehan'  => 'Pembelian',
                'tanggal_perolehan' => '2026-06-16',
                'nilai_tercatat'    => 2_000_000,
            ]);

        $response->assertRedirect(route('dashboard.aset.index'));

        $aset = Aset::where('nama_aset', 'Lemari Arsip')->first();
        $this->assertNotNull($aset);
        $this->assertEquals('AKTIF', $aset->status_aset);
        $this->assertEquals('2.000.000.00', number_format((float) $aset->nilai_buku, 2, '.', '.')); // = nilai_tercatat
        $this->assertEquals((float) $aset->nilai_tercatat, (float) $aset->nilai_buku);
        $this->assertEquals(0, (float) $aset->akumulasi_penyusutan);
        $this->assertStringStartsWith('ASET-2026-', $aset->kode_aset);
    }

    /** UT-F52-03 — store dengan dokumen → file tersimpan di storage */
    public function test_UT_F52_03_store_dengan_dokumen(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->post(route('dashboard.aset.store'), [
                'nama_aset'         => 'Proyektor',
                'kondisi_aset'      => 'BAIK',
                'lokasi_aset'       => 'Aula',
                'sumber_perolehan'  => 'Pembelian',
                'tanggal_perolehan' => '2026-06-16',
                'nilai_tercatat'    => 5_000_000,
                'dokumen_pendukung' => UploadedFile::fake()->create('nota.pdf', 120, 'application/pdf'),
            ]);

        $response->assertRedirect(route('dashboard.aset.index'));

        $aset = Aset::where('nama_aset', 'Proyektor')->first();
        $this->assertNotNull($aset->dokumen_pendukung);
        Storage::disk('public')->assertExists($aset->dokumen_pendukung);
    }

    /** UT-F55-03 — update disusutkan=true → akumulasi & nilai_buku real-time, umur dipertahankan */
    public function test_UT_F55_03_update_disusutkan_true(): void
    {
        $aset = $this->buatAset([
            'nilai_tercatat'           => 12_000_000,
            'umur_manfaat'             => 8,
            'tanggal_mulai_penyusutan' => now()->subYears(2)->toDateString(),
            'status_aset'              => 'AKTIF',
            'akumulasi_penyusutan'     => 0,
            'nilai_buku'               => 12_000_000,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('dashboard.aset.update', $aset), [
                'nama_aset'                => $aset->nama_aset,
                'kondisi_aset'             => 'BAIK',
                'lokasi_aset'              => 'Ruang Utama Masjid',
                'sumber_perolehan'         => $aset->sumber_perolehan,
                'tanggal_perolehan'        => '2024-06-16',
                'nilai_tercatat'           => 12_000_000,
                'umur_manfaat'             => 8,
                'tanggal_mulai_penyusutan' => now()->subYears(2)->toDateString(),
                'disusutkan'               => 1,
            ]);

        $response->assertRedirect(route('dashboard.aset.index'));

        $aset->refresh();
        $this->assertEquals(8, $aset->umur_manfaat);                          // dipertahankan
        $this->assertGreaterThan(0, (float) $aset->akumulasi_penyusutan);    // snapshot real-time
    }

    /** UT-F55-05 — toggleStatus reaktivasi (TIDAK AKTIF → AKTIF) reset akumulasi=0 */
    public function test_UT_F55_05_toggle_status_reaktivasi(): void
    {
        $aset = $this->buatAset([
            'status_aset'              => 'TIDAK AKTIF',
            'umur_manfaat'             => 8,
            'nilai_tercatat'           => 28_000_000,
            'tanggal_mulai_penyusutan' => now()->subYears(3)->toDateString(),
            'akumulasi_penyusutan'     => 10_500_000,
            'nilai_buku'               => 17_500_000,
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('dashboard.aset.toggle-status', $aset));

        $response->assertRedirect(route('dashboard.aset.index'));

        $aset->refresh();
        $this->assertEquals('AKTIF', $aset->status_aset);
        $this->assertEquals(0, (float) $aset->akumulasi_penyusutan); // direset
    }

    /**
     * UT-F54-01
     * AsetController::index() - Filter (status, sumber, kondisi, lokasi)
     */
    public function test_UT_F54_01_index_filter(): void
    {
        $this->buatAset(['nama_aset' => 'Karpet Wakaf', 'status_aset' => 'AKTIF', 'sumber_perolehan' => 'Wakaf', 'kondisi_aset' => 'BAIK']);
        $this->buatAset(['nama_aset' => 'AC Beli',      'status_aset' => 'TIDAK AKTIF', 'sumber_perolehan' => 'Pembelian', 'kondisi_aset' => 'RUSAK BERAT', 'kode_aset' => 'ASET-2026-999']);

        // Ambil partial table via AJAX agar tidak tergantung layout penuh
        $response = $this->actingAs($this->user)
            ->get(route('dashboard.aset.index', [
                'status' => 'aktif', 'sumber' => 'Wakaf', 'kondisi' => 'BAIK',
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertSee('Karpet Wakaf', false);
        $response->assertDontSee('AC Beli', false);
    }

    /**
     * UT-F54-02
     * AsetController::index() - Pencarian (nama/kode/lokasi)
     */
    public function test_UT_F54_02_index_pencarian(): void
    {
        $this->buatAset(['nama_aset' => 'Karpet Sajadah Masjid']);
        $this->buatAset(['nama_aset' => 'AC Split 2 PK', 'kode_aset' => 'ASET-2026-998']);

        $response = $this->actingAs($this->user)
            ->get(route('dashboard.aset.index', ['search' => 'Karpet']),
                  ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertSee('Karpet Sajadah Masjid', false);
        $response->assertDontSee('AC Split 2 PK', false);
    }

    /**
     * UT-F55-01
     * AsetController::update() - Ubah Kondisi Aset
     * Expected: kondisi & data diperbarui (validasi in:BAIK,RUSAK RINGAN,RUSAK BERAT).
     */
    public function test_UT_F55_01_update_ubah_kondisi(): void
    {
        $aset = $this->buatAset();

        $response = $this->actingAs($this->user)
            ->put(route('dashboard.aset.update', $aset), [
                'nama_aset'         => $aset->nama_aset,
                'kondisi_aset'      => 'RUSAK RINGAN',
                'lokasi_aset'       => 'Gudang',
                'sumber_perolehan'  => $aset->sumber_perolehan,
                'tanggal_perolehan' => '2026-06-16',
                'nilai_tercatat'    => $aset->nilai_tercatat,
                'status_aset'       => 'AKTIF',
                // 'disusutkan' tidak dikirim → aset dianggap tidak disusutkan
            ]);

        $response->assertRedirect(route('dashboard.aset.index'));
        $this->assertDatabaseHas('aset', [
            'id'           => $aset->id,
            'kondisi_aset' => 'RUSAK RINGAN',
            'lokasi_aset'  => 'Gudang',
        ]);

        $aset->refresh();
        $this->assertNull($aset->umur_manfaat);              // dinolkan karena disusutkan=false
        $this->assertEquals(0, (float) $aset->akumulasi_penyusutan);
    }

    /**
     * UT-F55-02
     * AsetController::toggleStatus() - Snapshot Nonaktif
     * Expected: status -> TIDAK AKTIF; akumulasi & nilai_buku tersimpan sebagai snapshot.
     */
    public function test_UT_F55_02_toggle_status_snapshot(): void
    {
        $aset = $this->buatAset([
            'nama_aset'                => 'AC Split 2 PK',
            'status_aset'              => 'AKTIF',
            'nilai_tercatat'           => 28_000_000,
            'umur_manfaat'             => 8,
            'tanggal_mulai_penyusutan' => now()->subYears(3)->toDateString(),
        ]);

        // Nilai real-time SEBELUM toggle (yang akan di-snapshot controller)
        $akumulasiSebelum = round($aset->akumulasi_real_time, 2);
        $nilaiBukuSebelum = round($aset->nilai_buku_real_time, 2);

        $response = $this->actingAs($this->user)
            ->patch(route('dashboard.aset.toggle-status', $aset));

        $response->assertRedirect(route('dashboard.aset.index'));

        $aset->refresh();
        $this->assertEquals('TIDAK AKTIF', $aset->status_aset);
        $this->assertEquals($akumulasiSebelum, round((float) $aset->akumulasi_penyusutan, 2));
        $this->assertEquals($nilaiBukuSebelum, round((float) $aset->nilai_buku, 2));
    }

    /**
     * UT-F56-01
     * AsetController::destroy() - Soft Delete & Hapus Berkas
     * Expected: deleted_at terisi (SoftDeletes) + berkas storage terhapus.
     */
    public function test_UT_F56_01_destroy_soft_delete_dan_hapus_berkas(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->create('dok.pdf', 100)->store('aset/dokumen', 'public');
        Storage::disk('public')->assertExists($path);

        $aset = $this->buatAset(['dokumen_pendukung' => $path]);

        $response = $this->actingAs($this->user)
            ->delete(route('dashboard.aset.destroy', $aset));

        $response->assertRedirect(route('dashboard.aset.index'));
        $this->assertSoftDeleted('aset', ['id' => $aset->id]);
        Storage::disk('public')->assertMissing($path);
    }

    /**
     * UT-F57-01
     * AsetController::index() - Statistik (stats_only)
     * Expected: { stats: { total, aktif, tidak_aktif } } sesuai jumlah record.
     */
    public function test_UT_F57_01_index_statistik_stats_only(): void
    {
        $this->buatAset(['status_aset' => 'AKTIF']);
        $this->buatAset(['status_aset' => 'AKTIF', 'nama_aset' => 'Aset 2', 'kode_aset' => 'ASET-2026-101']);
        $this->buatAset(['status_aset' => 'TIDAK AKTIF', 'nama_aset' => 'Aset 3', 'kode_aset' => 'ASET-2026-102']);

        $response = $this->actingAs($this->user)
            ->getJson(route('dashboard.aset.index', ['stats_only' => 1]));

        $response->assertOk()->assertJson([
            'stats' => [
                'total'       => 3,
                'aktif'       => 2,
                'tidak_aktif' => 1,
            ],
        ]);
    }
}