<?php

namespace Tests\Unit;

use App\Models\Akun;
use App\Models\DetailJurnal;
use App\Models\Dompet;
use App\Models\Jurnal;
use App\Models\KategoriAkun;
use App\Models\Periode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class Inc2TestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Lewati guard permission & aktif (pola sama persis dgn UserControllerTest yg lulus)
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role = Role::create(['role_name' => 'Admin']);

        $this->user = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        $this->actingAs($this->user);
    }

    /** Akun butuh kategori_akun_id (FK wajib) */
    public function buatAkun(string $kode, string $nama, string $saldoNormal = 'DEBIT'): \App\Models\Akun
    {
        $prefix = substr($kode, 0, 1);
        $kat = \App\Models\KategoriAkun::firstOrCreate(
            ['kode_kategori' => $prefix],
            ['nama_kategori' => 'Kategori ' . $prefix, 'status' => true]  // KategoriAkun.status = boolean ✓
        );

        return \App\Models\Akun::create([
            'kategori_akun_id' => $kat->id,
            'kode_akun'        => $kode,
            'nama_akun'        => $nama,
            'saldo_normal'     => $saldoNormal,
            'status'           => 'aktif',   // ← ganti dari true ke 'aktif'
        ]);
    }

    /** Pengganti Periode::factory() (factory tidak ada di proyek) */
    protected function periodeAktif(string $awal = '2026-06-01', string $akhir = '2026-06-30'): Periode
    {
        return Periode::create([
            'nama_periode'  => 'Juni 2026',
            'tipe'          => 'bulanan',
            'tanggal_awal'  => $awal,
            'tanggal_akhir' => $akhir,
            'status'        => true, // scopeAktif() butuh true
        ]);
    }

    /** jenis_dompet WAJIB (enum CASH/BANK tanpa default) */
    protected function buatDompet(string $nama = 'Kas Masjid', string $jenis = 'CASH'): Dompet
    {
        return Dompet::create([
            'nama_dompet'  => $nama,
            'jenis_dompet' => $jenis,
            'saldo_awal'   => 0,
        ]);
    }

    protected function buatJurnal(array $attrs = []): Jurnal
    {
        return Jurnal::create(array_merge([
            'periode_id'   => $this->periodeAktif()->id,
            'jenis_jurnal' => 'UMUM',
            'tanggal'      => '2026-06-15',
            'keterangan'   => 'Jurnal uji',
            'status'       => 'DRAFT',
        ], $attrs));
    }

    protected function tambahDetail(Jurnal $jurnal, Akun $akun, string $tipe, float $nominal): DetailJurnal
    {
        return DetailJurnal::create([
            'jurnal_id' => $jurnal->id,
            'akun_id'   => $akun->id,
            'tipe'      => $tipe,
            'nominal'   => $nominal,
        ]);
    }
}