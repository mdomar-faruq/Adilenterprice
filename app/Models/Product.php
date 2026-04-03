<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'name',
        'unit_id',
        'purchase_price',
        'percent',
        'sale_price',
        'stock',
        'user_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
