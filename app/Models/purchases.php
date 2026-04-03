<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class purchases extends Model
{
    use HasFactory;
    protected $table = 'purchases';
    protected $fillable = ['purchase_no', 'purchase_date', 'company_id', 'user_id', 'total_amount', 'paid_amount', 'due_amount', 'discount'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(purchase_items::class, 'purchase_id');
    }
}
