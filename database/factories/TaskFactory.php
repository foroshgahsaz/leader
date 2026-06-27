<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'buyer_id' => null,
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'task_type' => 'general',
            'priority' => TaskPriority::Medium->value,
            'status' => TaskStatus::Pending->value,
            'due_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'completed_at' => null,
            'completed_by' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::Completed->value,
            'completed_at' => now(),
            'completed_by' => $attributes['assigned_to'] ?? User::factory(),
        ]);
    }
}
