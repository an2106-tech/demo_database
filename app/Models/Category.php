<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'image',
        'status'
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $casts = [
        'status' => 'string',
    ];

    public function recruitmentJobs(): BelongsToMany
    {
        return $this->belongsToMany(RecruitmentJob::class, 'recruitment_job_category', 'category_id', 'recruitment_job_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getIconClassAttribute(): string
    {
        $icon = trim((string) ($this->icon ?? ''));

        if ($icon === '') {
            return 'bi bi-grid';
        }

        return Str::startsWith($icon, 'bi') ? $icon : 'bi bi-' . $icon;
    }

    public function getImageUrlAttribute(): string
    {
        $image = trim((string) ($this->image ?? ''));

        if ($image === '') {
            return asset('assets/img/bg-3_3.jpg');
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        if (Str::startsWith($image, ['/storage/', 'storage/'])) {
            return '/' . ltrim($image, '/');
        }

        if (Str::startsWith($image, ['/assets/', 'assets/'])) {
            return asset(ltrim($image, '/'));
        }

        return '/storage/' . ltrim($image, '/');
    }
}
