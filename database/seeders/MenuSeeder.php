<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // ── Dashboard ──────────────────────────────────────────────
        Menu::create([
            'menu_name'  => 'Dashboard',
            'route_name' => 'dashboard.index',
            'icon'       => 'home',
            'sort_order' => 1,
        ]);

        // ── Manajemen User ─────────────────────────────────────────
        $manajemenUser = Menu::create([
            'menu_name'  => 'Manajemen User',
            'route_name' => null,
            'icon'       => 'users-group',
            'sort_order' => 2,
        ]);

        Menu::create([
            'menu_name'  => 'Users',
            'route_name' => 'dashboard.users.index',
            'icon'       => 'user',
            'parent_id'  => $manajemenUser->id,
            'sort_order' => 1,
        ]);

        Menu::create([
            'menu_name'  => 'Roles',
            'route_name' => 'dashboard.roles.index',
            'icon'       => 'shield',
            'parent_id'  => $manajemenUser->id,
            'sort_order' => 2,
        ]);

        Menu::create([
            'menu_name'  => 'Permissions',
            'route_name' => 'dashboard.permissions.index',
            'icon'       => 'lock',
            'parent_id'  => $manajemenUser->id,
            'sort_order' => 3,
        ]);

        // ── Pencatatan ─────────────────────────────────────────────
        $pencatatan = Menu::create([
            'menu_name'  => 'Pencatatan',
            'route_name' => null,
            'icon'       => 'notebook',
            'sort_order' => 3,
        ]);

        Menu::create([
            'menu_name'  => 'Pemasukan',
            'route_name' => 'dashboard.pemasukan.index',
            'icon'       => 'arrow-down-circle',
            'parent_id'  => $pencatatan->id,
            'sort_order' => 1,
        ]);

        Menu::create([
            'menu_name'  => 'Pengeluaran',
            'route_name' => 'dashboard.pengeluaran.index',
            'icon'       => 'arrow-up-circle',
            'parent_id'  => $pencatatan->id,
            'sort_order' => 2,
        ]);

        Menu::create([
            'menu_name'  => 'Kencleng',
            'route_name' => 'dashboard.kencleng.index',
            'icon'       => 'pig-money',
            'parent_id'  => $pencatatan->id,
            'sort_order' => 3,
        ]);

        // ── Kegiatan Khusus ────────────────────────────────────────
        $kegiatanKhusus = Menu::create([
            'menu_name'  => 'Kegiatan Khusus',
            'route_name' => null,
            'icon'       => 'calendar-event',
            'sort_order' => 4,
        ]);

        Menu::create([
            'menu_name'  => 'Data Kegiatan',
            'route_name' => 'dashboard.kegiatan.index',
            'icon'       => 'clipboard-list',
            'parent_id'  => $kegiatanKhusus->id,
            'sort_order' => 1,
        ]);

        Menu::create([
            'menu_name'  => 'Transaksi Kegiatan',
            'route_name' => 'dashboard.kegiatan-panitia.index',
            'icon'       => 'receipt',
            'parent_id'  => $kegiatanKhusus->id,
            'sort_order' => 2,
        ]);

        // ── Approval ───────────────────────────────────────────────
        Menu::create([
            'menu_name'  => 'Approval',
            'route_name' => 'dashboard.approval.index',
            'icon'       => 'checks',
            'sort_order' => 5,
        ]);

        // ── Master Data ────────────────────────────────────────────
        $masterData = Menu::create([
            'menu_name'  => 'Master Data',
            'route_name' => null,
            'icon'       => 'database',
            'sort_order' => 6,
        ]);

        Menu::create([
            'menu_name'  => 'Chart of Accounts',
            'route_name' => 'dashboard.coa.index',
            'icon'       => 'book-2',
            'parent_id'  => $masterData->id,
            'sort_order' => 1,
        ]);

        Menu::create([
            'menu_name'  => 'Kategori Transaksi',
            'route_name' => 'dashboard.kategori-transaksi.index',
            'icon'       => 'tag',
            'parent_id'  => $masterData->id,
            'sort_order' => 2,
        ]);
    }
}