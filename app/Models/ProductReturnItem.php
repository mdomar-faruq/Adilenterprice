<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReturnItem extends Model
{
    protected $fillable = [
        'product_return_id',
        'product_id',
        'good_qty',
        'damage_qty',
        'unit_price',
        'subtotal'
    ];

    /**
     * Relationship to the main Return Header
     */
    public function returnHeader(): BelongsTo
    {
        return $this->belongsTo(ProductReturn::class, 'product_return_id');
    }

    /**
     * Relationship to the Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
