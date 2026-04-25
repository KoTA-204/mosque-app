<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = 1;
        $bendahara1 = 2;
        $bendahara2 = 3;

        foreach(range(1,10) as $perm){
            DB::table('permission_role')->insert([
                'role_id'=>$superAdmin,
                'permission_id'=>$perm
            ]);
        }

        DB::table('permission_role')->insert([
            ['role_id'=>$bendahara1,'permission_id'=>1],
            ['role_id'=>$bendahara1,'permission_id'=>2],
            ['role_id'=>$bendahara1,'permission_id'=>3],
            ['role_id'=>$bendahara1,'permission_id'=>4],
            ['role_id'=>$bendahara1,'permission_id'=>5],
            ['role_id'=>$bendahara1,'permission_id'=>9],
        ]);

        DB::table('permission_role')->insert([
            ['role_id'=>$bendahara2,'permission_id'=>3],
            ['role_id'=>$bendahara2,'permission_id'=>4],
            ['role_id'=>$bendahara2,'permission_id'=>5],
        ]);
    }
}
