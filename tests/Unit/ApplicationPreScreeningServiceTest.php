<?php

namespace Tests\Unit;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\ApplicationPreScreeningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationPreScreeningServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_standardized_pre_screening_rejection(): void
    {
        [$hr, $application] = $this->makeApplication();

        $record = app(ApplicationPreScreeningService::class)->recordOutcome($application, $hr, [
            'contact_channel' => 'phone',
            'contacted_at' => now('Asia/Ho_Chi_Minh')->subMinutes(5),
            'outcome' => 'rejected',
            'rejection_reason_code' => 'candidate_withdrew',
        ]);

        $this->assertSame('candidate_withdrew', $record->rejection_reason_code);
        $this->assertSame('Ứng viên không còn quan tâm', $record->rejection_reason);
        $this->assertSame(StatusApplicationEnum::REJECTED, $application->fresh()->status);
        $this->assertSame('pre_screening', $application->fresh()->rejected_stage);
    }

    public function test_it_requires_a_future_follow_up_time_and_note(): void
    {
        [$hr, $application] = $this->makeApplication();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(ApplicationPreScreeningService::class)->recordOutcome($application, $hr, [
            'contact_channel' => 'email',
            'contacted_at' => now('Asia/Ho_Chi_Minh')->subMinutes(5),
            'outcome' => 'follow_up',
            'follow_up_at' => now('Asia/Ho_Chi_Minh')->subMinute(),
            'note' => '',
        ]);
    }

    public function test_it_reminds_the_assigned_hr_once_per_day_for_an_overdue_follow_up(): void
    {
        [$hr, $application] = $this->makeApplication();
        $application->update(['assigned_hr_id' => $hr->id]);
        $application->preScreenings()->create([
            'handled_by_user_id' => $hr->id,
            'contact_channel' => 'phone',
            'contacted_at' => now('Asia/Ho_Chi_Minh')->subDays(2),
            'outcome' => 'follow_up',
            'follow_up_at' => now('Asia/Ho_Chi_Minh')->subHour(),
            'note' => 'Ứng viên hẹn trao đổi lại.',
        ]);

        $service = app(ApplicationPreScreeningService::class);

        $this->assertSame(1, $service->remindDueFollowUps());
        $this->assertDatabaseHas('notifications', [
            'user_id' => $hr->id,
            'type' => 'pre_screening_follow_up_due',
        ]);
        $this->assertNotNull($application->fresh()->latestPreScreening->follow_up_reminded_at);
        $this->assertSame(0, $service->remindDueFollowUps());
        $this->assertSame(1, UserNotification::query()->where('type', 'pre_screening_follow_up_due')->count());
    }

    /** @return array{0: User, 1: Application} */
    private function makeApplication(): array
    {
        $branch = Branch::query()->create([
            'name' => 'Greenwich Việt Nam - Cần Thơ',
            'code' => 'GWCT-PS',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create(['role' => 'hr', 'branch_id' => $branch->id, 'is_active' => true]);
        $candidate = Candidate::query()->create([
            'name' => 'Ứng viên sơ tuyển',
            'email' => 'prescreening@example.com',
            'phone' => '0901234567',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Công nghệ thông tin',
            'slug' => 'giang-vien-cntt-pre-screening',
            'description' => 'Vị trí kiểm thử sơ tuyển.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        return [$hr, Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'branch_id' => $branch->id,
            'status' => StatusApplicationEnum::SCREENING,
            'cv_path' => 'applications/test.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
        ])];
    }
}
