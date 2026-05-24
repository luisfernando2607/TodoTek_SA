<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'client_id', 'user_id', 'invoice_number',
        'subtotal', 'tax_amount', 'total',
        'status', 'notes', 'issued_at'
    ];

    protected $casts = ['issued_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
