<?php

namespace Tests\Unit\CoA;

use App\Models\Akun;
use App\Models\Role;
use App\Models\User;
use App\Models\KategoriAkun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChartOfAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected int  $kategoriAkunId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $role        = Role::create(['role_name' => 'Super Admin']);
        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'status'  => 'active',
        ]);

        // Buat kategori_akun (FK wajib untuk tabel akun)
        $this->kategoriAkunId = DB::table('kategori_akun')->insertGetId([
            'kode_kategori' => '1',
            'nama_kategori' => 'Aset',
            'status'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * UT-F62-01
     * Menyimpan sub-kategori akun baru (Akun tanpa parent_id)
     */
    public function test_UT_F62_01_store_sub_kategori_valid(): void
    {
        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.coa.sub-kategori.store'), [
                             'kode_akun'        => '1-1000',
                             'nama_akun'        => 'Aset Lancar',
                             'kategori_akun_id' => $this->kategoriAkunId,
                             'saldo_normal'     => 'DEBIT',
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', ['kode_akun' => '1-1000']);
    }

    /**
     * UT-F62-02
     * Menyimpan akun baru dengan parent sub-kategori
     * StoreAkunRequest wajib: parent_id, kode_akun, nama_akun, saldo_normal, status
     */
    public function test_UT_F62_02_store_akun_with_parent(): void
    {
        // Buat sub-kategori (parent) terlebih dahulu
        $subKategori = Akun::create([
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Aset Lancar',
            'kategori_akun_id' => $this->kategoriAkunId,
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
                         ->post(route('dashboard.coa.akun.store'), [
                             'parent_id'    => $subKategori->id,
                             'kode_akun'    => '1-1100',
                             'nama_akun'    => 'Kas',
                             'saldo_normal' => 'DEBIT',
                             'status'       => 'aktif', // ← wajib oleh StoreAkunRequest
                         ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', [
            'kode_akun' => '1-1100',
            'parent_id' => $subKategori->id,
        ]);
    }

    /**
     * UT-F62-03
     * Menghapus sub-kategori yang masih punya child akun diblokir
     */
    public function test_UT_F62_03_delete_sub_kategori_with_children_blocked(): void
    {
        $subKategori = Akun::create([
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Aset Lancar',
            'kategori_akun_id' => $this->kategoriAkunId,
            'saldo_normal'     => 'DEBIT',
        ]);

        // Buat child akun
        Akun::create([
            'kode_akun'        => '1-1100',
            'nama_akun'        => 'Kas',
            'parent_id'        => $subKategori->id,
            'kategori_akun_id' => $this->kategoriAkunId,
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('dashboard.coa.sub-kategori.destroy', $subKategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('akun', ['id' => $subKategori->id]);
    }

    /** UT-F62-04 — Store kategori akun valid */
    public function test_UT_F62_04_store_kategori_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.coa.kategori.store'), [
                'kode_kategori' => '9',
                'nama_kategori' => 'Kategori Baru',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kategori_akun', ['kode_kategori' => '9']);
    }

    /** UT-F62-05 — Store kategori tolak kode duplikat */
    public function test_UT_F62_05_store_kategori_kode_duplikat(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.coa.kategori.store'), [
                'kode_kategori' => '1', // sudah dibuat di setUp
                'nama_kategori' => 'Duplikat',
            ]);

        $response->assertSessionHasErrors('kode_kategori');
    }

    /** UT-F62-06 — Update kategori akun */
    public function test_UT_F62_06_update_kategori(): void
    {
        $kategori = KategoriAkun::create(['kode_kategori' => '8', 'nama_kategori' => 'Lama']);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.coa.kategori.update', $kategori), [
                'kode_kategori' => '8',
                'nama_kategori' => 'Baru',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kategori_akun', ['id' => $kategori->id, 'nama_kategori' => 'Baru']);
    }

    /** UT-F62-07 — Hapus kategori yang punya sub kategori → diblokir */
    public function test_UT_F62_07_delete_kategori_dengan_sub_blocked(): void
    {
        $kategori = KategoriAkun::create(['kode_kategori' => '7', 'nama_kategori' => 'Punya Sub']);
        Akun::create([
            'kategori_akun_id' => $kategori->id,
            'kode_akun'        => '7-1000',
            'nama_akun'        => 'Sub A',
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.coa.kategori.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategori_akun', ['id' => $kategori->id]);
    }

    /** UT-F62-08 — Hapus kategori kosong → berhasil */
    public function test_UT_F62_08_delete_kategori_kosong(): void
    {
        $kategori = KategoriAkun::create(['kode_kategori' => '6', 'nama_kategori' => 'Kosong']);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.coa.kategori.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('kategori_akun', ['id' => $kategori->id]);
    }

    /** UT-F62-09 — Store sub kategori tolak kode_akun duplikat */
    public function test_UT_F62_09_store_sub_kategori_kode_duplikat(): void
    {
        Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-1000',
            'nama_akun'        => 'Existing',
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.coa.sub-kategori.store'), [
                'kategori_akun_id' => $this->kategoriAkunId,
                'kode_akun'        => '1-1000', // duplikat
                'nama_akun'        => 'Dup',
                'saldo_normal'     => 'DEBIT',
            ]);

        $response->assertSessionHasErrors('kode_akun');
    }

    /** UT-F62-10 — Update sub kategori */
    public function test_UT_F62_10_update_sub_kategori(): void
    {
        $sub = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-2000',
            'nama_akun'        => 'Sub Lama',
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.coa.sub-kategori.update', $sub), [
                'kategori_akun_id' => $this->kategoriAkunId,
                'kode_akun'        => '1-2000',
                'nama_akun'        => 'Sub Baru',
                'saldo_normal'     => 'KREDIT',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', ['id' => $sub->id, 'nama_akun' => 'Sub Baru', 'saldo_normal' => 'KREDIT']);
    }

    /** UT-F62-11 — Hapus sub kategori tanpa child → berhasil */
    public function test_UT_F62_11_delete_sub_kategori_kosong(): void
    {
        $sub = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-3000',
            'nama_akun'        => 'Sub Tanpa Anak',
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.coa.sub-kategori.destroy', $sub));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('akun', ['id' => $sub->id]);
    }

    /** UT-F62-12 — Store akun mewarisi kategori_akun_id dari parent */
    public function test_UT_F62_12_store_akun_mewarisi_kategori_dari_parent(): void
    {
        $sub = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-4000',
            'nama_akun'        => 'Sub Parent',
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.coa.akun.store'), [
                'parent_id'    => $sub->id,
                'kode_akun'    => '1-4100',
                'nama_akun'    => 'Akun Anak',
                'saldo_normal' => 'DEBIT',
                'status'       => 'aktif',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', [
            'kode_akun'        => '1-4100',
            'parent_id'        => $sub->id,
            'kategori_akun_id' => $this->kategoriAkunId, // diwarisi dari parent
        ]);
    }

    /** UT-F62-13 — Update akun */
    public function test_UT_F62_13_update_akun(): void
    {
        $sub = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-5000',
            'nama_akun'        => 'Sub',
            'saldo_normal'     => 'DEBIT',
        ]);
        $akun = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'parent_id'        => $sub->id,
            'kode_akun'        => '1-5100',
            'nama_akun'        => 'Akun Lama',
            'saldo_normal'     => 'DEBIT',
            'status'           => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.coa.akun.update', $akun), [
                'parent_id'    => $sub->id,
                'kode_akun'    => '1-5100',
                'nama_akun'    => 'Akun Baru',
                'saldo_normal' => 'DEBIT',
                'status'       => 'tidak_aktif',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('akun', ['id' => $akun->id, 'nama_akun' => 'Akun Baru', 'status' => 'tidak_aktif']);
    }

    /** UT-F62-14 — Hapus akun → berhasil (tanpa pemblokiran) */
    public function test_UT_F62_14_delete_akun(): void
    {
        $sub = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-6000',
            'nama_akun'        => 'Sub',
            'saldo_normal'     => 'DEBIT',
        ]);
        $akun = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'parent_id'        => $sub->id,
            'kode_akun'        => '1-6100',
            'nama_akun'        => 'Akun',
            'saldo_normal'     => 'DEBIT',
            'status'           => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.coa.akun.destroy', $akun));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('akun', ['id' => $akun->id]);
    }

    /** UT-F62-15 — Index menampilkan view + hitungan total */
    public function test_UT_F62_15_index_counts(): void
    {
        $this->withoutVite();
        $sub = Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'kode_akun'        => '1-7000',
            'nama_akun'        => 'Sub',
            'saldo_normal'     => 'DEBIT',
        ]);
        Akun::create([
            'kategori_akun_id' => $this->kategoriAkunId,
            'parent_id'        => $sub->id,
            'kode_akun'        => '1-7100',
            'nama_akun'        => 'Akun',
            'saldo_normal'     => 'DEBIT',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard.coa.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.coa.index');
        $response->assertViewHas('totalSubKategori', 1);
        $response->assertViewHas('totalAkun', 1);
    }
}