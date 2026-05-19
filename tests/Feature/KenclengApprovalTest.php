<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Dompet;
use App\Models\Kencleng;
use App\Models\Permission;
use App\Models\Transaksi;
use App\Models\KategoriTransaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KenclengApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $pengurus;
    protected $bendahara;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * =========================================
         * Permission
         * =========================================
         */
        $createKencleng = Permission::create([
            'permission_code' => 'CREATE_KENCLENG',
            'permission_name' => 'Create Kencleng',
            'module' => 'KENCLENG',
            'action' => 'CREATE',
            'is_active' => true,
        ]);

        $viewApproval = Permission::create([
            'permission_code' => 'VIEW_APPROVAL',
            'permission_name' => 'View Approval',
            'module' => 'APPROVAL',
            'action' => 'VIEW',
            'is_active' => true,
        ]);

        /**
         * =========================================
         * Role Pengurus
         * =========================================
         */
        $pengurusRole = Role::create([
            'role_name' => 'Pengurus',
            'description' => 'Pengurus Masjid',
            'is_active' => true,
        ]);

        $pengurusRole->permissions()->attach($createKencleng->id);

        /**
         * =========================================
         * Role Bendahara
         * =========================================
         */
        $bendaharaRole = Role::create([
            'role_name' => 'Bendahara',
            'description' => 'Bendahara Masjid',
            'is_active' => true,
        ]);

        $bendaharaRole->permissions()->attach($viewApproval->id);

        /**
         * =========================================
         * User Pengurus
         * =========================================
         */
        $this->pengurus = User::factory()->create();

        $this->pengurus->roles()->attach($pengurusRole->id);

        /**
         * =========================================
         * User Bendahara
         * =========================================
         */
        $this->bendahara = User::factory()->create();

        $this->bendahara->roles()->attach($bendaharaRole->id);
    }

    /**
     * =========================================
     * TC22
     * Pengurus dapat mengajukan kencleng
     * =========================================
     */
    public function test_tc22_pengurus_dapat_mengajukan_kencleng()
    {
        $response = $this
            ->actingAs($this->pengurus)
            ->post(route('dashboard.kencleng.store'), [
                'nama' => 'Kencleng Jumat',
                'jumlah' => 100000,
            ]);

        $response->assertStatus(302);
    }

    /**
     * =========================================
     * TC23
     * Bendahara dapat menyetujui kencleng
     * =========================================
     */
    public function test_tc23_bendahara_dapat_menyetujui_kencleng()
    {
        /**
         * =========================================
         * Dompet
         * =========================================
         */
        $dompet = Dompet::create([
            'nama_dompet' => 'Kas Masjid',
            'jenis_dompet' => 'CASH',
            'saldo_awal' => 0,
        ]);

        /**
         * =========================================
         * Kategori Transaksi
         * =========================================
         */
        $kategori = KategoriTransaksi::create([
            'nama_kategori' => 'Kencleng Jumat',
            'jenis_transaksi' => 'PEMASUKAN',
        ]);

        /**
         * =========================================
         * Transaksi
         * =========================================
         */
        $transaksi = Transaksi::create([
            'dompet_id' => $dompet->id,
            'user_id' => $this->pengurus->id,
            'kategori_transaksi_id' => $kategori->id,
            'tanggal_transaksi' => now(),
            'jumlah' => 100000,
            'deskripsi' => 'Kencleng Jumat',
            'status_approval' => 'PENDING',
            'status_jurnal' => 'UNMAPPED',
        ]);

        /**
         * =========================================
         * Kencleng
         * =========================================
         */
        $kencleng = Kencleng::create([
            'transaksi_id' => $transaksi->id,
            'berita_acara' => 'storage/berita-acara/test.pdf',
        ]);

        /**
         * =========================================
         * Approval
         * =========================================
         */
        $response = $this
            ->actingAs($this->bendahara)
            ->post(
                route('dashboard.approval.approve', $transaksi->id)
            );

        $response->assertStatus(302);
    }
}