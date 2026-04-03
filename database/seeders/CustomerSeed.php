<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'MD Omar Faruq',
            'phone' => '01681935050',
            'address' => 'Dhaka',
            'credit_limit' => '0',
            'opening_balance' => '0',
            'is_active' => '1',
        ]);
    }
}
