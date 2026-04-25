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
        'purchase_price',
        'percent',
        'sale_price',
        'opening_stock',
        'stock',
        'damage_stock',
        'company_id',
        'unit_id',
        'user_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
