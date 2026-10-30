<?php

namespace Database\Factories;

use App\DocumentType;
use App\Models\ApplicantDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantDocument>
 */
class ApplicantDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(DocumentType::cases()),
            'path' => 'documents/'.fake()->uuid().'.pdf',
            'original_name' => 'document.pdf',
            'mime_type' => 'application/pdf',
            'size' => 120_000,
        ];
    }
}
