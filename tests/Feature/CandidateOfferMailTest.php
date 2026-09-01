<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Mail\CandidateOfferMail;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\EmailTemplate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateOfferMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_mail_always_shows_candidate_response_actions_without_dashboard_button(): void
    {
        $creator = User::factory()->create();

        EmailTemplate::query()->create([
            'type' => 'offer',
            'name' => 'Legacy offer template',
            'subject' => 'Thu moi nhan viec - {{job_title}}',
            'body' => '<p>Chao {{candidate_name}}, vui long phan hoi bo phan tuyen dung.</p>',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        [$candidate, $application, $job, $offer] = $this->makeOffer();

        $html = (new CandidateOfferMail($candidate, $application, $job, $offer))->render();

        $this->assertStringContainsString('/offers/'.$offer->id.'/respond/accept', $html);
        $this->assertStringContainsString('/offers/'.$offer->id.'/respond/decline', $html);
        $this->assertStringContainsString('Phản hồi thư mời', $html);
        $this->assertStringNotContainsString('Truy cập Dashboard', $html);
        $this->assertStringNotContainsString(route('candidates.candidate_dashboard'), $html);
    }

    public function test_offer_mail_attaches_pdf_when_the_generated_file_exists(): void
    {
        Storage::fake('local');
        [$candidate, $application, $job, $offer] = $this->makeOffer();

        $offer->forceFill(['pdf_path' => 'offers/'.$offer->id.'/offer-letter.pdf'])->save();
        Storage::disk('local')->put($offer->pdf_path, '%PDF-1.4 test');

        $attachments = (new CandidateOfferMail($candidate, $application, $job, $offer->fresh()))->attachments();

        $this->assertCount(1, $attachments);
    }

    public function test_offer_mail_displays_the_exact_vietnam_response_deadline(): void
    {
        $deadline = Carbon::create(2026, 8, 31, 17, 0, 0, 'Asia/Ho_Chi_Minh');
        [$candidate, $application, $job, $offer] = $this->makeOffer($deadline);

        $html = (new CandidateOfferMail($candidate, $application, $job, $offer->fresh()))->render();

        $this->assertGreaterThanOrEqual(2, substr_count($html, '31/08/2026 17:00'));
    }

    public function test_constructing_queued_offer_mail_does_not_query_the_database(): void
    {
        [$candidate, $application, $job, $offer] = $this->makeOffer();

        DB::enableQueryLog();
        DB::flushQueryLog();

        $mail = new CandidateOfferMail($candidate, $application, $job, $offer);

        $this->assertSame([], DB::getQueryLog());
        $this->assertStringContainsString('Phản hồi thư mời', $mail->render());
    }

    private function makeOffer(?CarbonInterface $deadline = null): array
    {
        $branch = Branch::query()->create([
            'name' => 'Offer Branch',
            'code' => 'OB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Offer Candidate',
            'email' => 'offer-candidate@example.com',
            'phone' => '0901234567',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Offer Developer',
            'slug' => 'offer-developer',
            'description' => 'Offer test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/offer/cv.pdf',
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
            'expires_at' => $deadline ?? now()->addDays(3),
            'status' => 'pending',
            'content' => 'Offer content.',
        ]);

        return [$candidate, $application, $job, $offer];
    }
}
