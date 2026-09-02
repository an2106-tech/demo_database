<?php

namespace App\Livewire\Client;

use App\Enums\StatusApplicationEnum;
use App\Jobs\ProcessApplicationCvText;
use App\Mail\CandidateApplicationReceivedMail;
use App\Mail\GuestApplicationVerificationMail;
use App\Mail\HrNewApplicationMail;
use App\Models\Application;
use App\Models\Attachment;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Rules\CvUploadFile;
use App\Rules\VietnamPhone;
use App\Services\CandidateAccountService;
use App\Services\JobApplicationEligibilityService;
use App\Services\OutboundMailQueue;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ApplyJob extends Component
{
    use WithFileUploads;

    public RecruitmentJob $job;

    public ?int $candidateId = null;

    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?int $experience_years = null;

    public ?string $profile_title = null;

    public ?string $career_objective = null;

    public $cv = null;

    public string $selectedCvOption = 'new_upload';

    public bool $sync_profile_to_candidate = false;

    public bool $use_cv_as_primary = false;

    public bool $showSuccessModal = false;

    public function updatedCv(): void
    {
        $this->resetValidation('cv');
        if ($this->cv) {
            $this->selectedCvOption = 'new_upload';
        }
    }

    public function mount(RecruitmentJob $job): void
    {
        $this->job = $job->loadMissing(['branch', 'department', 'workplace', 'skills']);

        $user = Auth::user();
        if (! $user) {
            $this->selectedCvOption = 'new_upload';

            return;
        }

        if (! $this->canUseCandidateAccount($user)) {
            $metadata = is_array($user->metadata) ? $user->metadata : [];

            $this->name = (string) $user->name;
            $this->email = (string) $user->email;
            $this->phone = is_string($metadata['phone'] ?? null) ? $metadata['phone'] : null;

            return;
        }

        $candidateService = app(CandidateAccountService::class);
        $candidate = $candidateService->resolveFor($user);

        if (! $candidateService->isProfileReadyForApplication($candidate)) {
            session()
                ->flash('profile_incomplete', $candidateService->missingApplicationProfileFields($candidate));
            session()
                ->flash('status', 'Vui lòng hoàn thiện hồ sơ ứng viên trước khi ứng tuyển.');

            $this->redirectRoute('candidates.candidate_profile');

            return;
        }

        $this->candidateId = $candidate->id;
        $this->name = (string) $candidate->name;
        $this->email = (string) ($candidate->email ?? $user->email ?? '');
        $this->phone = $candidate->phone;
        $this->experience_years = $candidate->experience_years;

        $resume = CandidateResume::query()->firstOrNew(['candidate_id' => $candidate->id], []);
        $this->profile_title = $resume->profile_title;
        $this->career_objective = $resume->career_objective;

        // Apply with the saved CV itself; template changes belong to the CV builder.
        $primaryCv = data_get($candidate->metadata, 'primary_cv', []);
        $primaryType = $primaryCv['type'] ?? 'online';
        $primaryAttachmentId = (int) ($primaryCv['attachment_id'] ?? 0);
        $hasPrimaryAttachment = $primaryType === 'attachment'
            && $primaryAttachmentId > 0
            && $candidate->attachments()->where('type', 'cv')->whereKey($primaryAttachmentId)->exists();
        $onlineTemplate = $candidateService->savedOnlineCvTemplate($candidate);

        if ($hasPrimaryAttachment) {
            $this->selectedCvOption = 'attachment_'.$primaryAttachmentId;
        } elseif ($onlineTemplate !== null) {
            $this->selectedCvOption = 'online_'.$onlineTemplate;
        } else {
            $this->selectedCvOption = 'new_upload';
        }
    }

    public function submit(): mixed
    {
        if ($this->requiresCandidateActivation()) {
            $this->addError('account', 'Vui lòng kích hoạt hồ sơ ứng viên trước khi ứng tuyển bằng tài khoản hiện tại.');

            return null;
        }

        app(JobApplicationEligibilityService::class)->assertCanApply($this->job->fresh());

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', new VietnamPhone],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'profile_title' => ['nullable', 'string', 'max:255'],
            'career_objective' => ['nullable', 'string', 'max:4000'],
            'selectedCvOption' => ['required', Rule::in($this->availableCvOptionValues())],
        ];

        if ($this->selectedCvOption === 'new_upload') {
            $rules['cv'] = ['required', 'file', 'max:10240', new CvUploadFile];
        }

        $this->validate($rules);

        $result = DB::transaction(function (): array {
            $job = RecruitmentJob::query()->lockForUpdate()->findOrFail($this->job->id);
            app(JobApplicationEligibilityService::class)->assertCanApply($job);
            $this->job = $job->loadMissing(['branch', 'department', 'workplace', 'skills']);

            $candidate = $this->resolveCandidateForApplication();
            $application = Application::withTrashed()
                ->where('job_id', $this->job->id)
                ->where('candidate_id', $candidate->id)
                ->first();

            if ($application && ! $application->trashed()) {
                return [
                    'candidate' => $candidate,
                    'application' => $application,
                    'already_applied' => true,
                    'should_send_received_mail' => false,
                ];
            }

            $cv = $this->storeSubmittedCv($candidate);
            $cvPath = $cv['path'];
            $cvAttachment = $cv['attachment'];

            $resume = CandidateResume::query()->firstOrNew(['candidate_id' => $candidate->id], []);
            if ($this->sync_profile_to_candidate) {
                $resume->fill([
                    'profile_title' => $this->profile_title,
                    'career_objective' => $this->career_objective,
                    'personal_info' => array_filter([
                        'email' => trim($this->email),
                        'phone' => is_string($this->phone) ? trim($this->phone) : null,
                    ], fn ($value) => filled($value)),
                ]);

                $resume->save();
            }

            $resumeSnapshot = $this->buildResumeSnapshotForApplication($candidate, $resume);
            $profileSnapshot = $this->buildApplicationSnapshot($candidate, $resumeSnapshot, $cvPath, $cvAttachment);
            $cvTextSnapshot = null;

            $application ??= Application::withTrashed()
                ->firstOrNew([
                    'job_id' => $this->job->id,
                    'candidate_id' => $candidate->id,
                ]);

            $wasRecentlyCreated = ! $application->exists;
            $wasRestoredFromTrash = false;

            $application->fill([
                'cv_path' => $cvPath,
                'apply_method' => 'cv',
                'profile_snapshot' => $profileSnapshot,
                'cv_text_snapshot' => $cvTextSnapshot,
                'source' => 'website',
                'status' => StatusApplicationEnum::NEW,
                'applied_at' => now(),
                'branch_id' => $this->job->branch_id,
            ]);

            if ($application->trashed()) {
                $application->deleted_at = null;
                $wasRestoredFromTrash = true;
            }

            $application->save();

            $applicationCvAttachment = $this->syncApplicationCvAttachment($application, $cvPath, $cvAttachment);
            $profileSnapshot = $this->buildApplicationSnapshot($candidate, $resumeSnapshot, $cvPath, $applicationCvAttachment);

            $application->fill([
                'profile_snapshot' => $profileSnapshot,
                'cv_attachment_id' => $applicationCvAttachment?->id,
            ]);
            $application->save();

            if ($this->sync_profile_to_candidate) {
                $candidate->fill([
                    'name' => trim($this->name),
                    'email' => trim($this->email),
                    'phone' => is_string($this->phone) ? trim($this->phone) : null,
                    'experience_years' => $this->experience_years,
                ]);
                $candidate->save();
            }

            if ($this->use_cv_as_primary && $cvPath) {
                $candidate->cv_file = $cvPath;
                $candidate->save();

                $candidate->attachments()
                    ->where('type', 'cv')
                    ->delete();

                $candidate->attachments()->create([
                    'path' => $cvPath,
                    'type' => 'cv',
                    'original_filename' => $cvAttachment?->original_filename ?: basename($cvPath),
                    'mime_type' => $cvAttachment?->mime_type,
                    'size_bytes' => $cvAttachment?->size_bytes,
                ]);
            }

            CandidateJobSubmission::query()->updateOrCreate(
                [
                    'job_id' => $this->job->id,
                    'candidate_id' => $candidate->id,
                ],
                [
                    'apply_method' => 'cv',
                    'profile_snapshot' => $profileSnapshot,
                    'cv_path' => $cvPath,
                    'cv_attachment_id' => $applicationCvAttachment?->id,
                    'cv_text_snapshot' => $cvTextSnapshot,
                ],
            );

            $this->candidateId = $candidate->id;

            return [
                'candidate' => $candidate,
                'application' => $application,
                'should_send_received_mail' => $wasRecentlyCreated || $wasRestoredFromTrash,
            ];
        });

        if (($result['already_applied'] ?? false) === true) {
            $this->addError('application', 'Bạn đã ứng tuyển vị trí này. Vui lòng theo dõi trạng thái trong mục Việc làm đã ứng tuyển.');

            return null;
        }

        if (($result['should_send_received_mail'] ?? false) === true) {
            ProcessApplicationCvText::dispatch($result['application']->id);

            $this->sendApplicationReceivedMail(
                $result['candidate'],
                $result['application'],
            );

            $this->sendGuestApplicationVerificationMail(
                $result['candidate'],
                $result['application'],
            );

            $this->sendHrNewApplicationMail(
                $result['candidate'],
                $result['application'],
            );
        }

        $this->cv = null;
        $this->showSuccessModal = true;

        return null;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
    }

    protected function resolveExistingCandidate(): ?Candidate
    {
        $user = Auth::user();
        if ($user) {
            return $this->canUseCandidateAccount($user)
                ? app(CandidateAccountService::class)->resolveFor($user)
                : null;
        }

        $email = trim($this->email);
        $phone = is_string($this->phone) ? trim($this->phone) : null;

        $candidate = Candidate::query()
            ->where('email', $email)
            ->first();

        if ($candidate) {
            return $candidate;
        }

        if ($phone) {
            return Candidate::query()
                ->where('phone', $phone)
                ->first();
        }

        return null;
    }

    protected function resolveCandidateForApplication(): Candidate
    {
        $candidate = $this->resolveExistingCandidate() ?? new Candidate;

        if (! $candidate->exists && Auth::check()) {
            $candidate->user_id = Auth::id();
        }

        if (! $candidate->exists || $this->sync_profile_to_candidate) {
            $candidate->fill([
                'name' => trim($this->name),
                'email' => trim($this->email),
                'phone' => is_string($this->phone) ? trim($this->phone) : null,
                'experience_years' => $this->experience_years,
            ]);
        }

        $candidate->save();

        return $candidate;
    }

    /**
     * @return array{path: string, attachment: Attachment|null}
     */
    protected function storeSubmittedCv(Candidate $candidate): array
    {
        // 1. Uploaded new CV file
        if ($this->selectedCvOption === 'new_upload' && $this->cv) {
            $path = $this->cv->storePublicly("applications/{$candidate->id}/{$this->job->id}/cv", 'public');

            $attachment = new Attachment([
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

            return [
                'path' => $path,
                'attachment' => $attachment,
            ];
        }

        // 2. Selected an existing attachment
        if (str_starts_with($this->selectedCvOption, 'attachment_')) {
            $attId = (int) str_replace('attachment_', '', $this->selectedCvOption);
            $attachment = $candidate->attachments()->where('id', $attId)->first();
            if ($attachment) {
                return [
                    'path' => $attachment->path,
                    'attachment' => $attachment,
                ];
            }
        }

        // 3. Selected the single saved online CV. Template changes stay in the CV builder.
        if (str_starts_with($this->selectedCvOption, 'online_')) {
            $resume = CandidateResume::query()->firstOrNew(['candidate_id' => $candidate->id], []);
            $template = app(CandidateAccountService::class)->savedOnlineCvTemplate($candidate);

            if ($template === null || $this->selectedCvOption !== 'online_'.$template) {
                return [
                    'path' => (string) $candidate->cv_file,
                    'attachment' => null,
                ];
            }

            try {
                $pdf = Pdf::loadView('pdf.cv-template', [
                    'candidate' => $candidate,
                    'resume' => $resume,
                    'template' => $template,
                ])->setPaper('a4', 'portrait')
                    ->setOption('isHtml5ParserEnabled', true)
                    ->setOption('isRemoteEnabled', true)
                    ->setOption('defaultFont', 'DejaVu Sans');

                $fileName = 'CV_'.Str::slug($candidate->name ?: 'Candidate', '_').'_'.$template.'.pdf';
                $relativeDir = "applications/{$candidate->id}/{$this->job->id}/cv";
                $relativePath = "{$relativeDir}/{$fileName}";

                Storage::disk('public')->put($relativePath, $pdf->output());

                $attachment = new Attachment([
                    'path' => $relativePath,
                    'type' => 'cv',
                    'original_filename' => $fileName,
                    'mime_type' => 'application/pdf',
                    'size_bytes' => Storage::disk('public')->size($relativePath) ?: 0,
                ]);

                return [
                    'path' => $relativePath,
                    'attachment' => $attachment,
                ];
            } catch (\Throwable $e) {
                Log::error('Failed to generate online CV PDF during application: '.$e->getMessage());
            }
        }

        // 4. Fallback
        return [
            'path' => (string) $candidate->cv_file,
            'attachment' => $candidate->attachments()
                ->where('type', 'cv')
                ->latest('id')
                ->first(),
        ];
    }

    protected function syncApplicationCvAttachment(
        Application $application,
        string $cvPath,
        ?Attachment $sourceAttachment,
    ): ?Attachment {
        if (blank($cvPath)) {
            return null;
        }

        $application->attachments()
            ->where('type', 'cv')
            ->delete();

        return $application->attachments()->create([
            'path' => $cvPath,
            'type' => 'cv',
            'original_filename' => $sourceAttachment?->original_filename ?: basename($cvPath),
            'mime_type' => $sourceAttachment?->mime_type,
            'size_bytes' => $sourceAttachment?->size_bytes,
        ]);
    }

    protected function buildResumeSnapshotForApplication(Candidate $candidate, CandidateResume $resume): array
    {
        return [
            'profile_title' => filled($this->profile_title) ? $this->profile_title : $resume->profile_title,
            'career_objective' => filled($this->career_objective) ? $this->career_objective : $resume->career_objective,
            'personal_info' => array_filter([
                'email' => trim($this->email) ?: $candidate->email,
                'phone' => is_string($this->phone) ? trim($this->phone) : $candidate->phone,
            ], fn ($value) => filled($value)),
            'desired_job' => $resume->desired_job ?? [],
            'experiences' => $resume->experiences ?? [],
            'educations' => $resume->educations ?? [],
            'certifications' => $resume->certifications ?? [],
            'languages' => $resume->languages ?? [],
            'skills' => $resume->skills ?? [],
            'achievements' => $resume->achievements ?? [],
            'activities' => $resume->activities ?? [],
            'references' => $resume->references ?? [],
            'extra' => $resume->extra ?? [],
        ];
    }

    protected function buildApplicationSnapshot(
        Candidate $candidate,
        array $resumeSnapshot,
        ?string $cvPath,
        ?Attachment $cvAttachment = null,
    ): array {
        $candidateSnapshot = [
            'id' => $candidate->id,
            'user_id' => $candidate->user_id,
            'name' => trim($this->name),
            'email' => trim($this->email),
            'phone' => is_string($this->phone) ? trim($this->phone) : null,
            'experience_years' => $this->experience_years,
        ];

        return array_filter([
            // Top-level fields keep older client views working while the nested
            // structure becomes the canonical snapshot for application details.
            'name' => $candidateSnapshot['name'],
            'email' => $candidateSnapshot['email'],
            'phone' => $candidateSnapshot['phone'],
            'experience_years' => $candidateSnapshot['experience_years'],
            'profile_title' => $resumeSnapshot['profile_title'],
            'career_objective' => $resumeSnapshot['career_objective'],
            'candidate' => $candidateSnapshot,
            'resume' => $resumeSnapshot,
            'cv' => [
                'path' => $cvPath,
                'attachment_id' => $cvAttachment?->id,
                'original_filename' => $cvAttachment?->original_filename,
                'mime_type' => $cvAttachment?->mime_type,
                'size_bytes' => $cvAttachment?->size_bytes,
                'submitted_at' => now()->toDateTimeString(),
            ],
        ], fn ($value) => ! is_null($value));
    }

    protected function sendApplicationReceivedMail(Candidate $candidate, Application $application): void
    {
        $email = is_string($candidate->email) ? trim($candidate->email) : null;

        if (blank($email)) {
            return;
        }

        try {
            app(OutboundMailQueue::class)->queue(
                $email,
                new CandidateApplicationReceivedMail($candidate, $application, $this->job),
            );
        } catch (\Throwable $exception) {
            Log::warning('Unable to send candidate application confirmation email.', [
                'candidate_id' => $candidate->id,
                'application_id' => $application->id,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function sendGuestApplicationVerificationMail(Candidate $candidate, Application $application): void
    {
        if (Auth::check()) {
            return;
        }

        $email = is_string($candidate->email) ? trim($candidate->email) : null;

        if (blank($email)) {
            return;
        }

        try {
            app(OutboundMailQueue::class)->queue(
                $email,
                new GuestApplicationVerificationMail($candidate, $application),
            );
        } catch (\Throwable $exception) {
            Log::warning('Unable to send guest application verification email.', [
                'candidate_id' => $candidate->id,
                'application_id' => $application->id,
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function sendHrNewApplicationMail(Candidate $candidate, Application $application): void
    {
        $branchId = $this->job->branch_id;

        if (! $branchId) {
            return;
        }

        $hrUsers = User::query()
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query
                    ->where('role', 'hr')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'hr'));
            })
            ->get();

        foreach ($hrUsers as $hrUser) {
            $email = is_string($hrUser->email) ? trim($hrUser->email) : null;

            if (blank($email)) {
                continue;
            }

            try {
                app(OutboundMailQueue::class)->queue(
                    $email,
                    new HrNewApplicationMail($candidate, $application, $this->job),
                );
            } catch (\Throwable $exception) {
                Log::warning('Unable to send HR new application email.', [
                    'hr_user_id' => $hrUser->id,
                    'candidate_id' => $candidate->id,
                    'application_id' => $application->id,
                    'email' => $email,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    public function getExistingCvNameProperty(): ?string
    {
        if (! Auth::check() || $this->requiresCandidateActivation) {
            return null;
        }

        $candidate = $this->resolveExistingCandidate();
        if (! $candidate?->cv_file) {
            return null;
        }

        $attachment = $candidate->attachments()
            ->where('type', 'cv')
            ->latest('id')
            ->first();

        return $attachment?->original_filename ?: basename($candidate->cv_file);
    }

    public function getExistingCvUrlProperty(): ?string
    {
        if (! Auth::check() || $this->requiresCandidateActivation) {
            return null;
        }

        $candidate = $this->resolveExistingCandidate();
        if (! $candidate?->cv_file) {
            return null;
        }

        return Route::has('public-file.preview')
            ? route('public-file.preview', ['path' => $candidate->cv_file])
            : asset('storage/'.ltrim($candidate->cv_file, '/'));
    }

    public function getRequiresCandidateActivationProperty(): bool
    {
        return $this->requiresCandidateActivation();
    }

    private function requiresCandidateActivation(): bool
    {
        $user = Auth::user();

        return (bool) $user && ! $this->canUseCandidateAccount($user);
    }

    public function getCandidateActivationUrlProperty(): string
    {
        return route('candidates.register', [
            'next_route' => 'candidates.candidate_dashboard',
        ]);
    }

    private function canUseCandidateAccount(User $user): bool
    {
        return app(CandidateAccountService::class)->hasCandidateAccount($user);
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'experience_years.integer' => 'Số năm kinh nghiệm phải là số nguyên.',
            'experience_years.min' => 'Số năm kinh nghiệm không được âm.',
            'cv.required' => 'Vui lòng tải lên CV.',
            'cv.file' => 'CV tải lên không hợp lệ.',
            'cv.mimes' => 'CV chỉ hỗ trợ định dạng PDF, DOC hoặc DOCX.',
            'cv.max' => 'CV không được vượt quá 10MB.',
            'selectedCvOption.in' => 'CV đã chọn không còn khả dụng. Vui lòng chọn lại CV trước khi ứng tuyển.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'họ và tên',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'experience_years' => 'số năm kinh nghiệm',
            'profile_title' => 'tiêu đề hồ sơ',
            'career_objective' => 'mục tiêu nghề nghiệp',
            'cv' => 'CV',
        ];
    }

    #[Layout('layouts.client')]
    public function render()
    {
        $candidate = $this->resolveExistingCandidate();
        $attachments = $candidate
            ? $candidate->attachments()->where('type', 'cv')->latest()->get()
            : collect();

        $primaryCv = data_get($candidate?->metadata, 'primary_cv', []);
        $onlineTemplate = $candidate
            ? app(CandidateAccountService::class)->savedOnlineCvTemplate($candidate)
            : null;
        $onlineCv = $onlineTemplate ? [
            'template' => $onlineTemplate,
            'name' => $this->onlineTemplateName($onlineTemplate),
            'is_primary' => data_get($primaryCv, 'type') === 'online',
        ] : null;

        return view('livewire.client.apply-job', [
            'attachments' => $attachments,
            'primaryCv' => $primaryCv,
            'onlineCv' => $onlineCv,
        ]);
    }

    /** @return array<int, string> */
    private function availableCvOptionValues(): array
    {
        $options = ['new_upload'];
        $candidate = $this->resolveExistingCandidate();

        if (! $candidate) {
            return $options;
        }

        $onlineTemplate = app(CandidateAccountService::class)->savedOnlineCvTemplate($candidate);
        if ($onlineTemplate !== null) {
            $options[] = 'online_'.$onlineTemplate;
        }

        foreach ($candidate->attachments()->where('type', 'cv')->pluck('id') as $attachmentId) {
            $options[] = 'attachment_'.$attachmentId;
        }

        return $options;
    }

    private function onlineTemplateName(string $template): string
    {
        return match ($template) {
            'ats-classic' => 'ATS Classic Clean',
            'tech-executive' => 'Tech Executive',
            default => 'FPT Modern Pro',
        };
    }
}
