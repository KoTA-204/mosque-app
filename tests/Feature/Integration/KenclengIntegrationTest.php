<?php

namespace Tests\Feature\Integration;

use App\Models\Kencleng;
use App\Models\KenclengDetail;
use App\Services\ApprovalService;
use App\Services\KenclengService;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

/**
 * Integrasi Kencleng + Approval.
 *
 * PERINGATAN KODE ASLI: KenclengService::store() memanggil getKategoriKencleng()
 * yang memfilter where('jenis_transaksi','PEMASUKAN') -- kolom tsb TIDAK ada di
 * migration kategori_transaksi, sehingga store() akan melempar error kolom pada
 * DB nyata. Karena itu IT-F58-01 menyusun record langsung (Transaksi+Kencleng+
 * KenclengDetail) lalu memverifikasi total fisik via KenclengService::getTotalFisik()
 * (method yang bebas dari bug tsb). Perbaiki getKategoriKencleng() agar store()
 * dapat diuji end-to-end.
 */
class KenclengIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /** IT-F58-01 (+): Kencleng berstatus PENDING & total fisik = sum(pecahan * jumlah). */
    public function test_it_f58_01_total_fisik_dan_status_pending(): void
    {
        $user      = $this->buatUser($this->buatRole('Bendahara'));
        $transaksi = $this->buatTransaksi([
            'user_id'         => $user->id,
            'status_approval' => 'PENDING',
            'jumlah'          => 0,
        ]);

        $kencleng = Kencleng::create([
            'transaksi_id'   => $transaksi->id,
            'nomor_kwitansi' => 'KWT-' . Carbon::now()->year . '-001',
            'berita_acara'   => 'berita_acara/uji.pdf',
        ]);

        KenclengDetail::create([
            'kencleng_id'    => $kencleng->id,
            'pecahan'        => 2000,
            'jumlah_pecahan' => 10, // 20.000
        ]);
        KenclengDetail::create([
            'kencleng_id'    => $kencleng->id,
            'pecahan'        => 5000,
            'jumlah_pecahan' => 4,  // 20.000
        ]);

        $service = app(KenclengService::class);

        $this->assertSame(40000, $service->getTotalFisik($kencleng->fresh()));
        $this->assertSame('PENDING', $transaksi->fresh()->status_approval);
    }

    /** IT-F62-01 (-): Kencleng yang sudah APPROVED tidak bisa diedit. */
    public function test_it_f62_01_kencleng_approved_tidak_bisa_diedit(): void
    {
        $user      = $this->buatUser($this->buatRole('Bendahara', ['VIEW_KENCLENG', 'EDIT_KENCLENG']));
        $transaksi = $this->buatTransaksi([
            'user_id'         => $user->id,
            'status_approval' => 'APPROVED',
        ]);
        $kencleng = Kencleng::create([
            'transaksi_id'   => $transaksi->id,
            'nomor_kwitansi' => 'KWT-APPROVED',
            'berita_acara'   => 'berita_acara/approved.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.kencleng.edit', $kencleng))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /** IT-F58-02 (-): Tidak bisa melihat kencleng milik orang lain (403). */
    public function test_it_f58_02_owner_only_403(): void
    {
        $role     = $this->buatRole('Bendahara', ['VIEW_KENCLENG']);
        $pemilik  = $this->buatUser($role);
        $lainnya  = $this->buatUser($role);

        $transaksi = $this->buatTransaksi(['user_id' => $pemilik->id]);
        $kencleng  = Kencleng::create([
            'transaksi_id'   => $transaksi->id,
            'nomor_kwitansi' => 'KWT-OWNER',
            'berita_acara'   => 'berita_acara/owner.pdf',
        ]);

        $this->actingAs($lainnya)
            ->get(route('dashboard.kencleng.show', $kencleng))
            ->assertForbidden();
    }

    /** IT-F60-02 (+): ApprovalService::approve mengubah PENDING->APPROVED & menutup kegiatan. */
    public function test_it_f60_02_approve_menutup_kegiatan(): void
    {
        $panitia  = $this->buatUser($this->buatRole('Panitia Khusus'));
        $kegiatan = $this->buatKegiatan($panitia->id, [
            'tanggal_mulai'   => Carbon::now()->subDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::now()->subDay()->toDateString(), // sudah lewat
            'status'          => 'AKTIF',
        ]);

        $transaksi = $this->buatTransaksi([
            'kegiatan_id'     => $kegiatan->id,
            'user_id'         => $panitia->id,
            'status_approval' => 'PENDING',
        ]);

        $result = app(ApprovalService::class)->approve($transaksi);

        $this->assertTrue($result === true);
        $this->assertSame('APPROVED', $transaksi->fresh()->status_approval);
        $this->assertSame('DITUTUP', $kegiatan->fresh()->status);
    }
}
