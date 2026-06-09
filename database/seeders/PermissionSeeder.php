<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ── Manajemen User ─────────────────────────────────────
            ['permission_code' => 'VIEW_USERS',                 'permission_name' => 'View Users',                'module' => 'users',              'action' => 'view'],
            ['permission_code' => 'CREATE_USERS',               'permission_name' => 'Create Users',              'module' => 'users',              'action' => 'create'],
            ['permission_code' => 'EDIT_USERS',                 'permission_name' => 'Edit Users',                'module' => 'users',              'action' => 'update'],
            ['permission_code' => 'DELETE_USERS',               'permission_name' => 'Delete Users',              'module' => 'users',              'action' => 'delete'],

            ['permission_code' => 'VIEW_ROLES',                 'permission_name' => 'View Roles',                'module' => 'roles',              'action' => 'view'],
            ['permission_code' => 'CREATE_ROLES',               'permission_name' => 'Create Roles',              'module' => 'roles',              'action' => 'create'],
            ['permission_code' => 'EDIT_ROLES',                 'permission_name' => 'Edit Roles',                'module' => 'roles',              'action' => 'update'],
            ['permission_code' => 'DELETE_ROLES',               'permission_name' => 'Delete Roles',              'module' => 'roles',              'action' => 'delete'],

            ['permission_code' => 'VIEW_PERMISSIONS',           'permission_name' => 'View Permissions',          'module' => 'permissions',        'action' => 'view'],
            ['permission_code' => 'CREATE_PERMISSIONS',         'permission_name' => 'Create Permissions',        'module' => 'permissions',        'action' => 'create'],
            ['permission_code' => 'EDIT_PERMISSIONS',           'permission_name' => 'Edit Permissions',          'module' => 'permissions',        'action' => 'update'],
            ['permission_code' => 'DELETE_PERMISSIONS',         'permission_name' => 'Delete Permissions',        'module' => 'permissions',        'action' => 'delete'],

            // ── Transaksi ──────────────────────────────────────────
            ['permission_code' => 'VIEW_TRANSAKSI',             'permission_name' => 'Lihat Transaksi',           'module' => 'transaksi',          'action' => 'view'],
            ['permission_code' => 'CREATE_TRANSAKSI',           'permission_name' => 'Tambah Transaksi',          'module' => 'transaksi',          'action' => 'create'],
            ['permission_code' => 'EDIT_TRANSAKSI',             'permission_name' => 'Edit Transaksi',            'module' => 'transaksi',          'action' => 'update'],
            ['permission_code' => 'DELETE_TRANSAKSI',           'permission_name' => 'Hapus Transaksi',           'module' => 'transaksi',          'action' => 'delete'],

            ['permission_code' => 'VIEW_KENCLENG',              'permission_name' => 'View Kencleng',             'module' => 'kencleng',           'action' => 'view'],
            ['permission_code' => 'CREATE_KENCLENG',            'permission_name' => 'Create Kencleng',           'module' => 'kencleng',           'action' => 'create'],
            ['permission_code' => 'EDIT_KENCLENG',              'permission_name' => 'Edit Kencleng',             'module' => 'kencleng',           'action' => 'update'],
            ['permission_code' => 'DELETE_KENCLENG',            'permission_name' => 'Delete Kencleng',           'module' => 'kencleng',           'action' => 'delete'],

            ['permission_code' => 'VIEW_TRANSAKSI_KEGIATAN',   'permission_name' => 'View Transaksi Kegiatan',   'module' => 'kegiatan-panitia',   'action' => 'view'],
            ['permission_code' => 'CREATE_TRANSAKSI_KEGIATAN', 'permission_name' => 'Create Transaksi Kegiatan', 'module' => 'kegiatan-panitia',   'action' => 'create'],
            ['permission_code' => 'EDIT_TRANSAKSI_KEGIATAN',   'permission_name' => 'Edit Transaksi Kegiatan',   'module' => 'kegiatan-panitia',   'action' => 'update'],
            ['permission_code' => 'DELETE_TRANSAKSI_KEGIATAN', 'permission_name' => 'Delete Transaksi Kegiatan', 'module' => 'kegiatan-panitia',   'action' => 'delete'],

            // ── Kegiatan Khusus ────────────────────────────────────
            ['permission_code' => 'VIEW_KEGIATAN',              'permission_name' => 'View Kegiatan',             'module' => 'kegiatan',           'action' => 'view'],
            ['permission_code' => 'CREATE_KEGIATAN',            'permission_name' => 'Create Kegiatan',           'module' => 'kegiatan',           'action' => 'create'],
            ['permission_code' => 'EDIT_KEGIATAN',              'permission_name' => 'Edit Kegiatan',             'module' => 'kegiatan',           'action' => 'update'],
            ['permission_code' => 'DELETE_KEGIATAN',            'permission_name' => 'Delete Kegiatan',           'module' => 'kegiatan',           'action' => 'delete'],

            // ── Approval ───────────────────────────────────────────
            ['permission_code' => 'VIEW_APPROVAL',              'permission_name' => 'View Approval',             'module' => 'approval',           'action' => 'view'],
            ['permission_code' => 'CREATE_APPROVAL',            'permission_name' => 'Create Approval',           'module' => 'approval',           'action' => 'create'],
            ['permission_code' => 'EDIT_APPROVAL',              'permission_name' => 'Edit Approval',             'module' => 'approval',           'action' => 'update'],
            ['permission_code' => 'DELETE_APPROVAL',            'permission_name' => 'Delete Approval',           'module' => 'approval',           'action' => 'delete'],

            // ── Akuntansi - Jurnal Umum ────────────────────────────
            ['permission_code' => 'VIEW_JURNAL',                'permission_name' => 'View Jurnal Umum',          'module' => 'jurnal',             'action' => 'view'],
            ['permission_code' => 'CREATE_JURNAL',              'permission_name' => 'Create Jurnal Umum',        'module' => 'jurnal',             'action' => 'create'],
            ['permission_code' => 'EDIT_JURNAL',                'permission_name' => 'Edit Jurnal Umum',          'module' => 'jurnal',             'action' => 'update'],
            ['permission_code' => 'DELETE_JURNAL',              'permission_name' => 'Delete Jurnal Umum',        'module' => 'jurnal',             'action' => 'delete'],

            // ── Akuntansi - Jurnal Penyesuaian ────────────────────
            ['permission_code' => 'VIEW_JURNAL_PENYESUAIAN',   'permission_name' => 'View Jurnal Penyesuaian',   'module' => 'jurnal-penyesuaian', 'action' => 'view'],
            ['permission_code' => 'CREATE_JURNAL_PENYESUAIAN', 'permission_name' => 'Create Jurnal Penyesuaian', 'module' => 'jurnal-penyesuaian', 'action' => 'create'],
            ['permission_code' => 'EDIT_JURNAL_PENYESUAIAN',   'permission_name' => 'Edit Jurnal Penyesuaian',   'module' => 'jurnal-penyesuaian', 'action' => 'update'],
            ['permission_code' => 'DELETE_JURNAL_PENYESUAIAN', 'permission_name' => 'Delete Jurnal Penyesuaian', 'module' => 'jurnal-penyesuaian', 'action' => 'delete'],

            // ── Akuntansi - Jurnal Koreksi ─────────────────────────
            ['permission_code' => 'VIEW_JURNAL_KOREKSI',       'permission_name' => 'View Jurnal Koreksi',       'module' => 'jurnal-koreksi',     'action' => 'view'],
            ['permission_code' => 'CREATE_JURNAL_KOREKSI',     'permission_name' => 'Create Jurnal Koreksi',     'module' => 'jurnal-koreksi',     'action' => 'create'],
            ['permission_code' => 'EDIT_JURNAL_KOREKSI',       'permission_name' => 'Edit Jurnal Koreksi',       'module' => 'jurnal-koreksi',     'action' => 'update'],
            ['permission_code' => 'DELETE_JURNAL_KOREKSI',     'permission_name' => 'Delete Jurnal Koreksi',     'module' => 'jurnal-koreksi',     'action' => 'delete'],

            // ── Akuntansi - Jurnal Penutup ─────────────────────────
            ['permission_code' => 'VIEW_JURNAL_PENUTUP',       'permission_name' => 'View Jurnal Penutup',       'module' => 'jurnal-penutup',     'action' => 'view'],
            ['permission_code' => 'CREATE_JURNAL_PENUTUP',     'permission_name' => 'Create Jurnal Penutup',     'module' => 'jurnal-penutup',     'action' => 'create'],
            ['permission_code' => 'EDIT_JURNAL_PENUTUP',       'permission_name' => 'Edit Jurnal Penutup',       'module' => 'jurnal-penutup',     'action' => 'update'],
            ['permission_code' => 'DELETE_JURNAL_PENUTUP',     'permission_name' => 'Delete Jurnal Penutup',     'module' => 'jurnal-penutup',     'action' => 'delete'],

            // ── Akuntansi - Buku Besar ─────────────────────────────
            ['permission_code' => 'VIEW_BUKU_BESAR',           'permission_name' => 'View Buku Besar',           'module' => 'buku-besar',         'action' => 'view'],
            ['permission_code' => 'CREATE_BUKU_BESAR',         'permission_name' => 'Create Buku Besar',         'module' => 'buku-besar',         'action' => 'create'],
            ['permission_code' => 'EDIT_BUKU_BESAR',           'permission_name' => 'Edit Buku Besar',           'module' => 'buku-besar',         'action' => 'update'],
            ['permission_code' => 'DELETE_BUKU_BESAR',         'permission_name' => 'Delete Buku Besar',         'module' => 'buku-besar',         'action' => 'delete'],

            // ── Akuntansi - Neraca Saldo ───────────────────────────
            ['permission_code' => 'VIEW_NERACA_SALDO',         'permission_name' => 'View Neraca Saldo',         'module' => 'neraca-saldo',       'action' => 'view'],
            ['permission_code' => 'CREATE_NERACA_SALDO',       'permission_name' => 'Create Neraca Saldo',       'module' => 'neraca-saldo',       'action' => 'create'],
            ['permission_code' => 'EDIT_NERACA_SALDO',         'permission_name' => 'Edit Neraca Saldo',         'module' => 'neraca-saldo',       'action' => 'update'],
            ['permission_code' => 'DELETE_NERACA_SALDO',       'permission_name' => 'Delete Neraca Saldo',       'module' => 'neraca-saldo',       'action' => 'delete'],

            // ── Laporan Keuangan ───────────────────────────────────
            ['permission_code' => 'VIEW_LAPORAN_KEUANGAN',    'permission_name' => 'View Laporan Keuangan',      'module' => 'laporan-keuangan',   'action' => 'view'],
            ['permission_code' => 'CREATE_LAPORAN_KEUANGAN',  'permission_name' => 'Create Laporan Keuangan',    'module' => 'laporan-keuangan',   'action' => 'create'],
            ['permission_code' => 'EDIT_LAPORAN_KEUANGAN',    'permission_name' => 'Edit Laporan Keuangan',      'module' => 'laporan-keuangan',   'action' => 'update'],
            ['permission_code' => 'DELETE_LAPORAN_KEUANGAN',  'permission_name' => 'Delete Laporan Keuangan',    'module' => 'laporan-keuangan',   'action' => 'delete'],

            // ── Master Data - Chart of Accounts ───────────────────
            ['permission_code' => 'VIEW_COA',                  'permission_name' => 'View Chart of Accounts',    'module' => 'coa',                'action' => 'view'],
            ['permission_code' => 'CREATE_COA',                'permission_name' => 'Create Chart of Accounts',  'module' => 'coa',                'action' => 'create'],
            ['permission_code' => 'EDIT_COA',                  'permission_name' => 'Edit Chart of Accounts',    'module' => 'coa',                'action' => 'update'],
            ['permission_code' => 'DELETE_COA',                'permission_name' => 'Delete Chart of Accounts',  'module' => 'coa',                'action' => 'delete'],

            // ── Master Data - Kategori Transaksi ───────────────────
            ['permission_code' => 'VIEW_KATEGORI',             'permission_name' => 'View Kategori Transaksi',   'module' => 'kategori-transaksi', 'action' => 'view'],
            ['permission_code' => 'CREATE_KATEGORI',           'permission_name' => 'Create Kategori Transaksi', 'module' => 'kategori-transaksi', 'action' => 'create'],
            ['permission_code' => 'EDIT_KATEGORI',             'permission_name' => 'Edit Kategori Transaksi',   'module' => 'kategori-transaksi', 'action' => 'update'],
            ['permission_code' => 'DELETE_KATEGORI',           'permission_name' => 'Delete Kategori Transaksi', 'module' => 'kategori-transaksi', 'action' => 'delete'],

            // ── Master Data - Aset ─────────────────────────────────
            ['permission_code' => 'VIEW_ASET',                 'permission_name' => 'View Aset',                 'module' => 'aset',               'action' => 'view'],
            ['permission_code' => 'CREATE_ASET',               'permission_name' => 'Create Aset',               'module' => 'aset',               'action' => 'create'],
            ['permission_code' => 'EDIT_ASET',                 'permission_name' => 'Edit Aset',                 'module' => 'aset',               'action' => 'update'],
            ['permission_code' => 'DELETE_ASET',               'permission_name' => 'Delete Aset',               'module' => 'aset',               'action' => 'delete'],

            // ── Legacy ─────────────────────────────────────────────
            ['permission_code' => 'VIEW_PEMASUKAN',            'permission_name' => 'View Pemasukan',            'module' => 'pemasukan',          'action' => 'view'],
            ['permission_code' => 'CREATE_PEMASUKAN',          'permission_name' => 'Create Pemasukan',          'module' => 'pemasukan',          'action' => 'create'],
            ['permission_code' => 'EDIT_PEMASUKAN',            'permission_name' => 'Edit Pemasukan',            'module' => 'pemasukan',          'action' => 'update'],
            ['permission_code' => 'DELETE_PEMASUKAN',          'permission_name' => 'Delete Pemasukan',          'module' => 'pemasukan',          'action' => 'delete'],

            ['permission_code' => 'VIEW_PENGELUARAN',          'permission_name' => 'View Pengeluaran',          'module' => 'pengeluaran',        'action' => 'view'],
            ['permission_code' => 'CREATE_PENGELUARAN',        'permission_name' => 'Create Pengeluaran',        'module' => 'pengeluaran',        'action' => 'create'],
            ['permission_code' => 'EDIT_PENGELUARAN',          'permission_name' => 'Edit Pengeluaran',          'module' => 'pengeluaran',        'action' => 'update'],
            ['permission_code' => 'DELETE_PENGELUARAN',        'permission_name' => 'Delete Pengeluaran',        'module' => 'pengeluaran',        'action' => 'delete'],
        ];

        foreach ($data as $item) {
            Permission::create($item);
        }
    }
}