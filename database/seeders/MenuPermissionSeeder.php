<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu_permission')->insert([
            ['menu_id'=>3,'permission_id'=>1],
            ['menu_id'=>4,'permission_id'=>3],
            ['menu_id'=>5,'permission_id'=>5],
            ['menu_id'=>6,'permission_id'=>7],
        ]);
    }
}
