<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // $this->call(UserSeeder::class);
        // $this->call(UnitSeeder::class);
        // \App\Models\Product::factory(100)->create();
        // $this->call(CompanySeeder::class);
        // $this->call(CustomerSeed::class);
        $this->call(ExpenseSeeder::class);
    }
}
