<?php

namespace Database\Factories;

use App\Models\Planning;
use App\Models\User;
use App\Models\Turbine;
use App\Enums\PlanningStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Planning>
 */
class PlanningFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Planning::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 months', '+3 months');
        $endDate = Carbon::instance($startDate)->addDays(fake()->numberBetween(1, 14));

        return [
            'turbineId' => Turbine::factory(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'createdBy' => User::factory(),
            'status' => fake()->randomElement(PlanningStatus::cases()),
        ];
    }
}
