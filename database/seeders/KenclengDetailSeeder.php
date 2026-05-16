<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KenclengDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kencleng_detail')->insert([
            [
                'kencleng_id' => 1,
                'pecahan' => 100000,
                'jumlah_pecahan' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kencleng_id' => 1,
                'pecahan' => 50000,
                'jumlah_pecahan' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kencleng_id' => 1,
                'pecahan' => 20000,
                'jumlah_pecahan' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
