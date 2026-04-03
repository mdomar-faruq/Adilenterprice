<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'payments';
    protected $fillable = [
        'customer_id',
        'amount',
        'payment_date',
        'payment_method',
        'note',
        'user_id',
    ];

    // Get the sale associated with this payment
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Get the customer who made this payment
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Get the user (staff) who recorded the payment
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
