<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Livewire\Client\ApplyJob;
use App\Models\Application;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\CandidateResume;
use App\Mail\GuestApplicationVerificationMail;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ApplyJobFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_apply_to_job_with_cv(): void
    {
        Mail::fake();
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $candidate = User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => 'candidate-apply@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);
        Candidate::query()->create([
            'user_id' => $candidate->id,
            'name' => 'Nguyen Van A',
            'email' => 'candidate-apply@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/existing/cv.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer',
            'description' => 'Build and maintain Laravel applications.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        $this->actingAs($candidate);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'Nguyen Van A Apply')
            ->set('email', 'candidate-apply@example.com')
            ->set('phone', '0987654321')
            ->set('experience_years', 3)
            ->set('profile_title', 'Senior PHP Developer')
            ->set('career_objective', 'Muon phat trien san pham tuyen dung on dinh theo huong on dinh va lau dai.')
            ->set('cv', UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertSet('showSuccessModal', true);

        $this->assertDatabaseHas('applications', [
            'job_id' => $job->id,
            'branch_id' => $branch->id,
            'status' => StatusApplicationEnum::NEW->value,
            'apply_method' => 'cv',
        ]);
        $this->assertDatabaseHas('candidate_job_submissions', [
            'job_id' => $job->id,
            'apply_method' => 'cv',
        ]);

        $application = Application::query()->first();
        $submission = CandidateJobSubmission::query()->first();
        $applicationAttachment = Attachment::query()->find($application?->cv_attachment_id);

        $this->assertNotNull($application?->candidate_id);
        $this->assertNotNull($application?->cv_attachment_id);
        $this->assertNotNull($applicationAttachment);
        $this->assertSame(Application::class, $applicationAttachment?->attachable_type);
        $this->assertSame($application?->id, $applicationAttachment?->attachable_id);
        $this->assertSame($application->candidate_id, $submission?->candidate_id);
        $this->assertSame($application->cv_attachment_id, $submission?->cv_attachment_id);
        $this->assertNotEmpty($submission?->cv_path);
        Storage::disk('public')->assertExists($application->cv_path);
        Storage::disk('public')->assertExists($submission->cv_path);
        $this->assertSame('Nguyen Van A Apply', data_get($application->profile_snapshot, 'candidate.name'));
        $this->assertSame('candidate-apply@example.com', data_get($application->profile_snapshot, 'candidate.email'));
        $this->assertSame('0987654321', data_get($application->profile_snapshot, 'candidate.phone'));
        $this->assertSame('Senior PHP Developer', data_get($application->profile_snapshot, 'resume.profile_title'));
        $this->assertSame('Muon phat trien san pham tuyen dung on dinh theo huong on dinh va lau dai.', data_get($application->profile_snapshot, 'resume.career_objective'));
        $this->assertNotEmpty(data_get($application->profile_snapshot, 'cv.path'));
        $this->assertSame($application->cv_attachment_id, data_get($application->profile_snapshot, 'cv.attachment_id'));
        $this->assertSame('cv.pdf', data_get($application->profile_snapshot, 'cv.original_filename'));
        $this->assertSame($application->profile_snapshot, $submission?->profile_snapshot);
        $candidateRecord = Candidate::query()->where('user_id', $candidate->id)->firstOrFail();
        $this->assertSame('Nguyen Van A', $candidateRecord->name);
        $this->assertSame('candidate-apply@example.com', $candidateRecord->email);
        $this->assertSame('0901234567', $candidateRecord->phone);
        $this->assertSame('candidates/existing/cv.pdf', $candidateRecord->cv_file);

        Candidate::query()
            ->whereKey($application->candidate_id)
            ->update([
                'name' => 'Updated Candidate Name',
                'email' => 'updated-contact@example.com',
            ]);
        CandidateResume::query()
            ->where('candidate_id', $application->candidate_id)
            ->update([
                'profile_title' => 'Updated Profile Title',
            ]);

        $application->refresh();

        $this->assertSame('Nguyen Van A Apply', data_get($application->profile_snapshot, 'candidate.name'));
        $this->assertSame('candidate-apply@example.com', data_get($application->profile_snapshot, 'candidate.email'));
        $this->assertSame('Senior PHP Developer', data_get($application->profile_snapshot, 'resume.profile_title'));

        Candidate::query()
            ->find($application->candidate_id)
            ?->attachments()
            ->where('type', 'cv')
            ->delete();

        $this->assertDatabaseHas('attachments', [
            'id' => $application->cv_attachment_id,
            'attachable_type' => Application::class,
            'attachable_id' => $application->id,
            'deleted_at' => null,
        ]);
    }

    public function test_hr_without_candidate_account_cannot_apply_as_candidate(): void
    {
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
            'metadata' => ['account_types' => ['employer']],
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer-hr-guard',
            'description' => 'Build and maintain Laravel applications.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $this->actingAs($hr);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'HR User')
            ->set('email', $hr->email)
            ->set('cv', UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasErrors('account');

        $this->assertDatabaseMissing('applications', [
            'job_id' => $job->id,
        ]);
        $this->assertDatabaseMissing('candidate_job_submissions', [
            'job_id' => $job->id,
        ]);
        $this->assertFalse(Candidate::query()->where('user_id', $hr->id)->exists());
    }

    public function test_apply_job_rejects_invalid_phone_number(): void
    {
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $candidate = User::factory()->create([
            'name' => 'Nguyen Van A',
            'email' => 'candidate-invalid-phone@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);
        Candidate::query()->create([
            'user_id' => $candidate->id,
            'name' => 'Nguyen Van A',
            'email' => 'candidate-invalid-phone@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/existing/cv.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Laravel Developer',
            'slug' => 'laravel-developer-invalid-phone',
            'description' => 'Build and maintain Laravel applications.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        $this->actingAs($candidate);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'Nguyen Van A')
            ->set('email', 'candidate-invalid-phone@example.com')
            ->set('phone', '12345')
            ->set('cv', UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasErrors(['phone']);

        $this->assertDatabaseMissing('applications', [
            'job_id' => $job->id,
        ]);
    }

    public function test_guest_can_apply_and_reuse_existing_candidate_by_email(): void
    {
        Mail::fake();
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $existingCandidate = Candidate::query()->create([
            'name' => 'Old Guest Name',
            'email' => 'guest-apply@example.com',
            'phone' => '0901234567',
            'cv_file' => null,
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Guest Apply Developer',
            'slug' => 'guest-apply-developer',
            'description' => 'Build guest apply flow.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'Guest Candidate Updated')
            ->set('email', 'guest-apply@example.com')
            ->set('phone', '0912345678')
            ->set('profile_title', 'Frontend Developer')
            ->set('cv', UploadedFile::fake()->create('guest-cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('showSuccessModal', true);

        $this->assertSame(1, Candidate::query()->where('email', 'guest-apply@example.com')->count());
        $this->assertDatabaseHas('candidates', [
            'id' => $existingCandidate->id,
            'user_id' => null,
            'name' => 'Old Guest Name',
            'email' => 'guest-apply@example.com',
            'phone' => '0901234567',
        ]);
        $this->assertDatabaseHas('applications', [
            'job_id' => $job->id,
            'candidate_id' => $existingCandidate->id,
            'apply_method' => 'cv',
            'branch_id' => $branch->id,
        ]);

        $application = Application::query()->where('candidate_id', $existingCandidate->id)->first();

        $this->assertNotNull($application?->cv_path);
        Storage::disk('public')->assertExists($application->cv_path);
        $this->assertSame('Guest Candidate Updated', data_get($application?->profile_snapshot, 'candidate.name'));
        $this->assertSame('guest-apply@example.com', data_get($application?->profile_snapshot, 'candidate.email'));
        $this->assertSame('guest-cv.pdf', data_get($application?->profile_snapshot, 'cv.original_filename'));

        $verificationUrl = null;
        Mail::assertSent(GuestApplicationVerificationMail::class, function (GuestApplicationVerificationMail $mail) use (&$verificationUrl, $existingCandidate, $application): bool {
            $verificationUrl = $mail->verificationUrl;

            return $mail->candidate->is($existingCandidate) && $mail->application->is($application);
        });

        $this->get($verificationUrl)
            ->assertRedirect(route('candidates.login', ['email' => 'guest-apply@example.com']));

        $existingCandidate->refresh();
        $this->assertNotNull(data_get($existingCandidate->metadata, 'guest_email_verified_at'));
        $this->assertSame($application->id, data_get($existingCandidate->metadata, 'guest_email_verified_application_id'));
        $this->assertSame('guest-apply@example.com', data_get($existingCandidate->metadata, 'guest_email_verified_email'));
    }

    public function test_candidate_can_opt_in_to_sync_application_data_to_profile(): void
    {
        Mail::fake();
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Da Nang',
            'code' => 'DN',
            'city' => 'Da Nang',
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $user = User::factory()->create([
            'name' => 'Profile Sync Candidate',
            'email' => 'sync-candidate@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);
        $candidate = Candidate::query()->create([
            'user_id' => $user->id,
            'name' => 'Profile Sync Candidate',
            'email' => 'sync-candidate@example.com',
            'phone' => '0900000000',
            'cv_file' => 'candidates/sync/current-cv.pdf',
            'experience_years' => 1,
        ]);
        CandidateResume::query()->create([
            'candidate_id' => $candidate->id,
            'profile_title' => 'Junior Developer',
            'career_objective' => 'Muốn học hỏi và phát triển.',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Sync Profile Developer',
            'slug' => 'sync-profile-developer',
            'description' => 'Build and maintain Laravel applications.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'Updated Sync Name')
            ->set('email', 'updated-sync@example.com')
            ->set('phone', '0911222333')
            ->set('experience_years', 4)
            ->set('profile_title', 'Mid Laravel Developer')
            ->set('career_objective', 'Muc tieu nghiep vu ro rang hon.')
            ->set('sync_profile_to_candidate', true)
            ->set('use_cv_as_primary', true)
            ->set('cv', UploadedFile::fake()->create('primary-cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('showSuccessModal', true);

        $candidate->refresh();

        $this->assertSame('Updated Sync Name', $candidate->name);
        $this->assertSame('updated-sync@example.com', $candidate->email);
        $this->assertSame('0911222333', $candidate->phone);
        $this->assertSame(4, $candidate->experience_years);
        $this->assertNotNull($candidate->cv_file);
        Storage::disk('public')->assertExists($candidate->cv_file);

        $this->assertDatabaseHas('candidate_resumes', [
            'candidate_id' => $candidate->id,
            'profile_title' => 'Mid Laravel Developer',
        ]);

        $application = Application::query()->where('candidate_id', $candidate->id)->firstOrFail();
        $this->assertSame('Updated Sync Name', data_get($application->profile_snapshot, 'candidate.name'));
        $this->assertSame('updated-sync@example.com', data_get($application->profile_snapshot, 'candidate.email'));
        $this->assertSame('Mid Laravel Developer', data_get($application->profile_snapshot, 'resume.profile_title'));
        $this->assertSame($candidate->cv_file, $application->cv_path);
    }

    public function test_candidate_cannot_apply_same_job_twice_or_overwrite_snapshot(): void
    {
        Storage::fake('public');

        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Ha Noi',
            'code' => 'HN',
            'city' => 'Ha Noi',
        ]);
        $employer = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);
        $user = User::factory()->create([
            'name' => 'Duplicate Candidate',
            'email' => 'duplicate-candidate@example.com',
            'role' => 'candidate',
            'is_active' => true,
            'metadata' => ['account_types' => ['candidate']],
        ]);
        $candidate = Candidate::query()->create([
            'user_id' => $user->id,
            'name' => 'Duplicate Candidate',
            'email' => 'duplicate-candidate@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/existing/cv.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Duplicate Guard Developer',
            'slug' => 'duplicate-guard-developer',
            'description' => 'Build duplicate guard flow.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $employer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'First Snapshot Name')
            ->set('email', 'duplicate-candidate@example.com')
            ->set('phone', '0901234567')
            ->set('profile_title', 'First Snapshot Title')
            ->set('cv', UploadedFile::fake()->create('first-cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('showSuccessModal', true);

        $application = Application::query()
            ->where('candidate_id', $candidate->id)
            ->where('job_id', $job->id)
            ->firstOrFail();
        $originalSnapshot = $application->profile_snapshot;
        $originalAttachmentId = $application->cv_attachment_id;
        $originalAppliedAt = $application->applied_at?->toDateTimeString();

        Livewire::test(ApplyJob::class, ['job' => $job])
            ->set('name', 'Second Snapshot Name')
            ->set('email', 'duplicate-candidate@example.com')
            ->set('phone', '0901234567')
            ->set('profile_title', 'Second Snapshot Title')
            ->set('cv', UploadedFile::fake()->create('second-cv.pdf', 120, 'application/pdf'))
            ->call('submit')
            ->assertHasErrors(['application'])
            ->assertSet('showSuccessModal', false);

        $application->refresh();

        $this->assertSame(1, Application::query()->where('candidate_id', $candidate->id)->where('job_id', $job->id)->count());
        $this->assertSame(1, CandidateJobSubmission::query()->where('candidate_id', $candidate->id)->where('job_id', $job->id)->count());
        $this->assertSame($originalSnapshot, $application->profile_snapshot);
        $this->assertSame($originalAttachmentId, $application->cv_attachment_id);
        $this->assertSame($originalAppliedAt, $application->applied_at?->toDateTimeString());
        $this->assertSame(1, Attachment::query()->where('attachable_type', Application::class)->where('attachable_id', $application->id)->where('type', 'cv')->count());
    }
}
