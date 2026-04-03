<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;
    // Explicitly define table name if it's not the plural form of the model
    protected $table = 'stock_movements';

    // Allow these fields to be filled via the create() method
    protected $fillable = [
        'product_id',
        'quantity',
        'balance_before',
        'balance_after',
        'type',
        'reference_no',
        'user_id',
        'remarks'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
