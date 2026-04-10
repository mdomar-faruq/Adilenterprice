<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDueCustomer extends Model
{
    use HasFactory;

    protected $table = 'sales_due_customers';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'due_amount',
        'note'
    ];

    /**
     * Get the customer associated with this due record.
     */
    public function customer()
    {
        // This is the missing link causing your error
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the sale associated with this due record.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Get all payment installments made for this specific due record.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'sales_due_customer_id');
    }

    /**
     * Helper to calculate remaining balance for this specific record.
     */
    public function getRemainingAttribute()
    {
        return $this->due_amount - $this->paid_amount;
    }

    
}
