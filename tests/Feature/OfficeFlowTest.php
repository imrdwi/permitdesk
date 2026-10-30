<?php

namespace Tests\Feature;

use App\Models\PermitApplication;
use App\Models\User;
use App\Notifications\PermitDecided;
use App\PermitStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficeFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create();
    }

    #[Test]
    public function office_pages_render(): void
    {
        $application = PermitApplication::factory()->approved()->for(User::factory()->applicant()->withAllDocuments(), 'applicant')->create();
        $head = User::factory()->head()->create();

        foreach (['/dashboard', '/office/applications', '/office/applications?status=expiring', "/office/applications/{$application->id}", "/permits/{$application->id}/print"] as $url) {
            $this->actingAs($this->staff)->get($url)->assertOk();
        }

        foreach (['/office/report', '/office/report?month=2026-01', '/office/staff', '/office/staff/create'] as $url) {
            $this->actingAs($head)->get($url)->assertOk();
        }
    }

    #[Test]
    public function only_the_head_sees_reports_and_manages_staff(): void
    {
        $this->actingAs($this->staff)->get('/office/report')->assertForbidden();
        $this->actingAs($this->staff)->get('/office/staff')->assertForbidden();
    }

    #[Test]
    public function staff_review_then_approve_and_the_applicant_is_notified(): void
    {
        Notification::fake();
        $application = PermitApplication::factory()->create();

        $this->actingAs($this->staff)->post("/office/applications/{$application->id}/review")->assertRedirect();
        $this->actingAs($this->staff)->post("/office/applications/{$application->id}/approve", ['permit_number' => '503/SIP/0009/2026'])->assertRedirect();

        $this->assertSame(PermitStatus::Approved, $application->fresh()?->status);
        Notification::assertSentTo($application->applicant, PermitDecided::class);
    }

    #[Test]
    public function permit_numbers_are_unique(): void
    {
        PermitApplication::factory()->approved()->create(['permit_number' => 'DUP-1']);
        $application = PermitApplication::factory()->inReview()->create();

        $this->actingAs($this->staff)->post("/office/applications/{$application->id}/approve", ['permit_number' => 'DUP-1'])
            ->assertSessionHasErrors('permit_number');
    }

    #[Test]
    public function an_invalid_step_returns_a_message_instead_of_an_error_page(): void
    {
        $application = PermitApplication::factory()->create();

        $this->actingAs($this->staff)->post("/office/applications/{$application->id}/approve", ['permit_number' => 'P-9'])
            ->assertSessionHasErrors('decision');
    }

    #[Test]
    public function a_rejection_needs_a_reason(): void
    {
        $application = PermitApplication::factory()->inReview()->create();

        $this->actingAs($this->staff)->post("/office/applications/{$application->id}/reject", ['reason' => 'no'])->assertSessionHasErrors('reason');
    }

    #[Test]
    public function permits_can_be_printed_only_once_approved(): void
    {
        $pending = PermitApplication::factory()->create();

        $this->actingAs($this->staff)->get("/permits/{$pending->id}/print")->assertForbidden();
        $this->actingAs($pending->applicant)->get("/permits/{$pending->id}/print")->assertForbidden();
    }

    #[Test]
    public function the_public_verification_page_reports_validity(): void
    {
        $valid = PermitApplication::factory()->approved()->create();
        $expired = PermitApplication::factory()->approved(now()->subDay()->toDateString())->create(['status' => PermitStatus::Expired]);

        $this->get("/verify/{$valid->verification_code}")->assertOk()->assertSee('Valid')->assertSee((string) $valid->permit_number);
        $this->get("/verify/{$expired->verification_code}")->assertOk()->assertSee('Expired');
        $this->get('/verify/NOT-A-CODE')->assertOk()->assertSee('Not found');
    }
}
