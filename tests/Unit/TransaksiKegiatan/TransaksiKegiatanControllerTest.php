<?php

namespace Tests\Unit\TransaksiKegiatan;

use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Kegiatan;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiKegiatanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User              $owner;   // panitia pemilik kegiatan
    protected User              $admin;
    protected Kegiatan          $kegiatan;
    protected Dompet            $dompet;
    protected KategoriTransaksi $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $panitiaRole = Role::create(['role_name' => 'Panitia Khusus']);
        $this->owner = User::factory()->create([
            'role_id' => $panitiaRole->id,
            'status'  => 'active',
        ]);

        $adminRole   = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status'  => 'active',
        ]);

        $this->dompet   = Dompet::factory()->create();
        $this->kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Infaq Kegiatan',
            'status'        => 'aktif',
        ]);

        // Kegiatan AKTIF, tanggal masa depan → bisaInputTransaksi() true
        $this->kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Qurban Aktif',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => now()->addDays(3)->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'anggaran'        => 10000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->owner->id,
        ]);
    }

    private function makeTransaksi(string $statusApproval = 'PENDING', ?int $userId = null): Transaksi
    {
        return Transaksi::create([
            'dompet_id'             => $this->dompet->id,
            'user_id'               => $userId ?? $this->owner->id,
            'kegiatan_id'           => $this->kegiatan->id,
            'kategori_transaksi_id' => $this->kategori->id,
            'tanggal_transaksi'     => now()->toDateString(),
            'jenis_transaksi'       => 'PEMASUKAN',
            'jumlah'                => 500000,
            'status_approval'       => $statusApproval,
            'status_jurnal'         => 'UNMAPPED',
        ]);
    }

    /**
     * UT-F68-01 — Catat transaksi PEMASUKAN pada kegiatan AKTIF → berhasil
     */
    public function test_UT_F68_01_store_transaksi_kegiatan_aktif(): void
    {
        $response = $this->actingAs($this->owner)
                         ->post(route('dashboard.transaksi-kegiatan.transaksi.store', $this->kegiatan), [
                             'jenis_transaksi'       => 'PEMASUKAN',
                             'tanggal_transaksi'     => now()->toDateString(),
                             'jumlah'                => 750000,
                             'dompet_id'             => $this->dompet->id,
                             'kategori_transaksi_id' => $this->kategori->id,
                             'deskripsi'             => 'Donasi qurban',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', [
            'kegiatan_id' => $this->kegiatan->id,
            'jumlah'      => 750000,
        ]);
    }

    /**
     * UT-F68-02 — Catat transaksi pada kegiatan DITUTUP → ditolak
     */
    public function test_UT_F68_02_store_transaksi_kegiatan_ditutup(): void
    {
        $this->kegiatan->update(['status' => 'DITUTUP']);

        $response = $this->actingAs($this->owner)
                         ->post(route('dashboard.transaksi-kegiatan.transaksi.store', $this->kegiatan), [
                             'jenis_transaksi'       => 'PEMASUKAN',
                             'tanggal_transaksi'     => now()->toDateString(),
                             'jumlah'                => 500000,
                             'dompet_id'             => $this->dompet->id,
                             'kategori_transaksi_id' => $this->kategori->id,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * UT-F68-03 — Edit transaksi milik sendiri status PENDING → berhasil
     */
    public function test_UT_F68_03_update_transaksi_own_pending(): void
    {
        $transaksi = $this->makeTransaksi('PENDING');

        $response = $this->actingAs($this->owner)
                         ->put(route('dashboard.transaksi-kegiatan.transaksi.update', [$this->kegiatan, $transaksi]), [
                             'jenis_transaksi'       => 'PEMASUKAN',
                             'tanggal_transaksi'     => now()->toDateString(),
                             'jumlah'                => 999000,
                             'dompet_id'             => $this->dompet->id,
                             'kategori_transaksi_id' => $this->kategori->id,
                             'deskripsi'             => 'Update donasi',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', [
            'id'     => $transaksi->id,
            'jumlah' => 999000,
        ]);
    }

    /**
     * UT-F68-04 — Edit transaksi milik user lain → 403
     */
    public function test_UT_F68_04_update_transaksi_other_user_forbidden(): void
    {
        // Transaksi di kegiatan owner, tapi dibuat user lain (admin)
        $transaksi = $this->makeTransaksi('PENDING', $this->admin->id);

        $response = $this->actingAs($this->owner)
                         ->put(route('dashboard.transaksi-kegiatan.transaksi.update', [$this->kegiatan, $transaksi]), [
                             'jenis_transaksi'       => 'PEMASUKAN',
                             'tanggal_transaksi'     => now()->toDateString(),
                             'jumlah'                => 111000,
                             'dompet_id'             => $this->dompet->id,
                             'kategori_transaksi_id' => $this->kategori->id,
                         ]);

        $response->assertStatus(403);
    }

    /**
     * UT-F68-05 — Edit transaksi yang sudah APPROVED → ditolak
     */
    public function test_UT_F68_05_update_transaksi_approved_blocked(): void
    {
        $transaksi = $this->makeTransaksi('APPROVED');

        $response = $this->actingAs($this->owner)
                         ->put(route('dashboard.transaksi-kegiatan.transaksi.update', [$this->kegiatan, $transaksi]), [
                             'jenis_transaksi'       => 'PEMASUKAN',
                             'tanggal_transaksi'     => now()->toDateString(),
                             'jumlah'                => 222000,
                             'dompet_id'             => $this->dompet->id,
                             'kategori_transaksi_id' => $this->kategori->id,
                         ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * UT-F68-06 — Hapus transaksi milik sendiri status PENDING → berhasil
     */
    public function test_UT_F68_06_delete_transaksi_own_pending(): void
    {
        $transaksi = $this->makeTransaksi('PENDING');

        $response = $this->actingAs($this->owner)
                         ->delete(route('dashboard.transaksi-kegiatan.transaksi.destroy', [$this->kegiatan, $transaksi]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('transaksi', ['id' => $transaksi->id]);
    }

    /**
     * UT-F68-07 — Hapus transaksi status APPROVED → ditolak
     */
    public function test_UT_F68_07_delete_transaksi_approved_blocked(): void
    {
        $transaksi = $this->makeTransaksi('APPROVED');

        $response = $this->actingAs($this->owner)
                         ->delete(route('dashboard.transaksi-kegiatan.transaksi.destroy', [$this->kegiatan, $transaksi]));

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id]);
    }
}