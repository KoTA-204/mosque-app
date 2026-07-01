<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Helper: ambil id permission dari kode
        $perm = fn (string $code) => Permission::where('permission_code', $code)->value('id');

        // ── Dashboard ──────────────────────────────────────────────
        $dashboard = Menu::create([
            'menu_name'  => 'Dashboard',
            'route_name' => null,
            'icon'       => 'home',
            'sort_order' => 1,
        ]);

        Menu::create([
            'menu_name'  => 'Dashboard Operasional',
            'route_name' => 'dashboard.index',
            'icon'       => 'home',
            'parent_id'  => $dashboard->id,
            'sort_order' => 1,
            // Dashboard bisa diakses semua role (tanpa permission khusus).
        ]);

        // ── Manajemen Pengguna ─────────────────────────────────────
        $manajemenUser = Menu::create([
            'menu_name'  => 'Manajemen Pengguna',
            'route_name' => null,
            'icon'       => 'users',
            'sort_order' => 10,
        ]);

        Menu::create(['menu_name' => 'Pengguna', 'route_name' => 'dashboard.users.index',       'icon' => 'user',   'parent_id' => $manajemenUser->id, 'sort_order' => 11, 'permission_id' => $perm('VIEW_USERS')]);
        Menu::create(['menu_name' => 'Peran',    'route_name' => 'dashboard.roles.index',       'icon' => 'shield', 'parent_id' => $manajemenUser->id, 'sort_order' => 12, 'permission_id' => $perm('VIEW_ROLES')]);
        Menu::create(['menu_name' => 'Hak Akses',     'route_name' => 'dashboard.permissions.index', 'icon' => 'lock',   'parent_id' => $manajemenUser->id, 'sort_order' => 13, 'permission_id' => $perm('VIEW_PERMISSIONS')]);
        Menu::create(['menu_name' => 'Menu',     'route_name' => 'dashboard.menus.index',       'icon' => 'menu-2', 'parent_id' => $manajemenUser->id, 'sort_order' => 14, 'permission_id' => $perm('VIEW_MENU')]);

        // ── Master Data ────────────────────────────────────────────
        $masterData = Menu::create([
            'menu_name'  => 'Master Data',
            'route_name' => null,
            'icon'       => 'database',
            'sort_order' => 20,
        ]);

        Menu::create(['menu_name' => 'Chart of Accounts',  'route_name' => 'dashboard.coa.index',                'icon' => 'book-2', 'parent_id' => $masterData->id, 'sort_order' => 71, 'permission_id' => $perm('VIEW_COA')]);
        Menu::create(['menu_name' => 'Kategori Transaksi', 'route_name' => 'dashboard.kategori-transaksi.index', 'icon' => 'tag',    'parent_id' => $masterData->id, 'sort_order' => 72, 'permission_id' => $perm('VIEW_KATEGORI')]);
        Menu::create(['menu_name' => 'Aset',               'route_name' => 'dashboard.aset.index',               'icon' => 'building-warehouse', 'parent_id' => $masterData->id, 'sort_order' => 73, 'permission_id' => $perm('VIEW_ASET')]);
        
        // ── Kegiatan Khusus ────────────────────────────────────────
        $kegiatanKhusus = Menu::create([
            'menu_name'  => 'Kegiatan Khusus',
            'route_name' => null,
            'icon'       => 'calendar-event',
            'sort_order' => 30,
        ]);

        Menu::create(['menu_name' => 'Data Kegiatan', 'route_name' => 'dashboard.kegiatan.index', 'icon' => 'clipboard-list', 'parent_id' => $kegiatanKhusus->id, 'sort_order' => 31, 'permission_id' => $perm('VIEW_KEGIATAN')]);
        
        // ── Transaksi ──────────────────────────────────────────────
        // Import transaksi (mutasi bank) TIDAK menjadi menu sidebar; aksinya
        // berupa tombol di halaman Transaksi (modal import -> halaman import review),
        // dikontrol oleh permission IMPORT_TRANSAKSI.
        $transaksi = Menu::create([
            'menu_name'  => 'Transaksi',
            'route_name' => null,
            'icon'       => 'money-bill-transfer',
            'sort_order' => 40,
        ]);

        Menu::create(['menu_name' => 'Pencatatan Transaksi', 'route_name' => 'dashboard.transaksi.index',           'icon' => 'arrows-exchange', 'parent_id' => $transaksi->id, 'sort_order' => 21, 'permission_id' => $perm('VIEW_TRANSAKSI')]);
        Menu::create(['menu_name' => 'Kencleng',             'route_name' => 'dashboard.kencleng.index',            'icon' => 'pig-money',       'parent_id' => $transaksi->id, 'sort_order' => 22, 'permission_id' => $perm('VIEW_KENCLENG')]);
        Menu::create(['menu_name' => 'Transaksi Kegiatan',   'route_name' => 'dashboard.transaksi-kegiatan.index',  'icon' => 'receipt',         'parent_id' => $transaksi->id, 'sort_order' => 23, 'permission_id' => $perm('VIEW_TRANSAKSI_KEGIATAN')]);

        // ── Approval ───────────────────────────────────────────────
        $approval = Menu::create([
            'menu_name'  => 'Approval',
            'route_name' => null,
            'icon'       => 'checks',
            'sort_order' => 50,
        ]);

        Menu::create(['menu_name' => 'Approval Center', 'route_name' => 'dashboard.approval.index', 'icon' => 'checks', 'parent_id' => $approval->id, 'sort_order' => 41, 'permission_id' => $perm('VIEW_APPROVAL')]);

        // ── Akuntansi ──────────────────────────────────────────────
        $akuntansi = Menu::create([
            'menu_name'  => 'Akuntansi',
            'route_name' => null,
            'icon'       => 'book-2',
            'sort_order' => 60,
        ]);

        Menu::create(['menu_name' => 'Jurnal Pembuka',      'route_name' => 'dashboard.jurnal-pembuka.index',      'icon' => 'book-upload', 'parent_id' => $akuntansi->id, 'sort_order' => 51, 'permission_id' => $perm('VIEW_JURNAL_PEMBUKA')]);
        Menu::create(['menu_name' => 'Jurnal Umum',         'route_name' => 'dashboard.jurnal-umum.index',         'icon' => 'book-open',   'parent_id' => $akuntansi->id, 'sort_order' => 52, 'permission_id' => $perm('VIEW_JURNAL')]);
        Menu::create(['menu_name' => 'Jurnal Penyesuaian',  'route_name' => 'dashboard.jurnal-penyesuaian.index',  'icon' => 'book-open',   'parent_id' => $akuntansi->id, 'sort_order' => 53, 'permission_id' => $perm('VIEW_JURNAL_PENYESUAIAN')]);
        Menu::create(['menu_name' => 'Jurnal Koreksi',      'route_name' => 'dashboard.jurnal-koreksi.index',      'icon' => 'book-open',   'parent_id' => $akuntansi->id, 'sort_order' => 54, 'permission_id' => $perm('VIEW_JURNAL_KOREKSI')]);
        Menu::create(['menu_name' => 'Jurnal Penutup',      'route_name' => 'dashboard.jurnal-penutup.index',      'icon' => 'book-open',   'parent_id' => $akuntansi->id, 'sort_order' => 55, 'permission_id' => $perm('VIEW_JURNAL_PENUTUP')]);
        Menu::create(['menu_name' => 'Buku Besar',          'route_name' => 'dashboard.buku-besar.index',          'icon' => 'briefcase',   'parent_id' => $akuntansi->id, 'sort_order' => 56, 'permission_id' => $perm('VIEW_BUKU_BESAR')]);
        Menu::create(['menu_name' => 'Neraca Saldo',        'route_name' => 'dashboard.neraca-saldo.index',        'icon' => 'scale',       'parent_id' => $akuntansi->id, 'sort_order' => 57, 'permission_id' => $perm('VIEW_NERACA_SALDO')]);
        Menu::create(['menu_name' => 'Manajemen Periode',   'route_name' => 'dashboard.periode.index',             'icon' => 'calendar',    'parent_id' => $akuntansi->id, 'sort_order' => 58, 'permission_id' => $perm('VIEW_PERIODE')]);

        // ── Laporan ────────────────────────────────────────────────
        $laporan = Menu::create([
            'menu_name'  => 'Laporan',
            'route_name' => null,
            'icon'       => 'chart-bar', 
            'sort_order' => 70,
        ]);

        Menu::create(['menu_name' => 'Laporan Keuangan', 'route_name' => 'dashboard.laporan-keuangan.index', 'icon' => 'file-chart', 'parent_id' => $laporan->id, 'sort_order' => 61, 'permission_id' => $perm('VIEW_LAPORAN_KEUANGAN')]);
    }
}
