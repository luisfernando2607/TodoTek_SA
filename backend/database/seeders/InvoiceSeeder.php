<?php
namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Client::all();
        $vendedor = User::first();

        if ($clientes->isEmpty() || !$vendedor) {
            $this->command->warn('Ejecuta primero los seeders de Client y User.');
            return;
        }

        $productos = Product::all();

        $facturas = [
            [
                'client_id' => $clientes->first()->id,
                'items' => [
                    ['sku' => 'ELEC-001', 'qty' => 2],
                    ['sku' => 'ELEC-002', 'qty' => 5],
                ],
                'issued_at' => '2026-05-10 09:30:00',
            ],
            [
                'client_id' => $clientes->skip(1)->first()->id,
                'items' => [
                    ['sku' => 'ROPA-001', 'qty' => 10],
                    ['sku' => 'ROPA-002', 'qty' => 3],
                ],
                'issued_at' => '2026-05-12 14:15:00',
            ],
            [
                'client_id' => $clientes->skip(2)->first()->id,
                'items' => [
                    ['sku' => 'ALIM-001', 'qty' => 5],
                    ['sku' => 'ALIM-002', 'qty' => 20],
                    ['sku' => 'OFIC-001', 'qty' => 10],
                ],
                'issued_at' => '2026-05-15 10:00:00',
            ],
            [
                'client_id' => $clientes->skip(3)->first()->id,
                'items' => [
                    ['sku' => 'ELEC-004', 'qty' => 1],
                    ['sku' => 'ELEC-005', 'qty' => 3],
                ],
                'issued_at' => '2026-05-18 16:45:00',
            ],
            [
                'client_id' => $clientes->skip(4)->first()->id,
                'items' => [
                    ['sku' => 'OFIC-002', 'qty' => 50],
                    ['sku' => 'ELEC-003', 'qty' => 5],
                ],
                'issued_at' => '2026-05-20 08:00:00',
            ],
        ];

        foreach ($facturas as $data) {
            $builtItems = [];
            $subtotalTotal = 0;
            $taxTotal = 0;

            foreach ($data['items'] as $item) {
                $producto = $productos->firstWhere('sku', $item['sku']);
                if (!$producto) continue;

                $subtotal  = round($producto->price * $item['qty'], 2);
                $taxAmount = round($subtotal * $producto->tax_rate / 100, 2);

                $subtotalTotal += $subtotal;
                $taxTotal      += $taxAmount;

                $builtItems[] = [
                    'product_id'   => $producto->id,
                    'product_name' => $producto->name,
                    'unit_price'   => $producto->price,
                    'tax_rate'     => $producto->tax_rate,
                    'quantity'     => $item['qty'],
                    'subtotal'     => $subtotal,
                    'tax_amount'   => $taxAmount,
                    'total'        => round($subtotal + $taxAmount, 2),
                ];
            }

            if (empty($builtItems)) continue;

            $lastNumber = Invoice::where('invoice_number', 'like', 'FAC-%')
                ->orderByDesc('id')
                ->value('invoice_number');
            $next = $lastNumber ? (int) substr($lastNumber, 4) + 1 : 1;
            $invoiceNumber = 'FAC-' . str_pad($next, 5, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'client_id'      => $data['client_id'],
                'user_id'        => $vendedor->id,
                'invoice_number' => $invoiceNumber,
                'subtotal'       => round($subtotalTotal, 2),
                'tax_amount'     => round($taxTotal, 2),
                'total'          => round($subtotalTotal + $taxTotal, 2),
                'status'         => 'issued',
                'issued_at'      => $data['issued_at'],
                'created_at'     => $data['issued_at'],
                'updated_at'     => $data['issued_at'],
            ]);

            $invoice->items()->createMany($builtItems);
        }
    }
}
