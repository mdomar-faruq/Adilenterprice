<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Keya Group',
            'email' => 'info@keya-bd.com',
            'phone' => '+8801722099200',
            'address' => 'Keya Group Jarun, Konabari, Gazipur',
            'opening_balance' => '10000',
            'is_active' => '1',
        ]);
    }
}
