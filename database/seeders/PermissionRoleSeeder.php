<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin      = Role::where('role_name', 'Administrator')->firstOrFail();
        $ketuaDkm   = Role::where('role_name', 'Ketua DKM')->firstOrFail();
        $bendahara1 = Role::where('role_name', 'Bendahara 1')->firstOrFail();
        $bendahara2 = Role::where('role_name', 'Bendahara 2')->firstOrFail();
        $phm        = Role::where('role_name', 'Pengurus Harian Masjid')->firstOrFail();
        $panitia    = Role::where('role_name', 'Panitia Kegiatan Khusus')->firstOrFail();
        $sekretaris = Role::where('role_name', 'Sekretaris')->firstOrFail();

        // ── Administrator → semua permission ─────────────────────────────────────────
        $admin->permissions()->sync(
            Permission::pluck('id')
        );

        // ── Ketua DKM → dashboard + laporan keuangan ────────────────────────
        // VIEW_LAPORAN_KEUANGAN dipakai sebagai gate menu Beranda & Laporan
        $ketuaDkm->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_LAPORAN_KEUANGAN',
            ])->pluck('id')
        );

        // ── Bendahara 1 → keuangan penuh ─────────────────────────────────
        // Termasuk VIEW_LAPORAN_KEUANGAN → juga melihat menu Beranda
        $bendahara1->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_TRANSAKSI',          'CREATE_TRANSAKSI',          'EDIT_TRANSAKSI',          'DELETE_TRANSAKSI',
                'VIEW_KENCLENG',           'CREATE_KENCLENG',           'EDIT_KENCLENG',           'DELETE_KENCLENG',
                'VIEW_TRANSAKSI_KEGIATAN', 'CREATE_TRANSAKSI_KEGIATAN', 'EDIT_TRANSAKSI_KEGIATAN', 'DELETE_TRANSAKSI_KEGIATAN',
                'VIEW_KEGIATAN',           'CREATE_KEGIATAN',           'EDIT_KEGIATAN',           'DELETE_KEGIATAN',
                'VIEW_APPROVAL',           'CREATE_APPROVAL',           'EDIT_APPROVAL',           'DELETE_APPROVAL',
                'VIEW_JURNAL',             'CREATE_JURNAL',             'EDIT_JURNAL',             'DELETE_JURNAL',
                'VIEW_JURNAL_PEMBUKA',     'CREATE_JURNAL_PEMBUKA',     'EDIT_JURNAL_PEMBUKA',     'DELETE_JURNAL_PEMBUKA',
                'VIEW_JURNAL_PENYESUAIAN', 'CREATE_JURNAL_PENYESUAIAN', 'EDIT_JURNAL_PENYESUAIAN', 'DELETE_JURNAL_PENYESUAIAN',
                'VIEW_JURNAL_KOREKSI',     'CREATE_JURNAL_KOREKSI',     'EDIT_JURNAL_KOREKSI',     'DELETE_JURNAL_KOREKSI',
                'VIEW_JURNAL_PENUTUP',     'CREATE_JURNAL_PENUTUP',     'EDIT_JURNAL_PENUTUP',     'DELETE_JURNAL_PENUTUP',
                'VIEW_BUKU_BESAR',
                'VIEW_NERACA_SALDO',
                'VIEW_LAPORAN_KEUANGAN',
                'VIEW_COA',                'CREATE_COA',                'EDIT_COA',                'DELETE_COA',
                'VIEW_KATEGORI',           'CREATE_KATEGORI',           'EDIT_KATEGORI',           'DELETE_KATEGORI',
                'VIEW_ASET',               'CREATE_ASET',               'EDIT_ASET',               'DELETE_ASET',
                'VIEW_PEMASUKAN',          'CREATE_PEMASUKAN',          'EDIT_PEMASUKAN',
                'VIEW_PENGELUARAN',        'CREATE_PENGELUARAN',        'EDIT_PENGELUARAN',
            ])->pluck('id')
        );

        // ── Bendahara 2 → dashboard + pencatatan transaksi saja ─────────────────────
        // VIEW_LAPORAN_KEUANGAN → gate menu Beranda (Dashboard Operasional)
        $bendahara2->permissions()->sync(
            Permission::whereIn('permission_code', [
                // Beranda / Dashboard
                'VIEW_LAPORAN_KEUANGAN',
                // Pencatatan Transaksi — Transaksi Umum saja
                'VIEW_TRANSAKSI', 'CREATE_TRANSAKSI', 'EDIT_TRANSAKSI',
            ])->pluck('id')
        );

        // ── PHM → kencleng saja ───────────────────────────────────────────
        // Tidak punya VIEW_LAPORAN_KEUANGAN → tidak melihat menu Beranda
        $phm->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_KENCLENG', 'CREATE_KENCLENG', 'EDIT_KENCLENG',
            ])->pluck('id')
        );

        // ── Panitia Kegiatan Khusus → transaksi kegiatan saja ───────────────
        // Tidak punya VIEW_LAPORAN_KEUANGAN → tidak melihat menu Beranda
        // Tidak punya VIEW_TRANSAKSI → tidak melihat Pencatatan Transaksi
        $panitia->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_TRANSAKSI_KEGIATAN',
                'CREATE_TRANSAKSI_KEGIATAN',
                'EDIT_TRANSAKSI_KEGIATAN',
            ])->pluck('id')
        );

        // ── Sekretaris → aset saja ───────────────────────────────────────
        // Tidak punya VIEW_LAPORAN_KEUANGAN → tidak melihat menu Beranda
        // Tidak punya VIEW_TRANSAKSI → tidak melihat Pencatatan Transaksi
        $sekretaris->permissions()->sync(
            Permission::whereIn('permission_code', [
                'VIEW_ASET', 'CREATE_ASET', 'EDIT_ASET', 'DELETE_ASET',
            ])->pluck('id')
        );
    }
}
