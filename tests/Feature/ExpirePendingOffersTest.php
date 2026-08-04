<?php

namespace Tests\Feature;

use App\Console\Commands\ExpirePendingOffers;
use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirePendingOffersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_expires_pending_offers_and_keeps_the_application_at_offer_stage(): void
    {
        $branch = Branch::query()->create([
            'name' => 'Offer Expiry Branch',
            'code' => 'OEB',
            'city' => 'can_tho',
            'is_active' => true,
        ]);
        $hr = User::factory()->create(['role' => 'hr', 'is_active' => true, 'branch_id' => $branch->id]);
        $candidate = Candidate::query()->create(['name' => 'Offer Expiry Candidate', 'email' => 'expiry@example.com', 'phone' => '0901234567']);
        $job = RecruitmentJob::query()->create([
            'title' => 'Offer Expiry Role',
            'slug' => 'offer-expiry-role',
            'description' => 'Offer expiry test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'applications/expiry/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::OFFER,
            'branch_id' => $branch->id,
            'applied_at' => now(),
        ]);
        $offer = Offer::query()->create([
            'application_id' => $application->id,
            'content' => 'Offer expiry content.',
            'salary_offered' => 5000000,
            'start_date' => now()->addWeek()->toDateString(),
            'probation_months' => 2,
            'expires_at' => now()->subMinute(),
            'sent_at' => now()->subDays(3),
            'status' => 'pending',
        ]);

        $this->artisan(ExpirePendingOffers::class)->assertSuccessful();

        $this->assertSame('expired', $offer->fresh()->status);
        $this->assertSame(StatusApplicationEnum::OFFER, $application->fresh()->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'comment' => 'Đề nghị tuyển dụng đã hết hạn phản hồi.',
        ]);
    }
}
