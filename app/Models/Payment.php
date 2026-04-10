<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'sales_due_customer_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_no',
        'note',
        'user_id'
    ];

    /**
     * The customer who made the payment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The specific invoice debt this payment is linked to.
     */
    public function dueRecord()
    {
        return $this->belongsTo(SalesDueCustomer::class, 'sales_due_customer_id');
    }

    /**
     * The staff member who collected/entered the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
