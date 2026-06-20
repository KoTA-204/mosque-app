<?php
namespace Tests\Unit\Kegiatan;

use App\Models\Dompet;
use App\Models\Kegiatan;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $panitia;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $adminRole   = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status'  => 'active',
        ]);

        $panitiaRole   = Role::create(['role_name' => 'Panitia Khusus']);
        $this->panitia = User::factory()->create([
            'role_id' => $panitiaRole->id,
            'status'  => 'active',
        ]);
    }

    /** Helper: buat transaksi untuk kegiatan */
    private function makeTransaksi(Kegiatan $kegiatan, string $statusApproval = 'PENDING'): Transaksi
    {
        $dompet = Dompet::factory()->create();
        return Transaksi::create([
            'dompet_id'         => $dompet->id,
            'user_id'           => $this->panitia->id,
            'kegiatan_id'       => $kegiatan->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis_transaksi'   => 'PEMASUKAN',
            'jumlah'            => 100000,
            'status_approval'   => $statusApproval,
            'status_jurnal'     => 'UNMAPPED',
        ]);
    }

    private function makePengeluaran(Kegiatan $kegiatan, int $jumlah, string $status = 'APPROVED'): Transaksi
    {
        $dompet = Dompet::factory()->create();
        return Transaksi::create([
            'dompet_id'         => $dompet->id,
            'user_id'           => $this->panitia->id,
            'kegiatan_id'       => $kegiatan->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis_transaksi'   => 'PENGELUARAN',
            'jumlah'            => $jumlah,
            'status_approval'   => $status,
            'status_jurnal'     => 'UNMAPPED',
        ]);
    }

    private function buatKegiatan(array $override = []): Kegiatan
    {
        return Kegiatan::create(array_merge([
            'nama_kegiatan'   => 'Kegiatan Uji',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => now()->subDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'anggaran'        => 1000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ], $override));
    }

    /** UT-F67-01 — Tambah kegiatan valid → status AKTIF */
    public function test_UT_F67_01_store_kegiatan_valid(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.kegiatan.store'), [
                             'nama_kegiatan'   => 'Qurban 1448 H',
                             'jenis_kegiatan'  => 'QURBAN',
                             'tanggal_mulai'   => now()->addDays(5)->toDateString(),
                             'tanggal_selesai' => now()->addDays(10)->toDateString(),
                             'anggaran'        => 5000000,
                             'panitia_id'      => $this->panitia->id,
                         ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kegiatan', [
            'nama_kegiatan' => 'Qurban 1448 H',
            'status'        => 'AKTIF',
        ]);
    }

    /** UT-F67-02 — Tanggal selesai < mulai → ditolak */
    public function test_UT_F67_02_store_kegiatan_tanggal_invalid(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.kegiatan.store'), [
                             'nama_kegiatan'   => 'Kegiatan Salah Tanggal',
                             'jenis_kegiatan'  => 'KAJIAN',
                             'tanggal_mulai'   => now()->addDays(10)->toDateString(),
                             'tanggal_selesai' => now()->addDays(5)->toDateString(),
                             'anggaran'        => 1000000,
                             'panitia_id'      => $this->panitia->id,
                         ]);
        $response->assertSessionHasErrors('tanggal_selesai');
    }

    /** UT-F67-03 — Anggaran negatif → ditolak */
    public function test_UT_F67_03_store_kegiatan_anggaran_negatif(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.kegiatan.store'), [
                             'nama_kegiatan'   => 'Kegiatan Anggaran Negatif',
                             'jenis_kegiatan'  => 'SOSIAL',
                             'tanggal_mulai'   => now()->addDays(5)->toDateString(),
                             'tanggal_selesai' => now()->addDays(10)->toDateString(),
                             'anggaran'        => -50000,
                             'panitia_id'      => $this->panitia->id,
                         ]);
        $response->assertSessionHasErrors('anggaran');
    }

    /** UT-F67-04 — Update kegiatan berhasil */
    public function test_UT_F67_04_update_kegiatan(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Nama Lama',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'anggaran'        => 1000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);
        $response = $this->actingAs($this->admin)
                         ->put(route('dashboard.kegiatan.update', $kegiatan), [
                             'nama_kegiatan'   => 'Nama Baru',
                             'jenis_kegiatan'  => 'KAJIAN',
                             'tanggal_mulai'   => now()->addDays(5)->toDateString(),
                             'tanggal_selesai' => now()->addDays(10)->toDateString(),
                             'anggaran'        => 2000000,
                             'panitia_id'      => $this->panitia->id,
                         ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kegiatan', [
            'id'            => $kegiatan->id,
            'nama_kegiatan' => 'Nama Baru',
        ]);
    }

    /** UT-F67-05 — Hapus kegiatan tanpa transaksi → berhasil */
    public function test_UT_F67_05_delete_kegiatan_tanpa_transaksi(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kegiatan Hapus',
            'jenis_kegiatan'  => 'LAINNYA',
            'tanggal_mulai'   => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'anggaran'        => 0,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);
        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.kegiatan.destroy', $kegiatan));
        $response->assertRedirect();
        $this->assertDatabaseMissing('kegiatan', ['id' => $kegiatan->id]);
    }

    /** UT-F67-06 — Hapus kegiatan yang punya transaksi → ditolak */
    public function test_UT_F67_06_delete_kegiatan_dengan_transaksi_blocked(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kegiatan Ada Transaksi',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
            'anggaran'        => 1000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);
        $this->makeTransaksi($kegiatan);
        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.kegiatan.destroy', $kegiatan));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kegiatan', ['id' => $kegiatan->id]);
    }

    /** UT-F67-07 — Auto-close: semua transaksi APPROVED + tanggal lewat → DITUTUP */
    public function test_UT_F67_07_auto_close_kegiatan_selesai(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kegiatan Selesai',
            'jenis_kegiatan'  => 'ZAKAT',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
            'anggaran'        => 1000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);
        $this->makeTransaksi($kegiatan, 'APPROVED');
        $kegiatan->tutupJikaSelesai();
        $this->assertDatabaseHas('kegiatan', [
            'id'     => $kegiatan->id,
            'status' => 'DITUTUP',
        ]);
    }

    /** UT-F67-08 — tutupJikaSelesai tidak menutup jika tanggal belum lewat */
    public function test_UT_F67_08_tutup_tidak_menutup_jika_tanggal_belum_lewat(): void
    {
        $kegiatan = $this->buatKegiatan([
            'tanggal_mulai'   => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(10)->toDateString(),
        ]);
        $this->makeTransaksi($kegiatan, 'APPROVED');

        $kegiatan->tutupJikaSelesai();

        $this->assertDatabaseHas('kegiatan', ['id' => $kegiatan->id, 'status' => 'AKTIF']);
    }

    /** UT-F67-09 — tutupJikaSelesai tidak menutup jika tidak ada transaksi */
    public function test_UT_F67_09_tutup_tanpa_transaksi(): void
    {
        $kegiatan = $this->buatKegiatan([
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
        ]);

        $kegiatan->tutupJikaSelesai();

        $this->assertDatabaseHas('kegiatan', ['id' => $kegiatan->id, 'status' => 'AKTIF']);
    }

    /** UT-F67-10 — bukaKembali mengubah DITUTUP → AKTIF */
    public function test_UT_F67_10_buka_kembali(): void
    {
        $kegiatan = $this->buatKegiatan(['status' => 'DITUTUP']);

        $kegiatan->bukaKembali();

        $this->assertDatabaseHas('kegiatan', ['id' => $kegiatan->id, 'status' => 'AKTIF']);
    }

    /** UT-F64-06 — bisaInputTransaksi true saat AKTIF dan dalam rentang */
    public function test_UT_F64_06_bisa_input_true_dalam_rentang(): void
    {
        $kegiatan = $this->buatKegiatan(); // AKTIF, selesai +10 hari
        $this->assertTrue($kegiatan->bisaInputTransaksi());
    }

    /** UT-F64-07 — bisaInputTransaksi false saat DITUTUP */
    public function test_UT_F64_07_bisa_input_false_saat_ditutup(): void
    {
        $kegiatan = $this->buatKegiatan(['status' => 'DITUTUP']);
        $this->assertFalse($kegiatan->bisaInputTransaksi());
    }

    /** UT-F67-11 — totalRealisasi hanya menjumlah transaksi APPROVED */
    public function test_UT_F67_11_total_realisasi_hanya_approved(): void
    {
        $kegiatan = $this->buatKegiatan();
        $this->makeTransaksi($kegiatan, 'APPROVED'); // 100000
        $this->makeTransaksi($kegiatan, 'PENDING');  // diabaikan

        $this->assertEquals(100000, $kegiatan->totalRealisasi());
    }

    /** UT-F67-12 — totalPengeluaranBerjalan (PENDING/REVISION/APPROVED) */
    public function test_UT_F67_12_total_pengeluaran_berjalan(): void
    {
        $kegiatan = $this->buatKegiatan();
        $this->makePengeluaran($kegiatan, 300000, 'APPROVED');
        $this->makePengeluaran($kegiatan, 200000, 'PENDING');
        $this->makePengeluaran($kegiatan, 100000, 'REJECTED'); // diabaikan

        $this->assertEquals(500000, $kegiatan->totalPengeluaranBerjalan());
    }

    /** UT-F67-13 — selisihLebihAnggaran mengembalikan kelebihan di atas anggaran */
    public function test_UT_F67_13_selisih_lebih_anggaran(): void
    {
        $kegiatan = $this->buatKegiatan(['anggaran' => 500000]);
        $this->makePengeluaran($kegiatan, 400000, 'APPROVED');

        $this->assertEquals(100000, $kegiatan->selisihLebihAnggaran(200000)); // 600k - 500k
        $this->assertEquals(0, $kegiatan->selisihLebihAnggaran());            // masih di bawah anggaran
    }

    /** UT-F67-14 — selisihLebihAnggaran 0 jika anggaran 0 (tanpa batas) */
    public function test_UT_F67_14_selisih_nol_jika_anggaran_nol(): void
    {
        $kegiatan = $this->buatKegiatan(['anggaran' => 0]);
        $this->makePengeluaran($kegiatan, 999999, 'APPROVED');

        $this->assertEquals(0, $kegiatan->selisihLebihAnggaran(500000));
    }

    /** UT-F67-15 — sisaAnggaran = anggaran - pengeluaran berjalan */
    public function test_UT_F67_15_sisa_anggaran(): void
    {
        $kegiatan = $this->buatKegiatan(['anggaran' => 1000000]);
        $this->makePengeluaran($kegiatan, 300000, 'APPROVED');

        $this->assertEquals(700000, $kegiatan->sisaAnggaran());
    }

    /** UT-F67-16 — Index menampilkan view daftar kegiatan */
    public function test_UT_F67_16_index_returns_view(): void
    {
        $this->withoutVite();
        $response = $this->actingAs($this->admin)->get(route('dashboard.kegiatan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.kegiatan.index');
    }

    /** UT-F67-17 — Index stats_only mengembalikan JSON statistik */
    public function test_UT_F67_17_index_stats_only_json(): void
    {
        $this->buatKegiatan();
        $response = $this->actingAs($this->admin)
            ->getJson(route('dashboard.kegiatan.index', ['stats_only' => 1]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['stats' => ['total', 'aktif', 'ditutup']]);
    }

    /** UT-F63-08 — tutupJikaSelesai() tidak menutup jika ada transaksi PENDING */
    public function test_UT_F63_08_tutup_jika_selesai_tidak_menutup_jika_ada_pending(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kegiatan Ada Pending',
            'jenis_kegiatan'  => 'SOSIAL',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
            'anggaran'        => 1000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);
        $this->makeTransaksi($kegiatan, 'PENDING');
        $kegiatan->tutupJikaSelesai();
        $this->assertDatabaseHas('kegiatan', [
            'id'     => $kegiatan->id,
            'status' => 'AKTIF',
        ]);
    }

    /** UT-F64-05 — bisaInputTransaksi() = false saat AKTIF tapi di luar rentang tanggal */
    public function test_UT_F64_05_bisa_input_transaksi_false_di_luar_rentang_tanggal(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kegiatan Tanggal Lewat',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(1)->toDateString(),
            'anggaran'        => 500000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);
        $this->assertFalse($kegiatan->bisaInputTransaksi());
    }
}