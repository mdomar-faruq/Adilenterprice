<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ExpenseCategories;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExpenseCategories::create([
            'name' => 'Administrative Costs',
        ]);
        ExpenseCategories::create([
            'name' => 'Rent & Utilitie',
        ]);
        ExpenseCategories::create([
            'name' => 'Entertainment ',
        ]);
        ExpenseCategories::create([
            'name' => 'Salaries',
        ]);
        ExpenseCategories::create([
            'name' => 'Research & Development (R&D)',
        ]);
    }
}
