<?php

namespace App\Livewire\Client;

use App\Models\Application;
use App\Models\RecruitmentJob;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
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
    public string $userName = '';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $this->userName = (string) ($user->name ?? '');

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        $candidateService = app(CandidateAccountService::class);

        $this->publishedJobsCount = RecruitmentJob::query()
            ->where('status', 'published')
            ->count();

        $this->appliedCount = Application::query()
            ->where('candidate_id', $candidate->id)
            ->count();

        $this->hasCv = $candidateService->candidateHasCv($candidate);

        // New data for premium dashboard
        $this->greeting = $this->getGreeting();
        $this->profileCompletion = $candidateService->profileCompletion($candidate);
        $this->recentApplications = Application::query()
            ->where('candidate_id', $candidate->id)
            ->with('job')
            ->latest('applied_at')
            ->latest()
            ->take(5)
            ->get();

        $this->aiScore = $candidate->match_score;
        $matchReasons = $candidate->match_reasons ?? [];
        $this->aiSummary = $matchReasons['summary'] ?? null;
        $this->aiStrengths = $matchReasons['strengths'] ?? [];
        $this->aiWeaknesses = $matchReasons['weaknesses'] ?? [];
        $this->aiSuggestions = $matchReasons['suggestions'] ?? [];
        $this->aiAtsKeywords = $matchReasons['ats_keywords'] ?? [];
        $this->aiMissingKeywords = $matchReasons['missing_keywords'] ?? [];
        $this->aiLayoutComment = $matchReasons['layout_comment'] ?? null;
        
        $metadata = is_array($candidate->metadata) ? $candidate->metadata : json_decode($candidate->metadata ?? '[]', true);
        $this->aiRecommendedJobs = $metadata['ai_recommended_jobs'] ?? [];
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;
        if ($hour < 12) return 'Chào buổi sáng';
        if ($hour < 18) return 'Chào buổi chiều';
        return 'Chào buổi tối';
    }

    public $aiScore = null;
    public $aiSummary = null;
    public $aiStrengths = [];
    public $aiWeaknesses = [];
    public $aiSuggestions = [];
    public $aiAtsKeywords = [];
    public $aiMissingKeywords = [];
    public $aiLayoutComment = null;
    public $aiRecommendedJobs = [];

    public function analyzeCvWithAi(\App\Services\AiMatchingService $aiService)
    {
        if (!$this->hasCv) {
            $this->dispatch('app-notify', message: 'Bạn chưa tải CV lên hệ thống.', type: 'error');
            return;
        }

        $user = Auth::user();
        if (!$user) return;

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        $resume = $candidate->resume()->first();

        $cvText = '';
        if ($resume) {
            $cvText = "Mục tiêu nghề nghiệp: " . ($resume->career_objective ?? 'Không có') . "\n";
            $cvText .= "Kinh nghiệm: " . json_encode($resume->experiences ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            $cvText .= "Học vấn: " . json_encode($resume->educations ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            $cvText .= "Kỹ năng: " . json_encode($resume->skills ?? [], JSON_UNESCAPED_UNICODE) . "\n";
            $cvText .= "Ngôn ngữ: " . json_encode($resume->languages ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            $cvText = "Họ tên: {$candidate->name}\nKinh nghiệm: {$candidate->experience_years} năm\nKỹ năng: " . json_encode($candidate->skills ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        }

        $pdfPath = null;
        if (!empty($candidate->cv_file)) {
            $pdfPath = \Illuminate\Support\Facades\Storage::disk('public')->path($candidate->cv_file);
        }

        $result = $aiService->evaluateGeneralCv($cvText, $pdfPath);

        if ($result && isset($result['score'])) {
            $this->aiScore = $result['score'];
            $this->aiSummary = $result['summary'] ?? null;
            $this->aiStrengths = $result['strengths'] ?? [];
            $this->aiWeaknesses = $result['weaknesses'] ?? [];
            $this->aiSuggestions = $result['suggestions'] ?? [];
            $this->aiAtsKeywords = $result['ats_keywords'] ?? [];
            $this->aiMissingKeywords = $result['missing_keywords'] ?? [];
            $this->aiLayoutComment = $result['layout_comment'] ?? null;

            $candidate->update([
                'match_score' => $result['score'],
                'match_reasons' => [
                    'summary' => $this->aiSummary,
                    'strengths' => $this->aiStrengths,
                    'weaknesses' => $this->aiWeaknesses,
                    'suggestions' => $this->aiSuggestions,
                    'ats_keywords' => $this->aiAtsKeywords,
                    'missing_keywords' => $this->aiMissingKeywords,
                    'layout_comment' => $this->aiLayoutComment,
                ]
            ]);
        } else {
            $this->dispatch('app-notify', message: 'Không thể phân tích CV lúc này hoặc CV quá ngắn. Vui lòng thử lại sau.', type: 'error');
        }
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
        
        // Build a broader candidate set; AI will rank and return at most 3 jobs.
        $jobs = \App\Models\RecruitmentJob::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('deadline')
                    ->orWhereDate('deadline', '>=', now()->toDateString());
            })
            ->with(['skills:id,name', 'department:id,name', 'workplace:id,name'])
            ->latest()
            ->take(30)
            ->get()
            ->map(function ($job) {
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

        $resume = $candidate->resume()->first();
        $cvText = '';
        if ($resume) {
            $cvText = "Mục tiêu nghề nghiệp: " . ($resume->career_objective ?? 'Không có') . "\n";
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
            $this->dispatch('app-notify', message: 'Không thể phân tích tìm việc lúc này. Vui lòng thử lại sau.', type: 'error');
        }
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-dashboard');
    }
}
