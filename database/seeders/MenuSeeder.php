<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama agar tidak duplikat saat db:seed dijalankan ulang
        \App\Models\Menu::query()->forceDelete();

        $perm = fn(string $code) => Permission::where('permission_code', $code)->value('id');

        // ── Beranda ──────────────────────────────────────────────────────────
        // Hanya Bendahara 1, Bendahara 2, Ketua DKM yang punya VIEW_LAPORAN_KEUANGAN
        // Pakai permission itu sebagai gate supaya PHM / Panitia / Sekretaris
        // tidak melihat menu ini.
        $beranda = Menu::create([
            'menu_name'     => 'Beranda',
            'route_name'    => null,
            'icon'          => 'home',
            'sort_order'    => 1,
            'permission_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Dashboard Operasional',
            'route_name'    => 'dashboard.index',
            'icon'          => 'home',
            'parent_id'     => $beranda->id,
            'sort_order'    => 2,
            'permission_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        // ── Manajemen Pengguna ───────────────────────────────────────────────
        $manajemenUser = Menu::create([
            'menu_name'     => 'Manajemen Pengguna',
            'route_name'    => null,
            'icon'          => 'users-group',
            'sort_order'    => 10,
            'permission_id' => $perm('VIEW_USERS'),
        ]);

        Menu::create([
            'menu_name'     => 'Pengguna',
            'route_name'    => 'dashboard.users.index',
            'icon'          => 'user',
            'parent_id'     => $manajemenUser->id,
            'sort_order'    => 11,
            'permission_id' => $perm('VIEW_USERS'),
        ]);

        Menu::create([
            'menu_name'     => 'Peran',
            'route_name'    => 'dashboard.roles.index',
            'icon'          => 'shield',
            'parent_id'     => $manajemenUser->id,
            'sort_order'    => 12,
            'permission_id' => $perm('VIEW_ROLES'),
        ]);

        Menu::create([
            'menu_name'     => 'Hak Akses',
            'route_name'    => 'dashboard.permissions.index',
            'icon'          => 'lock',
            'parent_id'     => $manajemenUser->id,
            'sort_order'    => 13,
            'permission_id' => $perm('VIEW_PERMISSIONS'),
        ]);

        // Manajemen Menu — hanya Administrator (punya VIEW_MENU) yang melihat & CRUD
        Menu::create([
            'menu_name'     => 'Manajemen Menu',
            'route_name'    => 'dashboard.menus.index',
            'icon'          => 'tables',
            'parent_id'     => $manajemenUser->id,
            'sort_order'    => 14,
            'permission_id' => $perm('VIEW_MENU'),
        ]);

        // ── Pencatatan Transaksi ─────────────────────────────────────────────
        // Hanya Bendahara 1 & 2 yang punya VIEW_TRANSAKSI
        $pencatatan = Menu::create([
            'menu_name'     => 'Pencatatan Transaksi',
            'route_name'    => null,
            'icon'          => 'notebook',
            'sort_order'    => 20,
            'permission_id' => $perm('VIEW_TRANSAKSI'),
        ]);

        Menu::create([
            'menu_name'     => 'Transaksi Umum',
            'route_name'    => 'dashboard.transaksi.index',
            'icon'          => 'forms',
            'parent_id'     => $pencatatan->id,
            'sort_order'    => 21,
            'permission_id' => $perm('VIEW_TRANSAKSI'),
        ]);

        Menu::create([
            'menu_name'     => 'Kencleng',
            'route_name'    => 'dashboard.kencleng.index',
            'icon'          => 'pig-money',
            'parent_id'     => $pencatatan->id,
            'sort_order'    => 22,
            'permission_id' => $perm('VIEW_KENCLENG'),
        ]);

        Menu::create([
            'menu_name'     => 'Transaksi Kegiatan',
            'route_name'    => 'dashboard.transaksi-kegiatan.index',
            'icon'          => 'receipt',
            'parent_id'     => $pencatatan->id,
            'sort_order'    => 23,
            'permission_id' => $perm('VIEW_TRANSAKSI_KEGIATAN'),
        ]);

        // ── Persetujuan ──────────────────────────────────────────────────────
        $persetujuan = Menu::create([
            'menu_name'     => 'Persetujuan',
            'route_name'    => null,
            'icon'          => 'checks',
            'sort_order'    => 30,
            'permission_id' => $perm('VIEW_APPROVAL'),
        ]);

        Menu::create([
            'menu_name'     => 'Persetujuan Transaksi',
            'route_name'    => 'dashboard.approval.index',
            'icon'          => 'checks',
            'parent_id'     => $persetujuan->id,
            'sort_order'    => 31,
            'permission_id' => $perm('VIEW_APPROVAL'),
        ]);

        // ── Akuntansi ────────────────────────────────────────────────────────
        $akuntansi = Menu::create([
            'menu_name'     => 'Akuntansi',
            'route_name'    => null,
            'icon'          => 'book-2',
            'sort_order'    => 40,
            'permission_id' => $perm('VIEW_JURNAL'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Pembuka',
            'route_name'    => 'dashboard.jurnal-pembuka.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 41,
            'permission_id' => $perm('VIEW_JURNAL_PEMBUKA'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Umum',
            'route_name'    => 'dashboard.jurnal-umum.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 42,
            'permission_id' => $perm('VIEW_JURNAL'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Penyesuaian',
            'route_name'    => 'dashboard.jurnal-penyesuaian.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 43,
            'permission_id' => $perm('VIEW_JURNAL_PENYESUAIAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Koreksi',
            'route_name'    => 'dashboard.jurnal-koreksi.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 44,
            'permission_id' => $perm('VIEW_JURNAL_KOREKSI'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Penutup',
            'route_name'    => 'dashboard.jurnal-penutup.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 45,
            'permission_id' => $perm('VIEW_JURNAL_PENUTUP'),
        ]);

        Menu::create([
            'menu_name'     => 'Buku Besar',
            'route_name'    => 'dashboard.buku-besar.index',
            'icon'          => 'database',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 46,
            'permission_id' => $perm('VIEW_BUKU_BESAR'),
        ]);

        Menu::create([
            'menu_name'     => 'Neraca Saldo',
            'route_name'    => 'dashboard.neraca-saldo.index',
            'icon'          => 'checks',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 47,
            'permission_id' => $perm('VIEW_NERACA_SALDO'),
        ]);

        // ── Laporan ──────────────────────────────────────────────────────────
        $laporan = Menu::create([
            'menu_name'     => 'Laporan',
            'route_name'    => null,
            'icon'          => 'charts',
            'sort_order'    => 50,
            'permission_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Laporan Keuangan',
            'route_name'    => 'dashboard.laporan-keuangan.index',
            'icon'          => 'receipt',
            'parent_id'     => $laporan->id,
            'sort_order'    => 51,
            'permission_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        // ── Data Induk ───────────────────────────────────────────────────────
        $dataInduk = Menu::create([
            'menu_name'     => 'Data Induk',
            'route_name'    => null,
            'icon'          => 'database',
            'sort_order'    => 60,
            'permission_id' => $perm('VIEW_COA'),
        ]);

        Menu::create([
            'menu_name'     => 'Chart of Accounts',
            'route_name'    => 'dashboard.coa.index',
            'icon'          => 'book-2',
            'parent_id'     => $dataInduk->id,
            'sort_order'    => 61,
            'permission_id' => $perm('VIEW_COA'),
        ]);

        Menu::create([
            'menu_name'     => 'Kategori Transaksi',
            'route_name'    => 'dashboard.kategori-transaksi.index',
            'icon'          => 'tag',
            'parent_id'     => $dataInduk->id,
            'sort_order'    => 62,
            'permission_id' => $perm('VIEW_KATEGORI'),
        ]);

        // Kegiatan Khusus — tetap dikelompokkan di bawah Data Induk
        // Gate CREATE_KEGIATAN agar hanya role yang berwenang (mis. Administrator) yang melihatnya
        Menu::create([
            'menu_name'     => 'Kegiatan Khusus',
            'route_name'    => 'dashboard.kegiatan.index',
            'icon'          => 'calendar-event',
            'parent_id'     => $dataInduk->id,
            'sort_order'    => 63,
            'permission_id' => $perm('CREATE_KEGIATAN'),
        ]);

        // Aset TIDAK di bawah Data Induk — lihat grup menu utama "Aset" (induk › anak) di bawah.

        // ── Kencleng (standalone untuk PHM, nama unik) ─────────────────────────────────
        // PHM hanya melihat menu ini, tidak melihat grup Pencatatan Transaksi
        Menu::create([
            'menu_name'     => 'Pencatatan Kencleng',
            'route_name'    => 'dashboard.kencleng.index',
            'icon'          => 'pig-money',
            'sort_order'    => 70,
            'permission_id' => $perm('VIEW_KENCLENG'),
        ]);

        // ── Transaksi Kegiatan (standalone untuk Panitia) ────────────────────
        Menu::create([
            'menu_name'     => 'Pencatatan Transaksi Kegiatan',
            'route_name'    => 'dashboard.transaksi-kegiatan.index',
            'icon'          => 'receipt',
            'sort_order'    => 71,
            'permission_id' => $perm('VIEW_TRANSAKSI_KEGIATAN'),
        ]);

        // ── Aset (grup menu utama khusus Sekretaris) ─────────────────────────
        // Struktur: Aset (induk) › Manajemen Aset (anak)
        $aset = Menu::create([
            'menu_name'     => 'Aset',
            'route_name'    => null,
            'icon'          => 'ecommerce',
            'sort_order'    => 72,
            'permission_id' => $perm('VIEW_ASET'),
        ]);

        Menu::create([
            'menu_name'     => 'Manajemen Aset',
            'route_name'    => 'dashboard.aset.index',
            'icon'          => 'ecommerce',
            'parent_id'     => $aset->id,
            'sort_order'    => 73,
            'permission_id' => $perm('VIEW_ASET'),
        ]);
    }
}

