<?php

namespace App\Livewire\Client;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\RecruitmentJob;
use App\Services\CandidateAccountService;
use App\Services\CvTextExtractor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public bool $use_existing_cv = true;

    public $cv = null;

    public function updatedCv(): void
    {
        $this->use_existing_cv = false;
        $this->resetValidation('cv');
    }

    public function mount(RecruitmentJob $job): void
    {
        $this->job = $job;

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
            'use_existing_cv' => ['boolean'],
        ];

        $candidate = $this->resolveExistingCandidate();
        $hasExistingCv = (bool) ($candidate?->cv_file);

        $application = DB::transaction(function () use ($candidate) {
            $cvPath = null;
            $cvAttachmentId = null;

            if ($this->apply_method === 'cv') {
                if ($this->cv) {
                    $cvPath = $this->cv->storePublicly("applications/{$candidate->id}/{$this->job->id}/cv", 'public');
                } elseif ($this->use_existing_cv && $candidate->cv_file) {
                    $cvPath = $candidate->cv_file;
                }

                if (! $cvPath) {
                    $this->addError('cv', 'Bạn cần chọn CV để nộp.');
                    return null;
                }
            } else {
                // Profile method: CV is optional. If candidate already has a CV keep it for convenience.
                $cvPath = $candidate->cv_file ?: null;
            }

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

            $application = Application::query()->updateOrCreate(
                ['job_id' => $this->job->id, 'candidate_id' => $candidate->id],
                [
                    'cv_path' => $cvPath,
                    'apply_method' => $this->apply_method,
                    'profile_snapshot' => $this->apply_method === 'profile' ? $profileSnapshot : null,
                    'cv_text_snapshot' => null,
                    'source' => 'website',
                    'status' => 'new',
                    'applied_at' => now(),
                ],
            );

            if ($this->apply_method === 'cv') {
                $cvText = app(CvTextExtractor::class)->extractFromPublicPath($cvPath);
                if (is_string($cvText) && $cvText !== '') {
                    $application->cv_text_snapshot = mb_substr($cvText, 0, 200000);
                }

                $application->attachments()
                    ->where('type', 'cv')
                    ->delete();

                $attachment = $application->attachments()->create([
                    'path' => $cvPath,
                    'type' => 'cv',
                    'original_filename' => $this->cv && method_exists($this->cv, 'getClientOriginalName')
                        ? $this->cv->getClientOriginalName()
                        : null,
                    'mime_type' => $this->cv && method_exists($this->cv, 'getMimeType')
                        ? $this->cv->getMimeType()
                        : null,
                    'size_bytes' => $this->cv && method_exists($this->cv, 'getSize')
                        ? $this->cv->getSize()
                        : null,
                ]);

                $cvAttachmentId = $attachment->id;
                $application->cv_attachment_id = $cvAttachmentId;
                $application->save();
            } else {
                $application->cv_attachment_id = null;
                $application->save();
            }

            return $application;
        });

        if (! $application) {
            return;
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

    public function getHasExistingCvProperty(): bool
    {
        return (bool) ($this->resolveExistingCandidate()?->cv_file);
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
        return view('livewire.client.apply-job', [
            'hasExistingCv' => $this->hasExistingCv,
        ]);
    }
}
