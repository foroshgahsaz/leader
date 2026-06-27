<?php

namespace Database\Factories;

use App\Models\LeadList;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadList>
 */
class LeadListFactory extends Factory
{
    protected $model = LeadList::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->optional()->hexColor(),
            'is_shared' => true,
            'owner_id' => User::factory(),
            'buyer_count' => 0,
            'created_by' => User::factory(),
        ];
    }
}
