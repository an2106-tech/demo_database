<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\OfferPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferPdfServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_non_empty_offer_pdf_on_the_private_disk(): void
    {
        Storage::fake('local');

        $branch = Branch::query()->create(['name' => 'PDF Branch', 'code' => 'PDF', 'city' => 'can_tho', 'is_active' => true]);
        $hr = User::factory()->create(['role' => 'hr', 'branch_id' => $branch->id]);
        $director = User::factory()->create(['name' => 'Giám đốc PDF', 'role' => 'director', 'branch_id' => $branch->id]);
        $candidate = Candidate::query()->create(['name' => 'PDF Candidate', 'email' => 'pdf@example.com', 'phone' => '0901234567']);
        $job = RecruitmentJob::query()->create([
            'title' => 'PDF Role', 'slug' => 'pdf-role', 'description' => 'PDF test.', 'status' => 'published',
            'branch_id' => $branch->id, 'positions_count' => 1, 'created_by' => $hr->id,
        ]);
        $application = Application::query()->create([
            'job_id' => $job->id, 'candidate_id' => $candidate->id, 'cv_path' => 'applications/pdf/cv.pdf',
            'apply_method' => 'cv', 'source' => 'website', 'status' => StatusApplicationEnum::OFFER,
            'branch_id' => $branch->id, 'applied_at' => now(),
        ]);
        $offer = Offer::query()->create([
            'application_id' => $application->id, 'content' => 'Nội dung đề nghị tuyển dụng.', 'salary_offered' => 5000000,
            'start_date' => now()->addWeek()->toDateString(), 'probation_months' => 2,
            'expires_at' => now()->addDays(3), 'status' => 'draft', 'approved_by_user_id' => $director->id,
        ]);

        $issuedAt = now();
        app(OfferPdfService::class)->refreshForOffer($offer, $issuedAt, $offer->expires_at, $director);
        app(OfferPdfService::class)->refreshForOffer($offer->fresh(), $issuedAt, $offer->expires_at, $director);

        $offer->refresh();
        $this->assertNotNull($offer->pdf_path);
        Storage::disk('local')->assertExists($offer->pdf_path);
        $this->assertGreaterThan(0, Storage::disk('local')->size($offer->pdf_path));

        $html = view('pdf.offer-letter', [
            'offer' => $offer,
            'letterInnerHtml' => '<p>Nội dung kiểm tra.</p>',
            'candidateName' => $candidate->name,
            'issuedAt' => $issuedAt,
            'responseDeadline' => $offer->expires_at,
            'offerReference' => 'OFR-'.$issuedAt->format('Y').'-'.str_pad((string) $offer->id, 6, '0', STR_PAD_LEFT),
            'approverName' => $director->name,
            'approverTitle' => 'Giám đốc chi nhánh',
        ])->render();

        $this->assertStringContainsString('THƯ MỜI NHẬN VIỆC', $html);
        $this->assertStringContainsString('OFR-'.$issuedAt->format('Y').'-'.str_pad((string) $offer->id, 6, '0', STR_PAD_LEFT), $html);
        $this->assertStringContainsString('Giám đốc PDF', $html);
    }
}
