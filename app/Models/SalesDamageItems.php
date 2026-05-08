<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDamageItems extends Model
{
    protected $table = 'sales_damage_items';
    protected $guarded = [];
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
