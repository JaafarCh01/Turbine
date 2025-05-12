<?php

namespace Database\Factories;

use App\Models\Revision;
use App\Models\Turbine;
use App\Models\PDR;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revision>
 */
class RevisionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Revision::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'turbineId' => Turbine::factory(),
            'revisionDate' => fake()->dateTimeThisYear(),
            'linkedPdrId' => fake()->boolean(75) ? PDR::factory() : null,
            'performedBy' => User::factory(),
        ];
    }
}
