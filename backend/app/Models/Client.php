<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'identification', 'email', 'phone', 'address', 'active'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
