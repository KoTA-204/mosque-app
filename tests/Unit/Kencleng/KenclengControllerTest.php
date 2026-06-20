<?php

namespace Tests\Unit\Kencleng;

use App\Models\Dompet;
use App\Models\Kencleng;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\KategoriTransaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KenclengControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User     $owner;
    protected User     $other;
    protected Kencleng $kencleng;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role        = Role::create(['role_name' => 'PHM']);
        $this->owner = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);
        $this->other = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        // Buat dompet TANPA user_id (kolom tidak ada di tabel dompet)
        $dompet = Dompet::factory()->create();

        // Buat transaksi milik owner
        $transaksi = Transaksi::create([
            'dompet_id'         => $dompet->id,
            'user_id'           => $this->owner->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis_transaksi'   => 'PEMASUKAN',
            'jumlah'            => 100000,
            'status_approval'   => 'PENDING',
            'status_jurnal'     => 'UNMAPPED',
        ]);

        // Gunakan DB::table untuk bypass fillable kencleng
        $kenclengId = DB::table('kencleng')->insertGetId([
            'transaksi_id'   => $transaksi->id,
            'nomor_kwitansi' => 'BA-TEST-001',
            'berita_acara'   => 'berita-acara/test.pdf',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $this->kencleng = Kencleng::find($kenclengId);
    }

    /** Helper: buat kencleng + transaksi (status approval bebas) milik user tertentu */
    private function buatKenclengStatus(User $owner, string $status): Kencleng
    {
        $dompet    = Dompet::factory()->create();
        $transaksi = Transaksi::create([
            'dompet_id'         => $dompet->id,
            'user_id'           => $owner->id,
            'tanggal_transaksi' => now()->toDateString(),
            'jenis_transaksi'   => 'PEMASUKAN',
            'jumlah'            => 100000,
            'status_approval'   => $status,
            'status_jurnal'     => 'UNMAPPED',
        ]);

        $id = DB::table('kencleng')->insertGetId([
            'transaksi_id'   => $transaksi->id,
            'nomor_kwitansi' => 'KWT-' . $status,
            'berita_acara'   => 'berita-acara/' . $status . '.pdf',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return Kencleng::find($id);
    }

    /** UT-F58-03 — owner dapat membuka form edit kencleng PENDING miliknya */
    public function test_UT_F58_03_owner_can_edit_own_pending(): void
    {
        $this->withoutVite();
        $this->actingAs($this->owner)
            ->get(route('dashboard.kencleng.edit', $this->kencleng))
            ->assertStatus(200);
    }

    /** UT-F58-04 — non-owner tidak bisa edit kencleng orang lain (403) */
    public function test_UT_F58_04_non_owner_cannot_edit(): void
    {
        $this->actingAs($this->other)
            ->get(route('dashboard.kencleng.edit', $this->kencleng))
            ->assertStatus(403);
    }

    /** UT-F58-05 — edit kencleng yang sudah APPROVED ditolak (redirect + error) */
    public function test_UT_F58_05_edit_approved_redirect_error(): void
    {
        $kencleng = $this->buatKenclengStatus($this->owner, 'APPROVED');

        $this->actingAs($this->owner)
            ->from(route('dashboard.kencleng.index'))
            ->get(route('dashboard.kencleng.edit', $kencleng))
            ->assertRedirect(route('dashboard.kencleng.index'))
            ->assertSessionHas('error');
    }

    /** UT-F58-06 — owner update valid → total dihitung ulang & status balik PENDING */
    public function test_UT_F58_06_owner_update_recalc_total(): void
    {
        $payload = [
            'tanggal_hitung' => now()->toDateString(),
            'dompet_id'      => $this->kencleng->transaksi->dompet_id,
            'pecahan'        => [100000 => 2, 50000 => 1],
            'jumlah_disetor' => '250000',
            'submit_type'    => 'ajukan',
        ];

        $this->actingAs($this->owner)
            ->put(route('dashboard.kencleng.update', $this->kencleng), $payload)
            ->assertRedirect(route('dashboard.kencleng.index'));

        $this->kencleng->transaksi->refresh();
        $this->assertEquals(250000, (float) $this->kencleng->transaksi->jumlah);
        $this->assertEquals('PENDING', $this->kencleng->transaksi->status_approval);
        $this->assertDatabaseHas('kencleng_detail', [
            'kencleng_id'    => $this->kencleng->id,
            'pecahan'        => 100000,
            'jumlah_pecahan' => 2,
        ]);
    }

    /** UT-F58-07 — non-owner tidak bisa update (403) */
    public function test_UT_F58_07_non_owner_cannot_update(): void
    {
        $payload = [
            'tanggal_hitung' => now()->toDateString(),
            'dompet_id'      => $this->kencleng->transaksi->dompet_id,
            'pecahan'        => [100000 => 1],
            'jumlah_disetor' => '100000',
            'submit_type'    => 'ajukan',
        ];

        $this->actingAs($this->other)
            ->put(route('dashboard.kencleng.update', $this->kencleng), $payload)
            ->assertStatus(403);
    }

    /** UT-F58-08 — owner destroy kencleng PENDING → transaksi & kencleng terhapus */
    public function test_UT_F58_08_owner_destroy_pending(): void
    {
        $id    = $this->kencleng->id;
        $trxId = $this->kencleng->transaksi_id;

        $this->actingAs($this->owner)
            ->delete(route('dashboard.kencleng.destroy', $this->kencleng))
            ->assertRedirect(route('dashboard.kencleng.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kencleng', ['id' => $id]);
        $this->assertDatabaseMissing('transaksi', ['id' => $trxId]);
    }

    /** UT-F58-09 — destroy kencleng APPROVED ditolak (tidak terhapus) */
    public function test_UT_F58_09_destroy_approved_blocked(): void
    {
        $kencleng = $this->buatKenclengStatus($this->owner, 'APPROVED');

        $this->actingAs($this->owner)
            ->from(route('dashboard.kencleng.index'))
            ->delete(route('dashboard.kencleng.destroy', $kencleng))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kencleng', ['id' => $kencleng->id]);
    }

    /** UT-F58-10 — non-owner tidak bisa destroy (error, tidak terhapus) */
    public function test_UT_F58_10_non_owner_cannot_destroy(): void
    {
        $this->actingAs($this->other)
            ->from(route('dashboard.kencleng.index'))
            ->delete(route('dashboard.kencleng.destroy', $this->kencleng))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kencleng', ['id' => $this->kencleng->id]);
    }

    /**
     * UT-F58-01
     * Owner dapat melihat detail kencleng miliknya sendiri
     */
    public function test_UT_F58_01_owner_can_view_own_kencleng(): void
    {
        $response = $this->actingAs($this->owner)
                         ->get(route('dashboard.kencleng.show', $this->kencleng));

        $response->assertStatus(200);
    }

    /**
     * UT-F58-02
     * Non-owner tidak dapat melihat kencleng milik orang lain (403)
     */
    public function test_UT_F58_02_non_owner_cannot_view_others_kencleng(): void
    {
        $response = $this->actingAs($this->other)
                         ->get(route('dashboard.kencleng.show', $this->kencleng));

        $response->assertStatus(403);
    }

    /** UT-F58-11 — store: owner submit kencleng valid → tersimpan & redirect success */
    public function test_UT_F58_11_store_kencleng_valid(): void
    {
        Storage::fake('public');
        KategoriTransaksi::create(['nama_kategori' => 'Setoran Kencleng', 'status' => 'aktif']);

        $dompet = Dompet::factory()->create();

        $payload = [
            'tanggal_hitung' => now()->toDateString(),
            'dompet_id'      => $dompet->id,
            'pecahan'        => [100000 => 1, 50000 => 2],   // 200.000
            'jumlah_disetor' => '200000',
            'submit_type'    => 'ajukan',
            'keterangan'     => 'Setoran kencleng',
            'berita_acara'   => UploadedFile::fake()->create('ba.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($this->owner)
            ->post(route('dashboard.kencleng.store'), $payload)
            ->assertRedirect(route('dashboard.kencleng.index'))
            ->assertSessionHas('success');

        // 1 dari setUp + 1 baru = 2
        $this->assertDatabaseCount('kencleng', 2);
        $this->assertDatabaseHas('transaksi', [
            'user_id'         => $this->owner->id,
            'jenis_transaksi' => 'PEMASUKAN',
            'status_approval' => 'PENDING',
            'status_jurnal'   => 'UNMAPPED',
        ]);
    }
}