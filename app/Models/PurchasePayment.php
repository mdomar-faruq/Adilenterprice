<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;
    protected $table = 'purchase_payments';
    protected $fillable = [
        'company_id',
        'amount',
        'payment_date',
        'payment_method',
        'note',
        'user_id',
    ];

    // Get the sale associated with this payment
    public function purchase()
    {
        return $this->belongsTo(purchases::class, 'purchase_id');
    }

    // Get the customer who made this payment
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Get the user (staff) who recorded the payment
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
