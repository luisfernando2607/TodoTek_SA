<?php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function paginate(array $filters, int $perPage = 15): mixed
    {
        return Product::with(['category', 'mainImage'])
            ->when(
                $search = $filters['search'] ?? null,
                fn($q, $v) => $q->where(function ($q) use ($v) {
                    $q->where('name', 'ilike', "%{$v}%")
                      ->orWhere('sku', 'ilike', "%{$v}%")
                      ->orWhere('description', 'ilike', "%{$v}%");
                })
            )
            ->when($filters['category_id'] ?? null, fn($q, $v) => $q->where('category_id', $v))
            ->when(isset($filters['active']), fn($q) => $q->where('active', $filters['active']))
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function create(array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($data, $images) {
            $product = Product::create($data);

            foreach ($images as $i => $file) {
                $path = $this->storeImage($file, $product->sku, $i);
                $product->images()->create([
                    'path'       => $path,
                    'disk'       => 'public',
                    'is_main'    => $i === 0,
                    'sort_order' => $i,
                ]);
            }

            return $product->load(['category', 'images', 'mainImage']);
        });
    }

    public function update(Product $product, array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($product, $data, $images) {
            $product->update($data);

            $lastSort = $product->images()->max('sort_order') ?? 0;

            foreach ($images as $i => $file) {
                $path = $this->storeImage($file, $product->sku, $lastSort + 1 + $i);
                $product->images()->create([
                    'path'       => $path,
                    'disk'       => 'public',
                    'is_main'    => false,
                    'sort_order' => $lastSort + 1 + $i,
                ]);
            }

            if (!$product->images()->where('is_main', true)->exists()) {
                $product->images()->first()?->update(['is_main' => true]);
            }

            return $product->load(['category', 'images', 'mainImage']);
        });
    }

    public function addImages(Product $product, array $images): Product
    {
        $lastSort = $product->images()->max('sort_order') ?? 0;

        foreach ($images as $i => $file) {
            $path = $this->storeImage($file, $product->sku, $lastSort + 1 + $i);
            $product->images()->create([
                'path'       => $path,
                'disk'       => 'public',
                'is_main'    => false,
                'sort_order' => $lastSort + 1 + $i,
            ]);
        }

        if (!$product->images()->where('is_main', true)->exists()) {
            $product->images()->first()?->update(['is_main' => true]);
        }

        return $product->fresh(['category', 'images', 'mainImage']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function storeImage(UploadedFile $file, string $sku, int $index): string
    {
        $safeSku = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sku);
        $ext     = $file->getClientOriginalExtension();
        $name    = "{$safeSku}-{$index}.{$ext}";
        return $file->storeAs('products', $name, 'public');
    }

    public function deleteImage(ProductImage $image): void
    {
        Storage::disk($image->disk)->delete($image->path);
        $image->delete();
    }

    public function setMainImage(Product $product, ProductImage $image): void
    {
        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_main' => false]);
            $image->update(['is_main' => true]);
        });
    }
}
