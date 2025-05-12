<?php

namespace Database\Factories;

use App\Models\PdrStep;
use App\Models\PDR;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PdrStep>
 */
class PdrStepFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PdrStep::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pdrId' => PDR::factory(),
            'description' => fake()->sentence(),
            'mandatory' => fake()->boolean(70),
            'ordre' => fake()->unique()->numberBetween(1, 100),
        ];
    }
}
