<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Manajemen User
            ['permission_code' => 'VIEW_USERS',                 'permission_name' => 'View Users',                'module' => 'users',               'action' => 'view'],
            ['permission_code' => 'CREATE_USERS',               'permission_name' => 'Create Users',              'module' => 'users',               'action' => 'create'],
            ['permission_code' => 'EDIT_USERS',                 'permission_name' => 'Edit Users',                'module' => 'users',               'action' => 'edit'],
            ['permission_code' => 'DELETE_USERS',               'permission_name' => 'Delete Users',              'module' => 'users',               'action' => 'delete'],

            ['permission_code' => 'VIEW_ROLES',                 'permission_name' => 'View Roles',                'module' => 'roles',               'action' => 'view'],
            ['permission_code' => 'CREATE_ROLES',               'permission_name' => 'Create Roles',              'module' => 'roles',               'action' => 'create'],
            ['permission_code' => 'EDIT_ROLES',                 'permission_name' => 'Edit Roles',                'module' => 'roles',               'action' => 'edit'],
            ['permission_code' => 'DELETE_ROLES',               'permission_name' => 'Delete Roles',              'module' => 'roles',               'action' => 'delete'],

            ['permission_code' => 'VIEW_PERMISSIONS',           'permission_name' => 'View Permissions',          'module' => 'permissions',         'action' => 'view'],
            ['permission_code' => 'CREATE_PERMISSIONS',         'permission_name' => 'Create Permissions',        'module' => 'permissions',         'action' => 'create'],
            ['permission_code' => 'EDIT_PERMISSIONS',           'permission_name' => 'Edit Permissions',          'module' => 'permissions',         'action' => 'edit'],
            ['permission_code' => 'DELETE_PERMISSIONS',         'permission_name' => 'Delete Permissions',        'module' => 'permissions',         'action' => 'delete'],

            // Pencatatan
            ['permission_code' => 'VIEW_PEMASUKAN',             'permission_name' => 'View Pemasukan',            'module' => 'pemasukan',           'action' => 'view'],
            ['permission_code' => 'CREATE_PEMASUKAN',           'permission_name' => 'Create Pemasukan',          'module' => 'pemasukan',           'action' => 'create'],
            ['permission_code' => 'EDIT_PEMASUKAN',             'permission_name' => 'Edit Pemasukan',            'module' => 'pemasukan',           'action' => 'edit'],
            ['permission_code' => 'DELETE_PEMASUKAN',           'permission_name' => 'Delete Pemasukan',          'module' => 'pemasukan',           'action' => 'delete'],

            ['permission_code' => 'VIEW_PENGELUARAN',           'permission_name' => 'View Pengeluaran',          'module' => 'pengeluaran',         'action' => 'view'],
            ['permission_code' => 'CREATE_PENGELUARAN',         'permission_name' => 'Create Pengeluaran',        'module' => 'pengeluaran',         'action' => 'create'],
            ['permission_code' => 'EDIT_PENGELUARAN',           'permission_name' => 'Edit Pengeluaran',          'module' => 'pengeluaran',         'action' => 'edit'],
            ['permission_code' => 'DELETE_PENGELUARAN',         'permission_name' => 'Delete Pengeluaran',        'module' => 'pengeluaran',         'action' => 'delete'],

            ['permission_code' => 'VIEW_KENCLENG',              'permission_name' => 'View Kencleng',             'module' => 'kencleng',            'action' => 'view'],
            ['permission_code' => 'CREATE_KENCLENG',            'permission_name' => 'Create Kencleng',           'module' => 'kencleng',            'action' => 'create'],
            ['permission_code' => 'EDIT_KENCLENG',              'permission_name' => 'Edit Kencleng',             'module' => 'kencleng',            'action' => 'edit'],
            ['permission_code' => 'DELETE_KENCLENG',            'permission_name' => 'Delete Kencleng',           'module' => 'kencleng',            'action' => 'delete'],

            // Kegiatan Khusus - Data Kegiatan (Admin)
            ['permission_code' => 'VIEW_KEGIATAN',              'permission_name' => 'View Kegiatan',             'module' => 'kegiatan',            'action' => 'view'],
            ['permission_code' => 'CREATE_KEGIATAN',            'permission_name' => 'Create Kegiatan',           'module' => 'kegiatan',            'action' => 'create'],
            ['permission_code' => 'EDIT_KEGIATAN',              'permission_name' => 'Edit Kegiatan',             'module' => 'kegiatan',            'action' => 'edit'],
            ['permission_code' => 'DELETE_KEGIATAN',            'permission_name' => 'Delete Kegiatan',           'module' => 'kegiatan',            'action' => 'delete'],

            // Kegiatan Khusus - Transaksi Kegiatan (Panitia)
            ['permission_code' => 'VIEW_TRANSAKSI_KEGIATAN',   'permission_name' => 'View Transaksi Kegiatan',   'module' => 'transaksi_kegiatan',  'action' => 'view'],
            ['permission_code' => 'CREATE_TRANSAKSI_KEGIATAN', 'permission_name' => 'Create Transaksi Kegiatan', 'module' => 'transaksi_kegiatan',  'action' => 'create'],
            ['permission_code' => 'EDIT_TRANSAKSI_KEGIATAN',   'permission_name' => 'Edit Transaksi Kegiatan',   'module' => 'transaksi_kegiatan',  'action' => 'edit'],
            ['permission_code' => 'DELETE_TRANSAKSI_KEGIATAN', 'permission_name' => 'Delete Transaksi Kegiatan', 'module' => 'transaksi_kegiatan',  'action' => 'delete'],

            // Approval (Bendahara 1)
            ['permission_code' => 'VIEW_APPROVAL',              'permission_name' => 'View Approval',             'module' => 'approval',            'action' => 'view'],

            // Master Data - CoA
            ['permission_code' => 'VIEW_COA',                   'permission_name' => 'View Chart of Accounts',    'module' => 'coa',                 'action' => 'view'],
            ['permission_code' => 'CREATE_COA',                 'permission_name' => 'Create Chart of Accounts',  'module' => 'coa',                 'action' => 'create'],
            ['permission_code' => 'EDIT_COA',                   'permission_name' => 'Edit Chart of Accounts',    'module' => 'coa',                 'action' => 'edit'],
            ['permission_code' => 'DELETE_COA',                 'permission_name' => 'Delete Chart of Accounts',  'module' => 'coa',                 'action' => 'delete'],

            // Master Data - Kategori Transaksi
            ['permission_code' => 'VIEW_KATEGORI',              'permission_name' => 'View Kategori Transaksi',   'module' => 'kategori_transaksi',  'action' => 'view'],
            ['permission_code' => 'CREATE_KATEGORI',            'permission_name' => 'Create Kategori Transaksi', 'module' => 'kategori_transaksi',  'action' => 'create'],
            ['permission_code' => 'EDIT_KATEGORI',              'permission_name' => 'Edit Kategori Transaksi',   'module' => 'kategori_transaksi',  'action' => 'edit'],
            ['permission_code' => 'DELETE_KATEGORI',            'permission_name' => 'Delete Kategori Transaksi', 'module' => 'kategori_transaksi',  'action' => 'delete'],
        ];

        foreach ($data as $item) {
            Permission::create($item);
        }
    }
}