<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class purchase_items extends Model
{
    use HasFactory;

    // Explicitly define the table name if it's not the plural form
    protected $table = 'purchase_items';

    // Mass assignment protection - Add all columns you want to save
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    /**
     * Relationship: Each item belongs to a purchase header.
     */
    public function purchase()
    {
        return $this->belongsTo(purchases::class, 'purchase_id');
    }

    /**
     * Relationship: Each item belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
