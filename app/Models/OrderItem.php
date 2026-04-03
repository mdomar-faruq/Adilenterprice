<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = ['order_id', 'product_id', 'qty'];

    // Link back to the main Order
    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

    // Get the product details for this line item
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
