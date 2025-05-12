<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use App\Models\Turbine;
use App\Enums\DocumentType;
use App\Enums\DocumentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        return [
            'title' => $title,
            'fileData' => 'documents/' . Str::slug($title) . '_' . uniqid() . '.' . fake()->fileExtension(),
            'type' => fake()->randomElement(DocumentType::cases()),
            'category' => fake()->randomElement(DocumentCategory::cases()),
            'uploadDate' => fake()->dateTimeBetween('-1 year', 'now'),
            'uploadedBy' => User::factory(),
            'turbineId' => Turbine::factory(),
        ];
    }
}
