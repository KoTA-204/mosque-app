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
            KategoriTransaksiSeeder::class,
            DompetSeeder::class,
            KegiatanSeeder::class,
            TransaksiSeeder::class,
            KenclengSeeder::class,
            KenclengDetailSeeder::class,
            BuktiTransaksiSeeder::class,
            KategoriAkunSeeder::class,
            AkunSeeder::class,
            AsetSeeder::class,
            PeriodeSeeder::class,
            TransaksiKegiatanSeeder::class,
            JurnalUmumSeeder::class,
            JurnalLengkapSeeder::class,
        ]);
    }
}
