<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'category' => fake()->optional()->word(),
            'industry' => fake()->randomElement(['Food & Beverage', 'Textiles', 'Machinery', 'Chemicals']),
            'is_primary' => false,
            'status' => 'active',
            'created_by' => User::factory(),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
