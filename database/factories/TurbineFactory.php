<?php

namespace Database\Factories;

use App\Models\Turbine;
use App\Enums\TurbineStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Turbine>
 */
class TurbineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Turbine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Turbine ' . fake()->unique()->companySuffix() . ' ' . fake()->randomNumber(3),
            'location' => fake()->city() . ', ' . fake()->stateAbbr(),
            'status' => fake()->randomElement(TurbineStatus::cases()),
        ];
    }
}
