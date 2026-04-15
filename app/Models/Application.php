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

                        Mail::to($candidate->email)->send(new CandidateApplicationRejectedMail($candidate, $application, $job));
                        
                        Log::info('--- REJECTION EMAIL SENT SUCCESSFULLY ---', ['recipient' => $candidate->email]);
                    } catch (\Throwable $exception) {
                        Log::warning('Failed to send rejection email via Model observer.', [
                            'application_id' => $application->id,
                            'error' => $exception->getMessage(),
                        ]);
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
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'status',
        'salary_expected',
        'applied_at',
        'rejected_reason',
    ];

    protected $casts = [
        'salary_expected' => 'array',
        'applied_at' => 'datetime',
        'status' => StatusApplicationEnum::class,
        'profile_snapshot' => 'array',
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

    public function latestScorecard(): HasOne
    {
        return $this->hasOne(Scorecard::class)->latestOfMany();
    }
}
