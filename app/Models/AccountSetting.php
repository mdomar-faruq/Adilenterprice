<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountSetting extends Model
{
    use HasFactory;

    // Define the table name explicitly if you want to be safe
    protected $table = 'account_settings';

    // Allow these fields to be filled via create/update
    protected $fillable = [
        'opening_balance',
    ];

    /**
     * Optional: Cast the balance to a float/double automatically
     */
    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];
}
