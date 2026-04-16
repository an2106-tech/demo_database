<?php

namespace App\Models;

use App\Enums\StatusRecruitmentJobsEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentJob extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        // Auto-generate public_url whenever slug changes
        static::saving(function (RecruitmentJob $job) {
            if (filled($job->slug)) {
                $job->public_url = route('jobs.public', ['slug' => $job->slug]);
            }
        });
    }
    protected $fillable = [
        'title',
        'slug',
        'branch_id',
        'workplace_id',
        'description',
        'status',
        'salary_range',
        'deadline',
        'positions_count',
        'public_url',
        'thumbnail',
        'department_id',
        'created_by',
    ];

    protected $casts = [
        'salary_range' => 'array',
        'deadline'     => 'date',
        'status'       => StatusRecruitmentJobsEnum::class,
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skills', 'job_id', 'skill_id')
            ->withPivot(['level', 'is_required']);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'recruitment_job_category', 'recruitment_job_id', 'category_id');
    }
}
