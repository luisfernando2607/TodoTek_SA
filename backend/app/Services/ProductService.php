<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\StockService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(private readonly StockService $stockService) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'images'])
            ->when($filters['search'] ?? null, fn($q, $v) =>
                $q->where('name', 'ilike', "%{$v}%")
                  ->orWhere('sku', 'ilike', "%{$v}%")
            )
            ->when($filters['category_id'] ?? null, fn($q, $v) =>
                $q->where('category_id', $v)
            )
            ->when(isset($filters['active']), fn($q) =>
                $q->where('active', $filters['active'])
            )
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function create(array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($data, $images) {
            $product = Product::create($data);

            if (count($images)) {
                $this->storeImages($product, $images);
            }

            if (($data['stock'] ?? 0) > 0) {
                $this->stockService->move(
                    $product,
                    'entry',
                    $data['stock'],
                    'Stock inicial al crear producto'
                );
            }

            return $product->load(['category', 'images']);
        });
    }

    public function update(Product $product, array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($product, $data, $images) {
            $product->update($data);

            if (count($images)) {
                $this->storeImages($product, $images);
            }

            return $product->fresh(['category', 'images']);
        });
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function storeImages(Product $product, array $files): void
    {
        $hasMain  = $product->images()->where('is_main', true)->exists();
        $sortBase = $product->images()->max('sort_order') ?? 0;

        foreach ($files as $i => $file) {
            $path = $file->store("products/{$product->id}", 'public');

            $product->images()->create([
                'path'       => $path,
                'disk'       => 'public',
                'is_main'    => !$hasMain && $i === 0,
                'sort_order' => $sortBase + $i + 1,
            ]);

            if (!$hasMain && $i === 0) {
                $hasMain = true;
            }
        }
    }

    public function deleteImage(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {
            $wasMain = $image->is_main;

            Storage::disk($image->disk)->delete($image->path);
            $image->delete();

            if ($wasMain) {
                ProductImage::where('product_id', $image->product_id)
                    ->orderBy('sort_order')
                    ->first()
                    ?->update(['is_main' => true]);
            }
        });
    }

    public function setMainImage(Product $product, ProductImage $image): void
    {
        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_main' => false]);
            $image->update(['is_main' => true]);
        });
    }

    public function priceBreakdown(Product $product): array
    {
        $tax   = round($product->price * $product->tax_rate / 100, 2);
        $total = round($product->price + $tax, 2);

        return [
            'price'    => $product->price,
            'tax_rate' => $product->tax_rate,
            'tax'      => $tax,
            'total'    => $total,
        ];
    }
}
