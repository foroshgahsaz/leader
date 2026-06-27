<?php

namespace Database\Seeders;

use App\Models\GlobalBuyer;
use Illuminate\Database\Seeder;

class GlobalBuyerSeeder extends Seeder
{
    public function run(): void
    {
        if (GlobalBuyer::query()->exists()) {
            return;
        }

        GlobalBuyer::factory()
            ->count(75)
            ->create();

        GlobalBuyer::factory()->create([
            'display_name' => 'Mediterranean Foods GmbH',
            'legal_name' => 'Mediterranean Foods GmbH',
            'country_code' => 'DE',
            'city' => 'Hamburg',
            'industry' => 'Food & Beverage',
            'company_type' => 'importer',
            'import_activity_level' => 4,
            'import_profile' => ['products' => ['olive oil', 'canned tomatoes']],
        ]);

        GlobalBuyer::factory()->create([
            'display_name' => 'Anatolia Textile Trading',
            'legal_name' => 'Anatolia Textile Trading Ltd.',
            'country_code' => 'TR',
            'city' => 'Istanbul',
            'industry' => 'Textiles',
            'company_type' => 'distributor',
            'import_activity_level' => 5,
            'import_profile' => ['products' => ['cotton fabric', 'yarn']],
        ]);
    }
}
