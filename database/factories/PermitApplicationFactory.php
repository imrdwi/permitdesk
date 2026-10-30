<?php

namespace Database\Factories;

use App\Models\PermitApplication;
use App\Models\User;
use App\PermitStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PermitApplication>
 */
class PermitApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => User::factory()->applicant(),
            'is_temporary' => false,
            'practice_name' => fake()->randomElement(['Klinik', 'Apotek', 'Puskesmas', 'RS']).' '.fake()->lastName(),
            'practice_address' => fake()->address(),
            'practice_email' => fake()->safeEmail(),
            'practice_phone' => fake()->numerify('0281#######'),
            'practice_fax' => null,
            'status' => PermitStatus::Pending,
            'submitted_at' => now(),
        ];
    }

    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PermitStatus::InReview]);
    }

    public function approved(?string $expiresOn = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PermitStatus::Approved,
            'permit_number' => fake()->unique()->numerify('503/SIP/####/2026'),
            'expires_on' => $expiresOn ?? now()->addYear()->toDateString(),
            'verification_code' => Str::upper(Str::random(12)),
            'decided_at' => now(),
        ]);
    }

    public function temporary(): static
    {
        return $this->state(fn (array $attributes) => ['is_temporary' => true]);
    }
}
