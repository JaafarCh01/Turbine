<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Revision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plannedAt = fake()->optional(0.8)->dateTimeThisMonth();
        $doneAt = null;
        if ($plannedAt && fake()->boolean(60)) {
             $doneAt = fake()->dateTimeBetween($plannedAt, $plannedAt->format('Y-m-d H:i:s').' +7 days');
        }

        return [
            'revisionId' => Revision::factory(),
            'description' => fake()->sentence(),
            'ordre' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(\App\Enums\TaskStatus::cases())->value,
            'plannedAt' => $plannedAt,
            'doneAt' => $doneAt,
        ];
    }
}
