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
        // route_name => permission_code
        $map = [
            // ── Manajemen Pengguna ──────────────────────────────────
            'dashboard.users.index'              => 'VIEW_USERS',
            'dashboard.roles.index'              => 'VIEW_ROLES',
            'dashboard.permissions.index'        => 'VIEW_PERMISSIONS',

            // ── Transaksi ───────────────────────────────────────────
            'dashboard.transaksi.index'          => 'VIEW_TRANSAKSI',
            'dashboard.kencleng.index'           => 'VIEW_KENCLENG',
            'dashboard.kegiatan-panitia.index'   => 'VIEW_TRANSAKSI_KEGIATAN',

            // ── Kegiatan Khusus ─────────────────────────────────────
            'dashboard.kegiatan.index'           => 'VIEW_KEGIATAN',

            // ── Approval ────────────────────────────────────────────
            'dashboard.approval.index'           => 'VIEW_APPROVAL',

            // ── Akuntansi ───────────────────────────────────────────
            'dashboard.jurnal.index'             => 'VIEW_JURNAL',
            'dashboard.jurnal-penyesuaian.index' => 'VIEW_JURNAL_PENYESUAIAN',
            'dashboard.jurnal-koreksi.index'     => 'VIEW_JURNAL_KOREKSI',
            'dashboard.jurnal-penutup.index'     => 'VIEW_JURNAL_PENUTUP',
            'dashboard.buku-besar.index'         => 'VIEW_BUKU_BESAR',
            'dashboard.neraca-saldo.index'       => 'VIEW_NERACA_SALDO',

            // ── Laporan ─────────────────────────────────────────────
            'dashboard.laporan-keuangan.index'   => 'VIEW_LAPORAN_KEUANGAN',

            // ── Master Data ─────────────────────────────────────────
            'dashboard.coa.index'                => 'VIEW_COA',
            'dashboard.kategori-transaksi.index' => 'VIEW_KATEGORI',
            'dashboard.aset.index'               => 'VIEW_ASET',
        ];

        foreach ($map as $routeName => $permissionCode) {
            $menu       = Menu::where('route_name', $routeName)->first();
            $permission = Permission::where('permission_code', $permissionCode)->first();

            if ($menu && $permission) {
                DB::table('menu_permission')->insertOrIgnore([
                    'menu_id'       => $menu->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }
}