<?php

namespace Tests\Feature;

use App\Exceptions\InvalidPermitTransition;
use App\Models\ApplicantProfile;
use App\Models\PermitApplication;
use App\Models\User;
use App\Notifications\PermitExpiringSoon;
use App\PermitStatus;
use App\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermitWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create();
    }

    #[Test]
    public function an_application_moves_from_pending_through_review_to_approval(): void
    {
        $application = PermitApplication::submit(User::factory()->applicant()->create(), ['practice_name' => 'Klinik A', 'practice_address' => 'Jl. A']);

        $application->startReview($this->staff);
        $application->approve($this->staff, '503/SIP/0001/2026');

        $this->assertSame(PermitStatus::Approved, $application->status);
        $this->assertSame('503/SIP/0001/2026', $application->permit_number);
        $this->assertNotNull($application->verification_code);
        $this->assertSame(
            [null, PermitStatus::Pending, PermitStatus::InReview],
            $application->events()->get()->map->from_status->all(),
        );
    }

    #[Test]
    public function a_permit_lasts_as_long_as_the_professional_license(): void
    {
        $applicant = User::factory()->applicant()->create();
        $applicant->applicantProfile?->update(['license_expires_on' => '2029-03-31']);
        $application = PermitApplication::factory()->inReview()->for($applicant, 'applicant')->create();

        $application->approve($this->staff, 'P-1');

        $this->assertSame('2029-03-31', $application->expires_on?->toDateString());
    }

    #[Test]
    public function a_student_placement_lasts_one_year(): void
    {
        $this->freezeTime();
        $application = PermitApplication::factory()->inReview()->for(User::factory()->applicant(Profession::Student), 'applicant')->create();

        $application->approve($this->staff, 'P-2');

        $this->assertSame(today()->addYear()->toDateString(), $application->expires_on?->toDateString());
    }

    #[Test]
    public function no_permit_is_issued_against_an_expired_license(): void
    {
        $applicant = User::factory()->applicant()->create();
        $applicant->applicantProfile?->update(['license_expires_on' => now()->subDay()->toDateString()]);
        $application = PermitApplication::factory()->inReview()->for($applicant, 'applicant')->create();

        $this->expectException(InvalidPermitTransition::class);

        try {
            $application->approve($this->staff, 'P-3');
        } finally {
            $this->assertSame(PermitStatus::InReview, $application->fresh()?->status);
        }
    }

    #[Test]
    public function steps_cannot_be_skipped_or_repeated(): void
    {
        $pending = PermitApplication::factory()->create();
        $approved = PermitApplication::factory()->approved()->create();

        foreach ([fn () => $pending->approve($this->staff, 'X'), fn () => $approved->reject($this->staff, 'Too late'), fn () => $approved->startReview($this->staff)] as $step) {
            try {
                $step();
                $this->fail('An invalid transition was allowed.');
            } catch (InvalidPermitTransition) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame(PermitStatus::Pending, $pending->fresh()?->status);
        $this->assertSame(PermitStatus::Approved, $approved->fresh()?->status);
    }

    #[Test]
    public function a_stale_model_cannot_decide_an_application_that_was_already_decided(): void
    {
        $application = PermitApplication::factory()->inReview()->create();
        $staleCopy = PermitApplication::query()->findOrFail($application->id);

        $application->reject($this->staff, 'Missing signature on the statement.');

        $this->expectException(InvalidPermitTransition::class);
        $staleCopy->approve($this->staff, 'P-4');
    }

    #[Test]
    public function the_expire_command_lapses_only_past_due_permits_and_records_it(): void
    {
        $lapsed = PermitApplication::factory()->approved(now()->subDay()->toDateString())->create();
        $current = PermitApplication::factory()->approved(now()->addDay()->toDateString())->create();

        $this->artisan('permits:expire')->expectsOutputToContain('Expired 1 permit(s).')->assertSuccessful();

        $this->assertSame(PermitStatus::Expired, $lapsed->fresh()?->status);
        $this->assertSame(PermitStatus::Approved, $current->fresh()?->status);
        $this->assertDatabaseHas('permit_events', ['permit_application_id' => $lapsed->id, 'to_status' => 'expired', 'user_id' => null]);
    }

    #[Test]
    public function expiry_reminders_are_sent_once_per_permit(): void
    {
        Notification::fake();
        $soon = PermitApplication::factory()->approved(now()->addDays(10)->toDateString())->create();
        PermitApplication::factory()->approved(now()->addDays(90)->toDateString())->create();

        $this->artisan('permits:remind')->assertSuccessful();
        $this->artisan('permits:remind')->assertSuccessful();

        Notification::assertSentTimes(PermitExpiringSoon::class, 1);
        Notification::assertSentTo($soon->applicant, PermitExpiringSoon::class);
    }

    #[Test]
    public function profile_factory_matches_the_profession(): void
    {
        $student = User::factory()->applicant(Profession::Student)->create();

        $this->assertInstanceOf(ApplicantProfile::class, $student->applicantProfile);
        $this->assertNull($student->applicantProfile->license_number);
    }
}
