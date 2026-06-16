<?php

namespace Tests\Unit\NeracaSaldo;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\KategoriAkun;
use App\Models\Periode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeracaSaldoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Periode $periode;
    protected Akun $kas;        // child (parent_id terisi) → tampil di neraca
    protected Akun $pendapatan; // child

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role = Role::create(['role_name' => 'Bendahara 1']);
        $this->user = User::factory()->create(['role_id' => $role->id, 'status' => 'active']);

        $this->periode = Periode::create([
            'nama_periode'  => 'Juni 2026', 'tipe' => 'bulanan',
            'tanggal_awal'  => '2026-06-01', 'tanggal_akhir' => '2026-06-30', 'status' => true,
        ]);

        // Kategori + akun induk (parent) + akun anak (yang ditampilkan neraca)
        $katAset = KategoriAkun::create(['kode_kategori' => '1', 'nama_kategori' => 'Aset']);
        $katPend = KategoriAkun::create(['kode_kategori' => '4', 'nama_kategori' => 'Pendapatan']);

        $indukAset = Akun::create(['kode_akun' => '1-0000', 'nama_akun' => 'ASET', 'saldo_normal' => 'DEBIT', 'kategori_akun_id' => $katAset->id]);
        $indukPend = Akun::create(['kode_akun' => '4-0000', 'nama_akun' => 'PENDAPATAN', 'saldo_normal' => 'KREDIT', 'kategori_akun_id' => $katPend->id]);

        $this->kas = Akun::create([
            'kode_akun' => '1-1000', 'nama_akun' => 'Kas', 'saldo_normal' => 'DEBIT',
            'kategori_akun_id' => $katAset->id, 'parent_id' => $indukAset->id,
        ]);
        $this->pendapatan = Akun::create([
            'kode_akun' => '4-1000', 'nama_akun' => 'Pendapatan Infak', 'saldo_normal' => 'KREDIT',
            'kategori_akun_id' => $katPend->id, 'parent_id' => $indukPend->id,
        ]);
    }

    private function jurnalPosted(string $tanggal = '2026-06-10'): Jurnal
    {
        return Jurnal::create([
            'periode_id'   => $this->periode->id,
            'jenis_jurnal' => 'UMUM',
            'tanggal'      => $tanggal,
            'keterangan'   => 'Mutasi',
            'status'       => 'POSTED',
        ]);
    }

    private function detail(Jurnal $j, int $akunId, string $tipe, $nominal): void
    {
        DetailJurnal::create(['jurnal_id' => $j->id, 'akun_id' => $akunId, 'tipe' => $tipe, 'nominal' => $nominal]);
    }

    /**
     * UT-F90-01
     * NeracaSaldoController::index() - Grand Total & Selisih = 0 (seimbang)
     */
    public function test_UT_F90_01_grand_total_selisih_nol(): void
    {
        $j = $this->jurnalPosted();
        $this->detail($j, $this->kas->id,        'DEBIT',  500_000);
        $this->detail($j, $this->pendapatan->id, 'KREDIT', 500_000);

        $response = $this->actingAs($this->user)->get(route('dashboard.neraca-saldo.index'));
        $response->assertOk();

        $this->assertEquals(500_000, (float) $response->viewData('grandTotalDebit'));
        $this->assertEquals(500_000, (float) $response->viewData('grandTotalKredit'));
        $this->assertEquals(0, (float) $response->viewData('selisih'));
    }

    /**
     * UT-F79-02
     * NeracaSaldoController::index() - Agregasi saldo per akun (POSTED)
     * Expected: total_debit akun Kas = 300.000 + 200.000 = 500.000
     */
    public function test_UT_F79_02_agregasi_per_akun(): void
    {
        $j1 = $this->jurnalPosted('2026-06-05');
        $this->detail($j1, $this->kas->id,        'DEBIT',  300_000);
        $this->detail($j1, $this->pendapatan->id, 'KREDIT', 300_000);

        $j2 = $this->jurnalPosted('2026-06-12');
        $this->detail($j2, $this->kas->id,        'DEBIT',  200_000);
        $this->detail($j2, $this->pendapatan->id, 'KREDIT', 200_000);

        $response = $this->actingAs($this->user)->get(route('dashboard.neraca-saldo.index'));
        $response->assertOk();

        $akuns  = $response->viewData('akuns');                 // LengthAwarePaginator
        $akunKas = $akuns->getCollection()->firstWhere('id', $this->kas->id);

        $this->assertNotNull($akunKas);
        $this->assertEquals(500_000, (float) $akunKas->total_debit);
        $this->assertEquals(0, (float) $akunKas->total_kredit);
    }
}