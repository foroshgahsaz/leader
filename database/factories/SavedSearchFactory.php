<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    protected $model = SavedSearch::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->words(3, true),
            'criteria' => [
                'country_code' => fake()->countryCode(),
                'industry' => fake()->randomElement(['Food & Beverage', 'Textiles', 'Machinery']),
            ],
            'result_count_last' => fake()->optional()->numberBetween(0, 500),
            'last_run_at' => fake()->optional()->dateTimeThisMonth(),
            'created_by' => User::factory(),
        ];
    }
}
