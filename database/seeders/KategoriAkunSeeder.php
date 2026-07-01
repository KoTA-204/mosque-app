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
        $kategori = [
            ['kode_kategori' => '1', 'nama_kategori' => 'Aset'],
            ['kode_kategori' => '2', 'nama_kategori' => 'Liabilitas'],
            ['kode_kategori' => '3', 'nama_kategori' => 'Aset Neto'],
            ['kode_kategori' => '4', 'nama_kategori' => 'Pendapatan'],
            ['kode_kategori' => '5', 'nama_kategori' => 'Beban'],
        ];

        foreach ($kategori as $k) {
            KategoriAkun::create([
                'kode_kategori' => $k['kode_kategori'],
                'nama_kategori' => $k['nama_kategori'],
                'status'        => true,
            ]);
        }
    }
}
