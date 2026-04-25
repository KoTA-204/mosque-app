<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'=>'Admin',
            'email'=>'admin@mail.com',
            'password'=>Hash::make('password'),
            'status'=>'active'
        ]);

        User::create([
            'name'=>'Bendahara 1',
            'email'=>'bendahara1@mail.com',
            'password'=>Hash::make('password'),
            'status'=>'active'
        ]);

        User::create([
            'name'=>'Bendahara 2',
            'email'=>'bendahara2@mail.com',
            'password'=>Hash::make('password'),
            'status'=>'active'
        ]);
    }
}
