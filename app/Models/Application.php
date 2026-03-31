<?php

namespace App\Models;

use App\Enums\StatusApplicationEnum;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'candidate_id',
        'cv_path',
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
        'status' => StatusApplicationEnum::class
    ];


    public function job(): BelongsTo
    {
        return $this->belongsTo(RecruitmentJob::class, 'job_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
