<?php

namespace App\Livewire\Client;

use App\Models\CandidateResume;
use App\Models\Candidate;
use App\Rules\CvUploadFile;
use App\Rules\VietnamPhone;
use App\Services\CandidateAccountService;
use App\Support\CvUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateProfile extends Component
{
    use WithFileUploads;

    public int $candidateId;

    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?int $experience_years = null;

    public $avatar = null;

    public $cv = null;

    public ?string $profile_title = null;

    public array $personal_info = [
        'date_of_birth' => null,
        'gender' => null,
        'country' => null,
        'city' => null,
        'address' => null,
        'website' => null,
        'linkedin' => null,
    ];

    public ?string $career_objective = null;

    public array $desired_job = [
        'position' => null,
        'level' => null,
        'workplace' => null,
        'expected_salary' => null,
        'location' => null,
    ];

    public array $experiences = [];

    public array $educations = [];

    public array $certifications = [];

    public array $languages = [];

    public array $skills = [];

    public array $achievements = [];

    public array $activities = [];

    public array $references = [];

    public ?string $extra = null;

    public string $activeSection = 'personal-info';

    public int $applicationCompletion = 0;

    public array $missingApplicationFields = [];

    public ?string $lastSavedSectionLabel = null;

    public $aiScore = null;
    public $aiSummary = null;
    public $aiStrengths = [];
    public $aiWeaknesses = [];
    public $aiSuggestions = [];
    public $aiAtsKeywords = [];
    public $aiMissingKeywords = [];
    public $aiLayoutComment = null;

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $candidateAccounts = app(CandidateAccountService::class);
        $candidate = $candidateAccounts->resolveFor($user);

        $this->candidateId = $candidate->id;
        $this->name = (string) $candidate->name;
        $this->email = (string) ($candidate->email ?? $user->email);
        $this->phone = $candidate->phone;
        $this->experience_years = $candidate->experience_years;

        $resume = CandidateResume::query()->firstOrCreate(
            ['candidate_id' => $candidate->id],
            [],
        );

        $this->profile_title = $resume->profile_title;
        $this->career_objective = $resume->career_objective;
        $this->personal_info = array_merge($this->personal_info, $resume->personal_info ?? []);
        $this->desired_job = array_merge($this->desired_job, $resume->desired_job ?? []);

        $this->experiences = is_array($resume->experiences) ? $resume->experiences : [];
        $this->educations = is_array($resume->educations) ? $resume->educations : [];
        $this->certifications = is_array($resume->certifications) ? $resume->certifications : [];
        $this->languages = is_array($resume->languages) ? $resume->languages : [];
        $this->skills = is_array($resume->skills) ? $resume->skills : [];
        $this->achievements = is_array($resume->achievements) ? $resume->achievements : [];
        $this->activities = is_array($resume->activities) ? $resume->activities : [];
        $this->references = is_array($resume->references) ? $resume->references : [];
        $this->extra = is_array($resume->extra) ? ($resume->extra['text'] ?? null) : null;

        $this->refreshApplicationStatus($candidate);
        $this->focusSectionForIncompleteProfile($candidateAccounts->missingApplicationProfileFields($candidate));
    }

    public function save(): void
    {
        $this->validate($this->allValidationRules());

        if (! $this->assertCvPresent()) {
            $this->activeSection = 'extra-info';

            return;
        }

        $this->persistProfile();
        $this->lastSavedSectionLabel = 'toàn bộ hồ sơ';
        $this->dispatch('app-notify', message: $this->savedMessage($this->lastSavedSectionLabel));
    }

    public function saveSection(?string $nextSection = null): void
    {
        $savedSection = $this->activeSection;

        $this->validate($this->rulesForSection($this->activeSection));

        if ($this->activeSection === 'extra-info' && ! $this->assertCvPresent()) {
            return;
        }

        $this->persistProfile();

        if ($nextSection) {
            $this->activeSection = $nextSection;
        }

        $this->lastSavedSectionLabel = $this->sectionLabel($savedSection);
        $this->dispatch('app-notify', message: $this->savedMessage($this->lastSavedSectionLabel));
    }

    public function switchSection(string $section): void
    {
        if (! array_key_exists($section, $this->sectionLabels())) {
            return;
        }

        $this->activeSection = $section;
    }

    /**
     * @return array<string, mixed>
     */
    private function allValidationRules(): array
    {
        return array_merge(
            $this->rulesForSection('personal-info'),
            $this->rulesForSection('career-objective'),
            $this->rulesForSection('desired-job'),
            $this->rulesForSection('experiences'),
            $this->rulesForSection('educations'),
            $this->rulesForSection('skills'),
            $this->rulesForSection('languages'),
            $this->rulesForSection('certifications'),
            $this->rulesForSection('extra-info'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rulesForSection(string $section): array
    {
        return match ($section) {
            'personal-info' => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:50', new VietnamPhone()],
                'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
                'avatar' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
                'profile_title' => ['nullable', 'string', 'max:255'],
                'personal_info' => ['array'],
                'personal_info.date_of_birth' => ['nullable', 'date'],
                'personal_info.gender' => ['nullable', 'string', 'max:50'],
                'personal_info.country' => ['nullable', 'string', 'max:100'],
                'personal_info.city' => ['nullable', 'string', 'max:100'],
                'personal_info.address' => ['nullable', 'string', 'max:255'],
                'personal_info.website' => ['nullable', 'string', 'max:255'],
                'personal_info.linkedin' => ['nullable', 'string', 'max:255'],
            ],
            'career-objective' => [
                'career_objective' => ['nullable', 'string', 'max:4000'],
            ],
            'desired-job' => [
                'desired_job' => ['array'],
                'desired_job.position' => ['nullable', 'string', 'max:255'],
                'desired_job.level' => ['nullable', 'string', 'max:100'],
                'desired_job.workplace' => ['nullable', 'string', 'max:100'],
                'desired_job.expected_salary' => ['nullable', 'string', 'max:100'],
                'desired_job.location' => ['nullable', 'string', 'max:255'],
            ],
            'experiences' => [
                'experiences' => ['array'],
                'experiences.*.company' => ['nullable', 'string', 'max:255'],
                'experiences.*.position' => ['nullable', 'string', 'max:255'],
                'experiences.*.from' => ['nullable', 'string', 'max:50'],
                'experiences.*.to' => ['nullable', 'string', 'max:50'],
                'experiences.*.description' => ['nullable', 'string', 'max:4000'],
            ],
            'educations' => [
                'educations' => ['array'],
                'educations.*.school' => ['nullable', 'string', 'max:255'],
                'educations.*.degree' => ['nullable', 'string', 'max:255'],
                'educations.*.from' => ['nullable', 'string', 'max:50'],
                'educations.*.to' => ['nullable', 'string', 'max:50'],
                'educations.*.description' => ['nullable', 'string', 'max:4000'],
            ],
            'skills' => [
                'skills' => ['array'],
                'skills.*.name' => ['nullable', 'string', 'max:100'],
                'skills.*.level' => ['nullable', 'string', 'max:100'],
            ],
            'languages' => [
                'languages' => ['array'],
                'languages.*.name' => ['nullable', 'string', 'max:100'],
                'languages.*.level' => ['nullable', 'string', 'max:100'],
            ],
            'certifications' => [
                'certifications' => ['array'],
                'certifications.*.name' => ['nullable', 'string', 'max:255'],
                'certifications.*.issuer' => ['nullable', 'string', 'max:255'],
                'certifications.*.date' => ['nullable', 'string', 'max:50'],
                'certifications.*.description' => ['nullable', 'string', 'max:2000'],
            ],
            'extra-info' => [
                'cv' => ['nullable', 'file', 'max:10240', new CvUploadFile()],
                'extra' => ['nullable', 'string', 'max:4000'],
            ],
            default => [],
        };
    }

    private function sectionLabel(string $section): string
    {
        return $this->sectionLabels()[$section] ?? 'hồ sơ';
    }

    /**
     * @return array<string, string>
     */
    private function sectionLabels(): array
    {
        return [
            'personal-info' => 'Thông tin cá nhân',
            'career-objective' => 'Mục tiêu nghề nghiệp',
            'desired-job' => 'Công việc mong muốn',
            'experiences' => 'Kinh nghiệm',
            'educations' => 'Học vấn',
            'skills' => 'Kỹ năng',
            'languages' => 'Ngôn ngữ',
            'certifications' => 'Chứng chỉ',
            'extra-info' => 'CV',
        ];
    }

    private function savedMessage(string $sectionLabel): string
    {
        return "Đã lưu {$sectionLabel}.";
    }

    private function assertCvPresent(): bool
    {
        $candidate = Candidate::query()->find($this->candidateId);

        if ($this->cv || ($candidate && app(CandidateAccountService::class)->candidateHasCv($candidate))) {
            return true;
        }

        $this->addError('cv', 'Vui lòng tải CV lên để hoàn tất hồ sơ ứng viên.');

        return false;
    }

    private function persistProfile(): void
    {
        $candidate = Candidate::query()->findOrFail($this->candidateId);
        $user = Auth::user();
        $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);

        DB::transaction(function () use ($candidate, $resume, $user) {
            $candidate->name = trim($this->name);
            $candidate->email = trim($this->email);
            $candidate->phone = $this->phone;
            $candidate->experience_years = $this->experience_years;

            if ($user) {
                $user->name = trim($this->name);

                if ($user->isDirty()) {
                    $user->save();
                }
            }

            if ($this->avatar && $user) {
                $oldAvatar = $user->avatar;
                $avatarPath = $this->avatar->storePublicly("users/{$user->id}/avatar", 'public');

                $user->avatar = $avatarPath;
                $user->save();

                if ($oldAvatar && $oldAvatar !== $avatarPath && Storage::disk('public')->exists($oldAvatar)) {
                    Storage::disk('public')->delete($oldAvatar);
                }
            }

            if ($this->cv) {
                $path = $this->cv->storePublicly("candidates/{$candidate->id}/cv", 'public');

                $candidate->cv_file = $path;

                $candidate->attachments()
                    ->where('type', 'cv')
                    ->delete();

                $candidate->attachments()->create([
                    'path' => $path,
                    'type' => 'cv',
                    'original_filename' => method_exists($this->cv, 'getClientOriginalName')
                        ? $this->cv->getClientOriginalName()
                        : null,
                    'mime_type' => method_exists($this->cv, 'getMimeType')
                        ? $this->cv->getMimeType()
                        : null,
                    'size_bytes' => method_exists($this->cv, 'getSize')
                        ? $this->cv->getSize()
                        : null,
                ]);
            }

            $candidate->save();

            $resume->fill([
                'profile_title' => $this->profile_title,
                'personal_info' => $this->personal_info,
                'career_objective' => $this->career_objective,
                'desired_job' => $this->desired_job,
                'experiences' => $this->experiences,
                'educations' => $this->educations,
                'certifications' => $this->certifications,
                'languages' => $this->languages,
                'skills' => $this->skills,
                'achievements' => $this->achievements,
                'activities' => $this->activities,
                'references' => $this->references,
                'extra' => ['text' => $this->extra],
            ]);
            $resume->save();
        });

        $this->avatar = null;
        $this->cv = null;

        $candidate->refresh();
        $this->refreshApplicationStatus($candidate);
    }

    /**
     * @param  array<string>  $missingFields
     */
    private function focusSectionForIncompleteProfile(array $missingFields): void
    {
        if ($missingFields === []) {
            return;
        }

        $contactFields = ['họ tên', 'email', 'số điện thoại'];

        if (array_intersect($missingFields, $contactFields) !== []) {
            $this->activeSection = 'personal-info';

            return;
        }

        if (in_array('CV', $missingFields, true)) {
            $this->activeSection = 'extra-info';
        }
    }

    public function addExperience(): void
    {
        $this->experiences[] = ['company' => null, 'position' => null, 'from' => null, 'to' => null, 'description' => null];
    }

    public function removeExperience(int $index): void
    {
        unset($this->experiences[$index]);
        $this->experiences = array_values($this->experiences);
    }

    public function addEducation(): void
    {
        $this->educations[] = ['school' => null, 'degree' => null, 'from' => null, 'to' => null, 'description' => null];
    }

    public function removeEducation(int $index): void
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    public function addCertification(): void
    {
        $this->certifications[] = ['name' => null, 'issuer' => null, 'date' => null, 'description' => null];
    }

    public function removeCertification(int $index): void
    {
        unset($this->certifications[$index]);
        $this->certifications = array_values($this->certifications);
    }

    public function addLanguage(): void
    {
        $this->languages[] = ['name' => null, 'level' => null];
    }

    public function removeLanguage(int $index): void
    {
        unset($this->languages[$index]);
        $this->languages = array_values($this->languages);
    }

    public function addSkill(): void
    {
        $this->skills[] = ['name' => null, 'level' => null];
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    public function addAchievement(): void
    {
        $this->achievements[] = ['title' => null, 'date' => null, 'description' => null];
    }

    public function removeAchievement(int $index): void
    {
        unset($this->achievements[$index]);
        $this->achievements = array_values($this->achievements);
    }

    public function addActivity(): void
    {
        $this->activities[] = ['title' => null, 'from' => null, 'to' => null, 'description' => null];
    }

    public function removeActivity(int $index): void
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
    }

    public function addReference(): void
    {
        $this->references[] = ['name' => null, 'company' => null, 'position' => null, 'phone' => null, 'email' => null, 'note' => null];
    }

    public function removeReference(int $index): void
    {
        unset($this->references[$index]);
        $this->references = array_values($this->references);
    }

    public function getCurrentCvUrlProperty(): ?string
    {
        $candidate = Candidate::query()->find($this->candidateId);
        if (! $candidate?->cv_file) {
            return null;
        }

        return Route::has('public-file.preview')
            ? route('public-file.preview', ['path' => $candidate->cv_file])
            : asset('storage/' . ltrim($candidate->cv_file, '/'));
    }

    public function getCurrentAvatarUrlProperty(): string
    {
        $avatar = Auth::user()?->avatar;

        if (! $avatar) {
            return asset('assets/img/avatar_detail.jpg');
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return asset('storage/' . ltrim($avatar, '/'));
    }

    private function refreshApplicationStatus(Candidate $candidate): void
    {
        $candidateAccounts = app(CandidateAccountService::class);

        $this->applicationCompletion = $candidateAccounts->applicationProfileCompletion($candidate);
        $this->missingApplicationFields = $candidateAccounts->missingApplicationProfileFields($candidate);

        $this->aiScore = $candidate->match_score;
        $matchReasons = $candidate->match_reasons ?? [];
        $this->aiSummary = $matchReasons['summary'] ?? null;
        $this->aiStrengths = $matchReasons['strengths'] ?? [];
        $this->aiWeaknesses = $matchReasons['weaknesses'] ?? [];
        $this->aiSuggestions = $matchReasons['suggestions'] ?? [];
        $this->aiAtsKeywords = $matchReasons['ats_keywords'] ?? [];
        $this->aiMissingKeywords = $matchReasons['missing_keywords'] ?? [];
        $this->aiLayoutComment = $matchReasons['layout_comment'] ?? null;
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.candidate-profile');
    }
    public function analyzeCvWithAi(\App\Services\AiMatchingService $aiService)
    {
        $candidate = Candidate::find($this->candidateId);
        if (!$candidate) return;
        
        $cvText = '';
        $cvText .= "Mục tiêu nghề nghiệp: " . ($this->career_objective ?? 'Không có') . "\n";
        $cvText .= "Kinh nghiệm: " . json_encode($this->experiences ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Học vấn: " . json_encode($this->educations ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Kỹ năng: " . json_encode($this->skills ?? [], JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Ngôn ngữ: " . json_encode($this->languages ?? [], JSON_UNESCAPED_UNICODE) . "\n";

        $pdfPath = null;
        if ($this->cv && method_exists($this->cv, 'getRealPath')) {
            $pdfPath = $this->cv->getRealPath();
        } elseif (!empty($candidate->cv_file)) {
            $pdfPath = \Illuminate\Support\Facades\Storage::disk('public')->path($candidate->cv_file);
        }

        // Validate that there is actually something to analyze
        $hasSignificantText = !empty($this->experiences) || !empty($this->educations) || !empty($this->skills);
        
        if (!$pdfPath && !$hasSignificantText) {
            $this->dispatch(
                'app-notify',
                message: 'Bạn chưa điền đủ thông tin hồ sơ (Kinh nghiệm, Học vấn, Kỹ năng) hoặc chưa tải lên file CV. Vui lòng cập nhật hồ sơ trước khi dùng AI chấm điểm.',
                type: 'warning'
            );
            return;
        }

        \Illuminate\Support\Facades\Log::info("CV TEXT SENT TO AI:\n" . $cvText . "\nPDF Path: " . $pdfPath);

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

            // Save to DB
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
            $this->dispatch(
                'app-notify',
                message: $aiService->getLastError() ?: 'Không thể phân tích CV lúc này. Vui lòng thử lại sau.',
                type: 'error'
            );
        }
    }
}
