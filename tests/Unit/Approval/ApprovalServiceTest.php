<?php

namespace Tests\Unit\Approval;

use App\Models\Dompet;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $approver;
    protected Dompet $dompet;
    protected ApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $role = Role::create(['role_name' => 'Bendahara 1']);
        $this->approver = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        $this->dompet = Dompet::create([
            'nama_dompet'    => 'Kas Masjid',
            'jenis_dompet'   => 'CASH',
            'nomor_rekening' => null,
            'nama_bank'      => null,
            'saldo_awal'     => 0,
        ]);

        $this->service = new ApprovalService();
    }

    private function buatTransaksi(string $status): Transaksi
    {
        return Transaksi::create([
            'dompet_id'             => $this->dompet->id,
            'kegiatan_id'           => null,
            'user_id'               => $this->approver->id,
            'kategori_transaksi_id' => null,
            'tanggal_transaksi'     => now()->toDateString(),
            'jenis_transaksi'       => 'PENGELUARAN',
            'jumlah'                => 100000,
            'deskripsi'             => 'Test transaksi',
            'status_approval'       => $status,
            'status_jurnal'         => 'UNMAPPED',
        ]);
    }

    /**
     * UT-F60-01
     * Reject transaksi PENDING → status berubah ke REJECTED
     */
    public function test_UT_F60_01_reject_pending_transaksi_changes_status(): void
    {
        $transaksi = $this->buatTransaksi('PENDING');

        // reject() adalah public method yang memanggil changeStatus() secara internal
        $result = $this->service->reject($transaksi, 'Tidak sesuai dengan bukti');

        $this->assertTrue($result === true);
        $this->assertDatabaseHas('transaksi', [
            'id'              => $transaksi->id,
            'status_approval' => 'REJECTED',
        ]);
    }

    /**
     * UT-F60-02
     * Approve transaksi PENDING → status berubah ke APPROVED
     */
    public function test_UT_F60_02_approve_pending_transaksi_changes_status_to_approved(): void
    {
        $transaksi = $this->buatTransaksi('PENDING');

        // approve() adalah public method
        $result = $this->service->approve($transaksi);

        $this->assertTrue($result === true);
        $this->assertDatabaseHas('transaksi', [
            'id'              => $transaksi->id,
            'status_approval' => 'APPROVED',
        ]);
    }

    /**
     * UT-F60-03
     * Approve transaksi yang bukan PENDING → service return string error
     */
    public function test_UT_F60_03_approve_non_pending_transaksi_returns_error_string(): void
    {
        $transaksi = $this->buatTransaksi('APPROVED'); // sudah APPROVED, bukan PENDING

        $result = $this->service->approve($transaksi);

        // changeStatus() return string jika status bukan PENDING
        $this->assertIsString($result);
        $this->assertEquals('Transaksi tidak dalam status PENDING', $result);

        // Status tidak berubah
        $this->assertDatabaseHas('transaksi', [
            'id'              => $transaksi->id,
            'status_approval' => 'APPROVED',
        ]);
    }

    /**
     * UT-F60-04
     * Revision transaksi PENDING dengan catatan → status berubah ke REVISION
     */
    public function test_UT_F60_04_revision_pending_transaksi_with_note(): void
    {
        $transaksi = $this->buatTransaksi('PENDING');

        // revision() adalah public method yang wajib ada catatan
        $result = $this->service->revision($transaksi, 'Mohon lengkapi kwitansi pembayaran');

        $this->assertTrue($result === true);
        $this->assertDatabaseHas('transaksi', [
            'id'              => $transaksi->id,
            'status_approval' => 'REVISION',
        ]);
    }
}