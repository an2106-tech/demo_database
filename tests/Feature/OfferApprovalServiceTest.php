<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Mail\OfferApprovedNotificationMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\OfferApprovalService;
use App\Services\OfferPdfService;
use App\Services\RecruitmentInternalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OfferApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_offer_is_not_silently_extended_when_director_approves(): void
    {
        [$offer, $director] = $this->makeOffer(now()->subMinute(), now()->addWeek());
        $originalDeadline = $offer->expires_at->toDateTimeString();

        $service = app(OfferApprovalService::class);

        $this->assertFalse($service->approve($offer, $director));
        $this->assertSame('Hạn phản hồi đã qua. HR cần cập nhật đề nghị trước khi gửi lại duyệt.', $service->lastError());
        $this->assertSame('awaiting_approval', $offer->fresh()->status);
        $this->assertSame($originalDeadline, $offer->fresh()->expires_at?->toDateTimeString());
    }

    public function test_legacy_role_column_users_receive_offer_approval_notification(): void
    {
        Mail::fake();
        [$offer, $director, $hr] = $this->makeOffer(now()->addDays(2), now()->addWeek());

        $service = new class(
            app(OfferPdfService::class),
            app(RecruitmentInternalNotificationService::class),
        ) extends OfferApprovalService
        {
            public function notifyBranchTeam(Offer $offer, User $approver): void
            {
                $this->notifyTeam($offer, $approver);
            }
        };

        $service->notifyBranchTeam($offer, $director);

        Mail::assertQueued(OfferApprovedNotificationMail::class, function (OfferApprovedNotificationMail $mail) use ($hr): bool {
            return $mail->recipient->is($hr) && $mail->recipientRole === 'hr';
        });
    }

    /**
     * @return array{0: Offer, 1: User, 2: User}
     */
    private function makeOffer($deadline, $startDate): array
    {
        $branch = Branch::query()->create([
            'name' => 'FPT Polytechnic Can Tho',
            'code' => 'CT-OFFER-APPROVAL',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create([
            'role' => 'hr',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $director = User::factory()->create([
            'role' => 'director',
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $candidate = Candidate::query()->create([
            'name' => 'Offer Approval Candidate',
            'email' => 'offer-approval@example.com',
            'phone' => '0901234567',
        ]);
        $job = RecruitmentJob::query()->create([
            'title' => 'Giang vien Lap trinh Web',
            'slug' => 'offer-approval-job-'.uniqid(),
            'description' => 'Offer approval test.',
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
            'cv_path' => 'applications/offer-approval.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
        ]);
        $offer = Offer::query()->create([
            'application_id' => $application->id,
            'salary_offered' => 12000000,
            'start_date' => $startDate,
            'probation_months' => 2,
            'expires_at' => $deadline,
            'status' => 'awaiting_approval',
            'content' => 'Offer approval content.',
        ]);

        return [$offer, $director, $hr];
    }
}
