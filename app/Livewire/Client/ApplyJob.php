<?php

namespace App\Livewire\Client;

use App\Models\Candidate;
use App\Models\CandidateJobSubmission;
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

    public int $candidateId;

    public string $apply_method = 'profile';

    public bool $use_existing_cv = true;

    public $cv = null;

    public function mount(RecruitmentJob $job): void
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $this->job = $job;

        $candidate = app(CandidateAccountService::class)->resolveFor($user);

        $this->candidateId = $candidate->id;
    }

    public function submit(): void
    {
        $this->validate([
            'apply_method' => ['required', 'in:profile,cv'],
            'use_existing_cv' => ['boolean'],
            'cv' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
        ]);

        $candidate = Candidate::query()->with('resume')->findOrFail($this->candidateId);

        $submission = DB::transaction(function () use ($candidate) {
            $cvPath = null;
            $cvAttachmentId = null;

            if ($this->apply_method === 'cv') {
                if ($this->cv) {
                    $cvPath = $this->cv->storePublicly("submissions/{$candidate->id}/{$this->job->id}/cv", 'public');
                } elseif ($this->use_existing_cv && $candidate->cv_file) {
                    $cvPath = $candidate->cv_file;
                }

                if (! $cvPath) {
                    $this->addError('cv', 'Bạn cần chọn CV để nộp.');
                    return null;
                }
            } else {
                // Profile method: allow empty cv_path, but if candidate already has a CV keep it for convenience.
                $cvPath = $candidate->cv_file ?: null;
            }

            $profileSnapshot = [
                'candidate' => [
                    'id' => $candidate->id,
                    'user_id' => $candidate->user_id,
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                    'phone' => $candidate->phone,
                    'experience_years' => $candidate->experience_years,
                ],
                'resume' => $candidate->resume?->toArray(),
            ];

            $submission = CandidateJobSubmission::query()->updateOrCreate(
                ['job_id' => $this->job->id, 'candidate_id' => $candidate->id],
                [
                    'cv_path' => $cvPath,
                    'apply_method' => $this->apply_method,
                    'profile_snapshot' => $this->apply_method === 'profile' ? $profileSnapshot : null,
                    'cv_text_snapshot' => null,
                ],
            );

            if ($this->apply_method === 'cv') {
                $cvText = app(CvTextExtractor::class)->extractFromPublicPath($cvPath);
                if (is_string($cvText) && $cvText !== '') {
                    $submission->cv_text_snapshot = mb_substr($cvText, 0, 200000);
                }

                $submission->attachments()
                    ->where('type', 'cv')
                    ->delete();

                $attachment = $submission->attachments()->create([
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
                $submission->cv_attachment_id = $cvAttachmentId;
                $submission->save();
            } else {
                $submission->cv_attachment_id = null;
                $submission->save();
            }

            return $submission;
        });

        if (! $submission) {
            return;
        }

        $this->cv = null;
        session()->flash('status', 'Đã nộp ứng tuyển thành công.');
    }

    #[Layout('layouts.client')]
    public function render()
    {
        $candidate = Candidate::query()->with('resume')->find($this->candidateId);
        $hasCv = (bool) ($candidate?->cv_file);

        return view('livewire.client.apply-job', [
            'candidate' => $candidate,
            'hasCv' => $hasCv,
        ]);
    }
}
