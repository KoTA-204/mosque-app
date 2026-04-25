<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keuangan = Menu::create([
            'menu_name'=>'Keuangan',
            'route_name'=>null,
            'icon'=>'wallet',
            'sort_order'=>2
        ]);

        Menu::create([
            'menu_name'=>'Dashboard',
            'route_name'=>'dashboard',
            'icon'=>'home',
            'sort_order'=>1
        ]);

        Menu::create([
            'menu_name'=>'Pemasukan',
            'route_name'=>'pemasukan.index',
            'icon'=>'arrow-down',
            'parent_id'=>$keuangan->id,
            'sort_order'=>1
        ]);

        Menu::create([
            'menu_name'=>'Pengeluaran',
            'route_name'=>'pengeluaran.index',
            'icon'=>'arrow-up',
            'parent_id'=>$keuangan->id,
            'sort_order'=>2
        ]);

        Menu::create([
            'menu_name'=>'Kencleng',
            'route_name'=>'kencleng.index',
            'icon'=>'box',
            'parent_id'=>$keuangan->id,
            'sort_order'=>3
        ]);

        Menu::create([
            'menu_name'=>'Dana Khusus',
            'route_name'=>'danakhusus.index',
            'icon'=>'gift',
            'parent_id'=>$keuangan->id,
            'sort_order'=>4
        ]);
    }
}
