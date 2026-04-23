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
        'designation',
        'salary',
        'joining_date',
        'nid_number',
        'address',
        'is_active'
    ];
}
