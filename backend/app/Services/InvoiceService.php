<?php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Crear una factura completa con sus ítems y descontar stock automáticamente.
     *
     * @param  int    $clientId
     * @param  array  $items    [['product_id' => X, 'quantity' => Y], ...]
     * @param  string $notes
     * @return Invoice
     *
     * @throws \RuntimeException si stock insuficiente en algún producto
     */
    public function create(int $clientId, array $items, string $notes = ''): Invoice
    {
        return DB::transaction(function () use ($clientId, $items, $notes) {

            $builtItems   = [];
            $subtotalTotal = 0;
            $taxTotal      = 0;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Validar stock suficiente
                if ($product->stock < $item['quantity']) {
                    throw new \RuntimeException(
                        "Stock insuficiente para \"{$product->name}\". "
                        . "Disponible: {$product->stock}, solicitado: {$item['quantity']}."
                    );
                }

                $subtotal   = round($product->price * $item['quantity'], 2);
                $taxAmount  = round($subtotal * $product->tax_rate / 100, 2);
                $total      = $subtotal + $taxAmount;

                $subtotalTotal += $subtotal;
                $taxTotal      += $taxAmount;

                $builtItems[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,      // Snapshot
                    'unit_price'   => $product->price,     // Snapshot
                    'tax_rate'     => $product->tax_rate,  // Snapshot
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $subtotal,
                    'tax_amount'   => $taxAmount,
                    'total'        => $total,
                ];

                // Descontar stock
                $product->decrement('stock', $item['quantity']);
            }

            // Crear cabecera de la factura
            $invoice = Invoice::create([
                'client_id'      => $clientId,
                'user_id'        => Auth::id(),
                'invoice_number' => $this->nextInvoiceNumber(),
                'subtotal'       => round($subtotalTotal, 2),
                'tax_amount'     => round($taxTotal, 2),
                'total'          => round($subtotalTotal + $taxTotal, 2),
                'status'         => 'issued',
                'notes'          => $notes,
                'issued_at'      => now(),
            ]);

            // Insertar ítems
            $invoice->items()->createMany($builtItems);

            return $invoice->load(['client', 'items', 'user']);
        });
    }

    /**
     * Cancelar una factura y revertir el stock de sus ítems.
     */
    public function cancel(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'cancelled') {
            throw new \RuntimeException('La factura ya está cancelada.');
        }

        return DB::transaction(function () use ($invoice) {
            // Revertir stock
            foreach ($invoice->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }

            $invoice->update(['status' => 'cancelled']);
            return $invoice->fresh(['client', 'items']);
        });
    }

    /**
     * Generar el siguiente número de factura secuencial: FAC-00001
     */
    private function nextInvoiceNumber(): string
    {
        $last = Invoice::where('invoice_number', 'like', 'FAC-%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = $last ? (int) substr($last, 4) + 1 : 1;

        return 'FAC-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
