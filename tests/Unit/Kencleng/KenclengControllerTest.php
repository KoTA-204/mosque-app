<?php

namespace Tests\Unit\Kencleng;

use App\Models\Dompet;
use App\Models\Kencleng;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
}