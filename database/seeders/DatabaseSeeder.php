<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Urutan dijaga sesuai dependensi:
     * 1) Autentikasi & otorisasi  -> Role, Permission, Menu, User, PermissionRole
     * 2) Master data keuangan     -> KategoriAkun, Akun, Dompet, KategoriTransaksi, Periode, Aset
     * 3) Saldo awal               -> JurnalPembuka (butuh Periode + Akun)
     * 4) Data transaksional       -> Kegiatan, Transaksi, Kencleng, dst (butuh User)
     * 5) Jurnal umum              -> JurnalUmumSeeder (butuh semua transaksi + Akun + Periode)
     *
     * RoleUserSeeder & MenuPermissionSeeder DIHAPUS (tabel role_user / menu_permission
     * tidak ada; relasi via users.role_id & menus.permission_id).
     */
    public function run(): void
    {
        $this->call([
            // 1) Autentikasi & otorisasi
            RoleSeeder::class,
            PermissionSeeder::class,
            MenuSeeder::class,
            UserSeeder::class,
            PermissionRoleSeeder::class,

            // 2) Master data keuangan
            KategoriAkunSeeder::class,
            AkunSeeder::class,
            DompetSeeder::class,
            KategoriTransaksiSeeder::class,
            PeriodeSeeder::class,
            AsetSeeder::class,

            // 3) Saldo awal
            JurnalPembukaSeeder::class,

            // 4) Data transaksional (butuh User & Kegiatan)
            KegiatanSeeder::class,
            TransaksiSeeder::class,          // infak kencleng + pencatatan bendahara
            TransaksiKegiatanSeeder::class,  // transaksi kegiatan khusus
            KenclengSeeder::class,
            KenclengDetailSeeder::class,
            BuktiTransaksiSeeder::class,

            // 5) Jurnal umum (memetakan transaksi MAPPED -> jurnal)
            JurnalUmumSeeder::class,
        ]);
    }
}
