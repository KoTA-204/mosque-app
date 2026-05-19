<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class MenuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            // Manajemen User
            'dashboard.users.index'              => 'VIEW_USERS',
            'dashboard.roles.index'              => 'VIEW_ROLES',
            'dashboard.permissions.index'        => 'VIEW_PERMISSIONS',

            // Pencatatan
            'dashboard.pemasukan.index'          => 'VIEW_PEMASUKAN',
            'dashboard.pengeluaran.index'        => 'VIEW_PENGELUARAN',
            'dashboard.kencleng.index'           => 'VIEW_KENCLENG',

            // Kegiatan Khusus
            'dashboard.kegiatan.index'           => 'VIEW_KEGIATAN',
            'dashboard.kegiatan-panitia.index'   => 'VIEW_TRANSAKSI_KEGIATAN',

            // Approval
            'dashboard.approval.index'           => 'VIEW_APPROVAL',

            // Master Data
            'dashboard.coa.index'                => 'VIEW_COA',
            'dashboard.kategori-transaksi.index' => 'VIEW_KATEGORI',
        ];

        foreach ($map as $routeName => $permissionCode) {
            $menu       = Menu::where('route_name', $routeName)->first();
            $permission = Permission::where('permission_code', $permissionCode)->first();

            if ($menu && $permission) {
                DB::table('menu_permission')->insert([
                    'menu_id'       => $menu->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}