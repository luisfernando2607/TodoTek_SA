<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('clients')->insert([
            [
                'name' => 'Juan Pérez',
                'identification' => '0912345678',
                'email' => 'juan@email.com',
                'phone' => '0999999999',
                'address' => 'Guayaquil, Ecuador',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'María López',
                'identification' => '0923456789',
                'email' => 'maria@email.com',
                'phone' => '0988888888',
                'address' => 'Quito, Ecuador',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}