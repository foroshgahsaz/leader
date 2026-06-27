<?php

namespace Database\Factories;

use App\Enums\BuyerStatus;
use App\Enums\CompanyType;
use App\Enums\LeadSource;
use App\Models\Buyer;
use App\Models\GlobalBuyer;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Buyer>
 */
class BuyerFactory extends Factory
{
    protected $model = Buyer::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'organization_id' => Organization::factory(),
            'global_buyer_id' => null,
            'provider_key' => 'buyer_'.Str::lower(Str::random(12)),
            'name' => $name,
            'country_code' => fake()->countryCode(),
            'city' => fake()->optional()->city(),
            'website' => fake()->optional()->url(),
            'industry' => fake()->randomElement(['Food & Beverage', 'Textiles', 'Machinery', 'Chemicals']),
            'company_type' => fake()->randomElement(CompanyType::cases())->value,
            'employee_range' => fake()->optional()->randomElement(['1-10', '11-50', '51-200']),
            'owner_id' => null,
            'source' => LeadSource::Search->value,
            'source_search_id' => null,
            'status' => BuyerStatus::Saved->value,
            'is_favorite' => false,
            'is_dnc' => false,
            'last_contacted_at' => null,
            'last_activity_at' => null,
            'snapshot' => null,
            'created_by' => User::factory(),
        ];
    }

    public function withGlobalBuyer(?GlobalBuyer $globalBuyer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'global_buyer_id' => ($globalBuyer ?? GlobalBuyer::factory()->create())->id,
            'provider_key' => null,
        ]);
    }

    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_favorite' => true,
        ]);
    }
}
