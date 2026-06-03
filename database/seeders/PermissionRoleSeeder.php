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

        // Super Admin → semua permission
        $superAdmin->permissions()->sync(
            Permission::pluck('id')
        );

        // Bendahara 1 → akses penuh keuangan + approval + jurnal
        $bendahara1->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_DASHBOARD',
                'VIEW_COA',
                'MANAGE_COA',
                'VIEW_KATEGORI',
                'MANAGE_KATEGORI',
                'VIEW_KEGIATAN',
                'MANAGE_KEGIATAN',
                'CREATE_KEGIATAN',
                'VIEW_KENCLENG',
                'VIEW_APPROVAL',
                'MANAGE_APPROVAL',
                'VIEW_JURNAL',
                'MANAGE_JURNAL',
                'VIEW_ASET',
                'MANAGE_ASET',
                'VIEW_LAPORAN',
                'MANAGE_PERIODE',
            ])->pluck('id')
        );

        // Bendahara 2 → akses terbatas keuangan
        $bendahara2->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_DASHBOARD',
                'VIEW_KEGIATAN',
                'CREATE_KEGIATAN',
                'VIEW_KENCLENG',
                'VIEW_LAPORAN',
            ])->pluck('id')
        );

        // PHM → hanya kencleng
        $phm->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_DASHBOARD',
                'VIEW_KENCLENG',
                'CREATE_KENCLENG',
            ])->pluck('id')
        );

        // Panitia Khusus → hanya kegiatan miliknya
        $panitiaKhusus->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_DASHBOARD',
                'VIEW_KEGIATAN',
                'CREATE_KEGIATAN',
            ])->pluck('id')
        );
    }
}