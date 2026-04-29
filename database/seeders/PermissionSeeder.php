<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [

            ['permission_code'=>'VIEW_PEMASUKAN','permission_name'=>'View Income','module'=>'pemasukan','action'=>'view'],
            ['permission_code'=>'CREATE_PEMASUKAN','permission_name'=>'Create Income','module'=>'pemasukan','action'=>'create'],

            ['permission_code'=>'VIEW_PENGELUARAN','permission_name'=>'View Expense','module'=>'pengeluaran','action'=>'view'],
            ['permission_code'=>'CREATE_PENGELUARAN','permission_name'=>'Create Expense','module'=>'pengeluaran','action'=>'create'],

            ['permission_code'=>'VIEW_KENCLENG','permission_name'=>'View Kencleng','module'=>'kencleng','action'=>'view'],
            ['permission_code'=>'CREATE_KENCLENG','permission_name'=>'Create Kencleng','module'=>'kencleng','action'=>'create'],

            ['permission_code'=>'VIEW_DANA_KHUSUS','permission_name'=>'View Special Fund','module'=>'dana_khusus','action'=>'view'],
            ['permission_code'=>'CREATE_DANA_KHUSUS','permission_name'=>'Create Special Fund','module'=>'dana_khusus','action'=>'create'],

            ['permission_code'=>'VIEW_LAPORAN','permission_name'=>'View Reports','module'=>'laporan','action'=>'view'],

            ['permission_code'=>'MANAGE_USERS','permission_name'=>'Manage Users','module'=>'users','action'=>'manage'],
        ];

        foreach ($data as $item) {
            Permission::create($item);
        }
    }
}
