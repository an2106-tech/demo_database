<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateResume extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'profile_title',
        'personal_info',
        'career_objective',
        'desired_job',
        'experiences',
        'educations',
        'certifications',
        'languages',
        'skills',
        'achievements',
        'activities',
        'references',
        'extra',
    ];

    protected $casts = [
        'personal_info' => 'array',
        'desired_job' => 'array',
        'experiences' => 'array',
        'educations' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'skills' => 'array',
        'achievements' => 'array',
        'activities' => 'array',
        'references' => 'array',
        'extra' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}

