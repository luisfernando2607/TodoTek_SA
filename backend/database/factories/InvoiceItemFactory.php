<?php
namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();
        $qty     = fake()->numberBetween(1, 10);
        $price   = $product?->price ?? fake()->randomFloat(2, 5, 500);
        $taxRate = $product?->tax_rate ?? 15;
        $subtotal = round($price * $qty, 2);
        $taxAmt   = round($subtotal * $taxRate / 100, 2);

        return [
            'invoice_id'   => Invoice::inRandomOrder()->first()?->id ?? 1,
            'product_id'   => $product?->id ?? 1,
            'product_name' => $product?->name ?? fake()->word(),
            'unit_price'   => $price,
            'tax_rate'     => $taxRate,
            'quantity'     => $qty,
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmt,
            'total'        => round($subtotal + $taxAmt, 2),
        ];
    }
}
