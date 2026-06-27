<?php

namespace Database\Factories;

use App\Enums\CompanyType;
use App\Models\GlobalBuyer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GlobalBuyer>
 */
class GlobalBuyerFactory extends Factory
{
    protected $model = GlobalBuyer::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'provider_key' => 'gb_'.Str::lower(Str::random(12)),
            'legal_name' => $name.' '.fake()->randomElement(['Inc.', 'LLC', 'Ltd.', 'GmbH']),
            'display_name' => $name,
            'country_code' => fake()->countryCode(),
            'city' => fake()->city(),
            'website' => fake()->url(),
            'industry' => fake()->randomElement(['Food & Beverage', 'Textiles', 'Machinery', 'Chemicals']),
            'company_type' => fake()->randomElement(CompanyType::cases())->value,
            'employee_range' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'firmographics' => [],
            'import_profile' => [],
            'import_activity_level' => fake()->numberBetween(0, 5),
            'data_freshness_at' => now(),
        ];
    }
}
