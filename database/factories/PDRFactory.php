<?php

namespace Database\Factories;

use App\Models\PDR;
use App\Models\User;
use App\Models\Turbine;
use App\Enums\PDRStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PDR>
 */
class PDRFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PDR::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(PDRStatus::cases());
        $approvedAt = null;
        $approverId = null;

        if ($status === PDRStatus::APPROVED) {
            $approvedAt = fake()->dateTimeThisYear();
            // Ensure approverId is set if status is APPROVED
            // We'll create a new user for approver, or you could fetch an existing APPROVER role user
            $approverId = User::factory();
        } elseif ($status === PDRStatus::REJECTED) {
            // Optionally set an approver even for rejected PDRs
            $approverId = User::factory();
        }


        return [
            'turbineId' => Turbine::factory(),
            'title' => fake()->sentence(4),
            'status' => $status,
            'createdBy' => User::factory(),
            'approverId' => $approverId,
            'approvedAt' => $approvedAt,
            // Timestamps (createdAt, updatedAt) handled automatically
        ];
    }
}
