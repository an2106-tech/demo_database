<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationPreScreening;
use App\Models\ApplicationStatusHistory;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\InterviewEvaluator;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\Scorecard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApplicationDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_page_shows_interview_panel_and_offer_without_mutating_assignments(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect(['ViewAny:Application', 'View:Application'])
            ->map(fn (string $name): Permission => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $role = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $branch = Branch::query()->create([
            'name' => 'Trường Cao đẳng FPT Polytechnic - Cần Thơ',
            'code' => 'FPTCT',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'name' => 'HR Cần Thơ',
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $evaluator = User::factory()->create([
            'name' => 'Mai Anh Tú',
            'role' => 'pm',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Nguyễn Minh Khang',
            'email' => 'minh-khang@example.com',
            'phone' => '0901234567',
            'cv_file' => 'candidates/minh-khang.pdf',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giảng viên Công nghệ thông tin',
            'slug' => 'giang-vien-cong-nghe-thong-tin-detail-test',
            'description' => 'Giảng dạy tại FPT Polytechnic.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/minh-khang.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::INTERVIEWING,
            'branch_id' => $branch->id,
            'assigned_hr_id' => $hr->id,
            'applied_at' => now()->subDays(5),
            'profile_snapshot' => [
                'candidate' => ['name' => 'Nguyễn Minh Khang', 'email' => 'minh-khang@example.com'],
                'resume' => ['profile_title' => 'Giảng viên PHP/Laravel'],
                'cv' => ['original_filename' => 'CV_Nguyen_Minh_Khang.pdf'],
            ],
        ]);
        ApplicationPreScreening::query()->create([
            'application_id' => $application->id,
            'handled_by_user_id' => $hr->id,
            'contact_channel' => 'phone',
            'contacted_at' => now()->subDays(3),
            'outcome' => 'passed',
            'note' => 'Đạt',
        ]);
        $interview = Interview::query()->create([
            'application_id' => $application->id,
            'interviewer_id' => $evaluator->id,
            'round_number' => 1,
            'round_name' => 'Chuyên môn và năng lực giảng dạy',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
            'type' => 'online',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'invite_sent_at' => now()->subDays(2),
            'result' => 'pass',
            'finalized_at' => now()->subHours(12),
            'finalized_by_user_id' => $evaluator->id,
        ]);
        InterviewEvaluator::query()->create([
            'interview_id' => $interview->id,
            'user_id' => $evaluator->id,
            'role' => 'lead',
            'is_required' => true,
            'assigned_at' => now()->subDays(2),
            'submitted_at' => now()->subDay(),
        ]);
        Scorecard::query()->create([
            'application_id' => $application->id,
            'interview_id' => $interview->id,
            'evaluator_id' => $evaluator->id,
            'criteria' => [['name' => 'Năng lực chuyên môn', 'score' => 9]],
            'average_score' => 9,
            'conclusion' => 'pass',
            'notes' => 'Đáp ứng tốt yêu cầu giảng dạy.',
            'submitted_at' => now()->subDay(),
        ]);
        $offer = Offer::query()->create([
            'application_id' => $application->id,
            'content' => 'Thư mời nhận việc.',
            'salary_offered' => 12000000,
            'start_date' => now()->addWeek()->toDateString(),
            'probation_months' => 2,
            'status' => 'pending',
            'expires_at' => now()->addDays(3),
            'sent_at' => now(),
        ]);

        foreach (range(1, 7) as $index) {
            ApplicationStatusHistory::query()->create([
                'application_id' => $application->id,
                'from_status' => StatusApplicationEnum::SCREENING->value,
                'to_status' => StatusApplicationEnum::INTERVIEWING->value,
                'changed_by_id' => $hr->id,
                'comment' => "Cập nhật quy trình {$index}",
            ]);
        }

        $assignmentCount = InterviewEvaluator::query()->count();

        $this->actingAs($hr)
            ->get(ApplicationResource::getUrl('view', ['record' => $application]))
            ->assertOk()
            ->assertSee('Chi tiết ứng tuyển')
            ->assertSee('Nguyễn Minh Khang')
            ->assertSee('Chuyên môn và năng lực giảng dạy')
            ->assertSee('Mai Anh Tú')
            ->assertSee('Đáp ứng tốt yêu cầu giảng dạy.')
            ->assertSee('Đạt sơ tuyển')
            ->assertSee('Không có ghi chú bổ sung.')
            ->assertSee('7 cập nhật')
            ->assertSee('Xem thêm 2 cập nhật trước đó')
            ->assertSee('OFF-'.str_pad((string) $offer->id, 6, '0', STR_PAD_LEFT));

        $this->assertSame($assignmentCount, InterviewEvaluator::query()->count());
    }
}
