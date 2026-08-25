<?php

namespace App\Livewire\Client;

use App\Enums\StatusApplicationEnum;
use App\Models\Application;
use App\Models\RecruitmentJob;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CandidateDashboard extends Component
{
    public bool $hasCv = false;
    public string $greeting = '';
    public int $profileCompletion = 0;
    public $recentApplications = [];
    public int $publishedJobsCount = 0;
    public int $appliedCount = 0;
    public int $interviewCount = 0;
    public int $offeredCount = 0;
    public string $userName = '';
    public ?string $userEmail = '';
    public ?string $cvFileName = null;
    public array $checklistItems = [];
    public $aiRecommendedJobs = [];

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $this->userName = (string) ($user->name ?? '');
        $this->userEmail = (string) ($user->email ?? '');

        $candidateService = app(CandidateAccountService::class);
        $candidate = $candidateService->resolveFor($user);

        $this->publishedJobsCount = RecruitmentJob::query()
            ->where('status', 'published')
            ->count();

        $this->appliedCount = Application::query()
            ->where('candidate_id', $candidate->id)
            ->count();

        $this->interviewCount = Application::query()
            ->where('candidate_id', $candidate->id)
            ->whereIn('status', [
                StatusApplicationEnum::INTERVIEW_SCHEDULED,
                StatusApplicationEnum::INTERVIEWING,
                'interview_scheduled',
                'interview'
            ])
            ->count();

        $this->offeredCount = Application::query()
            ->where('candidate_id', $candidate->id)
            ->whereIn('status', [
                StatusApplicationEnum::OFFERED,
                StatusApplicationEnum::HIRED,
                'offer',
                'hired'
            ])
            ->count();

        $this->hasCv = $candidateService->candidateHasCv($candidate);
        if (!empty($candidate->cv_file)) {
            $this->cvFileName = basename($candidate->cv_file);
        }

        // New data for premium dashboard
        $this->greeting = $this->getGreeting();
        $this->profileCompletion = $candidateService->profileCompletion($candidate);

        // Checklist for profile completion
        $resume = $candidate->resume()->first();
        $this->checklistItems = [
            [
                'title' => 'Thông tin liên hệ & Họ tên',
                'completed' => filled($candidate->name) && filled($candidate->phone) && filled($candidate->email),
                'route' => route('candidates.candidate_profile'),
            ],
            [
                'title' => 'CV đính kèm (PDF / Word)',
                'completed' => $this->hasCv,
                'route' => route('candidates.manage_cv'),
            ],
            [
                'title' => 'Vị trí & Mục tiêu nghề nghiệp',
                'completed' => filled($resume?->profile_title) || filled($resume?->career_objective),
                'route' => route('candidates.candidate_profile'),
            ],
            [
                'title' => 'Kinh nghiệm & Dự án thực tế',
                'completed' => filled($candidate->experience_years) || !empty($resume?->experiences),
                'route' => route('candidates.candidate_profile'),
            ],
            [
                'title' => 'Kỹ năng chuyên môn & Học vấn',
                'completed' => !empty($candidate->skills) || !empty($resume?->skills) || !empty($resume?->educations),
                'route' => route('candidates.candidate_profile'),
            ],
        ];

        $this->recentApplications = Application::query()
            ->where('candidate_id', $candidate->id)
            ->with(['job.department', 'job.workplace'])
            ->latest('applied_at')
            ->latest()
            ->take(5)
            ->get();

        $metadata = is_array($candidate->metadata) ? $candidate->metadata : json_decode($candidate->metadata ?? '[]', true);
        $this->aiRecommendedJobs = $metadata['ai_recommended_jobs'] ?? [];

        if (session()->pull('run_candidate_job_match')) {
            $this->findMatchingJobsWithAi(app(\App\Services\AiMatchingService::class));
        }
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) return 'Chào buổi sáng';
        if ($hour < 18) return 'Chào buổi chiều';
        return 'Chào buổi tối';
    }

    public function findMatchingJobsWithAi(\App\Services\AiMatchingService $aiService)
    {
        if (!$this->hasCv) {
            $this->dispatch('app-notify', message: 'Bạn chưa tải CV lên hệ thống.', type: 'error');
            return;
        }

        $user = Auth::user();
        if (!$user) return;

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        
        $resume = $candidate->resume()->first();

        // Pre-rank a broad set using profile signals before asking AI for the final ranking.
        $jobs = \App\Models\RecruitmentJob::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('deadline')
                    ->orWhereDate('deadline', '>=', now()->toDateString());
            })
            ->with(['skills:id,name', 'department:id,name', 'workplace:id,name'])
            ->latest()
            ->take(100)
            ->get()
            ->map(fn ($job) => [
                'job' => $job,
                'pre_score' => $this->preliminaryJobScore($job, $resume, $candidate),
            ])
            ->sortByDesc('pre_score')
            ->take(30)
            ->values()
            ->map(function ($job) {
                $job = $job['job'];

                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => trim(mb_substr(strip_tags($job->description), 0, 3000)),
                    'department' => $job->department?->name,
                    'workplace' => $job->workplace?->name,
                    'skills' => $job->skills->map(fn ($skill) => [
                        'name' => $skill->name,
                        'level' => $skill->pivot->level,
                        'is_required' => (bool) $skill->pivot->is_required,
                    ])->values()->all(),
                ];
            })
            ->toArray();

        if (empty($jobs)) {
            $this->dispatch('app-notify', message: 'Hiện tại chưa có công việc nào trên hệ thống.', type: 'error');
            return;
        }

        $cvText = '';
        if ($resume) {
            $cvText = "Mục tiêu nghề nghiệp: " . ($resume->career_objective ?? 'Không có') . "\n";
            $cvText .= "Công việc mong muốn: " . json_encode($resume->desired_job ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            $cvText .= "Kinh nghiệm: " . json_encode($resume->experiences ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            $cvText .= "Học vấn: " . json_encode($resume->educations ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            $cvText .= "Kỹ năng: " . json_encode($resume->skills ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            $cvText = "Họ tên: {$candidate->name}\nKinh nghiệm: {$candidate->experience_years} năm\nKỹ năng: " . json_encode($candidate->skills ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        }

        $pdfPath = null;
        if (!empty($candidate->cv_file)) {
            $pdfPath = \Illuminate\Support\Facades\Storage::disk('public')->path($candidate->cv_file);
        }

        $result = $aiService->matchJobsWithCv($cvText, $jobs, $pdfPath);

        if (is_array($result)) {
            // Load full job details for the recommended jobs
            $recommendedJobs = [];
            foreach ($result as $item) {
                $job = \App\Models\RecruitmentJob::find($item['job_id']);
                if ($job) {
                    $recommendedJobs[] = [
                        'job' => $job, // We won't save the full job model to DB, just for current render
                        'job_id' => $job->id,
                        'title' => $job->title,
                        'public_url' => $job->public_url,
                        'match_percentage' => $item['match_percentage'] ?? 0,
                        'reason' => $item['reason'] ?? '',
                        'matched_requirements' => $item['matched_requirements'] ?? [],
                        'missing_requirements' => $item['missing_requirements'] ?? [],
                    ];
                }
            }
            
            $this->aiRecommendedJobs = $recommendedJobs;
            
            $metadata = is_array($candidate->metadata) ? $candidate->metadata : json_decode($candidate->metadata ?? '[]', true);
            $metadata['ai_recommended_jobs'] = array_map(function($j) {
                unset($j['job']); // Don't serialize the model
                return $j;
            }, $recommendedJobs);
            
            $candidate->update(['metadata' => $metadata]);
            
            $this->dispatch('app-notify', message: 'AI đã phân tích và tìm ra ' . count($recommendedJobs) . ' công việc phù hợp với bạn.', type: 'success');
        } else {
            $this->dispatch(
                'app-notify',
                message: $aiService->getLastError() ?: 'Không thể phân tích tìm việc lúc này. Vui lòng thử lại sau.',
                type: 'error'
            );
        }
    }

    private function preliminaryJobScore($job, $resume, $candidate): int
    {
        $desiredJob = is_array($resume?->desired_job) ? $resume->desired_job : [];
        $profileSkills = $resume?->skills ?: ($candidate->skills ?? []);

        $desiredTerms = $this->searchTerms([
            $desiredJob['position'] ?? null,
            $desiredJob['level'] ?? null,
            $desiredJob['location'] ?? ($desiredJob['workplace'] ?? null),
            $resume?->profile_title,
            $profileSkills,
        ]);

        if ($desiredTerms === []) {
            return 0;
        }

        $title = $this->normalizeSearchText($job->title);
        $department = $this->normalizeSearchText($job->department?->name);
        $workplace = $this->normalizeSearchText($job->workplace?->name);
        $skills = $this->normalizeSearchText($job->skills->pluck('name')->implode(' '));
        $description = $this->normalizeSearchText(strip_tags($job->description));
        $score = 0;

        foreach ($desiredTerms as $term) {
            $score += str_contains($title, $term) ? 10 : 0;
            $score += str_contains($skills, $term) ? 7 : 0;
            $score += str_contains($department, $term) ? 5 : 0;
            $score += str_contains($workplace, $term) ? 4 : 0;
            $score += str_contains($description, $term) ? 1 : 0;
        }

        return $score;
    }

    private function searchTerms(array $values): array
    {
        $flattened = [];
        array_walk_recursive($values, function ($value) use (&$flattened) {
            if (is_string($value) && filled($value)) {
                $flattened[] = $value;
            }
        });

        $tokens = preg_split('/\s+/u', $this->normalizeSearchText(implode(' ', $flattened))) ?: [];

        return array_values(array_unique(array_filter(
            $tokens,
            fn (string $token) => mb_strlen($token) >= 3
        )));
    }

    private function normalizeSearchText(?string $value): string
    {
        return Str::lower(Str::ascii(trim((string) $value)));
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-dashboard');
    }
}
