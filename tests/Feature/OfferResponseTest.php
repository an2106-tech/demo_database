<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OfferResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_accepts_offer_from_signed_link(): void
    {
        [$application, $offer] = $this->makeOffer();

        $response = $this->get(URL::temporarySignedRoute(
            'offers.respond.accept',
            now()->addDays(3),
            [
                'offer' => $offer->id,
                'sent' => $offer->sent_at->getTimestamp(),
            ],
            absolute: false,
        ));

        $response->assertOk();

        $offer->refresh();
        $application->refresh();

        $this->assertSame('accepted', $offer->status);
        $this->assertNotNull($offer->accepted_at);
        $this->assertSame(StatusApplicationEnum::HIRED, $application->status);
    }

    public function test_candidate_declines_offer_from_signed_link_without_rejecting_application(): void
    {
        [$application, $offer] = $this->makeOffer();

        $formResponse = $this->get(URL::temporarySignedRoute(
            'offers.respond.decline',
            now()->addDays(3),
            [
                'offer' => $offer->id,
                'sent' => $offer->sent_at->getTimestamp(),
            ],
            absolute: false,
        ));

        $formResponse->assertOk();

        $response = $this->post(URL::temporarySignedRoute(
            'offers.respond.decline.submit',
            now()->addDays(3),
            [
                'offer' => $offer->id,
                'sent' => $offer->sent_at->getTimestamp(),
            ],
            absolute: false,
        ), [
            'decline_reason' => 'career_plan',
        ]);

        $response->assertOk();

        $offer->refresh();
        $application->refresh();

        $this->assertSame('declined', $offer->status);
        $this->assertSame(StatusApplicationEnum::OFFER, $application->status);
    }

    private function makeOffer(): array
    {
        $branch = Branch::query()->create([
            'name' => 'Offer Response Branch',
            'code' => 'ORB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Offer Response Candidate',
            'email' => 'offer-response@example.com',
            'phone' => '0901234567',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Offer Response Developer',
            'slug' => 'offer-response-developer',
            'description' => 'Offer response test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/offer-response/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::OFFER,
            'branch_id' => $branch->id,
            'applied_at' => now(),
        ]);

        $offer = Offer::query()->create([
            'application_id' => $application->id,
            'salary_offered' => 5000000,
            'start_date' => now()->addWeek()->toDateString(),
            'probation_months' => 2,
            'expires_at' => now()->addDays(3),
            'sent_at' => now(),
            'status' => 'pending',
            'content' => 'Offer content.',
        ]);

        return [$application, $offer];
    }
}
