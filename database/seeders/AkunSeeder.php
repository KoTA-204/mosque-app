<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('akun')->insert([

            // =========================
            // HEADER ACCOUNT
            // =========================

            [
                'kategori_akun_id' => 1,
                'parent_id' => null,
                'kode_akun' => '1-1000',
                'nama_akun' => 'Kas & Bank',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 1,
                'parent_id' => null,
                'kode_akun' => '1-2000',
                'nama_akun' => 'Aset Tetap',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 4,
                'parent_id' => null,
                'kode_akun' => '4-1000',
                'nama_akun' => 'Pendapatan Operasional',
                'saldo_normal' => 'KREDIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 5,
                'parent_id' => null,
                'kode_akun' => '5-1000',
                'nama_akun' => 'Beban Operasional',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =========================
            // DETAIL ACCOUNT
            // =========================

            [
                'kategori_akun_id' => 1,
                'parent_id' => 1,
                'kode_akun' => '1-1100',
                'nama_akun' => 'Kas Masjid',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 1,
                'parent_id' => 1,
                'kode_akun' => '1-1200',
                'nama_akun' => 'Bank BSI',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 1,
                'parent_id' => 2,
                'kode_akun' => '1-2100',
                'nama_akun' => 'Peralatan Masjid',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 4,
                'parent_id' => 3,
                'kode_akun' => '4-1100',
                'nama_akun' => 'Pendapatan Infaq',
                'saldo_normal' => 'KREDIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 4,
                'parent_id' => 3,
                'kode_akun' => '4-1200',
                'nama_akun' => 'Pendapatan Donasi',
                'saldo_normal' => 'KREDIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'kategori_akun_id' => 5,
                'parent_id' => 4,
                'kode_akun' => '5-1100',
                'nama_akun' => 'Beban Listrik',
                'saldo_normal' => 'DEBIT',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
