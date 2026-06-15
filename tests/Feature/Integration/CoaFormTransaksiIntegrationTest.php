<?php

namespace Tests\Feature\Integration;

use App\Models\Akun;
use App\Models\KategoriAkun;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integrasi: akun level-3 tampil pada halaman CoA (sebagai opsi form transaksi/jurnal).
 */
class CoaFormTransaksiIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /** IT-F73-01 (+): Akun level-3 tampil di halaman CoA. */
    public function test_it_f73_01_akun_level3_tampil_di_form(): void
    {
        $user = $this->buatUser($this->buatRole('Bendahara', ['VIEW_COA']));

        $kategori = KategoriAkun::create([
            'kode_kategori' => '1',
            'nama_kategori' => 'ASET',
            'status'        => true,
        ]);

        $sub = Akun::create([
            'kategori_akun_id' => $kategori->id,
            'parent_id'        => null,
            'kode_akun'        => '1-100',
            'nama_akun'        => 'Aset Lancar',
            'saldo_normal'     => 'DEBIT',
        ]);

        Akun::create([
            'kategori_akun_id' => $kategori->id,
            'parent_id'        => $sub->id,
            'kode_akun'        => '1-101',
            'nama_akun'        => 'Kas Masjid',
            'saldo_normal'     => 'DEBIT',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.coa.index'))
            ->assertOk()
            ->assertSee('Kas Masjid');
    }
}
