<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'country_code' => fake()->countryCode(),
            'website' => fake()->url(),
            'industry' => fake()->randomElement(['Food & Beverage', 'Textiles', 'Machinery', 'Chemicals']),
            'timezone' => 'UTC',
            'status' => 'active',
            'onboarding_completed_at' => now(),
        ];
    }
}
