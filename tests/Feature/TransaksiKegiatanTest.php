<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Kegiatan;
use App\Models\Dompet;
use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransaksiKegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Role
        $role = Role::create([
            'role_name' => 'panitia-khusus',
        ]);

        // Permission
        $permission = Permission::create([
            'permission_name' => 'Create Kegiatan',
            'permission_code' => 'CREATE_KEGIATAN',
            'module'          => 'KEGIATAN',
            'action'          => 'CREATE',
        ]);

        // Attach permission ke role
        $role->permissions()->attach($permission->id);

        // User
        $this->user = User::factory()->create();

        // Attach role ke user
        $this->user->roles()->attach($role->id);
    }

    /**
     * =========================================
     * TC24
     * Panitia dapat mencatat transaksi kegiatan
     * =========================================
     */
    public function test_tc24_panitia_dapat_mencatat_transaksi_kegiatan()
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kajian Jumat',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now(),
            'tanggal_selesai' => now()->addDay(),
            'anggaran'        => 1000000,
            'status'          => 'BERJALAN',
            'panitia_id'      => $this->user->id,
        ]);

        $dompet = Dompet::create([
            'nama_dompet' => 'Kas Masjid',
            'jenis_dompet' => 'CASH',
            'saldo_awal' => 500000,
        ]);

        $kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Donasi',
            'jenis_transaksi' => 'PEMASUKAN',
        ]);

        $response = $this->actingAs($this->user)
            ->post(
                route('dashboard.kegiatan-panitia.transaksi.store', $kegiatan->id),
                [
                    'jenis_transaksi'       => 'PEMASUKAN',
                    'tanggal_transaksi'     => now()->format('Y-m-d'),
                    'jumlah'                => 150000,
                    'dompet_id'             => $dompet->id,
                    'kategori_transaksi_id' => $kategori->id,
                    'deskripsi'             => 'Donasi jamaah',
                ]
            );

        $response->assertStatus(302);

        $this->assertDatabaseHas('transaksi', [
            'kegiatan_id' => $kegiatan->id,
            'jumlah'      => 150000,
            'status_approval' => 'PENDING',
        ]);
    }

    /**
     * =========================================
     * TC25
     * Panitia dapat memperbaiki transaksi revisi
     * =========================================
     */
    public function test_tc25_panitia_dapat_memperbaiki_transaksi_revisi()
    {
        $kegiatan = Kegiatan::create([
            'nama_kegiatan'   => 'Kajian Jumat',
            'jenis_kegiatan'  => 'KAJIAN',
            'tanggal_mulai'   => now(),
            'tanggal_selesai' => now()->addDay(),
            'anggaran'        => 1000000,
            'status'          => 'BERJALAN',
            'panitia_id'      => $this->user->id,
        ]);

        $dompet = Dompet::create([
            'nama_dompet' => 'Kas Masjid',
            'jenis_dompet' => 'CASH',
            'saldo_awal' => 500000,
        ]);

        $kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Donasi',
            'jenis_transaksi' => 'PEMASUKAN',
        ]);

        $transaksi = Transaksi::create([
            'dompet_id'             => $dompet->id,
            'kegiatan_id'           => $kegiatan->id,
            'user_id'               => $this->user->id,
            'kategori_transaksi_id' => $kategori->id,
            'tanggal_transaksi'     => now(),
            'jumlah'                => 100000,
            'deskripsi'             => 'Donasi awal',
            'status_approval'       => 'REVISION',
            'status_jurnal'         => 'UNMAPPED',
            'catatan_revisi'        => 'Jumlah salah',
        ]);

        $response = $this->actingAs($this->user)
            ->put(
                route('dashboard.kegiatan-panitia.transaksi.update', [
                    'kegiatan'  => $kegiatan->id,
                    'transaksi' => $transaksi->id,
                ]),
                [
                    'tanggal_transaksi'     => now()->format('Y-m-d'),
                    'jumlah'                => 200000,
                    'dompet_id'             => $dompet->id,
                    'kategori_transaksi_id' => $kategori->id,
                    'deskripsi'             => 'Donasi revisi',
                ]
            );

        $response->assertStatus(302);

        $this->assertDatabaseHas('transaksi', [
            'id'                => $transaksi->id,
            'jumlah'            => 200000,
            'status_approval'   => 'PENDING',
            'catatan_revisi'    => null,
        ]);
    }
}