<?php

namespace Database\Factories;

use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiGeneration>
 */
class AiGenerationFactory extends Factory
{
    protected $model = AiGeneration::class;

    public function definition(): array
    {
        return [
            'buyer_id' => Buyer::factory(),
            'user_id' => User::factory(),
            'type' => AiGenerationType::Email,
            'status' => AiGenerationStatus::Completed,
            'input' => ['tone' => 'professional'],
            'output' => [
                'subject' => fake()->sentence(),
                'body' => fake()->paragraph(),
            ],
            'prompt_tokens' => 100,
            'completion_tokens' => 200,
            'total_tokens' => 300,
            'model' => 'gpt-4o-mini',
            'model_version' => 'openai-v1',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => AiGenerationStatus::Pending,
            'output' => null,
            'prompt_tokens' => null,
            'completion_tokens' => null,
            'total_tokens' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }
}
