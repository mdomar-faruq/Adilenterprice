<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'credit_limit',
        'opening_balance',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Calculate total due across all sales
    public function getTotalDueAttribute()
    {
        // Current unpaid sales + opening balance
        $salesDue = $this->sales()->sum('due_amount');
        return $this->opening_balance + $salesDue;
    }

    // Check if they are over their credit limit
    public function isOverLimit($newSaleAmount)
    {
        if ($this->credit_limit <= 0) return false; // No limit set
        return ($this->total_due + $newSaleAmount) > $this->credit_limit;
    }
}
