<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReturn extends Model
{
    protected $fillable = [
        'type',          // sales_return or purchase_return
        'dsr_id',        // nullable (employee)
        'return_date',
        'total_amount',
        'remarks',
        'user_id',
    ];

    /**
     * Relationship to the items in this return
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductReturnItem::class);
    }

    /**
     * Relationship to the DSR (Employee)
     */
    public function dsr(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'dsr_id');
    }
}
