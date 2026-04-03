<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $table = 'sales_returns';
    protected $fillable = [
        'return_no',
        'customer_id',
        'total_amount',
        'return_date',
        'remarks',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Access all items in this return
    public function items()
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    // Access the customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
