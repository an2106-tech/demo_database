<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\RecruitmentInternalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferInternalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_sent_notification_is_created_for_the_branch_recruitment_team(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Greenwich Viet Nam - Can Tho',
            'code' => 'GWCT',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create(['role' => 'hr', 'branch_id' => $branch->id, 'is_active' => true]);
        $director = User::factory()->create(['role' => 'director', 'branch_id' => $branch->id, 'is_active' => true]);
        $candidate = Candidate::query()->create([
            'name' => 'Ung vien thu moi',
            'email' => 'offer-notification@example.com',
            'phone' => '0901234567',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giang vien Cong nghe thong tin',
            'slug' => 'offer-internal-notification',
            'description' => 'Mo ta cong viec.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'branch_id' => $branch->id,
            'status' => StatusApplicationEnum::OFFER,
            'cv_path' => 'applications/offer-notification.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
        ]);
        $offer = Offer::query()->create([
            'application_id' => $application->id,
            'salary_offered' => 10000000,
            'start_date' => now()->addWeek()->toDateString(),
            'probation_months' => 2,
            'expires_at' => now()->addDays(3),
            'status' => 'pending',
            'content' => '',
        ]);

        app(RecruitmentInternalNotificationService::class)->notifyOfferSentToCandidate($offer);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $hr->id,
            'type' => 'offer_sent_to_candidate',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $director->id,
            'type' => 'offer_sent_to_candidate',
        ]);
        $this->assertSame(
            'Đề nghị đã gửi ứng viên',
            UserNotification::query()->latest('id')->firstOrFail()->title,
        );
    }
}
