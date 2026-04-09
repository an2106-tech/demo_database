<?php

namespace App\Livewire\Client;

use App\Enums\StatusApplicationEnum;
use App\Mail\CandidateApplicationReceivedMail;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
use App\Models\CandidateResume;
use App\Models\RecruitmentJob;
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

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $this->candidateId = $candidate->id;
        $this->name = (string) $candidate->name;
        $this->email = (string) ($candidate->email ?? $user->email ?? '');
        $this->phone = $candidate->phone;
        $this->experience_years = $candidate->experience_years;

        $resume = CandidateResume::query()->firstOrCreate(['candidate_id' => $candidate->id], []);
        $this->profile_title = $resume->profile_title;
        $this->career_objective = $resume->career_objective;
    }

    public function submit(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'profile_title' => ['nullable', 'string', 'max:255'],
            'career_objective' => ['nullable', 'string', 'max:4000'],
        ];

        $rules['cv'] = ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx'];

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

            $application->fill([
                'cv_path' => $cvPath,
                'source' => 'website',
                'status' => StatusApplicationEnum::NEW,
                'applied_at' => now(),
            ]);

            if ($application->trashed()) {
                $application->deleted_at = null;
            }

            $application->save();

            CandidateJobSubmission::query()->updateOrCreate(
                [
                    'job_id' => $this->job->id,
                    'candidate_id' => $candidate->id,
                ],
                [
                    'apply_method' => 'cv',
                    'profile_snapshot' => [
                        'candidate' => [
                            'id' => $candidate->id,
                            'user_id' => $candidate->user_id,
                            'name' => $candidate->name,
                            'email' => $candidate->email,
                            'phone' => $candidate->phone,
                            'experience_years' => $candidate->experience_years,
                        ],
                    ],
                    'cv_path' => $cvPath,
                    'cv_text_snapshot' => is_string($cvText) && $cvText !== '' ? mb_substr($cvText, 0, 200000) : null,
                ],
            );

            $this->candidateId = $candidate->id;

            return [
                'candidate' => $candidate,
                'application' => $application,
            ];
        });

        $this->sendApplicationReceivedMail(
            $result['candidate'],
            $result['application'],
        );

        $this->cv = null;
        $this->showSuccessModal = true;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
    }

    protected function resolveExistingCandidate(): ?Candidate
    {
        $user = Auth::user();
        if ($user) {
            return app(CandidateAccountService::class)->resolveFor($user);
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

        $candidate->fill([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'phone' => is_string($this->phone) ? trim($this->phone) : null,
            'experience_years' => $this->experience_years,
        ]);

        if (! $candidate->exists && Auth::check()) {
            $candidate->user_id = Auth::id();
        }

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

    public function getExistingCvNameProperty(): ?string
    {
        if (! Auth::check()) {
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
        if (! Auth::check()) {
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

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui long nhap ho va ten.',
            'email.required' => 'Vui long nhap email.',
            'email.email' => 'Email khong dung dinh dang.',
            'experience_years.integer' => 'So nam kinh nghiem phai la so nguyen.',
            'experience_years.min' => 'So nam kinh nghiem khong duoc am.',
            'cv.required' => 'Vui long tai len CV.',
            'cv.file' => 'CV tai len khong hop le.',
            'cv.mimes' => 'CV chi ho tro dinh dang PDF, DOC hoac DOCX.',
            'cv.max' => 'CV khong duoc vuot qua 10MB.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'ho va ten',
            'email' => 'email',
            'phone' => 'so dien thoai',
            'experience_years' => 'so nam kinh nghiem',
            'profile_title' => 'tieu de ho so',
            'career_objective' => 'muc tieu nghe nghiep',
            'cv' => 'CV',
        ];
    }

    #[Layout('layouts.client')]
    public function render()
    {
        return view('livewire.client.apply-job');
    }
}
