<?php

namespace App\Livewire\Client;

use App\Models\Candidate;
use App\Models\CandidateResume;
use App\Services\AiCvAssistantService;
use App\Services\AiMatchingService;
use App\Services\CandidateAccountService;
use App\Services\CvTextExtractor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class CvBuilder extends Component
{
    use WithFileUploads;

    private const TEMPLATES = ['fpt-modern', 'ats-classic', 'tech-executive'];

    #[Locked]
    public int $candidateId;
    public string $name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?int $experience_years = null;
    public ?string $profile_title = null;
    public ?string $career_objective = null;

    // Avatar
    public $avatar = null;
    public ?string $currentAvatar = null;

    public array $personal_info = [
        'date_of_birth' => null,
        'gender' => null,
        'country' => null,
        'city' => null,
        'address' => null,
        'website' => null,
        'linkedin' => null,
    ];

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

    // UI & Template settings
    #[Locked]
    public string $selectedTemplate = 'fpt-modern';
    public string $activeTab = 'personal';
    public $uploadedCvFile = null;
    public ?string $lastSavedAt = null;

    // AI States
    public bool $isProcessingAi = false;
    public ?int $aiScore = null;
    public ?string $aiSummary = null;
    public array $aiStrengths = [];
    public array $aiWeaknesses = [];
    public array $aiSuggestions = [];
    public array $aiAtsKeywords = [];
    public array $aiMissingKeywords = [];

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        $this->candidateId = $candidate->id;
        $this->name = (string) $candidate->name;
        $this->email = (string) ($candidate->email ?? $user->email);
        $this->phone = $candidate->phone;
        $this->experience_years = $candidate->experience_years;
        $this->currentAvatar = $user->avatar;

        $resume = CandidateResume::query()->firstOrCreate(
            ['candidate_id' => $candidate->id],
            [],
        );

        $this->profile_title = $resume->profile_title ?: 'Chuyên viên';
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

        $metadata = is_array($candidate->metadata) ? $candidate->metadata : [];
        $primaryCv = is_array($metadata['primary_cv'] ?? null) ? $metadata['primary_cv'] : [];
        $resumeExtra = is_array($resume->extra) ? $resume->extra : [];
        $requestedTemplate = request()->query('template');
        $primaryTemplate = ($primaryCv['type'] ?? null) === 'online'
            ? ($primaryCv['template'] ?? null)
            : null;

        $this->selectedTemplate = $this->normalizeTemplate(
            is_string($requestedTemplate) && $requestedTemplate !== ''
                ? $requestedTemplate
                : ($primaryTemplate ?? $resumeExtra['builder_template'] ?? null),
        );
        $this->lastSavedAt = $resume->wasRecentlyCreated
            ? null
            : $resume->updated_at?->timezone(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y');

        // Load existing AI evaluations if available
        if ($candidate->match_score) {
            $this->aiScore = (int) $candidate->match_score;
            $reasons = is_array($candidate->match_reasons) ? $candidate->match_reasons : [];
            $this->aiSummary = $reasons['summary'] ?? null;
            $this->aiStrengths = $reasons['strengths'] ?? [];
            $this->aiWeaknesses = $reasons['weaknesses'] ?? [];
            $this->aiSuggestions = $reasons['suggestions'] ?? [];
            $this->aiAtsKeywords = $reasons['ats_keywords'] ?? [];
            $this->aiMissingKeywords = $reasons['missing_keywords'] ?? [];
        }
    }

    public function setTemplate(string $template): void
    {
        if (in_array($template, self::TEMPLATES, true)) {
            $this->selectedTemplate = $template;
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /* ---------------- Array Management ---------------- */

    public function addExperience(): void
    {
        $this->experiences[] = ['company' => '', 'position' => '', 'from' => '', 'to' => '', 'description' => ''];
    }

    public function removeExperience(int $index): void
    {
        unset($this->experiences[$index]);
        $this->experiences = array_values($this->experiences);
    }

    public function addEducation(): void
    {
        $this->educations[] = ['school' => '', 'degree' => '', 'from' => '', 'to' => '', 'description' => ''];
    }

    public function removeEducation(int $index): void
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    public function addSkill(): void
    {
        $this->skills[] = ['name' => '', 'level' => 'Khá'];
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    public function addLanguage(): void
    {
        $this->languages[] = ['name' => '', 'level' => 'Giao tiếp'];
    }

    public function removeLanguage(int $index): void
    {
        unset($this->languages[$index]);
        $this->languages = array_values($this->languages);
    }

    public function addCertification(): void
    {
        $this->certifications[] = ['name' => '', 'issuer' => '', 'date' => '', 'description' => ''];
    }

    public function removeCertification(int $index): void
    {
        unset($this->certifications[$index]);
        $this->certifications = array_values($this->certifications);
    }

    public function addAchievement(): void
    {
        $this->achievements[] = ['title' => '', 'date' => '', 'description' => ''];
    }

    public function removeAchievement(int $index): void
    {
        unset($this->achievements[$index]);
        $this->achievements = array_values($this->achievements);
    }

    public function addActivity(): void
    {
        $this->activities[] = ['title' => '', 'from' => '', 'to' => '', 'description' => ''];
    }

    public function removeActivity(int $index): void
    {
        unset($this->activities[$index]);
        $this->activities = array_values($this->activities);
    }

    /* ---------------- AI Co-pilot Features ---------------- */

    /**
     * AI Auto-fill: Tải lên file CV cũ để AI tự điền vào form
     */
    public function importCvWithAi(AiCvAssistantService $aiAssistant, CvTextExtractor $textExtractor): void
    {
        $this->validate([
            'uploadedCvFile' => ['required', 'file', 'mimes:pdf,docx', 'max:10240'],
        ], [
            'uploadedCvFile.required' => 'Vui lòng chọn 1 file CV (PDF hoặc DOCX) để AI xử lý.',
            'uploadedCvFile.mimes' => 'Định dạng hỗ trợ: PDF hoặc DOCX.',
            'uploadedCvFile.max' => 'Dung lượng file tối đa là 10MB.',
        ]);

        $this->isProcessingAi = true;

        try {
            $path = $this->uploadedCvFile->store('temp-cv-imports', 'public');
            $fullPath = Storage::disk('public')->path($path);

            $extractedText = $textExtractor->extractFromPublicPath($path) ?: '';

            $parsedData = $aiAssistant->parseCvToStructuredJson($extractedText, $fullPath);

            // Cleanup temp file
            @unlink($fullPath);
            $this->uploadedCvFile = null;

            if ($parsedData && is_array($parsedData)) {
                if (!empty($parsedData['name'])) $this->name = $parsedData['name'];
                if (!empty($parsedData['email'])) $this->email = $parsedData['email'];
                if (!empty($parsedData['phone'])) $this->phone = $parsedData['phone'];
                if (!empty($parsedData['profile_title'])) $this->profile_title = $parsedData['profile_title'];
                if (!empty($parsedData['career_objective'])) $this->career_objective = $parsedData['career_objective'];

                if (!empty($parsedData['date_of_birth'])) $this->personal_info['date_of_birth'] = $parsedData['date_of_birth'];
                if (!empty($parsedData['gender'])) $this->personal_info['gender'] = $parsedData['gender'];
                if (!empty($parsedData['city'])) $this->personal_info['city'] = $parsedData['city'];
                if (!empty($parsedData['address'])) $this->personal_info['address'] = $parsedData['address'];

                if (!empty($parsedData['experiences']) && is_array($parsedData['experiences'])) {
                    $this->experiences = $parsedData['experiences'];
                }
                if (!empty($parsedData['educations']) && is_array($parsedData['educations'])) {
                    $this->educations = $parsedData['educations'];
                }
                if (!empty($parsedData['skills']) && is_array($parsedData['skills'])) {
                    $this->skills = $parsedData['skills'];
                }
                if (!empty($parsedData['languages']) && is_array($parsedData['languages'])) {
                    $this->languages = $parsedData['languages'];
                }
                if (!empty($parsedData['certifications']) && is_array($parsedData['certifications'])) {
                    $this->certifications = $parsedData['certifications'];
                }
                if (!empty($parsedData['achievements']) && is_array($parsedData['achievements'])) {
                    $this->achievements = $parsedData['achievements'];
                }
                if (!empty($parsedData['activities']) && is_array($parsedData['activities'])) {
                    $this->activities = $parsedData['activities'];
                }
                if (!empty($parsedData['references']) && is_array($parsedData['references'])) {
                    $this->references = $parsedData['references'];
                }

                $this->save();

                $this->dispatch('app-notify', message: '✨ AI đã trích xuất và điền tự động dữ liệu từ CV của bạn thành công!', type: 'success');
            } else {
                $this->dispatch('app-notify', message: $aiAssistant->getLastError() ?: 'Không thể trích xuất dữ liệu từ file CV này.', type: 'error');
            }
        } catch (\Throwable $e) {
            Log::error('AI Import Error: ' . $e->getMessage());
            $this->dispatch('app-notify', message: 'Đã có lỗi xảy ra trong quá trình bóc tách CV.', type: 'error');
        } finally {
            $this->isProcessingAi = false;
        }
    }

    /**
     * AI Sinh mục tiêu nghề nghiệp
     */
    public function generateObjectiveWithAi(AiCvAssistantService $aiAssistant): void
    {
        $position = $this->profile_title ?: ($this->desired_job['position'] ?? 'Chuyên viên');
        $skillsList = array_map(fn($s) => is_array($s) ? ($s['name'] ?? '') : (string)$s, $this->skills);

        $this->isProcessingAi = true;

        $generated = $aiAssistant->generateObjective($position, 'Mid-level / Chuyên nghiệp', $skillsList);

        $this->isProcessingAi = false;

        if ($generated) {
            $this->career_objective = $generated;
            $this->dispatch('app-notify', message: '✨ AI đã viết xong đoạn mục tiêu nghề nghiệp ấn tượng cho bạn!', type: 'success');
        } else {
            $this->dispatch('app-notify', message: $aiAssistant->getLastError() ?: 'Không thể tạo mục tiêu lúc này.', type: 'error');
        }
    }

    /**
     * AI Tối ưu câu mô tả kinh nghiệm
     */
    public function enhanceExperienceWithAi(int $index, AiCvAssistantService $aiAssistant): void
    {
        if (!isset($this->experiences[$index])) return;

        $draft = $this->experiences[$index]['description'] ?? '';
        $position = $this->experiences[$index]['position'] ?? $this->profile_title ?? '';

        if (blank($draft)) {
            $this->dispatch('app-notify', message: 'Vui lòng nhập 1 vài ý tóm tắt công việc trước khi nhờ AI tối ưu.', type: 'warning');
            return;
        }

        $this->isProcessingAi = true;

        $enhanced = $aiAssistant->enhanceExperienceDescription($draft, $position);

        $this->isProcessingAi = false;

        if ($enhanced) {
            $this->experiences[$index]['description'] = $enhanced;
            $this->dispatch('app-notify', message: '✨ Đã tối ưu mô tả công việc theo chuẩn STAR thành công!', type: 'success');
        } else {
            $this->dispatch('app-notify', message: $aiAssistant->getLastError() ?: 'Không thể tối ưu đoạn văn.', type: 'error');
        }
    }

    /**
     * AI Chấm điểm & Phân tích ATS
     */
    public function runAiAudit(AiMatchingService $aiMatching): void
    {
        $cvText = "Họ tên: {$this->name}\nVị trí: {$this->profile_title}\n";
        $cvText .= "Mục tiêu: {$this->career_objective}\n";
        $cvText .= "Kinh nghiệm: " . json_encode($this->experiences, JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Học vấn: " . json_encode($this->educations, JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Kỹ năng: " . json_encode($this->skills, JSON_UNESCAPED_UNICODE) . "\n";
        $cvText .= "Chứng chỉ: " . json_encode($this->certifications, JSON_UNESCAPED_UNICODE) . "\n";

        $this->isProcessingAi = true;

        $result = $aiMatching->evaluateGeneralCv($cvText);

        $this->isProcessingAi = false;

        if ($result && isset($result['score'])) {
            $this->aiScore = $result['score'];
            $this->aiSummary = $result['summary'] ?? null;
            $this->aiStrengths = $result['strengths'] ?? [];
            $this->aiWeaknesses = $result['weaknesses'] ?? [];
            $this->aiSuggestions = $result['suggestions'] ?? [];
            $this->aiAtsKeywords = $result['ats_keywords'] ?? [];
            $this->aiMissingKeywords = $result['missing_keywords'] ?? [];

            // Update candidate
            Candidate::where('id', $this->candidateId)->update([
                'match_score' => $result['score'],
                'match_reasons' => [
                    'summary' => $this->aiSummary,
                    'strengths' => $this->aiStrengths,
                    'weaknesses' => $this->aiWeaknesses,
                    'suggestions' => $this->aiSuggestions,
                    'ats_keywords' => $this->aiAtsKeywords,
                    'missing_keywords' => $this->aiMissingKeywords,
                ]
            ]);

            $this->dispatch('app-notify', message: "✨ Đã hoàn tất chấm điểm CV: {$this->aiScore}/100 điểm!", type: 'success');
        } else {
            $this->dispatch('app-notify', message: $aiMatching->getLastError() ?: 'Không thể chấm điểm CV lúc này.', type: 'error');
        }
    }

    /* ---------------- Avatar Management ---------------- */

    public function updatedAvatar(): void
    {
        $this->validate([
            'avatar' => ['image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
        ]);
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();
        if ($user && $user->avatar) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
            $user->save();
        }
        $this->avatar = null;
        $this->currentAvatar = null;
        $this->dispatch('app-notify', message: 'Đã xóa ảnh đại diện!', type: 'info');
    }

    /* ---------------- Persistence ---------------- */

    public function save(): void
    {
        if (! $this->persistCv()) {
            return;
        }

        $this->dispatch('app-notify', message: 'Đã lưu CV thành công.', type: 'success');
    }

    public function downloadPdf(): void
    {
        if (! $this->persistCv()) {
            return;
        }

        $this->dispatch(
            'download-cv-file',
            url: route('candidates.cv.download', ['template' => $this->selectedTemplate, 't' => time()]),
        );
    }

    public function openPdf(): void
    {
        if (! $this->persistCv()) {
            return;
        }

        $this->dispatch(
            'open-pdf-window',
            url: route('candidates.cv.download', [
                'template' => $this->selectedTemplate,
                'mode' => 'stream',
                't' => time(),
            ]),
        );
    }

    private function persistCv(): bool
    {
        try {
            $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'profile_title' => ['required', 'string', 'max:255'],
                'avatar' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            ], [
                'name.required' => 'Vui lòng nhập họ và tên.',
                'email.required' => 'Vui lòng nhập email liên hệ.',
                'email.email' => 'Email liên hệ chưa đúng định dạng.',
                'profile_title.required' => 'Vui lòng nhập chức danh hoặc vị trí chuyên môn.',
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->dispatch('cv-action-failed');
            $this->dispatch('app-notify', message: 'CV chưa được lưu. Vui lòng kiểm tra các trường bắt buộc.', type: 'error');

            return false;
        }

        $user = Auth::user();
        abort_unless($user, 401);

        $candidate = app(CandidateAccountService::class)->resolveFor($user);
        abort_unless($candidate->id === $this->candidateId, 403);

        $this->selectedTemplate = $this->normalizeTemplate($this->selectedTemplate);
        $oldAvatarPath = is_string($user->avatar) ? $user->avatar : null;
        $newAvatarPath = null;

        try {
            if ($this->avatar) {
                $newAvatarPath = $this->avatar->storePublicly("users/{$user->id}/avatar", 'public');
            }

            DB::transaction(function () use ($candidate, $newAvatarPath, $user): void {
                if ($newAvatarPath) {
                    $user->avatar = $newAvatarPath;
                    $user->save();
                }

                $candidate->refresh();
                $metadata = is_array($candidate->metadata) ? $candidate->metadata : [];
                $primaryCv = is_array($metadata['primary_cv'] ?? null) ? $metadata['primary_cv'] : [];

                $candidate->fill([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'experience_years' => $this->experience_years,
                ]);

                if (($primaryCv['type'] ?? 'online') !== 'attachment') {
                    $metadata['primary_cv'] = [
                        'type' => 'online',
                        'template' => $this->selectedTemplate,
                        'attachment_id' => null,
                        'title' => 'CV Online ('.$this->templateName($this->selectedTemplate).')',
                        'updated_at' => now()->toIso8601String(),
                    ];
                    $candidate->metadata = $metadata;
                }

                $candidate->save();

                $resume = CandidateResume::firstOrCreate(['candidate_id' => $candidate->id]);
                $extra = is_array($resume->extra) ? $resume->extra : [];
                $extra['builder_template'] = $this->selectedTemplate;

                $resume->fill([
                    'profile_title' => $this->profile_title,
                    'career_objective' => $this->career_objective,
                    'personal_info' => $this->personal_info,
                    'desired_job' => $this->desired_job,
                    'experiences' => $this->experiences,
                    'educations' => $this->educations,
                    'certifications' => $this->certifications,
                    'languages' => $this->languages,
                    'skills' => $this->skills,
                    'achievements' => $this->achievements,
                    'activities' => $this->activities,
                    'references' => $this->references,
                    'extra' => $extra,
                ]);
                $resume->save();
            });
        } catch (Throwable $exception) {
            try {
                if ($newAvatarPath && Storage::disk('public')->exists($newAvatarPath)) {
                    Storage::disk('public')->delete($newAvatarPath);
                }
            } catch (Throwable $cleanupException) {
                Log::warning('Failed to clean up an unused CV avatar.', [
                    'candidate_id' => $this->candidateId,
                    'path' => $newAvatarPath,
                    'exception' => $cleanupException,
                ]);
            }

            $user->refresh();

            Log::error('Failed to save online CV.', [
                'candidate_id' => $this->candidateId,
                'exception' => $exception,
            ]);
            $this->addError('save', 'Không thể lưu CV lúc này. Vui lòng thử lại.');
            $this->dispatch('cv-action-failed');
            $this->dispatch('app-notify', message: 'Không thể lưu CV lúc này. Vui lòng thử lại.', type: 'error');

            return false;
        }

        if ($newAvatarPath) {
            try {
                if ($oldAvatarPath && $oldAvatarPath !== $newAvatarPath && Storage::disk('public')->exists($oldAvatarPath)) {
                    Storage::disk('public')->delete($oldAvatarPath);
                }
            } catch (Throwable $cleanupException) {
                Log::warning('Failed to delete the previous CV avatar.', [
                    'candidate_id' => $this->candidateId,
                    'path' => $oldAvatarPath,
                    'exception' => $cleanupException,
                ]);
            }

            $this->currentAvatar = $newAvatarPath;
            $this->avatar = null;
        }

        $this->lastSavedAt = now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i, d/m/Y');
        $this->resetErrorBag();

        return true;
    }

    private function normalizeTemplate(mixed $template): string
    {
        return is_string($template) && in_array($template, self::TEMPLATES, true)
            ? $template
            : 'fpt-modern';
    }

    private function templateName(string $template): string
    {
        return match ($template) {
            'ats-classic' => 'ATS Classic Clean',
            'tech-executive' => 'Tech Executive',
            default => 'FPT Modern Pro',
        };
    }

    #[Layout('layouts.client')]
    public function render()
    {
        $candidate = Candidate::find($this->candidateId);
        $resume = CandidateResume::where('candidate_id', $this->candidateId)->first();

        // Build mock resume for live preview state
        $avatarFileForPreview = null;
        if ($this->avatar) {
            try {
                $avatarFileForPreview = $this->avatar->getRealPath();
            } catch (\Throwable $e) {}
        }

        $previewResume = [
            'avatar' => $avatarFileForPreview ?? $this->currentAvatar,
            'profile_title' => $this->profile_title,
            'career_objective' => $this->career_objective,
            'personal_info' => $this->personal_info,
            'desired_job' => $this->desired_job,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
            'certifications' => $this->certifications,
            'languages' => $this->languages,
            'skills' => $this->skills,
            'achievements' => $this->achievements,
            'activities' => $this->activities,
            'references' => $this->references,
        ];

        return view('livewire.client.cv-builder', [
            'candidate' => $candidate,
            'resume' => $resume,
            'previewResume' => $previewResume,
        ]);
    }
}
