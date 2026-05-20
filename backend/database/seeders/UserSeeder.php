<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Administrador',
                'email' => 'admin@todostock.com',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}