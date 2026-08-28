<?php

namespace App\Models;

use App\Enums\StatusRecruitmentJobsEnum;
use App\Services\InterviewProcessTemplateService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

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

            $processFieldsChanged = $job->isDirty([
                'interview_process_template_id',
                'interview_process_snapshot',
            ]);

            if (! $processFieldsChanged) {
                return;
            }

            if ($job->exists && $job->applications()->exists()) {
                throw ValidationException::withMessages([
                    'interview_process_template_id' => 'Không thể đổi quy trình khi tin tuyển dụng đã có ứng viên.',
                ]);
            }

            $job->interview_process_snapshot = filled($job->interview_process_template_id)
                ? app(InterviewProcessTemplateService::class)
                    ->snapshotFromTemplateId((int) $job->interview_process_template_id)
                : null;
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
        'interview_process_template_id',
        'interview_process_snapshot',
        'created_by',
    ];

    protected $casts = [
        'salary_range' => 'array',
        'deadline' => 'date',
        'status' => StatusRecruitmentJobsEnum::class,
        'interview_process_snapshot' => 'array',
    ];

    public function getFormattedSalaryAttribute(): string
    {
        $range = $this->salary_range;

        if (empty($range)) {
            return 'Thỏa thuận';
        }

        if (is_string($range)) {
            return trim($range) !== '' ? $range : 'Thỏa thuận';
        }

        if (! is_array($range)) {
            return 'Thỏa thuận';
        }

        $min = $range['min'] ?? ($range[0] ?? null);
        $max = $range['max'] ?? ($range[1] ?? null);
        $currency = strtoupper(trim((string) ($range['currency'] ?? '')));

        if ($min === null && $max === null) {
            return 'Thỏa thuận';
        }

        $minVal = is_numeric($min) ? (float) $min : null;
        $maxVal = is_numeric($max) ? (float) $max : null;

        $isUsd = $currency === 'USD' || str_contains($currency, '$') || ($minVal !== null && $minVal < 10000 && ($maxVal === null || $maxVal < 10000) && $currency !== 'VND' && $currency !== 'VNĐ');

        $formatNumber = function (?float $num) use ($isUsd) {
            if ($num === null || $num <= 0) {
                return null;
            }

            if ($isUsd) {
                return '$'.number_format($num, 0, ',', '.');
            }

            if ($num >= 1000000) {
                $million = $num / 1000000;
                $formatted = round($million, 1);

                return rtrim(rtrim(number_format($formatted, 1, ',', '.'), '0'), ',').' triệu';
            }

            if ($num >= 1000) {
                return number_format($num, 0, ',', '.').' đ';
            }

            return $num.' triệu';
        };

        $minStr = $formatNumber($minVal);
        $maxStr = $formatNumber($maxVal);

        if ($minStr && $maxStr) {
            if ($isUsd) {
                return $minStr.' - '.$maxStr;
            }

            $cleanMin = str_replace(' triệu', '', $minStr);
            if (str_contains($maxStr, 'triệu')) {
                return $cleanMin.' - '.$maxStr.' VNĐ';
            }

            return $minStr.' - '.$maxStr;
        }

        if ($minStr) {
            return 'Từ '.$minStr.($isUsd ? '' : ' VNĐ');
        }

        if ($maxStr) {
            return 'Lên đến '.$maxStr.($isUsd ? '' : ' VNĐ');
        }

        return 'Thỏa thuận';
    }

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

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id');
    }

    public function interviewProcessTemplate(): BelongsTo
    {
        return $this->belongsTo(InterviewProcessTemplate::class);
    }

    public function hasLockedInterviewProcess(): bool
    {
        return $this->exists && $this->applications()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolvedInterviewProcess(): array
    {
        return app(InterviewProcessTemplateService::class)->resolveForJob($this);
    }
}
