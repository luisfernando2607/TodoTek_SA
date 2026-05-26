<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'sku', 'description',
        'price', 'tax_rate', 'stock', 'stock_min', 'active'
    ];

    protected $casts = ['active' => 'boolean', 'price' => 'float', 'tax_rate' => 'float'];

    protected $appends = ['price_with_tax', 'tax_amount', 'is_low_stock'];

    protected function priceWithTax(): Attribute
    {
        return Attribute::make(get: fn() => round($this->price * (1 + $this->tax_rate / 100), 2));
    }

    protected function taxAmount(): Attribute
    {
        return Attribute::make(get: fn() => round($this->price * $this->tax_rate / 100, 2));
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::make(get: fn() => $this->stock <= $this->stock_min);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
