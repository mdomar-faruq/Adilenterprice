<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $fillable = [
        'expense_category_id',
        'amount',
        'expense_date',
        'payment_method',
        'note',
        'user_id'
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategories::class, 'expense_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
