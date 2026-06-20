<?php

namespace Tests\Unit\Approval;

use App\Models\Dompet;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Kegiatan;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    private function buatKegiatan(string $status = 'AKTIF', ?string $tanggalSelesai = null): Kegiatan
    {
        return Kegiatan::create([
            'nama_kegiatan'   => 'Qurban 2026',
            'jenis_kegiatan'  => 'QURBAN',
            'tanggal_mulai'   => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => $tanggalSelesai,
            'anggaran'        => 0,
            'status'          => $status,
            'panitia_id'      => $this->approver->id,
        ]);
    }

    private function buatTransaksiKegiatan(int $kegiatanId, string $status = 'PENDING'): Transaksi
    {
        return Transaksi::create([
            'dompet_id'             => $this->dompet->id,
            'kegiatan_id'           => $kegiatanId,
            'user_id'               => $this->approver->id,
            'kategori_transaksi_id' => null,
            'tanggal_transaksi'     => now()->toDateString(),
            'jenis_transaksi'       => 'PENGELUARAN',
            'jumlah'                => 50000,
            'status_approval'       => $status,
            'status_jurnal'         => 'UNMAPPED',
        ]);
    }

    /** Buat transaksi yang punya kencleng (untuk uji scope baseQuery). */
    private function buatTransaksiKencleng(string $status = 'PENDING'): Transaksi
    {
        $t = Transaksi::create([
            'dompet_id'             => $this->dompet->id,
            'kegiatan_id'           => null,
            'user_id'               => $this->approver->id,
            'kategori_transaksi_id' => null,
            'tanggal_transaksi'     => now()->toDateString(),
            'jenis_transaksi'       => 'PEMASUKAN',
            'jumlah'                => 75000,
            'status_approval'       => $status,
            'status_jurnal'         => 'UNMAPPED',
        ]);

        DB::table('kencleng')->insert([
            'transaksi_id'   => $t->id,
            'nomor_kwitansi' => 'KWT-TEST',
            'berita_acara'   => 'ba/test.pdf',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return $t;
    }

    /** UT-F60-05 — reject transaksi non-PENDING mengembalikan string error */
    public function test_UT_F60_05_reject_non_pending_returns_error_string(): void
    {
        $transaksi = $this->buatTransaksi('REJECTED');
        $result = $this->service->reject($transaksi, 'apa pun');
        $this->assertIsString($result);
        $this->assertEquals('Transaksi tidak dalam status PENDING', $result);
    }

    /** UT-F60-06 — revision transaksi non-PENDING mengembalikan string error */
    public function test_UT_F60_06_revision_non_pending_returns_error_string(): void
    {
        $transaksi = $this->buatTransaksi('APPROVED');
        $result = $this->service->revision($transaksi, 'lengkapi bukti');
        $this->assertIsString($result);
        $this->assertEquals('Transaksi tidak dalam status PENDING', $result);
    }

    /** UT-F60-07 — reject tanpa catatan menyimpan catatan NULL */
    public function test_UT_F60_07_reject_empty_catatan_saves_null(): void
    {
        $transaksi = $this->buatTransaksi('PENDING');
        $result = $this->service->reject($transaksi, '');
        $this->assertTrue($result === true);
        $this->assertDatabaseHas('transaksi', [
            'id'              => $transaksi->id,
            'status_approval' => 'REJECTED',
            'catatan'         => null,
        ]);
    }

    /** UT-F60-08 — approve transaksi kegiatan yang sudah selesai → kegiatan auto DITUTUP */
    public function test_UT_F60_08_approve_kegiatan_selesai_auto_ditutup(): void
    {
        $kegiatan  = $this->buatKegiatan('AKTIF', now()->subDay()->toDateString());
        $transaksi = $this->buatTransaksiKegiatan($kegiatan->id, 'PENDING');

        $result = $this->service->approve($transaksi);

        $this->assertTrue($result === true);
        $this->assertEquals('DITUTUP', $kegiatan->fresh()->status);
    }

    /** UT-F60-09 — approve transaksi kegiatan belum selesai → kegiatan tetap AKTIF */
    public function test_UT_F60_09_approve_kegiatan_belum_selesai_tetap_aktif(): void
    {
        $kegiatan  = $this->buatKegiatan('AKTIF', now()->addDays(5)->toDateString());
        $transaksi = $this->buatTransaksiKegiatan($kegiatan->id, 'PENDING');

        $result = $this->service->approve($transaksi);

        $this->assertTrue($result === true);
        $this->assertEquals('AKTIF', $kegiatan->fresh()->status);
    }

    /** UT-F60-10 — bulkApprove campuran PENDING + non-PENDING → done & skipped benar */
    public function test_UT_F60_10_bulk_approve_mix_done_skipped(): void
    {
        $pending  = $this->buatTransaksi('PENDING');
        $approved = $this->buatTransaksi('APPROVED'); // akan di-skip

        $result = $this->service->bulkApprove([$pending->id, $approved->id]);

        $this->assertEquals(1, $result['done']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertDatabaseHas('transaksi', ['id' => $pending->id, 'status_approval' => 'APPROVED']);
    }

    /** UT-F60-11 — bulkReject dengan catatanMap → REJECTED + catatan tersimpan */
    public function test_UT_F60_11_bulk_reject_with_catatan_map(): void
    {
        $t1 = $this->buatTransaksi('PENDING');
        $t2 = $this->buatTransaksi('PENDING');

        $result = $this->service->bulkReject([$t1->id => 'tidak valid', $t2->id => null]);

        $this->assertEquals(2, $result['done']);
        $this->assertDatabaseHas('transaksi', ['id' => $t1->id, 'status_approval' => 'REJECTED', 'catatan' => 'tidak valid']);
        $this->assertDatabaseHas('transaksi', ['id' => $t2->id, 'status_approval' => 'REJECTED', 'catatan' => null]);
    }

    /** UT-F60-12 — bulkRevisi dengan catatanMap → REVISION + catatan tersimpan */
    public function test_UT_F60_12_bulk_revisi_with_catatan_map(): void
    {
        $t1 = $this->buatTransaksi('PENDING');

        $result = $this->service->bulkRevisi([$t1->id => 'mohon lengkapi']);

        $this->assertEquals(1, $result['done']);
        $this->assertDatabaseHas('transaksi', ['id' => $t1->id, 'status_approval' => 'REVISION', 'catatan' => 'mohon lengkapi']);
    }

    /** UT-F61-01 — getStats menghitung per status & sumber, mengabaikan transaksi polos */
    public function test_UT_F61_01_get_stats_counts(): void
    {
        $kegiatan = $this->buatKegiatan('AKTIF', now()->addDays(3)->toDateString());
        $this->buatTransaksiKegiatan($kegiatan->id, 'PENDING'); // kegiatan + pending
        $this->buatTransaksiKencleng('APPROVED');               // kencleng + approved
        $this->buatTransaksi('PENDING');                        // POLOS → tak dihitung

        $stats = $this->service->getStats();

        $this->assertEquals(1, $stats['pending']);  // hanya yg punya kegiatan/kencleng
        $this->assertEquals(1, $stats['approved']);
        $this->assertEquals(1, $stats['kegiatan']);
        $this->assertEquals(1, $stats['kencleng']);
    }

    /** UT-F61-02 — getTransaksiByStatus hanya kembalikan transaksi kegiatan/kencleng */
    public function test_UT_F61_02_list_scope_only_kegiatan_kencleng(): void
    {
        $kegiatan = $this->buatKegiatan('AKTIF', now()->addDays(3)->toDateString());
        $milik    = $this->buatTransaksiKegiatan($kegiatan->id, 'PENDING');
        $this->buatTransaksi('PENDING'); // polos → tak muncul

        $hasil = $this->service->getTransaksiByStatus('PENDING');

        $this->assertCount(1, $hasil->items());
        $this->assertEquals($milik->id, $hasil->items()[0]->id);
    }

    /** UT-F61-03 — filter sumber=kegiatan hanya transaksi kegiatan (tanpa kencleng) */
    public function test_UT_F61_03_filter_sumber_kegiatan(): void
    {
        $kegiatan = $this->buatKegiatan('AKTIF', now()->addDays(3)->toDateString());
        $keg      = $this->buatTransaksiKegiatan($kegiatan->id, 'PENDING');
        $this->buatTransaksiKencleng('PENDING');

        $hasil = $this->service->getTransaksiByStatus('PENDING', '', 'kegiatan');

        $this->assertCount(1, $hasil->items());
        $this->assertEquals($keg->id, $hasil->items()[0]->id);
    }

    /** UT-F61-04 — filter sumber=kencleng hanya transaksi kencleng */
    public function test_UT_F61_04_filter_sumber_kencleng(): void
    {
        $kegiatan = $this->buatKegiatan('AKTIF', now()->addDays(3)->toDateString());
        $this->buatTransaksiKegiatan($kegiatan->id, 'PENDING');
        $ken = $this->buatTransaksiKencleng('PENDING');

        $hasil = $this->service->getTransaksiByStatus('PENDING', '', 'kencleng');

        $this->assertCount(1, $hasil->items());
        $this->assertEquals($ken->id, $hasil->items()[0]->id);
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