<?php

namespace Database\Factories;

use App\Models\PermitApplication;
use App\Models\PermitEvent;
use App\PermitStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PermitEvent>
 */
class PermitEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'permit_application_id' => PermitApplication::factory(),
            'user_id' => null,
            'from_status' => null,
            'to_status' => PermitStatus::Pending,
            'note' => null,
        ];
    }
}
