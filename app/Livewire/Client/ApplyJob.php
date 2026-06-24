<?php

namespace App\Livewire\Client;

use App\Enums\StatusApplicationEnum;
use App\Mail\CandidateApplicationReceivedMail;
use App\Mail\HrNewApplicationMail;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
use App\Models\User;
use App\Services\CandidateAccountService;
use App\Services\CvTextExtractor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
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

    public bool $showSuccessModal = false;

    public function updatedCv(): void
    {
        $this->resetValidation('cv');
    }

    public function mount(RecruitmentJob $job): void
    {
        $this->job = $job->loadMissing(['branch', 'department', 'workplace', 'skills']);

        $user = Auth::user();
        if (! $user) {
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
            $this->redirectRoute('candidates.candidate_profile');

            return;
        }

        $this->candidateId = $candidate->id;
        $this->name = (string) $candidate->name;
        $this->email = (string) ($candidate->email ?? $user->email ?? '');
        $this->phone = $candidate->phone;
        $this->experience_years = $candidate->experience_years;

        $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);
        $this->profile_title = $resume->profile_title;
        $this->career_objective = $resume->career_objective;
    }

    public function submit(): mixed
    {
        if ($this->requiresCandidateActivation()) {
            $this->addError('account', 'Vui lòng kích hoạt hồ sơ ứng viên trước khi ứng tuyển bằng tài khoản hiện tại.');

            return null;
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'profile_title' => ['nullable', 'string', 'max:255'],
            'career_objective' => ['nullable', 'string', 'max:4000'],
        ];

        $rules['cv'] = [$this->existing_cv_url ? 'nullable' : 'required', 'file', 'max:10240', 'mimes:pdf,doc,docx'];

        $this->validate($rules);

        $result = DB::transaction(function (): array {
            $candidate = $this->upsertCandidate();
            $cvPath = $this->storeCandidateCv($candidate);
            $cvText = $cvPath ? app(CvTextExtractor::class)->extractFromPublicPath($cvPath) : null;

            $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);
            $resume->fill([
                'profile_title' => $this->profile_title,
                'career_objective' => $this->career_objective,
                'personal_info' => array_filter([
                    'email' => $candidate->email,
                    'phone' => $candidate->phone,
                ], fn ($value) => filled($value)),
                'extra' => array_filter([
                    'cv_text' => is_string($cvText) && $cvText !== '' ? mb_substr($cvText, 0, 20000) : null,
                ], fn ($value) => filled($value)),
            ]);
            $resume->save();

            $profileSnapshot = $this->buildApplicationSnapshot($candidate, $resume, $cvPath);
            $cvTextSnapshot = is_string($cvText) && $cvText !== '' ? mb_substr($cvText, 0, 200000) : null;

            $candidateMetadata = is_array($candidate->metadata) ? $candidate->metadata : [];
            if (is_string($cvText) && $cvText !== '') {
                $candidateMetadata['cv_text_excerpt'] = mb_substr($cvText, 0, 4000);
            }
            $candidate->metadata = $candidateMetadata;
            $candidate->save();

            $application = Application::withTrashed()
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

            CandidateJobSubmission::query()->updateOrCreate(
                [
                    'job_id' => $this->job->id,
                    'candidate_id' => $candidate->id,
                ],
                [
                    'apply_method' => 'cv',
                    'profile_snapshot' => $profileSnapshot,
                    'cv_path' => $cvPath,
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

        if (($result['should_send_received_mail'] ?? false) === true) {
            $this->sendApplicationReceivedMail(
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

    protected function upsertCandidate(): Candidate
    {
        $candidate = $this->resolveExistingCandidate() ?? new Candidate();

        if (! $candidate->exists && Auth::check()) {
            $candidate->user_id = Auth::id();
        }

        $candidate->fill([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'phone' => is_string($this->phone) ? trim($this->phone) : null,
            'experience_years' => $this->experience_years,
        ]);

        $candidate->save();

        return $candidate;
    }

    protected function storeCandidateCv(Candidate $candidate): string
    {
        if ($this->cv) {
            $path = $this->cv->storePublicly("candidates/{$candidate->id}/cv", 'public');

            $candidate->cv_file = $path;
            $candidate->save();

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

            return $path;
        }

        return (string) $candidate->cv_file;
    }

    protected function buildApplicationSnapshot(Candidate $candidate, CandidateResume $resume, ?string $cvPath): array
    {
        $candidateSnapshot = [
            'id' => $candidate->id,
            'user_id' => $candidate->user_id,
            'name' => $candidate->name,
            'email' => $candidate->email,
            'phone' => $candidate->phone,
            'experience_years' => $candidate->experience_years,
        ];

        $resumeSnapshot = [
            'profile_title' => $resume->profile_title,
            'career_objective' => $resume->career_objective,
            'personal_info' => $resume->personal_info ?? [],
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
            Mail::to($email)->send(
                new CandidateApplicationReceivedMail($candidate, $application, $this->job)
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
                Mail::to($email)->send(
                    new HrNewApplicationMail($candidate, $application, $this->job)
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
            : asset('storage/' . ltrim($candidate->cv_file, '/'));
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
        return view('livewire.client.apply-job');
    }
}
