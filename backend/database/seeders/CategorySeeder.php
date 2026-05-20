<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'Electrónica',
                'slug' => 'electronica',
                'description' => 'Productos electrónicos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ropa',
                'slug' => 'ropa',
                'description' => 'Prendas de vestir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Alimentos',
                'slug' => 'alimentos',
                'description' => 'Productos alimenticios',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ferretería',
                'slug' => 'ferreteria',
                'description' => 'Herramientas y accesorios',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Oficina',
                'slug' => 'oficina',
                'description' => 'Productos de oficina',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}