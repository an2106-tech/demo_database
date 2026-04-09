<?php

namespace App\Models;

use App\Enums\StatusApplicationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

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
}
