<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Interview;
use App\Models\Offer;
use App\Models\RecruitmentJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AiChatContextService
{
    /**
     * @return array<int, array{key: string, label: string, content: string, url: string|null}>
     */
    public function build(User $user, string $audience): array
    {
        return $audience === 'employer'
            ? $this->employerContext($user)
            : $this->candidateContext($user);
    }

    private function candidateContext(User $user): array
    {
        $candidate = Candidate::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhere(function (Builder $fallback) use ($user): void {
                        $fallback->whereNull('user_id')->where('email', $user->email);
                    });
            })
            ->with('resume')
            ->first();

        $sources = [];

        if ($candidate) {
            $resume = $candidate->resume;
            $sources[] = [
                'key' => 'candidate-profile',
                'label' => 'Hồ sơ ứng viên của bạn',
                'url' => route('candidates.candidate_profile'),
                'content' => $this->clean(implode("\n", array_filter([
                    'Họ tên: '.$candidate->name,
                    'Số năm kinh nghiệm: '.($candidate->experience_years ?? 'chưa cập nhật'),
                    'Tiêu đề hồ sơ: '.($resume?->profile_title ?? 'chưa cập nhật'),
                    'Mục tiêu nghề nghiệp: '.($resume?->career_objective ?? 'chưa cập nhật'),
                    'Vị trí mong muốn: '.data_get($resume?->desired_job, 'position', 'chưa cập nhật'),
                    'Kỹ năng: '.$this->summarizeArray($resume?->skills ?? []),
                ]))),
            ];

            $applications = Application::query()
                ->where('candidate_id', $candidate->id)
                ->with(['job', 'latestInterview', 'latestOffer'])
                ->latest('applied_at')
                ->take(6)
                ->get();

            foreach ($applications as $application) {
                $status = $application->status?->getLabel() ?? (string) $application->status;
                $sources[] = [
                    'key' => 'application-'.$application->id,
                    'label' => 'Hồ sơ ứng tuyển: '.($application->job?->title ?? '#'.$application->id),
                    'url' => route('candidates.application_detail', ['application' => $application]),
                    'content' => $this->clean(implode("\n", array_filter([
                        'Vị trí: '.($application->job?->title ?? 'Không xác định'),
                        'Trạng thái: '.$status,
                        'Ngày ứng tuyển: '.$application->applied_at?->format('d/m/Y H:i'),
                        $application->latestInterview?->scheduled_at
                            ? 'Lịch phỏng vấn gần nhất: '.$application->latestInterview->scheduled_at->format('d/m/Y H:i')
                            : null,
                        $application->latestOffer
                            ? 'Trạng thái offer: '.$application->latestOffer->status
                            : null,
                    ]))),
                ];
            }
        }

        $jobs = RecruitmentJob::query()
            ->where('status', 'published')
            ->where(fn (Builder $query) => $query->whereNull('deadline')->orWhereDate('deadline', '>=', now()))
            ->with(['branch:id,name', 'workplace:id,name', 'skills:id,name'])
            ->latest('id')
            ->take(80)
            ->get()
            ->map(fn (RecruitmentJob $job): array => [
                'job' => $job,
                'score' => $this->candidateJobScore($job, $candidate),
            ])
            ->sortByDesc('score')
            ->take(6)
            ->pluck('job');

        foreach ($jobs as $job) {
            $sources[] = [
                'key' => 'job-'.$job->id,
                'label' => 'Tin tuyển dụng: '.$job->title,
                'url' => route('jobs.public', ['slug' => $job->slug]),
                'content' => $this->clean(implode("\n", array_filter([
                    'Vị trí: '.$job->title,
                    'Chi nhánh: '.$job->branch?->name,
                    'Nơi làm việc: '.$job->workplace?->name,
                    'Hạn nộp: '.$job->deadline?->format('d/m/Y'),
                    'Kỹ năng: '.$job->skills->pluck('name')->implode(', '),
                    'Mô tả: '.$job->description,
                ]))),
            ];
        }

        return $sources;
    }

    private function employerContext(User $user): array
    {
        $jobQuery = RecruitmentJob::query();
        $this->scopeJobsForEmployer($jobQuery, $user);

        $jobs = $jobQuery
            ->withCount('applications')
            ->latest('id')
            ->take(8)
            ->get();

        $applicationQuery = Application::query()
            ->with(['candidate:id,name', 'job:id,title,branch_id,created_by', 'latestInterview']);
        $this->scopeApplicationsForEmployer($applicationQuery, $user);
        $applications = $applicationQuery->latest('id')->take(10)->get();

        $statusCountsQuery = Application::query();
        $this->scopeApplicationsForEmployer($statusCountsQuery, $user);
        $statusCounts = $statusCountsQuery
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $sources = $this->operationalContext($user);
        $sources[] = [
            'key' => 'recruitment-pipeline',
            'label' => 'Tổng quan pipeline tuyển dụng',
            'url' => route('employers.application_pipeline'),
            'content' => $this->clean('Số lượng hồ sơ theo trạng thái: '.json_encode($statusCounts, JSON_UNESCAPED_UNICODE)),
        ];

        foreach ($jobs as $job) {
            $sources[] = [
                'key' => 'employer-job-'.$job->id,
                'label' => 'Tin tuyển dụng: '.$job->title,
                'url' => route('employers.manage_jobs'),
                'content' => $this->clean(implode("\n", [
                    'Vị trí: '.$job->title,
                    'Trạng thái: '.($job->status?->value ?? (string) $job->status),
                    'Số hồ sơ: '.$job->applications_count,
                    'Hạn nộp: '.($job->deadline?->format('d/m/Y') ?? 'không có'),
                    'Mô tả: '.$job->description,
                ])),
            ];
        }

        foreach ($applications as $application) {
            $sources[] = [
                'key' => 'employer-application-'.$application->id,
                'label' => 'Ứng viên '.$application->candidate?->name.' — '.$application->job?->title,
                'url' => $application->candidate_id
                    ? route('employers.candidate_detail', ['candidate' => $application->candidate_id])
                    : route('employers.application_pipeline'),
                'content' => $this->clean(implode("\n", array_filter([
                    'Ứng viên: '.$application->candidate?->name,
                    'Vị trí: '.$application->job?->title,
                    'Trạng thái: '.($application->status?->getLabel() ?? (string) $application->status),
                    'Ngày ứng tuyển: '.$application->applied_at?->format('d/m/Y H:i'),
                    'Phỏng vấn: '.$application->latestInterview?->scheduled_at?->format('d/m/Y H:i'),
                ]))),
            ];
        }

        return $sources;
    }

    /**
     * @return array<int, array{key: string, label: string, content: string, url: string|null}>
     */
    private function operationalContext(User $user): array
    {
        $applications = Application::query();
        $this->scopeApplicationsForEmployer($applications, $user);

        $pendingJobs = RecruitmentJob::query()->where('status', 'pending');
        $this->scopeJobsForEmployer($pendingJobs, $user);

        $offers = Offer::query()->whereHas('application', function (Builder $query) use ($user) {
            $this->scopeApplicationsForEmployer($query, $user);
        });
        $interviews = Interview::query()->whereHas('application', function (Builder $query) use ($user) {
            $this->scopeApplicationsForEmployer($query, $user);
        });

        $workload = [
            'tin tuyển dụng chờ duyệt' => (clone $pendingJobs)->count(),
            'CV chờ sàng lọc' => (clone $applications)->where('status', 'cv_reviewing')->count(),
            'lịch phỏng vấn chưa gửi thư mời' => (clone $interviews)
                ->whereNull('invite_sent_at')
                ->where('scheduled_at', '>=', now())
                ->where('result', 'pending')
                ->count(),
            'phỏng vấn quá hạn chưa chấm' => (clone $interviews)
                ->where('scheduled_at', '<', now())
                ->where('result', 'pending')
                ->count(),
            'đề nghị tuyển dụng nháp' => (clone $offers)->where('status', 'draft')->count(),
            'đề nghị chờ giám đốc duyệt' => (clone $offers)->where('status', 'awaiting_approval')->count(),
            'đề nghị sắp hết hạn' => (clone $offers)
                ->where('status', 'pending')
                ->whereBetween('expires_at', [now(), now()->addDays(2)])
                ->count(),
        ];

        $sources = [[
            'key' => 'operational-workload',
            'label' => $user->role === 'director' ? 'Việc cần giám đốc xử lý' : 'Việc tuyển dụng cần ưu tiên',
            'url' => route('employers.application_pipeline'),
            'content' => $this->clean(collect($workload)
                ->map(fn (int $count, string $label): string => $label.': '.$count)
                ->implode("\n")),
        ]];

        if (in_array($user->role, ['director', 'admin'], true) || $user->isSuperAdmin()) {
            $recentApplications = clone $applications;
            $recentApplications->where('created_at', '>=', now()->subDays(30));
            $totalRecent = (clone $recentApplications)->count();
            $hiredRecent = (clone $recentApplications)->where('status', 'hired')->count();
            $rejectedRecent = (clone $recentApplications)->where('status', 'rejected')->count();
            $conversion = $totalRecent > 0 ? round(($hiredRecent / $totalRecent) * 100, 1) : 0;

            $sources[] = [
                'key' => 'branch-performance',
                'label' => 'Hiệu quả tuyển dụng 30 ngày',
                'url' => route('employers.dashboard'),
                'content' => $this->clean(implode("\n", [
                    'Hồ sơ mới: '.$totalRecent,
                    'Đã tuyển: '.$hiredRecent,
                    'Đã từ chối: '.$rejectedRecent,
                    'Tỷ lệ tuyển trên hồ sơ mới: '.$conversion.'%',
                ])),
            ];

            $hrLoads = (clone $applications)
                ->whereNotNull('assigned_hr_id')
                ->whereNotIn('status', ['hired', 'rejected'])
                ->selectRaw('assigned_hr_id, count(*) as aggregate')
                ->groupBy('assigned_hr_id')
                ->orderByDesc('aggregate')
                ->limit(8)
                ->get();
            $hrNames = User::query()
                ->whereIn('id', $hrLoads->pluck('assigned_hr_id'))
                ->pluck('name', 'id');

            $sources[] = [
                'key' => 'hr-workload',
                'label' => 'Khối lượng hồ sơ đang mở theo HR',
                'url' => route('employers.application_pipeline'),
                'content' => $this->clean($hrLoads
                    ->map(fn ($row): string => ($hrNames[$row->assigned_hr_id] ?? 'HR #'.$row->assigned_hr_id).': '.$row->aggregate.' hồ sơ')
                    ->implode("\n") ?: 'Chưa có hồ sơ đang mở được phân công cho HR.'),
            ];

            $awaitingOffers = (clone $offers)
                ->where('status', 'awaiting_approval')
                ->with(['application.candidate:id,name', 'application.job:id,title'])
                ->latest('approval_requested_at')
                ->take(6)
                ->get();

            $sources[] = [
                'key' => 'offers-awaiting-approval',
                'label' => 'Đề nghị tuyển dụng chờ duyệt',
                'url' => route('employers.application_pipeline'),
                'content' => $this->clean($awaitingOffers
                    ->map(fn (Offer $offer): string => implode(' — ', array_filter([
                        $offer->application?->candidate?->name,
                        $offer->application?->job?->title,
                        $offer->approval_requested_at?->format('d/m/Y H:i'),
                    ])))
                    ->implode("\n") ?: 'Không có đề nghị tuyển dụng đang chờ duyệt.'),
            ];
        }

        return $sources;
    }

    private function scopeJobsForEmployer(Builder $query, User $user): void
    {
        if ($branchId = $user->branchScopeId()) {
            $query->where('branch_id', $branchId);

            return;
        }

        if (! $user->isSuperAdmin() && ! in_array($user->role, ['admin', 'director'], true)) {
            $query->where('created_by', $user->id);
        }
    }

    private function scopeApplicationsForEmployer(Builder $query, User $user): void
    {
        if ($branchId = $user->branchScopeId()) {
            $query->where(function (Builder $scoped) use ($branchId) {
                $scoped->where('branch_id', $branchId)
                    ->orWhere(function (Builder $legacy) use ($branchId): void {
                        $legacy->whereNull('branch_id')
                            ->whereHas('job', fn (Builder $job) => $job->where('branch_id', $branchId));
                    });
            });

            return;
        }

        if (! $user->isSuperAdmin() && ! in_array($user->role, ['admin', 'director'], true)) {
            $query->whereHas('job', fn (Builder $job) => $job->where('created_by', $user->id));
        }
    }

    private function summarizeArray(array $items): string
    {
        $values = [];
        array_walk_recursive($items, function ($value, $key) use (&$values) {
            if (is_string($value) && filled($value) && in_array($key, ['name', 'title', 'position', 'level'], true)) {
                $values[] = $value;
            }
        });

        return implode(', ', array_slice(array_values(array_unique($values)), 0, 20)) ?: 'chưa cập nhật';
    }

    private function candidateJobScore(RecruitmentJob $job, ?Candidate $candidate): int
    {
        if (! $candidate) {
            return 0;
        }

        $resume = $candidate->resume;
        $terms = $this->searchTerms([
            $resume?->profile_title,
            data_get($resume?->desired_job, 'position'),
            data_get($resume?->desired_job, 'level'),
            $resume?->skills ?? [],
        ]);

        if ($terms === []) {
            return 0;
        }

        $title = $this->normalizeSearchText($job->title);
        $skills = $this->normalizeSearchText($job->skills->pluck('name')->implode(' '));
        $description = $this->normalizeSearchText(strip_tags((string) $job->description));
        $score = 0;

        foreach ($terms as $term) {
            $score += str_contains($title, $term) ? 12 : 0;
            $score += str_contains($skills, $term) ? 8 : 0;
            $score += str_contains($description, $term) ? 2 : 0;
        }

        return $score;
    }

    private function searchTerms(array $values): array
    {
        $flattened = [];
        array_walk_recursive($values, function ($value) use (&$flattened): void {
            if (is_string($value) && filled($value)) {
                $flattened[] = $value;
            }
        });

        $tokens = preg_split('/\s+/u', $this->normalizeSearchText(implode(' ', $flattened))) ?: [];

        return array_values(array_unique(array_filter(
            $tokens,
            fn (string $token): bool => mb_strlen($token) >= 3
        )));
    }

    private function normalizeSearchText(?string $value): string
    {
        return Str::lower(Str::ascii(trim((string) $value)));
    }

    private function clean(?string $value): string
    {
        $plain = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/u', ' ', $plain) ?: '';

        return Str::limit(trim($plain), 1800, '…');
    }
}
