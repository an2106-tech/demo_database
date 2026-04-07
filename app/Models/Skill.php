<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
    ];

    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(RecruitmentJob::class, 'job_skills', 'skill_id', 'job_id')
            ->withPivot(['level', 'is_required']);
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_skills', 'skill_id', 'candidate_id')
            ->withPivot(['proficiency_level', 'years_experience']);
    }
}
