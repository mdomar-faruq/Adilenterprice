<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'), // always hash passwords
            'role' => '1',
            'valid' => '1',
        ]);

        User::create([
            'name' => 'Opu',
            'email' => 'test@example.com',
            'password' => Hash::make('adil@coe'),
            'role' => '1',
            'valid' => '1',
        ]);
    }
}
