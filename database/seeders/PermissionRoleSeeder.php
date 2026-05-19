<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'role_name');
        $perms = Permission::pluck('id', 'permission_code');

        $map = [
            'Super Admin' => $perms->keys()->all(), // semua permission

            'Bendahara 1' => [
                // Pencatatan
                'VIEW_PEMASUKAN', 'CREATE_PEMASUKAN', 'EDIT_PEMASUKAN', 'DELETE_PEMASUKAN',
                'VIEW_PENGELUARAN', 'CREATE_PENGELUARAN', 'EDIT_PENGELUARAN', 'DELETE_PENGELUARAN',
                'VIEW_KENCLENG',
                // Transaksi Kegiatan (bisa lihat untuk keperluan approval)
                'VIEW_TRANSAKSI_KEGIATAN',
                // Approval
                'VIEW_APPROVAL',
                // Master Data
                'VIEW_COA', 'CREATE_COA', 'EDIT_COA', 'DELETE_COA',
                'VIEW_KATEGORI', 'CREATE_KATEGORI', 'EDIT_KATEGORI', 'DELETE_KATEGORI',
            ],

            'Bendahara 2' => [
                // Pencatatan
                'VIEW_PEMASUKAN', 'CREATE_PEMASUKAN', 'EDIT_PEMASUKAN', 'DELETE_PEMASUKAN',
                'VIEW_PENGELUARAN', 'CREATE_PENGELUARAN', 'EDIT_PENGELUARAN', 'DELETE_PENGELUARAN',
                // Master Data
                'VIEW_COA', 'CREATE_COA', 'EDIT_COA', 'DELETE_COA',
                'VIEW_KATEGORI', 'CREATE_KATEGORI', 'EDIT_KATEGORI', 'DELETE_KATEGORI',
            ],

            'PHM' => [
                'VIEW_KENCLENG', 'CREATE_KENCLENG', 'EDIT_KENCLENG', 'DELETE_KENCLENG',
            ],

            'Panitia' => [
                'VIEW_TRANSAKSI_KEGIATAN',
                'CREATE_TRANSAKSI_KEGIATAN', 'EDIT_TRANSAKSI_KEGIATAN', 'DELETE_TRANSAKSI_KEGIATAN',
            ],
        ];

        foreach ($map as $roleName => $permissionCodes) {
            $roleId = $roles[$roleName] ?? null;

            if (!$roleId) {
                $this->command->warn("Role '{$roleName}' tidak ditemukan, dilewati.");
                continue;
            }

            foreach ($permissionCodes as $code) {
                $permId = $perms[$code] ?? null;

                if (!$permId) {
                    $this->command->warn("Permission '{$code}' tidak ditemukan, dilewati.");
                    continue;
                }

                \Illuminate\Support\Facades\DB::table('permission_role')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }
}