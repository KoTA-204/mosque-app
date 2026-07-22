<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Peran;
use App\Models\HakAkses;

class HakAksesPeranSeeder extends Seeder
{
    public function run(): void
    {
        $admin      = Peran::where('nama_peran', 'Administrator')->firstOrFail();
        $ketuaDkm   = Peran::where('nama_peran', 'Ketua DKM')->firstOrFail();
        $bendahara1 = Peran::where('nama_peran', 'Bendahara 1')->firstOrFail();
        $bendahara2 = Peran::where('nama_peran', 'Bendahara 2')->firstOrFail();
        $phm        = Peran::where('nama_peran', 'Pengurus Harian Masjid')->firstOrFail();
        $panitia    = Peran::where('nama_peran', 'Panitia Kegiatan Khusus')->firstOrFail();
        $sekretaris = Peran::where('nama_peran', 'Sekretaris')->firstOrFail();

        // ── Administrator → akses ke SELURUH tampilan/menu, TAPI aksi tulis pada modul
        //    fungsional tetap dijaga peran yang bertanggung jawab (segregation of duties).
        //    Admin TETAP punya VIEW_ASET & VIEW_JURNAL (bisa buka halaman + modal),
        //    tapi TIDAK punya izin simpan/ubah/hapus Aset & Jurnal Umum:
        //      - Aksi Aset (tambah/ubah/hapus/toggle)   → hanya Sekretaris.
        //      - Aksi Jurnal Umum (post/bulk-post/hapus) → hanya Bendahara 1.
        $aksiDikecualikanDariAdmin = [
            'CREATE_ASET', 'EDIT_ASET', 'DELETE_ASET',
            'CREATE_JURNAL', 'EDIT_JURNAL', 'DELETE_JURNAL',
        ];
        $admin->hak_akses()->sync(
            HakAkses::whereNotIn('kode_hak_akses', $aksiDikecualikanDariAdmin)->pluck('id')
        );

        // ── Ketua DKM → dashboard + laporan keuangan ────────────────────────
        // VIEW_LAPORAN_KEUANGAN dipakai sebagai gate menu Beranda & Laporan
        $ketuaDkm->hak_akses()->sync(
            HakAkses::whereIn('kode_hak_akses', [
                'VIEW_LAPORAN_KEUANGAN',
            ])->pluck('id')
        );

        // ── Bendahara 1 → keuangan penuh ─────────────────────────────────
        // Termasuk VIEW_LAPORAN_KEUANGAN → juga melihat menu Beranda
        $bendahara1->hak_akses()->sync(
            HakAkses::whereIn('kode_hak_akses', [
                'VIEW_TRANSAKSI',          'CREATE_TRANSAKSI',          'EDIT_TRANSAKSI',          'DELETE_TRANSAKSI',
                'VIEW_KENCLENG',           'CREATE_KENCLENG',           'EDIT_KENCLENG',           'DELETE_KENCLENG',
                'VIEW_TRANSAKSI_KEGIATAN', 'CREATE_TRANSAKSI_KEGIATAN', 'EDIT_TRANSAKSI_KEGIATAN', 'DELETE_TRANSAKSI_KEGIATAN',
                // Kegiatan Khusus (event) dicabut dari Bendahara 1 → menu Kegiatan Khusus di Data Induk tidak muncul untuk Bendahara 1 (hanya CoA + Kategori)
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
                // Aset dikelola khusus oleh Sekretaris — Bendahara 1 TIDAK punya akses menu Aset
                'VIEW_PEMASUKAN',          'CREATE_PEMASUKAN',          'EDIT_PEMASUKAN',
                'VIEW_PENGELUARAN',        'CREATE_PENGELUARAN',        'EDIT_PENGELUARAN',
            ])->pluck('id')
        );

        // ── Bendahara 2 → dashboard + pencatatan transaksi saja ─────────────────────
        // VIEW_LAPORAN_KEUANGAN → gate menu Beranda (Dashboard Operasional)
        $bendahara2->hak_akses()->sync(
            HakAkses::whereIn('kode_hak_akses', [
                // Beranda / Dashboard
                'VIEW_LAPORAN_KEUANGAN',
                // Pencatatan Transaksi — Transaksi Umum saja
                'VIEW_TRANSAKSI', 'CREATE_TRANSAKSI', 'EDIT_TRANSAKSI',
            ])->pluck('id')
        );

        // ── PHM → kencleng saja ───────────────────────────────────────────
        // Tidak punya VIEW_LAPORAN_KEUANGAN → tidak melihat menu Beranda
        $phm->hak_akses()->sync(
            HakAkses::whereIn('kode_hak_akses', [
                'VIEW_KENCLENG', 'CREATE_KENCLENG', 'EDIT_KENCLENG',
            ])->pluck('id')
        );

        // ── Panitia Kegiatan Khusus → transaksi kegiatan saja ───────────────
        // Tidak punya VIEW_LAPORAN_KEUANGAN → tidak melihat menu Beranda
        // Tidak punya VIEW_TRANSAKSI → tidak melihat Pencatatan Transaksi
        $panitia->hak_akses()->sync(
            HakAkses::whereIn('kode_hak_akses', [
                'VIEW_TRANSAKSI_KEGIATAN',
                'CREATE_TRANSAKSI_KEGIATAN',
                'EDIT_TRANSAKSI_KEGIATAN',
            ])->pluck('id')
        );

        // ── Sekretaris → aset saja ───────────────────────────────────────
        // Tidak punya VIEW_LAPORAN_KEUANGAN → tidak melihat menu Beranda
        // Tidak punya VIEW_TRANSAKSI → tidak melihat Pencatatan Transaksi
        $sekretaris->hak_akses()->sync(
            HakAkses::whereIn('kode_hak_akses', [
                'VIEW_ASET', 'CREATE_ASET', 'EDIT_ASET', 'DELETE_ASET',
            ])->pluck('id')
        );
    }
}
