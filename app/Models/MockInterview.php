<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockInterview extends Model
{
    protected $fillable = [
        'candidate_id',
        'job_id',
        'application_id',
        'status',
        'current_step',
        'total_score',
        'summary_feedback',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'total_score' => 'integer',
        'summary_feedback' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(RecruitmentJob::class, 'job_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MockInterviewMessage::class)->orderBy('question_number', 'asc');
    }
}
