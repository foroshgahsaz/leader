<?php

namespace Database\Factories;

use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
    protected $model = ImportBatch::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'filename' => fake()->word().'.csv',
            'status' => ImportBatchStatus::Pending->value,
            'total_rows' => 0,
            'processed_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 0,
            'errors' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportBatchStatus::Completed->value,
            'completed_at' => now(),
        ]);
    }
}
