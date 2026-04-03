<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SalesReturnItem extends Model
{
    use HasFactory;
    protected $table = 'sales_return_items';
    protected $fillable = [
        'sales_return_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    /**
     * Relationship to the Parent Return Header
     */
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    /**
     * Relationship to the Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
