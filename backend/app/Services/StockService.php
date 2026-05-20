<?php
namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Registrar un movimiento de stock.
     *
     * @param  Product $product
     * @param  string  $type      'entry' | 'exit' | 'adjustment'
     * @param  int     $quantity  Siempre positivo; la dirección la define $type
     * @param  string  $reason    Motivo del movimiento
     * @return array   ['movement' => StockMovement, 'low_stock' => bool]
     *
     * @throws \RuntimeException si la salida deja el stock negativo
     */
    public function move(Product $product, string $type, int $quantity, string $reason = ''): array
    {
        return DB::transaction(function () use ($product, $type, $quantity, $reason) {

            $stockBefore = $product->stock;

            // Calcular stock resultante según el tipo
            $stockAfter = match ($type) {
                'entry'      => $stockBefore + $quantity,
                'exit'       => $stockBefore - $quantity,
                'adjustment' => $quantity,               // Ajuste: valor absoluto
                default      => throw new \InvalidArgumentException("Tipo de movimiento inválido: {$type}"),
            };

            if ($stockAfter < 0) {
                throw new \RuntimeException(
                    "Stock insuficiente. Stock actual: {$stockBefore}, intentando retirar: {$quantity}."
                );
            }

            // Actualizar stock en el producto
            $product->update(['stock' => $stockAfter]);

            // Registrar el movimiento (auditoría)
            $movement = StockMovement::create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'type'         => $type,
                'quantity'     => $quantity,
                'stock_before' => $stockBefore,
                'stock_after'  => $stockAfter,
                'reason'       => $reason,
            ]);

            return [
                'movement'  => $movement->load('user'),
                'low_stock' => $stockAfter <= $product->stock_min,
                'stock'     => $stockAfter,
            ];
        });
    }
}
