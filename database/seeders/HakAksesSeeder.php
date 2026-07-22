<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HakAkses;

class HakAksesSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ── Dashboard ──────────────────────────────────────────
            ['kode_hak_akses' => 'VIEW_DASHBOARD', 'nama_hak_akses' => 'View Dashboard', 'modul' => 'dashboard', 'aksi' => 'view'],

            // ── Manajemen Pengguna ─────────────────────────────────────
            ['kode_hak_akses' => 'VIEW_PENGGUNA',   'nama_hak_akses' => 'View Pengguna',   'modul' => 'pengguna', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_PENGGUNA', 'nama_hak_akses' => 'Create Pengguna', 'modul' => 'pengguna', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_PENGGUNA',   'nama_hak_akses' => 'Edit Pengguna',   'modul' => 'pengguna', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_PENGGUNA', 'nama_hak_akses' => 'Delete Pengguna', 'modul' => 'pengguna', 'aksi' => 'delete'],

            ['kode_hak_akses' => 'VIEW_PERAN',   'nama_hak_akses' => 'View Peran',   'modul' => 'peran', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_PERAN', 'nama_hak_akses' => 'Create Peran', 'modul' => 'peran', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_PERAN',   'nama_hak_akses' => 'Edit Peran',   'modul' => 'peran', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_PERAN', 'nama_hak_akses' => 'Delete Peran', 'modul' => 'peran', 'aksi' => 'delete'],

            ['kode_hak_akses' => 'VIEW_HAK_AKSES',   'nama_hak_akses' => 'View HakAkses',   'modul' => 'hak_akses', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_HAK_AKSES', 'nama_hak_akses' => 'Create HakAkses', 'modul' => 'hak_akses', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_HAK_AKSES',   'nama_hak_akses' => 'Edit HakAkses',   'modul' => 'hak_akses', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_HAK_AKSES', 'nama_hak_akses' => 'Delete HakAkses', 'modul' => 'hak_akses', 'aksi' => 'delete'],

            // ── Manajemen Menu ─────────────────────────────────────
            ['kode_hak_akses' => 'VIEW_MENUS',   'nama_hak_akses' => 'View Menu',   'modul' => 'menu', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_MENUS', 'nama_hak_akses' => 'Create Menu', 'modul' => 'menu', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_MENUS',   'nama_hak_akses' => 'Edit Menu',   'modul' => 'menu', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_MENUS', 'nama_hak_akses' => 'Delete Menu', 'modul' => 'menu', 'aksi' => 'delete'],

            // ── Manajemen Menu (khusus Administrator) ─────────────
            ['kode_hak_akses' => 'VIEW_MENUS',                 'nama_hak_akses' => 'View Menu',                 'modul' => 'menus',              'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_MENUS',               'nama_hak_akses' => 'Create Menu',               'modul' => 'menus',              'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_MENUS',                 'nama_hak_akses' => 'Edit Menu',                 'modul' => 'menus',              'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_MENUS',               'nama_hak_akses' => 'Delete Menu',               'modul' => 'menus',              'aksi' => 'delete'],

            // ── Transaksi ──────────────────────────────────────────
            ['kode_hak_akses' => 'VIEW_TRANSAKSI',   'nama_hak_akses' => 'Lihat Transaksi',  'modul' => 'transaksi', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_TRANSAKSI', 'nama_hak_akses' => 'Tambah Transaksi', 'modul' => 'transaksi', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_TRANSAKSI',   'nama_hak_akses' => 'Edit Transaksi',   'modul' => 'transaksi', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_TRANSAKSI', 'nama_hak_akses' => 'Hapus Transaksi',  'modul' => 'transaksi', 'aksi' => 'delete'],
            ['kode_hak_akses' => 'IMPORT_TRANSAKSI', 'nama_hak_akses' => 'Import Transaksi (Mutasi Bank)', 'modul' => 'transaksi', 'aksi' => 'import'],

            ['kode_hak_akses' => 'VIEW_KENCLENG',   'nama_hak_akses' => 'View Kencleng',   'modul' => 'kencleng', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_KENCLENG', 'nama_hak_akses' => 'Create Kencleng', 'modul' => 'kencleng', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_KENCLENG',   'nama_hak_akses' => 'Edit Kencleng',   'modul' => 'kencleng', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_KENCLENG', 'nama_hak_akses' => 'Delete Kencleng', 'modul' => 'kencleng', 'aksi' => 'delete'],

            ['kode_hak_akses' => 'VIEW_TRANSAKSI_KEGIATAN',   'nama_hak_akses' => 'View Transaksi Kegiatan',   'modul' => 'transaksi-kegiatan',   'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_TRANSAKSI_KEGIATAN', 'nama_hak_akses' => 'Create Transaksi Kegiatan', 'modul' => 'transaksi-kegiatan',   'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_TRANSAKSI_KEGIATAN',   'nama_hak_akses' => 'Edit Transaksi Kegiatan',   'modul' => 'transaksi-kegiatan',   'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_TRANSAKSI_KEGIATAN', 'nama_hak_akses' => 'Delete Transaksi Kegiatan', 'modul' => 'transaksi-kegiatan',   'aksi' => 'delete'],

            // ── Kegiatan Khusus ────────────────────────────────────
            ['kode_hak_akses' => 'VIEW_KEGIATAN',   'nama_hak_akses' => 'View Kegiatan',   'modul' => 'kegiatan', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_KEGIATAN', 'nama_hak_akses' => 'Create Kegiatan', 'modul' => 'kegiatan', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_KEGIATAN',   'nama_hak_akses' => 'Edit Kegiatan',   'modul' => 'kegiatan', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_KEGIATAN', 'nama_hak_akses' => 'Delete Kegiatan', 'modul' => 'kegiatan', 'aksi' => 'delete'],

            // ── Persetujuan ───────────────────────────────────────────
            ['kode_hak_akses' => 'VIEW_PERSETUJUAN',   'nama_hak_akses' => 'View Persetujuan',   'modul' => 'persetujuan', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_PERSETUJUAN', 'nama_hak_akses' => 'Create Persetujuan', 'modul' => 'persetujuan', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_PERSETUJUAN',   'nama_hak_akses' => 'Edit Persetujuan',   'modul' => 'persetujuan', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_PERSETUJUAN', 'nama_hak_akses' => 'Delete Persetujuan', 'modul' => 'persetujuan', 'aksi' => 'delete'],

            // ── Akuntansi - Jurnal Umum ────────────────────────────
            ['kode_hak_akses' => 'VIEW_JURNAL',                'nama_hak_akses' => 'View Jurnal Umum',          'modul' => 'jurnal',             'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_JURNAL',              'nama_hak_akses' => 'Create Jurnal Umum',        'modul' => 'jurnal',             'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_JURNAL',                'nama_hak_akses' => 'Edit Jurnal Umum',          'modul' => 'jurnal',             'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_JURNAL',              'nama_hak_akses' => 'Delete Jurnal Umum',        'modul' => 'jurnal',             'aksi' => 'delete'],

            // ── Akuntansi - Jurnal Pembuka ─────────────────────────
            ['kode_hak_akses' => 'VIEW_JURNAL_PEMBUKA',   'nama_hak_akses' => 'View Jurnal Pembuka',   'modul' => 'jurnal-pembuka', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_JURNAL_PEMBUKA', 'nama_hak_akses' => 'Create Jurnal Pembuka', 'modul' => 'jurnal-pembuka', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_JURNAL_PEMBUKA',   'nama_hak_akses' => 'Edit Jurnal Pembuka',   'modul' => 'jurnal-pembuka', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_JURNAL_PEMBUKA', 'nama_hak_akses' => 'Delete Jurnal Pembuka', 'modul' => 'jurnal-pembuka', 'aksi' => 'delete'],

            // ── Akuntansi - Jurnal Penyesuaian ─────────────────────
            ['kode_hak_akses' => 'VIEW_JURNAL_PENYESUAIAN',   'nama_hak_akses' => 'View Jurnal Penyesuaian',   'modul' => 'jurnal-penyesuaian', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_JURNAL_PENYESUAIAN', 'nama_hak_akses' => 'Create Jurnal Penyesuaian', 'modul' => 'jurnal-penyesuaian', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_JURNAL_PENYESUAIAN',   'nama_hak_akses' => 'Edit Jurnal Penyesuaian',   'modul' => 'jurnal-penyesuaian', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_JURNAL_PENYESUAIAN', 'nama_hak_akses' => 'Delete Jurnal Penyesuaian', 'modul' => 'jurnal-penyesuaian', 'aksi' => 'delete'],

            // ── Akuntansi - Jurnal Koreksi ─────────────────────────
            ['kode_hak_akses' => 'VIEW_JURNAL_KOREKSI',   'nama_hak_akses' => 'View Jurnal Koreksi',   'modul' => 'jurnal-koreksi', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_JURNAL_KOREKSI', 'nama_hak_akses' => 'Create Jurnal Koreksi', 'modul' => 'jurnal-koreksi', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_JURNAL_KOREKSI',   'nama_hak_akses' => 'Edit Jurnal Koreksi',   'modul' => 'jurnal-koreksi', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_JURNAL_KOREKSI', 'nama_hak_akses' => 'Delete Jurnal Koreksi', 'modul' => 'jurnal-koreksi', 'aksi' => 'delete'],

            // ── Akuntansi - Jurnal Penutup ─────────────────────────
            ['kode_hak_akses' => 'VIEW_JURNAL_PENUTUP',   'nama_hak_akses' => 'View Jurnal Penutup',   'modul' => 'jurnal-penutup', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_JURNAL_PENUTUP', 'nama_hak_akses' => 'Create Jurnal Penutup', 'modul' => 'jurnal-penutup', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_JURNAL_PENUTUP',   'nama_hak_akses' => 'Edit Jurnal Penutup',   'modul' => 'jurnal-penutup', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_JURNAL_PENUTUP', 'nama_hak_akses' => 'Delete Jurnal Penutup', 'modul' => 'jurnal-penutup', 'aksi' => 'delete'],

            // ── Akuntansi - Buku Besar ─────────────────────────────
            ['kode_hak_akses' => 'VIEW_BUKU_BESAR', 'nama_hak_akses' => 'View Buku Besar', 'modul' => 'buku-besar', 'aksi' => 'view'],

            // ── Akuntansi - Neraca Saldo ───────────────────────────
            ['kode_hak_akses' => 'VIEW_NERACA_SALDO', 'nama_hak_akses' => 'View Neraca Saldo', 'modul' => 'neraca-saldo', 'aksi' => 'view'],

            // ── Akuntansi - Periode / Tutup Buku ───────────────────
            ['kode_hak_akses' => 'VIEW_PERIODE',   'nama_hak_akses' => 'View Periode',   'modul' => 'periode', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_PERIODE', 'nama_hak_akses' => 'Create Periode', 'modul' => 'periode', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_PERIODE',   'nama_hak_akses' => 'Edit / Tutup Periode', 'modul' => 'periode', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_PERIODE', 'nama_hak_akses' => 'Delete Periode', 'modul' => 'periode', 'aksi' => 'delete'],

            // ── Laporan Keuangan ───────────────────────────────────
            ['kode_hak_akses' => 'VIEW_LAPORAN_KEUANGAN', 'nama_hak_akses' => 'View Laporan Keuangan', 'modul' => 'laporan-keuangan', 'aksi' => 'view'],

            // ── Master Data - Chart of Accounts ────────────────────
            ['kode_hak_akses' => 'VIEW_COA',   'nama_hak_akses' => 'View Chart of Accounts',   'modul' => 'coa', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_COA', 'nama_hak_akses' => 'Create Chart of Accounts', 'modul' => 'coa', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_COA',   'nama_hak_akses' => 'Edit Chart of Accounts',   'modul' => 'coa', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_COA', 'nama_hak_akses' => 'Delete Chart of Accounts', 'modul' => 'coa', 'aksi' => 'delete'],

            // ── Master Data - Kategori Transaksi ───────────────────
            ['kode_hak_akses' => 'VIEW_KATEGORI',   'nama_hak_akses' => 'View Kategori Transaksi',   'modul' => 'kategori-transaksi', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_KATEGORI', 'nama_hak_akses' => 'Create Kategori Transaksi', 'modul' => 'kategori-transaksi', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_KATEGORI',   'nama_hak_akses' => 'Edit Kategori Transaksi',   'modul' => 'kategori-transaksi', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_KATEGORI', 'nama_hak_akses' => 'Delete Kategori Transaksi', 'modul' => 'kategori-transaksi', 'aksi' => 'delete'],

            // ── Master Data - Aset ─────────────────────────────────
            ['kode_hak_akses' => 'VIEW_ASET',   'nama_hak_akses' => 'View Aset',   'modul' => 'aset', 'aksi' => 'view'],
            ['kode_hak_akses' => 'CREATE_ASET', 'nama_hak_akses' => 'Create Aset', 'modul' => 'aset', 'aksi' => 'create'],
            ['kode_hak_akses' => 'EDIT_ASET',   'nama_hak_akses' => 'Edit Aset',   'modul' => 'aset', 'aksi' => 'update'],
            ['kode_hak_akses' => 'DELETE_ASET', 'nama_hak_akses' => 'Delete Aset', 'modul' => 'aset', 'aksi' => 'delete'],
        ];

        foreach ($data as $item) {
            HakAkses::firstOrCreate(['kode_hak_akses' => $item['kode_hak_akses']], $item);
        }
    }
}
