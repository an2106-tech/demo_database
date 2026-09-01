<?php

namespace Tests\Feature;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\Branch;
use App\Models\Candidate;
use App\Models\Offer;
use App\Models\OfferLetterTemplate;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\ApplicationWorkflowGuard;
use App\Services\OfferPdfService;
use App\Services\OfferWorkflowService;
use App\Services\RecruitmentInternalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferWorkflowPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_an_unchanged_draft_reuses_its_valid_pdf(): void
    {
        [$application, $offer, $template, $hr, $data] = $this->makeDraftOffer();

        $guard = $this->createMock(ApplicationWorkflowGuard::class);
        $guard->method('canManageOffer')->willReturn(true);
        $guard->method('shouldCreateReplacementOffer')->willReturn(false);
        $guard->method('canEditOffer')->willReturn(true);

        $pdfService = $this->createMock(OfferPdfService::class);
        $pdfService->expects($this->once())
            ->method('hasValidPdf')
            ->with($this->callback(fn (Offer $candidate): bool => $candidate->is($offer)))
            ->willReturn(true);
        $pdfService->expects($this->never())->method('refreshForOffer');

        $service = new OfferWorkflowService(
            $guard,
            $pdfService,
            $this->createMock(RecruitmentInternalNotificationService::class),
        );

        $saved = $service->saveDraft($application, [
            ...$data,
            'offer_letter_template_id' => $template->id,
        ], $hr);

        $this->assertTrue($saved->is($offer));
        $this->assertSame('offers/'.$offer->id.'/offer-letter.pdf', $saved->pdf_path);
    }

    public function test_changing_candidate_facing_offer_data_regenerates_the_pdf(): void
    {
        [$application, $offer, $template, $hr, $data] = $this->makeDraftOffer();

        $guard = $this->createMock(ApplicationWorkflowGuard::class);
        $guard->method('canManageOffer')->willReturn(true);
        $guard->method('shouldCreateReplacementOffer')->willReturn(false);
        $guard->method('canEditOffer')->willReturn(true);

        $pdfService = $this->createMock(OfferPdfService::class);
        $pdfService->expects($this->never())->method('hasValidPdf');
        $pdfService->expects($this->once())
            ->method('refreshForOffer')
            ->with($this->callback(fn (Offer $candidate): bool => $candidate->is($offer)));

        $service = new OfferWorkflowService(
            $guard,
            $pdfService,
            $this->createMock(RecruitmentInternalNotificationService::class),
        );

        $saved = $service->saveDraft($application, [
            ...$data,
            'salary_offered' => 5500000,
            'offer_letter_template_id' => $template->id,
        ], $hr);

        $this->assertSame('5500000', $saved->salary_offered);
    }

    private function makeDraftOffer(): array
    {
        $branch = Branch::query()->create([
            'name' => 'Offer Workflow Branch',
            'code' => 'OWB',
            'city' => 'ho_chi_minh',
            'is_active' => true,
        ]);

        $hr = User::factory()->create([
            'role' => 'hr',
            'is_active' => true,
            'branch_id' => $branch->id,
        ]);

        $candidate = Candidate::query()->create([
            'name' => 'Offer Workflow Candidate',
            'email' => 'offer-workflow@example.com',
            'phone' => '0901234567',
        ]);

        $job = RecruitmentJob::query()->create([
            'title' => 'Offer Workflow Developer',
            'slug' => 'offer-workflow-developer',
            'description' => 'Offer workflow test.',
            'status' => 'published',
            'branch_id' => $branch->id,
            'positions_count' => 1,
            'created_by' => $hr->id,
        ]);

        $application = Application::query()->create([
            'job_id' => $job->id,
            'candidate_id' => $candidate->id,
            'cv_path' => 'candidates/offer-workflow/cv.pdf',
            'apply_method' => 'cv',
            'source' => 'website',
            'status' => StatusApplicationEnum::OFFER,
            'branch_id' => $branch->id,
            'applied_at' => now(),
        ]);

        $template = OfferLetterTemplate::query()->create([
            'slug' => 'offer-workflow-template',
            'name' => 'Offer workflow template',
            'body_html' => '<p>Offer workflow template body.</p>',
            'is_active' => true,
        ]);

        $data = [
            'salary_offered' => 5000000,
            'salary_adjustment_reason' => null,
            'start_date' => now()->addDays(10)->startOfDay(),
            'probation_months' => 2,
            'expires_at' => now()->addDays(3)->startOfMinute(),
            'content' => 'Offer workflow content.',
        ];

        $offer = Offer::query()->create([
            'application_id' => $application->id,
            'offer_letter_template_id' => $template->id,
            'letter_template_snapshot' => [
                'name' => $template->name,
                'body_html' => $template->body_html,
            ],
            ...$data,
            'status' => 'draft',
            'pdf_path' => 'offers/pending/offer-letter.pdf',
        ]);
        $offer->forceFill(['pdf_path' => 'offers/'.$offer->id.'/offer-letter.pdf'])->save();

        return [$application, $offer, $template, $hr, $data];
    }
}
