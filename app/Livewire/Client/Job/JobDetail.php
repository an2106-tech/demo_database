<?php

namespace App\Livewire\Client\Job;

use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
use App\Services\AiMatchingService;
use App\Services\AiMockInterviewService;
use App\Services\CandidateAccountService;
use App\Services\CvTextExtractor;
use App\Services\InterviewProcessTemplateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class JobDetail extends Component
{
    public $id;

    public $job;

    public bool $hasCandidateAccess = false;

    public bool $hasCv = false;

    public bool $showApplyAction = false;

    public ?array $jobFitAiResult = null;

    public function mount($id = null, $slug = null)
    {
        // Livewire updates are served through its own endpoint, so this must be
        // captured on the initial page request rather than recomputed in Blade.
        $this->showApplyAction = request()->routeIs('candidates.*') || request()->routeIs('jobs.public');

        if ($slug !== null) {
            $this->job = RecruitmentJob::with(['branch', 'workplace', 'department', 'skills', 'categories'])
                ->where('slug', $slug)
                ->firstOrFail();
            $this->id = $this->job->id;
        } else {
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
            $this->dispatch('app-notify', message: 'Vui long dang nhap bang tai khoan ung vien de dung AI kiem tra phu hop.', type: 'error');

            return;
        }

        $candidateService = app(CandidateAccountService::class);
        $candidate = $candidateService->resolveFor($user);

        if (! $candidateService->candidateHasCv($candidate)) {
            $this->dispatch('app-notify', message: 'Ban can tai CV len ho so truoc khi kiem tra AI.', type: 'error');

            return;
        }

        $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);

        // Build structured CV text from resume form data
        $cvParts = [];

        if (filled($candidate->name)) {
            $cvParts[] = "Ten ung vien: {$candidate->name}";
        }
        if (filled($candidate->experience_years)) {
            $cvParts[] = "So nam kinh nghiem: {$candidate->experience_years}";
        }
        if (filled($resume->profile_title)) {
            $cvParts[] = "Tieu de ho so: {$resume->profile_title}";
        }
        if (filled($resume->career_objective)) {
            $cvParts[] = "Muc tieu nghe nghiep: {$resume->career_objective}";
        }
        if (! empty($resume->desired_job)) {
            $cvParts[] = 'Vi tri mong muon: '.$this->formatArrayField($resume->desired_job);
        }
        if (! empty($resume->skills)) {
            $cvParts[] = 'Ky nang: '.$this->formatArrayField($resume->skills);
        }
        if (! empty($resume->experiences)) {
            $cvParts[] = "Kinh nghiem lam viec:\n".$this->formatExperiences($resume->experiences);
        }
        if (! empty($resume->educations)) {
            $cvParts[] = "Hoc van:\n".$this->formatArrayField($resume->educations);
        }
        if (! empty($resume->certifications)) {
            $cvParts[] = 'Chung chi: '.$this->formatArrayField($resume->certifications);
        }
        if (! empty($resume->languages)) {
            $cvParts[] = 'Ngoai ngu: '.$this->formatArrayField($resume->languages);
        }
        if (! empty($resume->achievements)) {
            $cvParts[] = 'Thanh tich: '.$this->formatArrayField($resume->achievements);
        }

        $cvText = implode("\n", $cvParts);

        // Extract text from uploaded CV file (PDF/DOCX) and append
        $pdfPath = null;
        if (! empty($candidate->cv_file)) {
            $pdfPath = Storage::disk('public')->path($candidate->cv_file);

            try {
                $extractedFileText = app(CvTextExtractor::class)->extractFromPublicPath($candidate->cv_file);
                if (filled($extractedFileText) && strlen($extractedFileText) > 50) {
                    $cvText .= "\n\n[Noi dung file CV]\n".mb_substr($extractedFileText, 0, 4000);
                }
            } catch (\Throwable) {
                // silently ignore extraction errors
            }
        }

        // Build enriched JD data with structured sections
        $descriptionHtml = (string) ($this->job->description ?? '');
        $descriptionPlain = trim(strip_tags($descriptionHtml));
        $parsedSections = $this->parseJdSections($descriptionHtml);

        $jobData = [
            'id' => $this->job->id,
            'title' => $this->job->title,
            'description' => $descriptionPlain,
            'requirements' => $parsedSections['requirements'] ?? '',
            'responsibilities' => $parsedSections['responsibilities'] ?? '',
            'benefits' => $parsedSections['benefits'] ?? '',
            'department' => $this->job->department?->name,
            'workplace' => $this->job->workplace?->name,
            'branch' => $this->job->branch?->name,
            'skills' => $this->job->skills->map(fn ($s) => $s->name)->values()->all(),
            'categories' => $this->job->categories->map(fn ($c) => $c->name)->values()->all(),
            'deadline' => optional($this->job->deadline)?->toDateString(),
        ];

        // Smart cache version – invalidates when candidate, resume or job changes
        $cacheVersion = md5(
            ($candidate->updated_at?->timestamp ?? 0).'_'.
            ($resume->updated_at?->timestamp ?? 0).'_'.
            ($this->job->updated_at?->timestamp ?? 0)
        );

        $result = $aiService->evaluateJobFitWithCv($cvText, $jobData, $pdfPath, $cacheVersion);

        if (is_array($result)) {
            $this->jobFitAiResult = $result;
            $this->dispatch('app-notify', message: 'AI da kiem tra do phu hop cho cong viec nay.', type: 'success');

            return;
        }

        $this->dispatch(
            'app-notify',
            message: $aiService->getLastError() ?: 'Khong the kiem tra do phu hop luc nay. Vui long thu lai sau.',
            type: 'error'
        );
    }

    private function formatArrayField(mixed $field): string
    {
        if (empty($field)) {
            return 'N/A';
        }
        if (is_string($field)) {
            return $field;
        }
        if (is_array($field)) {
            $first = reset($field);
            if (is_string($first) || is_numeric($first)) {
                return implode(', ', array_filter(array_map('strval', $field)));
            }

            return json_encode($field, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return json_encode($field, JSON_UNESCAPED_UNICODE);
    }

    private function formatExperiences(mixed $experiences): string
    {
        if (empty($experiences) || ! is_array($experiences)) {
            return 'N/A';
        }
        $lines = [];
        foreach ($experiences as $exp) {
            if (! is_array($exp)) {
                continue;
            }
            $line = '';
            if (! empty($exp['position'])) {
                $line .= $exp['position'];
            }
            if (! empty($exp['company'])) {
                $line .= ' at '.$exp['company'];
            }
            if (! empty($exp['duration'])) {
                $line .= ' ('.$exp['duration'].')';
            }
            if (! empty($exp['description'])) {
                $line .= ': '.$exp['description'];
            }
            if ($line) {
                $lines[] = '- '.$line;
            }
        }

        return $lines ? implode("\n", $lines) : json_encode($experiences, JSON_UNESCAPED_UNICODE);
    }

    private function parseJdSections(string $html): array
    {
        if (blank($html)) {
            return [];
        }

        $sections = [];
        $keywordMap = [
            'requirements' => ['yeu cau', 'requirement', 'qualification'],
            'responsibilities' => ['trach nhiem', 'cong viec', 'responsibilit', 'nhiem vu'],
            'benefits' => ['quyen loi', 'phuc loi', 'benefit', 'dai ngo'],
        ];

        $pattern = '/<h[23][^>]*>(.*?)<\/h[23]>(.*?)(?=<h[23]|$)/is';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $heading = strtolower(strip_tags($match[1]));
            // normalise Vietnamese diacritics for matching
            $headingAscii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $heading) ?: $heading;
            $body = trim(strip_tags($match[2] ?? ''));

            foreach ($keywordMap as $key => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($headingAscii, $keyword) || str_contains($heading, $keyword)) {
                        $sections[$key] = $body;
                        break 2;
                    }
                }
            }
        }

        return $sections;
    }

    public function render()
    {
        $routeName = Route::currentRouteName();
        $layout = str_starts_with((string) $routeName, 'employers.')
            ? 'layouts.employer'
            : 'layouts.client';

        return view('livewire.client.job.job-detail', [
            'job' => $this->job,
            'publicInterviewProcess' => app(InterviewProcessTemplateService::class)
                ->publicSummaryForJob($this->job),
        ])->layout($layout);
    }

    public function startAiMockInterview(AiMockInterviewService $service)
    {
        if (! auth()->check()) {
            return redirect()->route('candidates.login');
        }

        $candidate = Candidate::where('user_id', auth()->id())->first();
        if (! $candidate) {
            $this->dispatch('app-notify', message: 'Không tìm thấy hồ sơ ứng viên.');

            return;
        }

        // Tạo luôn một cuộc trò chuyện mới mỗi khi bấm
        $chat = $service->startInterview($candidate, $this->job);

        if ($chat) {
            return redirect()->route('candidates.messages', ['chat' => $chat->id]);
        }

        $this->dispatch('app-notify', message: 'Không thể khởi tạo phỏng vấn. Vui lòng thử lại sau.');
    }
}
