<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create([
            'name' => 'PCS',
            'valid' => '1',
        ]);
        Unit::create([
            'name' => 'KG',
            'valid' => '1',
        ]);
        Unit::create([
            'name' => 'LTR',
            'valid' => '1',
        ]);
    }
}
