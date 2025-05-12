<?php

namespace Database\Factories;

use App\Models\Issue;
use App\Models\Revision;
use App\Enums\Severity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Issue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'revisionId' => Revision::factory(),
            'description' => fake()->paragraph(),
            'severity' => fake()->randomElement(Severity::cases()),
            'reportedAt' => fake()->dateTimeThisYear(),
        ];
    }
}
