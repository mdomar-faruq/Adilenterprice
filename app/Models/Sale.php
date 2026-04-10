<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $table = 'sales';

    protected $fillable = [
        'invoice_no',
        'sale_date',
        'delivery_id',
        'sr_id',
        'route_no',
        'total_amount',
        'discount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'remarks',
        'user_id'
    ];

    public function delivery()
    {
        return $this->belongsTo(Employee::class, 'delivery_id');
    }

    public function sr()
    {
        return $this->belongsTo(Employee::class, 'sr_id');
    }

    // Changed this to 'user' to match typical controller usage, 
    // or keep as 'creator' if you prefer.
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function customerDues()
    {
        // Explicitly define foreign key 'sale_id' to be safe
        return $this->hasMany(SalesDueCustomer::class, 'sale_id');
    }
}
