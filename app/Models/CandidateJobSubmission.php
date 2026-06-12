<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateJobSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'candidate_id',
        'apply_method',
        'profile_snapshot',
        'cv_path',
        'cv_attachment_id',
        'cv_text_snapshot',
        'ai_matching_score',
        'ai_analysis',
    ];

    protected $casts = [
        'profile_snapshot' => 'array',
        'ai_analysis' => 'array',
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
}
