<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category_id'=>1,'name'=>'Laptop HP 15"',        'sku'=>'ELEC-001','price'=>850.00, 'tax_rate'=>15,'stock'=>20,'stock_min'=>3],
            ['category_id'=>1,'name'=>'Mouse Inalámbrico',    'sku'=>'ELEC-002','price'=>25.00,  'tax_rate'=>15,'stock'=>50,'stock_min'=>10],
            ['category_id'=>1,'name'=>'Teclado Mecánico',     'sku'=>'ELEC-003','price'=>75.00,  'tax_rate'=>15,'stock'=>30,'stock_min'=>5],
            ['category_id'=>1,'name'=>'Monitor 24"',          'sku'=>'ELEC-004','price'=>320.00, 'tax_rate'=>15,'stock'=>15,'stock_min'=>2],
            ['category_id'=>2,'name'=>'Camiseta Polo',        'sku'=>'ROPA-001','price'=>18.00,  'tax_rate'=>0, 'stock'=>100,'stock_min'=>20],
            ['category_id'=>2,'name'=>'Pantalón Jean',        'sku'=>'ROPA-002','price'=>45.00,  'tax_rate'=>0, 'stock'=>60, 'stock_min'=>10],
            ['category_id'=>3,'name'=>'Arroz 5kg',            'sku'=>'ALIM-001','price'=>5.50,   'tax_rate'=>0, 'stock'=>200,'stock_min'=>50],
            ['category_id'=>3,'name'=>'Aceite 1L',            'sku'=>'ALIM-002','price'=>3.20,   'tax_rate'=>0, 'stock'=>150,'stock_min'=>30],
            ['category_id'=>4,'name'=>'Taladro Percutor',     'sku'=>'FERR-001','price'=>95.00,  'tax_rate'=>15,'stock'=>12, 'stock_min'=>2],
            ['category_id'=>4,'name'=>'Set Destornilladores', 'sku'=>'FERR-002','price'=>22.00,  'tax_rate'=>15,'stock'=>40, 'stock_min'=>8],
            ['category_id'=>5,'name'=>'Resma Papel A4',       'sku'=>'OFIC-001','price'=>4.80,   'tax_rate'=>12,'stock'=>300,'stock_min'=>50],
            ['category_id'=>5,'name'=>'Carpeta Archivadora',  'sku'=>'OFIC-002','price'=>3.50,   'tax_rate'=>12,'stock'=>80, 'stock_min'=>15],
        ];

        foreach ($products as $p) {
            Product::create(array_merge($p, ['description' => 'Descripción del producto '.$p['sku']]));
        }
    }
}
