<?php

namespace Tests\Feature;

use App\Models\ApplicantDocument;
use App\Models\PermitApplication;
use App\Models\User;
use App\PermitStatus;
use App\Profession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registering_creates_an_applicant_with_the_chosen_profession(): void
    {
        $this->post('/register', [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'profession' => 'midwife',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();

        $user = User::query()->where('email', 'siti@example.com')->sole();
        $this->assertSame(Profession::Midwife, $user->profession);
        $this->assertFalse($user->isOffice());
    }

    #[Test]
    public function registration_cannot_create_an_office_account(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'profession' => 'doctor',
            'role' => 'head',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertFalse(User::query()->where('email', 'sneaky@example.com')->sole()->isOffice());
    }

    #[Test]
    public function an_applicant_cannot_apply_until_profile_and_documents_are_complete(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/applications/create')->assertRedirect('/applications');
        $this->actingAs($user)->post('/applications', ['practice_name' => 'Klinik', 'practice_address' => 'Jl. B'])->assertStatus(422);
    }

    #[Test]
    public function a_ready_applicant_submits_one_primary_and_then_only_temporary_applications(): void
    {
        $user = User::factory()->applicant()->withAllDocuments()->create();

        $this->actingAs($user)->post('/applications', ['practice_name' => 'Klinik Utama', 'practice_address' => 'Jl. C', 'is_temporary' => '0'])->assertRedirect();
        $this->actingAs($user)->post('/applications', ['practice_name' => 'Klinik Kedua', 'practice_address' => 'Jl. D', 'is_temporary' => '0'])
            ->assertSessionHasErrors('is_temporary');
        $this->actingAs($user)->post('/applications', ['practice_name' => 'Klinik Kedua', 'practice_address' => 'Jl. D', 'is_temporary' => '1'])->assertRedirect();

        $this->assertSame(2, $user->permitApplications()->count());
        $this->assertSame(1, $user->permitApplications()->where('is_temporary', false)->count());
    }

    #[Test]
    public function a_rejected_primary_frees_the_slot_for_a_new_primary_application(): void
    {
        $user = User::factory()->applicant()->withAllDocuments()->create();
        PermitApplication::factory()->for($user, 'applicant')->create(['status' => PermitStatus::Rejected]);

        $this->actingAs($user)->post('/applications', ['practice_name' => 'Klinik Baru', 'practice_address' => 'Jl. E', 'is_temporary' => '0'])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function an_application_can_be_edited_or_withdrawn_only_while_pending(): void
    {
        $user = User::factory()->applicant()->withAllDocuments()->create();
        $pending = PermitApplication::factory()->for($user, 'applicant')->create();
        $inReview = PermitApplication::factory()->inReview()->temporary()->for($user, 'applicant')->create();

        $this->actingAs($user)->get("/applications/{$pending->id}/edit")->assertOk();
        $this->actingAs($user)->get("/applications/{$inReview->id}/edit")->assertForbidden();
        $this->actingAs($user)->delete("/applications/{$inReview->id}")->assertForbidden();
        $this->actingAs($user)->delete("/applications/{$pending->id}")->assertRedirect('/applications');

        $this->assertSoftDeleted($pending);
    }

    #[Test]
    public function applicants_cannot_see_each_others_applications_or_documents(): void
    {
        $owner = User::factory()->applicant()->withAllDocuments()->create();
        $stranger = User::factory()->applicant()->create();
        $application = PermitApplication::factory()->for($owner, 'applicant')->create();
        $document = $owner->documents()->firstOrFail();

        $this->actingAs($stranger)->get("/applications/{$application->id}")->assertForbidden();
        $this->actingAs($stranger)->get("/documents/{$document->id}")->assertForbidden();
        $this->actingAs($stranger)->get('/office/applications')->assertForbidden();
    }

    #[Test]
    public function documents_upload_to_the_private_disk_and_replace_the_previous_file(): void
    {
        Storage::fake('local');
        $user = User::factory()->applicant()->create();

        $this->actingAs($user)->post('/my/documents', ['type' => 'diploma', 'file' => UploadedFile::fake()->create('ijazah.pdf', 200, 'application/pdf')])->assertRedirect();
        $first = ApplicantDocument::query()->sole();
        $this->actingAs($user)->post('/my/documents', ['type' => 'diploma', 'file' => UploadedFile::fake()->create('ijazah-new.pdf', 200, 'application/pdf')])->assertRedirect();

        $document = ApplicantDocument::query()->sole();
        $this->assertSame('ijazah-new.pdf', $document->original_name);
        Storage::disk('local')->assertExists($document->path);
        Storage::disk('local')->assertMissing($first->path);
    }

    #[Test]
    public function a_document_type_not_required_for_the_profession_is_rejected(): void
    {
        $user = User::factory()->applicant(Profession::Doctor)->create();

        $this->actingAs($user)->post('/my/documents', ['type' => 'student_card', 'file' => UploadedFile::fake()->create('ktm.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('type');
    }

    #[Test]
    public function a_student_profile_requires_a_university_and_no_license(): void
    {
        $user = User::factory()->create(['profession' => Profession::Student]);

        $this->actingAs($user)->put('/my/profile', [
            'full_name' => 'Budi', 'address' => 'Jl. F', 'birth_place' => 'Purwokerto', 'birth_date' => '2001-02-03',
            'gender' => 'male', 'graduation_year' => 2024, 'mobile' => '0812', 'license_number' => 'STR-1',
        ])->assertSessionHasErrors(['university', 'license_number']);
    }
}
