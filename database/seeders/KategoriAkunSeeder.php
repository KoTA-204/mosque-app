<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriAkun;

class KategoriAkunSeeder extends Seeder
{
    /**
     * Kategori akun = Level 1 CoA (grup utama).
     */
    public function run(): void
    {
        DB::table('kategori_akun')->insertOrIgnore([
            [
                'kode_kategori' => '1',
                'nama_kategori' => 'Aset',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kode_kategori' => '2',
                'nama_kategori' => 'Liabilitas',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kode_kategori' => '3',
                'nama_kategori' => 'Ekuitas',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kode_kategori' => '4',
                'nama_kategori' => 'Pendapatan',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kode_kategori' => '5',
                'nama_kategori' => 'Beban',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
