<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = $this->faker->randomFloat(2, 50, 5000); // Between 50 and 5000
        $percent = $this->faker->numberBetween(5, 50); // 5% to 50% margin

        // Calculate Sale Price based on your business logic
        $salePrice = $purchasePrice + ($purchasePrice * ($percent / 100));

        return [
            'name' => $this->faker->words(3, true),
            'company_id' => 1,
            'purchase_price' => $purchasePrice,
            'percent' => $percent,
            'sale_price' => round($salePrice, 2),
            'unit_id' => '1',
            'stock' => 0,
            'user_id' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
