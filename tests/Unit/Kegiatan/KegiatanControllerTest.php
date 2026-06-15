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

    /**
     * UT-F67-01 — Tambah kegiatan valid → status AKTIF
     */
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

    /**
     * UT-F67-02 — Tanggal selesai < mulai → ditolak
     */
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

    /**
     * UT-F67-03 — Anggaran negatif → ditolak
     */
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

    /**
     * UT-F67-04 — Update kegiatan berhasil
     */
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

    /**
     * UT-F67-05 — Hapus kegiatan tanpa transaksi → berhasil
     */
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

    /**
     * UT-F67-06 — Hapus kegiatan yang punya transaksi → ditolak
     */
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

    /**
     * UT-F67-07 — Auto-close: semua transaksi APPROVED + tanggal lewat → DITUTUP
     */
    public function test_UT_F67_07_auto_close_kegiatan_selesai(): void
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kegiatan Selesai',
            'jenis_kegiatan'  => 'ZAKAT',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(), // sudah lewat
            'anggaran'        => 1000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->panitia->id,
        ]);

        // Semua transaksi APPROVED
        $this->makeTransaksi($kegiatan, 'APPROVED');

        $kegiatan->tutupJikaSelesai();

        $this->assertDatabaseHas('kegiatan', [
            'id'     => $kegiatan->id,
            'status' => 'DITUTUP',
        ]);
    }
}