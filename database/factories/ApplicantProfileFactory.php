<?php

namespace Database\Factories;

use App\Gender;
use App\Models\ApplicantProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicantProfile>
 */
class ApplicantProfileFactory extends Factory
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
            'full_name' => fake()->name(),
            'address' => fake()->address(),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-22 years'),
            'gender' => fake()->randomElement(Gender::cases()),
            'graduation_year' => fake()->numberBetween(2000, (int) date('Y')),
            'license_number' => fake()->numerify('STR-##########'),
            'license_expires_on' => now()->addYears(3)->toDateString(),
            'university' => null,
            'phone' => null,
            'mobile' => fake()->numerify('08##########'),
        ];
    }

    public function licenseExpired(): static
    {
        return $this->state(fn (array $attributes) => ['license_expires_on' => now()->subDay()->toDateString()]);
    }
}
