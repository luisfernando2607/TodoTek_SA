<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electrónica',   'slug' => 'electronica',   'description' => 'Equipos y dispositivos electrónicos'],
            ['name' => 'Ropa',          'slug' => 'ropa',          'description' => 'Prendas de vestir'],
            ['name' => 'Alimentos',     'slug' => 'alimentos',     'description' => 'Productos alimenticios'],
            ['name' => 'Herramientas',  'slug' => 'herramientas',  'description' => 'Herramientas y ferretería'],
            ['name' => 'Oficina',       'slug' => 'oficina',       'description' => 'Artículos de oficina'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->upsert(
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()]),
                ['slug'],
                ['name', 'description', 'updated_at']
            );
        }
    }
}
