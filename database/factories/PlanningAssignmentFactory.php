<?php

namespace Database\Factories;

use App\Models\PlanningAssignment;
use App\Models\Planning;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanningAssignment>
 */
class PlanningAssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlanningAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planningId' => Planning::factory(),
            'userId' => User::factory(),
            'roleDansPlanning' => fake()->randomElement(Role::cases()),
            // No timestamps for this model
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterMaking(function (PlanningAssignment $planningAssignment) {
            //
        })->afterCreating(function (PlanningAssignment $planningAssignment) {
            // Ensure no timestamps are attempted to be set if not present
            unset($planningAssignment->created_at);
            unset($planningAssignment->updated_at);
        });
    }
}
