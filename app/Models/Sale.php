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
        'customer_id',
        'sale_date',
        'total_amount',
        'discount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'remarks',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**
     * A Sale also belongs to a Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all payments recorded for this sale.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }
}
