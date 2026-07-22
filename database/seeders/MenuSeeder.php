<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\HakAkses;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama agar tidak duplikat saat db:seed dijalankan ulang
        \App\Models\Menu::query()->forceDelete();

        $perm = fn(string $code) => HakAkses::where('kode_hak_akses', $code)->value('id');

        // ── Beranda ──────────────────────────────────────────────────────────
        // Hanya Bendahara 1, Bendahara 2, Ketua DKM yang punya VIEW_LAPORAN_KEUANGAN
        // Pakai hak_akses itu sebagai gate supaya PHM / Panitia / Sekretaris
        // tidak melihat menu ini.
        $beranda = Menu::create([
            'menu_name'     => 'Beranda',
            'route_name'    => null,
            'icon'          => 'home',
            'sort_order'    => 1,
            'hak_akses_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Dashboard Operasional',
            'route_name'    => 'dashboard.index',
            'icon'          => 'home',
            'parent_id'     => $beranda->id,
            'sort_order'    => 2,
            'hak_akses_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        // ── Manajemen Pengguna ───────────────────────────────────────────────
        $manajemenPengguna = Menu::create([
            'menu_name'     => 'Manajemen Pengguna',
            'route_name'    => null,
            'icon'          => 'pengguna-group',
            'sort_order'    => 10,
            'hak_akses_id' => $perm('VIEW_PENGGUNA'),
        ]);

        Menu::create([
            'menu_name'     => 'Pengguna',
            'route_name'    => 'dashboard.pengguna.index',
            'icon'          => 'pengguna',
            'parent_id'     => $manajemenPengguna->id,
            'sort_order'    => 11,
            'hak_akses_id' => $perm('VIEW_PENGGUNA'),
        ]);

        Menu::create([
            'menu_name'     => 'Peran',
            'route_name'    => 'dashboard.peran.index',
            'icon'          => 'shield',
            'parent_id'     => $manajemenPengguna->id,
            'sort_order'    => 12,
            'hak_akses_id' => $perm('VIEW_PERAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Hak Akses',
            'route_name'    => 'dashboard.hak-akses.index',
            'icon'          => 'lock',
            'parent_id'     => $manajemenPengguna->id,
            'sort_order'    => 13,
            'hak_akses_id' => $perm('VIEW_HAK_AKSES'),
        ]);

        // Manajemen Menu — hanya Administrator (punya VIEW_MENUS) yang melihat & CRUD
        Menu::create([
            'menu_name'     => 'Manajemen Menu',
            'route_name'    => 'dashboard.menus.index',
            'icon'          => 'tables',
            'parent_id'     => $manajemenPengguna->id,
            'sort_order'    => 14,
            'hak_akses_id' => $perm('VIEW_MENUS'),
        ]);

        // ── Pencatatan Transaksi ─────────────────────────────────────────────
        // Hanya Bendahara 1 & 2 yang punya VIEW_TRANSAKSI
        $pencatatan = Menu::create([
            'menu_name'     => 'Pencatatan Transaksi',
            'route_name'    => null,
            'icon'          => 'notebook',
            'sort_order'    => 20,
            'hak_akses_id' => $perm('VIEW_TRANSAKSI'),
        ]);

        Menu::create([
            'menu_name'     => 'Transaksi Umum',
            'route_name'    => 'dashboard.transaksi.index',
            'icon'          => 'forms',
            'parent_id'     => $pencatatan->id,
            'sort_order'    => 21,
            'hak_akses_id' => $perm('VIEW_TRANSAKSI'),
        ]);

        Menu::create([
            'menu_name'     => 'Kencleng',
            'route_name'    => 'dashboard.kencleng.index',
            'icon'          => 'pig-money',
            'parent_id'     => $pencatatan->id,
            'sort_order'    => 22,
            'hak_akses_id' => $perm('VIEW_KENCLENG'),
        ]);

        Menu::create([
            'menu_name'     => 'Transaksi Kegiatan',
            'route_name'    => 'dashboard.transaksi-kegiatan.index',
            'icon'          => 'receipt',
            'parent_id'     => $pencatatan->id,
            'sort_order'    => 23,
            'hak_akses_id' => $perm('VIEW_TRANSAKSI_KEGIATAN'),
        ]);

        // ── Persetujuan ──────────────────────────────────────────────────────
        $persetujuan = Menu::create([
            'menu_name'     => 'Persetujuan',
            'route_name'    => null,
            'icon'          => 'checks',
            'sort_order'    => 30,
            'hak_akses_id' => $perm('VIEW_APPROVAL'),
        ]);

        Menu::create([
            'menu_name'     => 'Persetujuan Transaksi',
            'route_name'    => 'dashboard.approval.index',
            'icon'          => 'checks',
            'parent_id'     => $persetujuan->id,
            'sort_order'    => 31,
            'hak_akses_id' => $perm('VIEW_APPROVAL'),
        ]);

        // ── Akuntansi ────────────────────────────────────────────────────────
        $akuntansi = Menu::create([
            'menu_name'     => 'Akuntansi',
            'route_name'    => null,
            'icon'          => 'book-2',
            'sort_order'    => 40,
            'hak_akses_id' => $perm('VIEW_JURNAL'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Pembuka',
            'route_name'    => 'dashboard.jurnal-pembuka.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 41,
            'hak_akses_id' => $perm('VIEW_JURNAL_PEMBUKA'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Umum',
            'route_name'    => 'dashboard.jurnal-umum.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 42,
            'hak_akses_id' => $perm('VIEW_JURNAL'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Penyesuaian',
            'route_name'    => 'dashboard.jurnal-penyesuaian.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 43,
            'hak_akses_id' => $perm('VIEW_JURNAL_PENYESUAIAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Koreksi',
            'route_name'    => 'dashboard.jurnal-koreksi.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 44,
            'hak_akses_id' => $perm('VIEW_JURNAL_KOREKSI'),
        ]);

        Menu::create([
            'menu_name'     => 'Jurnal Penutup',
            'route_name'    => 'dashboard.jurnal-penutup.index',
            'icon'          => 'notebook',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 45,
            'hak_akses_id' => $perm('VIEW_JURNAL_PENUTUP'),
        ]);

        Menu::create([
            'menu_name'     => 'Buku Besar',
            'route_name'    => 'dashboard.buku-besar.index',
            'icon'          => 'database',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 46,
            'hak_akses_id' => $perm('VIEW_BUKU_BESAR'),
        ]);

        Menu::create([
            'menu_name'     => 'Neraca Saldo',
            'route_name'    => 'dashboard.neraca-saldo.index',
            'icon'          => 'checks',
            'parent_id'     => $akuntansi->id,
            'sort_order'    => 47,
            'hak_akses_id' => $perm('VIEW_NERACA_SALDO'),
        ]);

        // ── Laporan ──────────────────────────────────────────────────────────
        $laporan = Menu::create([
            'menu_name'     => 'Laporan',
            'route_name'    => null,
            'icon'          => 'charts',
            'sort_order'    => 50,
            'hak_akses_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        Menu::create([
            'menu_name'     => 'Laporan Keuangan',
            'route_name'    => 'dashboard.laporan-keuangan.index',
            'icon'          => 'receipt',
            'parent_id'     => $laporan->id,
            'sort_order'    => 51,
            'hak_akses_id' => $perm('VIEW_LAPORAN_KEUANGAN'),
        ]);

        // ── Data Induk ───────────────────────────────────────────────────────
        $dataInduk = Menu::create([
            'menu_name'     => 'Data Induk',
            'route_name'    => null,
            'icon'          => 'database',
            'sort_order'    => 60,
            'hak_akses_id' => $perm('VIEW_COA'),
        ]);

        Menu::create([
            'menu_name'     => 'Chart of Accounts',
            'route_name'    => 'dashboard.coa.index',
            'icon'          => 'book-2',
            'parent_id'     => $dataInduk->id,
            'sort_order'    => 61,
            'hak_akses_id' => $perm('VIEW_COA'),
        ]);

        Menu::create([
            'menu_name'     => 'Kategori Transaksi',
            'route_name'    => 'dashboard.kategori-transaksi.index',
            'icon'          => 'tag',
            'parent_id'     => $dataInduk->id,
            'sort_order'    => 62,
            'hak_akses_id' => $perm('VIEW_KATEGORI'),
        ]);

        // Kegiatan Khusus — tetap dikelompokkan di bawah Data Induk
        // Gate CREATE_KEGIATAN agar hanya peran yang berwenang (mis. Administrator) yang melihatnya
        Menu::create([
            'menu_name'     => 'Kegiatan Khusus',
            'route_name'    => 'dashboard.kegiatan.index',
            'icon'          => 'calendar-event',
            'parent_id'     => $dataInduk->id,
            'sort_order'    => 63,
            'hak_akses_id' => $perm('CREATE_KEGIATAN'),
        ]);

        // Aset TIDAK di bawah Data Induk — lihat grup menu utama "Aset" (induk › anak) di bawah.

        // ── Kencleng (standalone untuk PHM, nama unik) ─────────────────────────────────
        // PHM hanya melihat menu ini, tidak melihat grup Pencatatan Transaksi
        Menu::create([
            'menu_name'     => 'Pencatatan Kencleng',
            'route_name'    => 'dashboard.kencleng.index',
            'icon'          => 'pig-money',
            'sort_order'    => 70,
            'hak_akses_id' => $perm('VIEW_KENCLENG'),
        ]);

        // ── Transaksi Kegiatan (standalone untuk Panitia) ────────────────────
        Menu::create([
            'menu_name'     => 'Pencatatan Transaksi Kegiatan',
            'route_name'    => 'dashboard.transaksi-kegiatan.index',
            'icon'          => 'receipt',
            'sort_order'    => 71,
            'hak_akses_id' => $perm('VIEW_TRANSAKSI_KEGIATAN'),
        ]);

        // ── Aset (grup menu utama khusus Sekretaris) ─────────────────────────
        // Struktur: Aset (induk) › Manajemen Aset (anak)
        $aset = Menu::create([
            'menu_name'     => 'Aset',
            'route_name'    => null,
            'icon'          => 'ecommerce',
            'sort_order'    => 72,
            'hak_akses_id' => $perm('VIEW_ASET'),
        ]);

        Menu::create([
            'menu_name'     => 'Manajemen Aset',
            'route_name'    => 'dashboard.aset.index',
            'icon'          => 'ecommerce',
            'parent_id'     => $aset->id,
            'sort_order'    => 73,
            'hak_akses_id' => $perm('VIEW_ASET'),
        ]);
    }
}

