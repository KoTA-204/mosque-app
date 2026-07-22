<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Urutan dijaga sesuai dependensi:
     * 1) Autentikasi & otorisasi  -> Peran, HakAkses, Menu, Pengguna, HakAksesPeran
     * 2) Master data keuangan     -> KategoriAkun, Akun, Dompet, KategoriTransaksi, Periode, Aset
     * 3) Saldo awal               -> JurnalPembuka (butuh Periode + Akun)
     * 4) Data transaksional       -> Kegiatan, Transaksi, Kencleng, dst (butuh Pengguna)
     * 5) Jurnal umum              -> JurnalUmumSeeder (butuh semua transaksi + Akun + Periode)
     *
     * PeranPenggunaSeeder & MenuHakAksesSeeder DIHAPUS (tabel peran_pengguna / menu_hak_akses
     * tidak ada; relasi via pengguna.peran_id & menus.hak_akses_id).
     */
    public function run(): void
    {
        $this->call([
            // 1) Autentikasi & otorisasi
            PeranSeeder::class,
            HakAksesSeeder::class,
            MenuSeeder::class,
            PenggunaSeeder::class,
            HakAksesPeranSeeder::class,
            KategoriTransaksiSeeder::class,
            // DompetSeeder::class,
            KegiatanSeeder::class,
            // TransaksiSeeder::class,
            // KenclengSeeder::class,
            KenclengDetailSeeder::class,
            // BuktiTransaksiSeeder::class,
            KategoriAkunSeeder::class,
            AkunSeeder::class,
            // AsetSeeder::class,
            ContohKeuanganJuniSeeder::class,
            // PeriodeSeeder::class,
            // TransaksiKegiatanSeeder::class,
            // JurnalUmumSeeder::class,
            // JurnalLengkapSeeder::class,
        ]);
    }
}
