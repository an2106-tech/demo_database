<?php

namespace App\Livewire\Client\Job;

use App\Models\CandidateResume;
use App\Services\AiMatchingService;
use App\Services\CandidateAccountService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Enums\VietnamProvince;
use App\Models\RecruitmentJob;
use Illuminate\Support\Facades\Route;

class JobDetail extends Component
{
    public $id;
    public $job;
    public bool $hasCandidateAccess = false;
    public bool $hasCv = false;
    public ?array $jobFitAiResult = null;

    public function mount($id = null, $slug = null)
    {
        if ($slug !== null) {
            // Public route: /jobs/{slug}
            $this->job = RecruitmentJob::with(['branch', 'workplace', 'department', 'skills', 'categories'])
                ->where('slug', $slug)
                ->firstOrFail();
            $this->id = $this->job->id;
        } else {
            // Internal route: /candidates/job-detail/{id}
            $this->id = $id;
            $this->job = RecruitmentJob::with(['branch', 'workplace', 'department', 'skills', 'categories'])
                ->findOrFail($id);
        }

        $user = Auth::user();
        if ($user) {
            $candidateService = app(CandidateAccountService::class);
            $this->hasCandidateAccess = $candidateService->hasCandidateAccount($user);

            if ($this->hasCandidateAccess) {
                $candidate = $candidateService->resolveFor($user);
                $this->hasCv = $candidateService->candidateHasCv($candidate);
            }
        }
    }

    public function checkJobFitWithAi(AiMatchingService $aiService): void
    {
        $user = Auth::user();

        if (! $user || ! $this->hasCandidateAccess) {
            $this->dispatch('app-notify', message: 'Vui lòng đăng nhập bằng tài khoản ứng viên để dùng AI kiểm tra phù hợp.', type: 'error');

            return;
        }

        $candidateService = app(CandidateAccountService::class);
        $candidate = $candidateService->resolveFor($user);

        if (! $candidateService->candidateHasCv($candidate)) {
            $this->dispatch('app-notify', message: 'Bạn cần tải CV lên hồ sơ trước khi kiểm tra AI.', type: 'error');

            return;
        }

        $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);

        $cvText = '';
        $cvText .= "Mục tiêu nghề nghiệp: " . ($resume->career_objective ?? 'Không có') . "\n";
        $cvText .= "Vị trí mong muốn: " . json_encode($resume->desired_job ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Kinh nghiệm: " . json_encode($resume->experiences ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Học vấn: " . json_encode($resume->educations ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Kỹ năng: " . json_encode($resume->skills ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Ngôn ngữ: " . json_encode($resume->languages ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Thông tin cá nhân: " . json_encode($resume->personal_info ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Tiêu đề hồ sơ: " . ($resume->profile_title ?? 'Không có') . "\n";

        if (filled($candidate->name)) {
            $cvText .= "Tên ứng viên: {$candidate->name}\n";
        }

        if (filled($candidate->experience_years)) {
            $cvText .= "Số năm kinh nghiệm: {$candidate->experience_years}\n";
        }

        $pdfPath = null;
        if (!empty($candidate->cv_file)) {
            $pdfPath = \Illuminate\Support\Facades\Storage::disk('public')->path($candidate->cv_file);
        }

        $result = $aiService->evaluateJobFitWithCv($cvText, [
            'id' => $this->job->id,
            'title' => $this->job->title,
            'description' => trim(strip_tags((string) $this->job->description)),
            'department' => $this->job->department?->name,
            'workplace' => $this->job->workplace?->name,
            'branch' => $this->job->branch?->name,
            'skills' => $this->job->skills->map(fn ($skill) => $skill->name)->values()->all(),
            'categories' => $this->job->categories->map(fn ($category) => $category->name)->values()->all(),
            'deadline' => optional($this->job->deadline)?->toDateString(),
        ], $pdfPath);

        if (is_array($result)) {
            $this->jobFitAiResult = $result;
            $this->dispatch('app-notify', message: 'AI đã kiểm tra độ phù hợp cho công việc này.', type: 'success');

            return;
        }

        $this->dispatch(
            'app-notify',
            message: $aiService->getLastError() ?: 'Không thể kiểm tra độ phù hợp lúc này. Vui lòng thử lại sau.',
            type: 'error'
        );
    }

    public function render()
    {
        $routeName = Route::currentRouteName();
        $layout = str_starts_with((string) $routeName, 'employers.')
            ? 'layouts.employer'
            : 'layouts.client';

        return view('livewire.client.job.job-detail', [
            'job' => $this->job
        ])->layout($layout);
    }
}
