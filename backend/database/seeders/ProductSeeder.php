<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catId = fn(string $slug) => DB::table('categories')->where('slug', $slug)->value('id');

        $products = [
            // Electrónica
            ['category_id' => $catId('electronica'), 'name' => 'Laptop HP 15"',       'sku' => 'ELEC-001', 'price' => 850.00,  'tax_rate' => 15, 'stock' => 20, 'stock_min' => 3],
            ['category_id' => $catId('electronica'), 'name' => 'Mouse Inalámbrico',    'sku' => 'ELEC-002', 'price' => 25.00,   'tax_rate' => 15, 'stock' => 50, 'stock_min' => 10],
            ['category_id' => $catId('electronica'), 'name' => 'Teclado Mecánico',     'sku' => 'ELEC-003', 'price' => 65.00,   'tax_rate' => 15, 'stock' => 30, 'stock_min' => 5],
            ['category_id' => $catId('electronica'), 'name' => 'Monitor 24" Full HD',  'sku' => 'ELEC-004', 'price' => 320.00,  'tax_rate' => 15, 'stock' => 15, 'stock_min' => 2],
            ['category_id' => $catId('electronica'), 'name' => 'Auriculares Bluetooth','sku' => 'ELEC-005', 'price' => 45.00,   'tax_rate' => 15, 'stock' => 40, 'stock_min' => 8],
            // Ropa
            ['category_id' => $catId('ropa'), 'name' => 'Camiseta Polo',               'sku' => 'ROPA-001', 'price' => 18.00,   'tax_rate' => 12, 'stock' => 100,'stock_min' => 20],
            ['category_id' => $catId('ropa'), 'name' => 'Pantalón Jean',               'sku' => 'ROPA-002', 'price' => 35.00,   'tax_rate' => 12, 'stock' => 80, 'stock_min' => 15],
            // Alimentos
            ['category_id' => $catId('alimentos'), 'name' => 'Arroz 25kg',            'sku' => 'ALIM-001', 'price' => 22.00,   'tax_rate' => 0,  'stock' => 200,'stock_min' => 50],
            ['category_id' => $catId('alimentos'), 'name' => 'Aceite 1L',             'sku' => 'ALIM-002', 'price' => 3.50,    'tax_rate' => 0,  'stock' => 150,'stock_min' => 30],
            // Oficina
            ['category_id' => $catId('oficina'), 'name' => 'Resma de Papel A4',       'sku' => 'OFIC-001', 'price' => 4.50,    'tax_rate' => 12, 'stock' => 300,'stock_min' => 50],
            ['category_id' => $catId('oficina'), 'name' => 'Bolígrafos x12',          'sku' => 'OFIC-002', 'price' => 2.80,    'tax_rate' => 12, 'stock' => 200,'stock_min' => 40],
        ];

        foreach ($products as $product) {
            DB::table('products')->upsert(
                array_merge($product, ['description' => null, 'active' => true, 'created_at' => now(), 'updated_at' => now()]),
                ['sku'],
                ['name', 'price', 'tax_rate', 'stock', 'stock_min', 'updated_at']
            );
        }
    }
}
