<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'opening_balance',
        'opening_paid',
        'designation',
        'salary',
        'joining_date',
        'nid_number',
        'address',
        'is_active'
    ];

    /**
     * Get all dues/invoices logged against the employee.
     */
    public function dues()
    {
        // Points to our newly linked balance sheet tracking model
        return $this->hasMany(SalesDueCustomer::class, 'customer_id');
    }

    /**
     * Get all financial payback ledger receipts from the employee.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }
}
