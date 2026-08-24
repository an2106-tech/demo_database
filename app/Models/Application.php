<?php

namespace App\Models;

use App\Enums\StatusApplicationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Mail\CandidateApplicationRejectedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Application extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::updated(function (Application $application) {
            $newStatus = $application->status;
            $oldStatus = $application->getOriginal('status');

            // Chuyển sang enum nếu cần (Laravel 11 casts should handle this but getOriginal might return raw value)
            if (is_string($newStatus)) {
                $newStatus = StatusApplicationEnum::tryFrom($newStatus);
            }
            if (is_string($oldStatus)) {
                $oldStatus = StatusApplicationEnum::tryFrom($oldStatus);
            }

            if ($newStatus !== $oldStatus) {
                $application->recordStatusHistory(
                    $oldStatus instanceof StatusApplicationEnum ? $oldStatus->value : $oldStatus,
                    $newStatus instanceof StatusApplicationEnum ? $newStatus->value : $newStatus,
                    'Tự động ghi nhận thay đổi trạng thái.'
                );

                if ($newStatus === StatusApplicationEnum::REJECTED && $oldStatus !== StatusApplicationEnum::REJECTED) {
                    $candidate = $application->candidate;
                    $job = $application->job;

                    if ($candidate?->email && $job) {
                        try {
                            Log::info('--- PREPARING TO SEND REJECTION EMAIL ---', [
                                'app_id' => $application->id,
                                'recipient_email' => $candidate->email,
                                'from_config' => config('mail.from.address')
                            ]);

                            Mail::to($candidate->email)->queue(new CandidateApplicationRejectedMail($candidate, $application, $job));
                            
                            Log::info('--- REJECTION EMAIL SENT SUCCESSFULLY ---', ['recipient' => $candidate->email]);
                        } catch (\Throwable $exception) {
                            Log::warning('Failed to send rejection email via Model observer.', [
                                'application_id' => $application->id,
                                'error' => $exception->getMessage(),
                            ]);
                        }
                    }
                }

            }
        });
    }

    protected $fillable = [
        'job_id',
        'candidate_id',
        'cv_path',
        'apply_method',
        'profile_snapshot',
        'cv_attachment_id',
        'cv_text_snapshot',
        'source',
        'referral_user_id',
        'assigned_hr_id',
        'branch_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'status',
        'rejected_stage',
        'salary_expected',
        'applied_at',
        'rejected_reason',
        'is_viewed',
        'viewed_at',
        'applied_by',
        'is_duplicate',
    ];

    protected $casts = [
        'salary_expected' => 'array',
        'applied_at' => 'datetime',
        'status' => StatusApplicationEnum::class,
        'profile_snapshot' => 'array',
        'is_viewed' => 'boolean',
        'viewed_at' => 'datetime',
        'is_duplicate' => 'boolean',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(RecruitmentJob::class, 'job_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function cvAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'cv_attachment_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function latestInterview(): HasOne
    {
        return $this->hasOne(Interview::class)->latestOfMany('scheduled_at');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function latestOffer(): HasOne
    {
        return $this->hasOne(Offer::class)->latestOfMany();
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(Scorecard::class);
    }

    public function aiAnalyses(): HasMany
    {
        return $this->hasMany(ApplicationAiAnalysis::class);
    }

    public function latestScreeningAiAnalysis(): HasOne
    {
        return $this->hasOne(ApplicationAiAnalysis::class)
            ->where('analysis_type', 'screening')
            ->latestOfMany();
    }

    public function latestInterviewQuestionAiAnalysis(): HasOne
    {
        return $this->hasOne(ApplicationAiAnalysis::class)
            ->where('analysis_type', 'interview_questions')
            ->latestOfMany();
    }

    public function latestScorecard(): HasOne
    {
        return $this->hasOne(Scorecard::class)->latestOfMany();
    }

    public function assignedHr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_hr_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(\App\Models\ApplicationStatusHistory::class);
    }

    public function preScreenings(): HasMany
    {
        return $this->hasMany(ApplicationPreScreening::class);
    }

    public function latestPreScreening(): HasOne
    {
        return $this->hasOne(ApplicationPreScreening::class)->latestOfMany();
    }

    public function recordStatusHistory(?string $fromStatus, string $toStatus, ?string $comment = null): void
    {
        $this->statusHistories()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by_id' => auth()->id(),
            'comment' => $comment,
        ]);
    }

    public function snapshotValue(string $legacyKey, string $nestedKey, mixed $default = null): mixed
    {
        $snapshot = is_array($this->profile_snapshot) ? $this->profile_snapshot : [];

        return data_get($snapshot, $legacyKey)
            ?? data_get($snapshot, $nestedKey)
            ?? $default;
    }

    public function snapshotCandidateName(): string
    {
        return (string) ($this->snapshotValue('name', 'candidate.name')
            ?: $this->candidate?->name
            ?: 'Ứng viên');
    }

    public function snapshotCandidateEmail(): ?string
    {
        return $this->snapshotValue('email', 'candidate.email') ?: $this->candidate?->email;
    }

    public function snapshotCandidatePhone(): ?string
    {
        return $this->snapshotValue('phone', 'candidate.phone') ?: $this->candidate?->phone;
    }

    public function snapshotCandidateExperienceYears(): mixed
    {
        return $this->snapshotValue('experience_years', 'candidate.experience_years', $this->candidate?->experience_years);
    }

    public function snapshotProfileTitle(): ?string
    {
        return $this->snapshotValue('profile_title', 'resume.profile_title');
    }

    public function submittedCvName(): ?string
    {
        $path = $this->submittedCvPath();

        return data_get($this->profile_snapshot ?? [], 'cv.original_filename')
            ?: $this->cvAttachment?->original_filename
            ?: ($path ? basename($path) : null);
    }

    public function submittedCvPath(): ?string
    {
        return data_get($this->profile_snapshot ?? [], 'cv.path')
            ?: $this->cvAttachment?->path
            ?: $this->cv_path;
    }

    public function submittedCvUrl(): ?string
    {
        $path = $this->submittedCvPath();

        if (! $path) {
            return null;
        }

        if (Route::has('public-file.preview') && Storage::disk('public')->exists($path)) {
            return route('public-file.preview', ['path' => $path]);
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
