<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';
    protected $fillable = ['customer_id', 'order_date', 'status'];

    // Get the customer who placed the order
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        // Ensure OrderItem is the name of your detail model
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
