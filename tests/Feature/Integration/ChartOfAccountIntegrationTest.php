<?php

namespace Tests\Feature\Integration;

use App\Models\Akun;
use App\Models\KategoriAkun;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integrasi CoA: membuat akun level-3 -> kategori_akun_id otomatis mengikuti parent.
 * ChartOfAccountController::storeAkun: kategori_akun_id = $subKategori->kategori_akun_id.
 */
class ChartOfAccountIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /**
     * IT-F71-01 (+): Store akun level-3 mewarisi kategori_akun_id dari sub-kategori (parent).
     *
     * Field mengikuti StoreAkunRequest: parent_id, kode_akun, nama_akun,
     * saldo_normal, dan status (required|in:aktif,tidak_aktif).
     */
    public function test_it_f71_01_akun_level3_mewarisi_kategori_dari_parent(): void
    {
        $user = $this->buatUser($this->buatRole('Super Admin', ['VIEW_COA', 'CREATE_COA']));

        $kategori = KategoriAkun::create([
            'kode_kategori' => '1',
            'nama_kategori' => 'ASET',
            'status'        => true,
        ]);

        // Sub-kategori = akun parent_id null
        $subKategori = Akun::create([
            'kategori_akun_id' => $kategori->id,
            'parent_id'        => null,
            'kode_akun'        => '1-100',
            'nama_akun'        => 'Aset Lancar',
            'saldo_normal'     => 'DEBIT',
        ]);

        $this->actingAs($user)->post(route('dashboard.coa.akun.store'), [
            'parent_id'    => $subKategori->id,
            'kode_akun'    => '1-101',
            'nama_akun'    => 'Kas',
            'saldo_normal' => 'DEBIT',
            'status'       => 'aktif', // wajib menurut StoreAkunRequest
        ])->assertRedirect();

        $this->assertDatabaseHas('akun', [
            'kode_akun'        => '1-101',
            'parent_id'        => $subKategori->id,
            'kategori_akun_id' => $kategori->id, // diwarisi dari parent
        ]);
    }
}
