<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\KategoriAkun;
use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Test untuk TC-07 dan TC-08
 *
 * TC-07 : Kelola Kategori Transaksi & Chart of Account
 *         REQ-F-16, REQ-F-17, REQ-F-18, REQ-F-19, REQ-F-20
 *
 * TC-08 : Cegah Hapus Kategori yang sudah digunakan
 *         REQ-F-21
 */
class KategoriTransaksiCoaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat dan autentikasi user untuk semua test
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // =========================================================================
    // TC-07 — REQ-F-16
    // Menguji penambahan kategori transaksi baru dengan data valid
    // =========================================================================

    /** @test */
    public function tc07_dapat_menambahkan_kategori_transaksi_baru_dengan_data_valid(): void
    {
        $payload = [
            'nama_kategori'   => 'Infak Pembangunan',
            'jenis_transaksi' => 'PEMASUKAN',
            'status'          => 'AKTIF',
            'deskripsi'       => 'Infak untuk pembangunan masjid',
        ];

        $response = $this->post(route('dashboard.kategori-transaksi.store'), $payload);

        $response->assertRedirect(route('dashboard.kategori-transaksi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_transaksi', [
            'nama_kategori'   => 'Infak Pembangunan',
            'jenis_transaksi' => 'PEMASUKAN',
        ]);
    }

    /** @test */
    public function tc07_dapat_menambahkan_kategori_transaksi_jenis_pengeluaran(): void
    {
        $payload = [
            'nama_kategori'   => 'Biaya Operasional',
            'jenis_transaksi' => 'PENGELUARAN',
            'status'          => 'AKTIF',
            'deskripsi'       => 'Biaya operasional rutin masjid',
        ];

        $response = $this->post(route('dashboard.kategori-transaksi.store'), $payload);

        $response->assertRedirect(route('dashboard.kategori-transaksi.index'));
        $this->assertDatabaseHas('kategori_transaksi', [
            'nama_kategori'   => 'Biaya Operasional',
            'jenis_transaksi' => 'PENGELUARAN',
        ]);
    }

    // =========================================================================
    // TC-07 — REQ-F-16 (validasi input)
    // Menguji penolakan tambah kategori jika nama sudah ada / field kosong
    // =========================================================================

    /** @test */
    public function tc07_gagal_tambah_kategori_jika_nama_duplikat(): void
    {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Infak Jumat']);

        $response = $this->post(route('dashboard.kategori-transaksi.store'), [
            'nama_kategori'   => 'Infak Jumat',
            'jenis_transaksi' => 'PEMASUKAN',
            'status'          => 'AKTIF',
        ]);

        $response->assertSessionHasErrors('nama_kategori');
        $this->assertDatabaseCount('kategori_transaksi', 1);
    }

    /** @test */
    public function tc07_gagal_tambah_kategori_jika_field_wajib_kosong(): void
    {
        $response = $this->post(route('dashboard.kategori-transaksi.store'), [
            'nama_kategori'   => '',
            'jenis_transaksi' => '',
            'status'          => '',
        ]);

        $response->assertSessionHasErrors(['nama_kategori', 'jenis_transaksi', 'status']);
    }

    /** @test */
    public function tc07_gagal_tambah_kategori_jika_jenis_transaksi_tidak_valid(): void
    {
        $response = $this->post(route('dashboard.kategori-transaksi.store'), [
            'nama_kategori'   => 'Kategori Baru',
            'jenis_transaksi' => 'TRANSFER', // nilai tidak valid
            'status'          => 'AKTIF',
        ]);

        $response->assertSessionHasErrors('jenis_transaksi');
    }

    // =========================================================================
    // TC-07 — REQ-F-18
    // Menguji tampilan daftar kategori transaksi beserta status
    // =========================================================================

    /** @test */
    public function tc07_halaman_daftar_kategori_transaksi_dapat_diakses(): void
    {
        KategoriTransaksi::factory()->count(3)->create();

        $response = $this->get(route('dashboard.kategori-transaksi.index'));

        $response->assertOk();
        $response->assertViewIs('pages.kategori-transaksi.index');
        $response->assertViewHas('kategori');
    }

    /** @test */
    public function tc07_daftar_kategori_menampilkan_status_aktif_dan_tidak_aktif(): void
    {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Infak Aktif',    'status' => 'aktif']);
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Infak Nonaktif', 'status' => 'tidak_aktif']);

        $response = $this->get(route('dashboard.kategori-transaksi.index'));

        $response->assertOk();
        $response->assertSee('Infak Aktif');
        $response->assertSee('Infak Nonaktif');
    }

    /** @test */
    public function tc07_daftar_kategori_dapat_difilter_berdasarkan_jenis(): void
    {
        KategoriTransaksi::factory()->create([
            'nama_kategori'   => 'Infak Masuk',
            'jenis_transaksi' => 'PEMASUKAN',
        ]);
        KategoriTransaksi::factory()->create([
            'nama_kategori'   => 'Biaya Keluar',
            'jenis_transaksi' => 'PENGELUARAN',
        ]);

        $response = $this->get(route('dashboard.kategori-transaksi.index', ['jenis' => 'PEMASUKAN']));

        $response->assertOk();
        $response->assertSee('Infak Masuk');
        $response->assertDontSee('Biaya Keluar');
    }

    /** @test */
    public function tc07_daftar_kategori_dapat_dicari_berdasarkan_nama(): void
    {
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Infak Jumat']);
        KategoriTransaksi::factory()->create(['nama_kategori' => 'Biaya Listrik']);

        $response = $this->get(route('dashboard.kategori-transaksi.index', ['search' => 'Infak']));

        $response->assertOk();
        $response->assertSee('Infak Jumat');
        $response->assertDontSee('Biaya Listrik');
    }

    // =========================================================================
    // TC-07 — REQ-F-20
    // Menguji penonaktifan kategori transaksi
    // =========================================================================

    /** @test */
    public function tc07_dapat_menonaktifkan_kategori_transaksi(): void
    {
        $kategori = KategoriTransaksi::factory()->create(['status' => 'aktif']);

        $response = $this->put(route('dashboard.kategori-transaksi.update', $kategori), [
            'nama_kategori'   => $kategori->nama_kategori,
            'jenis_transaksi' => $kategori->jenis_transaksi,
            'status'          => 'tidak_aktif',
            'deskripsi'       => $kategori->deskripsi,
        ]);

        $response->assertRedirect(route('dashboard.kategori-transaksi.index'));
        $this->assertDatabaseHas('kategori_transaksi', [
            'id'     => $kategori->id,
            'status' => 'tidak_aktif',
        ]);
    }

    /** @test */
    public function tc07_dapat_mengaktifkan_kembali_kategori_yang_nonaktif(): void
    {
        $kategori = KategoriTransaksi::factory()->create(['status' => 'tidak_aktif']);

        $response = $this->put(route('dashboard.kategori-transaksi.update', $kategori), [
            'nama_kategori'   => $kategori->nama_kategori,
            'jenis_transaksi' => $kategori->jenis_transaksi,
            'status'          => 'aktif',
            'deskripsi'       => $kategori->deskripsi,
        ]);

        $response->assertRedirect(route('dashboard.kategori-transaksi.index'));
        $this->assertDatabaseHas('kategori_transaksi', [
            'id'     => $kategori->id,
            'status' => 'aktif',
        ]);
    }

    // =========================================================================
    // TC-07 — REQ-F-19 (Chart of Account)
    // Menguji penambahan akun CoA baru
    // =========================================================================

    /** @test */
    public function tc07_dapat_menambahkan_kategori_akun_coa_baru(): void
    {
        $payload = [
            'kode_kategori' => '4',
            'nama_kategori' => 'Pendapatan',
        ];

        $response = $this->post(route('dashboard.coa.kategori.store'), $payload);

        $response->assertRedirect(route('dashboard.coa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_akun', [
            'kode_kategori' => '4',
            'nama_kategori' => 'Pendapatan',
        ]);
    }

    /** @test */
    public function tc07_dapat_menambahkan_sub_kategori_akun_coa(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create([
            'kode_kategori' => '4',
            'nama_kategori' => 'Pendapatan',
        ]);

        $payload = [
            'kategori_akun_id' => $kategoriAkun->id,
            'kode_akun'        => '4001',
            'nama_akun'        => 'Pendapatan Infak',
            'deskripsi'        => 'Sub kategori pendapatan dari infak',
        ];

        $response = $this->post(route('dashboard.coa.sub-kategori.store'), $payload);

        $response->assertRedirect(route('dashboard.coa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('akun', [
            'kode_akun'        => '4001',
            'nama_akun'        => 'Pendapatan Infak',
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
        ]);
    }

    /** @test */
    public function tc07_dapat_menambahkan_akun_coa_dengan_nomor_unik(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create();

        // Buat sub-kategori (parent)
        $subKategori = Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
            'kode_akun'        => '4001',
            'nama_akun'        => 'Pendapatan Infak',
            'saldo_normal'     => null,
        ]);

        $payload = [
            'parent_id'    => $subKategori->id,
            'kode_akun'    => '4001.1',
            'nama_akun'    => 'Pendapatan Zakat',
            'saldo_normal' => 'kredit',
        ];

        $response = $this->post(route('dashboard.coa.akun.store'), $payload);

        $response->assertRedirect(route('dashboard.coa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('akun', [
            'kode_akun'        => '4001.1',
            'nama_akun'        => 'Pendapatan Zakat',
            'parent_id'        => $subKategori->id,
            'kategori_akun_id' => $kategoriAkun->id,
        ]);
    }

    /** @test */
    public function tc07_gagal_tambah_akun_coa_jika_kode_duplikat(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create();
        $subKategori  = Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
            'kode_akun'        => '4001',
            'saldo_normal'     => null,
        ]);
        Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => $subKategori->id,
            'kode_akun'        => '4001.1',
            'saldo_normal'     => 'kredit',
        ]);

        // Coba tambah akun dengan kode yang sama
        $response = $this->post(route('dashboard.coa.akun.store'), [
            'parent_id'    => $subKategori->id,
            'kode_akun'    => '4001.1', // duplikat
            'nama_akun'    => 'Akun Lain',
            'saldo_normal' => 'debit',
        ]);

        $response->assertSessionHasErrors('kode_akun');
    }

    /** @test */
    public function tc07_halaman_daftar_coa_dapat_diakses(): void
    {
        $response = $this->get(route('dashboard.coa.index'));

        $response->assertOk();
        $response->assertViewIs('pages.coa.index');
    }

    /** @test */
    public function tc07_halaman_daftar_coa_menampilkan_kategori_dan_akun(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create(['nama_kategori' => 'Pendapatan']);
        Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
            'kode_akun'        => '4001',
            'nama_akun'        => 'Pendapatan Infak',
            'saldo_normal'     => null,
        ]);

        $response = $this->get(route('dashboard.coa.index'));

        $response->assertOk();
        $response->assertSee('Pendapatan');
        $response->assertSee('Pendapatan Infak');
    }

    // =========================================================================
    // TC-07 — REQ-F-17 (edit & update CoA)
    // =========================================================================

    /** @test */
    public function tc07_dapat_mengubah_data_kategori_akun_coa(): void
    {
        $kategori = KategoriAkun::factory()->create([
            'kode_kategori' => '4',
            'nama_kategori' => 'Pendapatan Lama',
        ]);

        $response = $this->put(route('dashboard.coa.kategori.update', $kategori), [
            'kode_kategori' => '4',
            'nama_kategori' => 'Pendapatan Baru',
        ]);

        $response->assertRedirect(route('dashboard.coa.index'));
        $this->assertDatabaseHas('kategori_akun', [
            'id'            => $kategori->id,
            'nama_kategori' => 'Pendapatan Baru',
        ]);
    }

    /** @test */
    public function tc07_dapat_mengubah_data_akun_coa(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create();
        $subKategori  = Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
            'kode_akun'        => '4001',
            'saldo_normal'     => null,
        ]);
        $akun = Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => $subKategori->id,
            'kode_akun'        => '4001.1',
            'nama_akun'        => 'Nama Lama',
            'saldo_normal'     => 'kredit',
        ]);

        $response = $this->put(route('dashboard.coa.akun.update', $akun), [
            'parent_id'    => $subKategori->id,
            'kode_akun'    => '4001.1',
            'nama_akun'    => 'Nama Baru',
            'saldo_normal' => 'kredit',
        ]);

        $response->assertRedirect(route('dashboard.coa.index'));
        $this->assertDatabaseHas('akun', [
            'id'       => $akun->id,
            'nama_akun' => 'Nama Baru',
        ]);
    }

    // =========================================================================
    // TC-08 — REQ-F-21
    // Menguji pencegahan penghapusan kategori yang sudah dipakai pada transaksi
    // =========================================================================

    /** @test */
    public function tc08_gagal_hapus_kategori_yang_sudah_digunakan_pada_transaksi(): void
    {
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Infak Jumat']);

        // Buat transaksi yang menggunakan kategori ini
        Transaksi::factory()->create([
            'kategori_transaksi_id' => $kategori->id,
        ]);

        $response = $this->delete(route('dashboard.kategori-transaksi.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Pastikan kategori tidak terhapus dari database
        $this->assertDatabaseHas('kategori_transaksi', ['id' => $kategori->id]);
    }

    /** @test */
    public function tc08_pesan_error_muncul_saat_hapus_kategori_yang_sudah_dipakai(): void
    {
        $kategori = KategoriTransaksi::factory()->create();
        Transaksi::factory()->create(['kategori_transaksi_id' => $kategori->id]);

        $response = $this->delete(route('dashboard.kategori-transaksi.destroy', $kategori));

        $response->assertSessionHas('error', 'Kategori tidak dapat dihapus karena sudah digunakan oleh transaksi.');
    }

    /** @test */
    public function tc08_dapat_menghapus_kategori_yang_belum_pernah_digunakan(): void
    {
        $kategori = KategoriTransaksi::factory()->create(['nama_kategori' => 'Kategori Baru Belum Dipakai']);

        $response = $this->delete(route('dashboard.kategori-transaksi.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('kategori_transaksi', ['id' => $kategori->id]);
    }

    /** @test */
    public function tc08_dapat_menghapus_kategori_akun_coa_yang_tidak_memiliki_akun(): void
    {
        $kategori = KategoriAkun::factory()->create();

        $response = $this->delete(route('dashboard.coa.kategori.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('kategori_akun', ['id' => $kategori->id]);
    }

    /** @test */
    public function tc08_gagal_hapus_kategori_akun_coa_yang_masih_memiliki_akun(): void
    {
        $kategori = KategoriAkun::factory()->create();
        Akun::factory()->create([
            'kategori_akun_id' => $kategori->id,
            'parent_id'        => null,
            'saldo_normal'     => null,
        ]);

        $response = $this->delete(route('dashboard.coa.kategori.destroy', $kategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('kategori_akun', ['id' => $kategori->id]);
    }

    /** @test */
    public function tc08_dapat_menghapus_sub_kategori_akun_yang_tidak_memiliki_child(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create();
        $subKategori  = Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
            'saldo_normal'     => null,
        ]);

        $response = $this->delete(route('dashboard.coa.sub-kategori.destroy', $subKategori));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('akun', ['id' => $subKategori->id]);
    }

    /** @test */
    public function tc08_gagal_hapus_sub_kategori_akun_yang_masih_memiliki_akun_child(): void
    {
        $kategoriAkun = KategoriAkun::factory()->create();
        $subKategori  = Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => null,
            'saldo_normal'     => null,
        ]);
        Akun::factory()->create([
            'kategori_akun_id' => $kategoriAkun->id,
            'parent_id'        => $subKategori->id,
            'saldo_normal'     => 'kredit',
        ]);

        $response = $this->delete(route('dashboard.coa.sub-kategori.destroy', $subKategori));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('akun', ['id' => $subKategori->id]);
    }
}
