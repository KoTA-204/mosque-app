<?php

namespace Tests\Unit\TransaksiKegiatan;

use App\Models\Akun;
use App\Models\Dompet;
use App\Models\KategoriAkun;
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

    protected User              $owner;
    protected User              $admin;
    protected Kegiatan          $kegiatan;
    protected Dompet            $dompet;
    protected KategoriTransaksi $kategori;
    protected Akun              $akunDebit;
    protected Akun              $akunKredit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $panitiaRole  = Role::create(['role_name' => 'Panitia Khusus']);
        $this->owner  = User::factory()->create([
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

        $this->kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Qurban Aktif',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => now()->addDays(3)->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'anggaran'        => 10000000,
            'status'          => 'AKTIF',
            'panitia_id'      => $this->owner->id,
        ]);

        // Akun diperlukan StoreTransaksiRequest (akun_debit_id / akun_kredit_id required)
        $katAset = KategoriAkun::firstOrCreate(
            ['kode_kategori' => '1'],
            ['nama_kategori' => 'Aset', 'status' => true]
        );
        $katPend = KategoriAkun::firstOrCreate(
            ['kode_kategori' => '4'],
            ['nama_kategori' => 'Pendapatan', 'status' => true]
        );

        $this->akunDebit = Akun::create([
            'kategori_akun_id' => $katAset->id,
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Kas Masjid',
            'saldo_normal'     => 'DEBIT',
            'status'           => 'aktif',   // ← bukan true
        ]);

        $this->akunKredit = Akun::create([
            'kategori_akun_id' => $katPend->id,
            'kode_akun'        => '4-1000',
            'nama_akun'        => 'Pendapatan Donasi',
            'saldo_normal'     => 'KREDIT',
            'status'           => 'aktif',   // ← bukan true
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

    private function payloadStore(int $jumlah = 750000): array
    {
        return [
            'jenis_transaksi'       => 'PEMASUKAN',
            'tanggal_transaksi'     => now()->toDateString(),
            'jumlah'                => $jumlah,
            'dompet_id'             => $this->dompet->id,
            'kategori_transaksi_id' => $this->kategori->id,
            'akun_debit_id'         => $this->akunDebit->id,
            'akun_kredit_id'        => $this->akunKredit->id,
            'deskripsi'             => 'Donasi qurban',
        ];
    }

    private function payloadUpdate(int $jumlah = 999000): array
    {
        return [
            'jenis_transaksi'       => 'PEMASUKAN',
            'tanggal_transaksi'     => now()->toDateString(),
            'jumlah'                => $jumlah,
            'dompet_id'             => $this->dompet->id,
            'kategori_transaksi_id' => $this->kategori->id,
            'deskripsi'             => 'Update donasi',
        ];
    }

    /** UT-F68-01 — Catat transaksi PEMASUKAN pada kegiatan AKTIF → berhasil */
    public function test_UT_F68_01_store_transaksi_kegiatan_aktif(): void
    {
        $response = $this->actingAs($this->owner)
                         ->post(
                             route('dashboard.transaksi-kegiatan.transaksi.store', $this->kegiatan),
                             $this->payloadStore(750000)
                         );

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', [
            'kegiatan_id' => $this->kegiatan->id,
            'jumlah'      => 750000,
        ]);
    }

    /** UT-F68-02 — Catat transaksi pada kegiatan DITUTUP → ditolak */
    public function test_UT_F68_02_store_transaksi_kegiatan_ditutup(): void
    {
        $this->kegiatan->update(['status' => 'DITUTUP']);

        $response = $this->actingAs($this->owner)
                         ->post(
                             route('dashboard.transaksi-kegiatan.transaksi.store', $this->kegiatan),
                             $this->payloadStore(500000)
                         );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * UT-F68-03 — Edit transaksi milik sendiri status PENDING → berhasil (redirect)
     */
    public function test_UT_F68_03_update_transaksi_own_pending(): void
    {
        $transaksi = $this->makeTransaksi('PENDING');

        $response = $this->actingAs($this->owner)
                         ->put(
                             route('dashboard.transaksi-kegiatan.transaksi.update', [$this->kegiatan, $transaksi]),
                             $this->payloadUpdate(999000)
                         );

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id]);
    }

    /**
     * UT-F68-04 — Edit transaksi milik user lain → diblokir
     */
    public function test_UT_F68_04_update_transaksi_other_user_forbidden(): void
    {
        $transaksi = $this->makeTransaksi('PENDING', $this->admin->id);

        $response = $this->actingAs($this->owner)
                         ->put(
                             route('dashboard.transaksi-kegiatan.transaksi.update', [$this->kegiatan, $transaksi]),
                             $this->payloadUpdate(111000)
                         );

        $this->assertContains($response->getStatusCode(), [302, 403]);
        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id, 'jumlah' => 500000]);
    }

    /**
     * UT-F68-05 — Edit transaksi yang sudah APPROVED → ditolak
     */
    public function test_UT_F68_05_update_transaksi_approved_blocked(): void
    {
        $transaksi = $this->makeTransaksi('APPROVED');

        $response = $this->actingAs($this->owner)
                         ->put(
                             route('dashboard.transaksi-kegiatan.transaksi.update', [$this->kegiatan, $transaksi]),
                             $this->payloadUpdate(222000)
                         );

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id, 'jumlah' => 500000]);
    }

    /** UT-F68-06 — Hapus transaksi milik sendiri status PENDING → berhasil */
    public function test_UT_F68_06_delete_transaksi_own_pending(): void
    {
        $transaksi = $this->makeTransaksi('PENDING');

        $response = $this->actingAs($this->owner)
                         ->delete(route('dashboard.transaksi-kegiatan.transaksi.destroy', [$this->kegiatan, $transaksi]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('transaksi', ['id' => $transaksi->id]);
    }

    /** UT-F68-07 — Hapus transaksi status APPROVED → ditolak */
    public function test_UT_F68_07_delete_transaksi_approved_blocked(): void
    {
        $transaksi = $this->makeTransaksi('APPROVED');

        $response = $this->actingAs($this->owner)
                         ->delete(route('dashboard.transaksi-kegiatan.transaksi.destroy', [$this->kegiatan, $transaksi]));

        $response->assertRedirect();
        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id]);
    }
}
