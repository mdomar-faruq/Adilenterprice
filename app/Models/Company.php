<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'opening_balance', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function purchases()
    {
        return $this->hasMany(purchases::class);
    }

    public function getTotalDueAttribute()
    {
        // Current unpaid sales + opening balance
        $salesDue = $this->sales()->sum('due_amount');
        return $this->opening_balance + $salesDue;
    }
}
