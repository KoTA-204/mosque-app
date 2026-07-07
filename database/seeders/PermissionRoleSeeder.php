<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin    = Role::where('role_name', 'Super Admin')->first();
        $bendahara1    = Role::where('role_name', 'Bendahara 1')->first();
        $bendahara2    = Role::where('role_name', 'Bendahara 2')->first();
        $phm           = Role::where('role_name', 'PHM')->first();
        $panitiaKhusus = Role::where('role_name', 'Panitia Khusus')->first();

        // Super Admin -> semua permission
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Bendahara 1 -> akses penuh keuangan, master data, approval, jurnal, periode
        $this->syncByCodes($bendahara1, [
            'VIEW_DASHBOARD',
            // Master data
            'VIEW_COA', 'CREATE_COA', 'EDIT_COA', 'DELETE_COA',
            'VIEW_KATEGORI', 'CREATE_KATEGORI', 'EDIT_KATEGORI', 'DELETE_KATEGORI',
            'VIEW_ASET', 'CREATE_ASET', 'EDIT_ASET', 'DELETE_ASET',
            // Kegiatan & transaksi
            'VIEW_KEGIATAN', 'CREATE_KEGIATAN', 'EDIT_KEGIATAN', 'DELETE_KEGIATAN',
            'VIEW_TRANSAKSI', 'CREATE_TRANSAKSI', 'EDIT_TRANSAKSI', 'DELETE_TRANSAKSI', 'IMPORT_TRANSAKSI',
            'VIEW_TRANSAKSI_KEGIATAN',
            'VIEW_KENCLENG',
            // Approval
            'VIEW_APPROVAL', 'CREATE_APPROVAL', 'EDIT_APPROVAL', 'DELETE_APPROVAL',
            // Jurnal & akuntansi
            'VIEW_JURNAL_PEMBUKA', 'CREATE_JURNAL_PEMBUKA', 'EDIT_JURNAL_PEMBUKA', 'DELETE_JURNAL_PEMBUKA',
            'VIEW_JURNAL', 'CREATE_JURNAL', 'EDIT_JURNAL', 'DELETE_JURNAL',
            'VIEW_JURNAL_PENYESUAIAN', 'CREATE_JURNAL_PENYESUAIAN', 'EDIT_JURNAL_PENYESUAIAN', 'DELETE_JURNAL_PENYESUAIAN',
            'VIEW_JURNAL_KOREKSI', 'CREATE_JURNAL_KOREKSI', 'EDIT_JURNAL_KOREKSI', 'DELETE_JURNAL_KOREKSI',
            'VIEW_JURNAL_PENUTUP', 'CREATE_JURNAL_PENUTUP', 'EDIT_JURNAL_PENUTUP', 'DELETE_JURNAL_PENUTUP',
            'VIEW_BUKU_BESAR',
            'VIEW_NERACA_SALDO',
            'VIEW_PERIODE', 'CREATE_PERIODE', 'EDIT_PERIODE', 'DELETE_PERIODE',
            'VIEW_LAPORAN_KEUANGAN',
        ]);

        // Bendahara 2 -> pembantu: catat transaksi, lihat kencleng & laporan
        $this->syncByCodes($bendahara2, [
            'VIEW_DASHBOARD',
            'VIEW_TRANSAKSI', 'CREATE_TRANSAKSI',
            'VIEW_KENCLENG',
            'VIEW_BUKU_BESAR',
            'VIEW_NERACA_SALDO',
            'VIEW_LAPORAN_KEUANGAN',
        ]);

        // PHM -> setoran kencleng harian
        $this->syncByCodes($phm, [
            'VIEW_DASHBOARD',
            'VIEW_KENCLENG', 'CREATE_KENCLENG',
        ]);

        // Panitia Khusus -> kegiatan yang ditugaskan + catat transaksi kegiatan
        $this->syncByCodes($panitiaKhusus, [
            'VIEW_DASHBOARD',
            'VIEW_KEGIATAN',
            'VIEW_TRANSAKSI_KEGIATAN', 'CREATE_TRANSAKSI_KEGIATAN',
        ]);
    }

    private function syncByCodes(Role $role, array $codes): void
    {
        $ids = Permission::whereIn('permission_code', $codes)->pluck('id');

        // Peringatkan jika ada kode yang tidak ditemukan (mencegah typo diam-diam).
        $missing = array_diff($codes, Permission::whereIn('permission_code', $codes)->pluck('permission_code')->all());
        if (!empty($missing)) {
            $this->command->warn("[{$role->role_name}] permission tidak ditemukan: " . implode(', ', $missing));
        }

        $role->permissions()->sync($ids);
    }
}
