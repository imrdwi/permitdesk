<?php

namespace Database\Seeders;

use App\Models\PermitApplication;
use App\Models\User;
use App\Profession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed office accounts and applicants with applications at every stage.
     */
    public function run(): void
    {
        $head = User::factory()->head()->create(['name' => 'Head of Office', 'email' => 'head@example.com']);
        $staff = User::factory()->staff()->create(['name' => 'Licensing Staff', 'email' => 'staff@example.com']);

        $doctor = User::factory()->applicant(Profession::Doctor)->withAllDocuments()->create(['name' => 'Demo Doctor', 'email' => 'doctor@example.com']);
        PermitApplication::submit($doctor, ['practice_name' => 'Klinik Sehat Sentosa', 'practice_address' => 'Jl. Merdeka 10']);

        foreach ([Profession::Doctor, Profession::Nurse, Profession::Midwife, Profession::Pharmacist, Profession::Student] as $index => $profession) {
            $applicant = User::factory()->applicant($profession)->withAllDocuments()->create();
            $application = PermitApplication::submit($applicant, PermitApplication::factory()->make()->only('practice_name', 'practice_address', 'practice_email', 'practice_phone'));

            if ($index >= 1) {
                $application->startReview($staff);
            }

            if ($index === 2 || $index === 4) {
                $application->approve($head, sprintf('503/SIP/%04d/%s', $index, now()->year));
            }

            if ($index === 3) {
                $application->reject($staff, 'The practice location statement is not signed.');
            }
        }
    }
}
