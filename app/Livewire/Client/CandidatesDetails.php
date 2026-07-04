<?php

namespace App\Livewire\Client;

use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CandidatesDetails extends Component
{
    public Candidate $candidate;

    public $latestSubmission = null;

    public $selectedApplication = null;

    public $applications = [];

    public $timeline = [];

    public $interviews = [];

    public $scorecards = [];

    public $latestOffer = null;

    public function mount(?Candidate $candidate = null)
    {
        $candidateId = $candidate?->id ?: request()->query('id');

        if (! $candidateId) {
            return redirect()->route('home');
        }

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('candidates.login');
        }

        $this->candidate = Candidate::query()
            ->with([
                'resume',
                'user',
                'applications' => fn ($query) => $query
                    ->with([
                        'job.branch',
                        'job.department',
                        'job.workplace',
                        'statusHistories.user',
                        'interviews.interviewer',
                        'interviews.workplace',
                        'interviews.scorecards.evaluator',
                        'scorecards.evaluator',
                        'latestOffer.approvedByUser',
                        'cvAttachment',
                    ])
                    ->latest('applied_at')
                    ->latest(),
                'submissions' => fn ($query) => $query
                    ->with(['job.branch', 'job.department', 'job.workplace'])
                    ->latest(),
            ])
            ->findOrFail($candidateId);

        abort_unless($this->canViewCandidate($user, $this->candidate), 403);

        $this->applications = $this->candidate->applications
            ->filter(fn ($application) => $this->canViewApplication($user, $application))
            ->values();

        $this->selectedApplication = $this->applications->first();
        $this->latestSubmission = $this->resolveLatestSubmission($user);
        $this->timeline = $this->buildTimeline();
        $this->interviews = $this->selectedApplication?->interviews?->sortByDesc('scheduled_at')->values() ?? collect();
        $this->scorecards = $this->selectedApplication?->scorecards?->sortByDesc('created_at')->values() ?? collect();
        $this->latestOffer = $this->selectedApplication?->latestOffer;
    }

    public function render()
    {
        $layout = request()->routeIs('employers.*') ? 'layouts.employer' : 'layouts.client';

        return view('livewire.client.candidates-details')->layout($layout);
    }

    private function canViewCandidate(User $user, Candidate $candidate): bool
    {
        if ((int) $candidate->user_id === (int) $user->id) {
            return true;
        }

        if ($user->isSuperAdmin() || $user->role === 'admin') {
            return true;
        }

        $branchId = $user->branchScopeId();

        if (! $branchId) {
            return false;
        }

        return $candidate->applications()
            ->whereHas('job', fn (Builder $query) => $query->where('branch_id', $branchId))
            ->exists()
            || $candidate->submissions()
                ->whereHas('job', fn (Builder $query) => $query->where('branch_id', $branchId))
                ->exists();
    }

    private function canViewApplication(User $user, $application): bool
    {
        if ((int) $application->candidate_id === (int) $user->candidate?->id) {
            return true;
        }

        if ($user->isSuperAdmin() || $user->role === 'admin') {
            return true;
        }

        $branchId = $user->branchScopeId();

        return $branchId && (int) $application->job?->branch_id === (int) $branchId;
    }

    private function resolveLatestSubmission(User $user): ?CandidateJobSubmission
    {
        return $this->candidate->submissions
            ->filter(function (CandidateJobSubmission $submission) use ($user): bool {
                if (! $user->branchScopeId()) {
                    return true;
                }

                return (int) $submission->job?->branch_id === (int) $user->branchScopeId();
            })
            ->when(
                $this->selectedApplication,
                fn ($submissions) => $submissions->sortByDesc(fn (CandidateJobSubmission $submission): int => (int) ($submission->job_id === $this->selectedApplication->job_id)),
            )
            ->sortByDesc('created_at')
            ->first();
    }

    private function buildTimeline()
    {
        if (! $this->selectedApplication) {
            return collect();
        }

        $events = collect([
            [
                'title' => 'Ứng viên nộp hồ sơ',
                'description' => $this->selectedApplication->job?->title,
                'time' => $this->selectedApplication->applied_at ?? $this->selectedApplication->created_at,
                'type' => 'application',
            ],
        ]);

        $historyEvents = $this->selectedApplication->statusHistories
            ->map(fn ($history): array => [
                'title' => 'Chuyển trạng thái hồ sơ',
                'description' => trim(($history->from_status ?: 'new').' → '.$history->to_status.($history->comment ? ' · '.$history->comment : '')),
                'time' => $history->created_at,
                'type' => 'status',
            ]);

        $interviewEvents = $this->selectedApplication->interviews
            ->map(fn ($interview): array => [
                'title' => 'Lịch phỏng vấn'.($interview->round_name ? ': '.$interview->round_name : ''),
                'description' => ($interview->interviewer?->name ? 'Người phỏng vấn: '.$interview->interviewer->name : 'Chưa gán người phỏng vấn'),
                'time' => $interview->scheduled_at,
                'type' => 'interview',
            ]);

        $scorecardEvents = $this->selectedApplication->scorecards
            ->map(fn ($scorecard): array => [
                'title' => 'Đã có scorecard',
                'description' => 'Điểm trung bình: '.($scorecard->average_score ?? '-').($scorecard->conclusion ? ' · '.$scorecard->conclusion : ''),
                'time' => $scorecard->created_at,
                'type' => 'scorecard',
            ]);

        $offerEvents = $this->selectedApplication->offers
            ->map(fn ($offer): array => [
                'title' => 'Offer '.$this->formatOfferStatus((string) $offer->status),
                'description' => $offer->salary_offered ? number_format((float) $offer->salary_offered, 0, ',', '.').' VND' : 'Chưa có mức lương',
                'time' => $offer->created_at,
                'type' => 'offer',
            ]);

        return $events
            ->merge($historyEvents)
            ->merge($interviewEvents)
            ->merge($scorecardEvents)
            ->merge($offerEvents)
            ->filter(fn (array $event): bool => filled($event['time']))
            ->sortByDesc('time')
            ->values();
    }

    private function formatOfferStatus(string $status): string
    {
        return match ($status) {
            'awaiting_approval' => 'chờ duyệt',
            'pending' => 'đã gửi ứng viên',
            'accepted' => 'đã chấp nhận',
            'declined' => 'đã từ chối',
            'rejected' => 'bị từ chối duyệt',
            'expired' => 'hết hạn',
            default => $status,
        };
    }
}
