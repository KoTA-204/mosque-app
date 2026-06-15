<?php

namespace Tests\Feature\Integration;

use App\Models\Kegiatan;
use App\Models\KategoriTransaksi;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

/**
 * Integrasi Kegiatan Khusus + Transaksi Kegiatan.
 * Rute transaksi-kegiatan.* HANYA auth + active (tanpa permission), otorisasi
 * dilakukan di controller via authorizeKegiatan() (panitia-khusus harus pemilik).
 *
 * Payload store mengikuti StoreTransaksiRequest:
 *  jenis_transaksi, tanggal_transaksi, jumlah(min:1), dompet_id(exists),
 *  kategori_transaksi_id(exists), deskripsi(nullable).
 *
 * Catatan: "Transaksi Kegiatan" bukan model terpisah -> memakai model Transaksi
 * dengan kegiatan_id (sesuai keterangan: tidak ada migration/model TransaksiKegiatan).
 */
class KegiatanTransaksiIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /** IT-F63-01 (+): Panitia pemilik kegiatan AKTIF mencatat transaksi kegiatan. */
    public function test_it_f63_01_panitia_mencatat_transaksi_kegiatan(): void
    {
        $panitia  = $this->buatUser($this->buatRole('Panitia Khusus'));
        $dompet   = $this->buatDompet();
        $kategori = KategoriTransaksi::factory()->create();
        $kegiatan = $this->buatKegiatan($panitia->id); // AKTIF & berjalan

        $this->actingAs($panitia)->post(
            route('dashboard.transaksi-kegiatan.transaksi.store', $kegiatan),
            [
                'dompet_id'             => $dompet->id,
                'kategori_transaksi_id' => $kategori->id,
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 500000,
                'tanggal_transaksi'     => Carbon::now()->toDateString(),
                'deskripsi'             => 'Donasi qurban',
            ]
        )->assertRedirect();

        $this->assertDatabaseHas('transaksi', [
            'kegiatan_id' => $kegiatan->id,
            'jumlah'      => 500000,
        ]);
    }

    /** IT-F63-02 (-): Kegiatan yang tanggalnya sudah lewat tidak menerima transaksi baru. */
    public function test_it_f63_02_kegiatan_lewat_tanggal_menolak_transaksi(): void
    {
        $panitia  = $this->buatUser($this->buatRole('Panitia Khusus'));
        $dompet   = $this->buatDompet();
        $kategori = KategoriTransaksi::factory()->create();
        $kegiatan = $this->buatKegiatan($panitia->id, [
            'tanggal_mulai'   => Carbon::now()->subDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::now()->subDay()->toDateString(), // sudah lewat
            'status'          => 'AKTIF',
        ]);

        // Logika model (sumber kebenaran)
        $this->assertFalse($kegiatan->bisaInputTransaksi());

        // Integrasi HTTP: payload VALID (lolos StoreTransaksiRequest), tapi ditolak
        // controller karena tanggal kegiatan sudah lewat -> session 'error'.
        $this->actingAs($panitia)->post(
            route('dashboard.transaksi-kegiatan.transaksi.store', $kegiatan),
            [
                'dompet_id'             => $dompet->id,
                'kategori_transaksi_id' => $kategori->id,
                'jenis_transaksi'       => 'PEMASUKAN',
                'jumlah'                => 100000,
                'tanggal_transaksi'     => Carbon::now()->toDateString(),
                'deskripsi'             => 'Telat catat',
            ]
        )->assertSessionHas('error');

        $this->assertDatabaseMissing('transaksi', ['kegiatan_id' => $kegiatan->id]);
    }

    /** IT-F63-03 (+): Kegiatan otomatis DITUTUP saat tanggal lewat & semua transaksi APPROVED. */
    public function test_it_f63_03_kegiatan_otomatis_ditutup(): void
    {
        $panitia  = $this->buatUser($this->buatRole('Panitia Khusus'));
        $kegiatan = $this->buatKegiatan($panitia->id, [
            'tanggal_mulai'   => Carbon::now()->subDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::now()->subDay()->toDateString(), // sudah lewat
            'status'          => 'AKTIF',
        ]);

        $this->buatTransaksi([
            'kegiatan_id'     => $kegiatan->id,
            'user_id'         => $panitia->id,
            'status_approval' => 'APPROVED',
        ]);

        $kegiatan->tutupJikaSelesai();

        $this->assertSame('DITUTUP', $kegiatan->fresh()->status);
    }

    /** IT-F64-01 (-): Panitia lain (bukan pemilik) -> 403 saat membuka kegiatan. */
    public function test_it_f64_01_panitia_lain_ditolak_403(): void
    {
        $role     = $this->buatRole('Panitia Khusus');
        $pemilik  = $this->buatUser($role);
        $lainnya  = $this->buatUser($role);
        $kegiatan = $this->buatKegiatan($pemilik->id);

        $this->actingAs($lainnya)
            ->get(route('dashboard.transaksi-kegiatan.show', $kegiatan))
            ->assertForbidden();
    }
}
