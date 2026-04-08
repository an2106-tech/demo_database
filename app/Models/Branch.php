<?php

namespace App\Models;

use App\Models\Workplace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_headquarters' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function workplaces(): HasMany
    {
        return $this->hasMany(Workplace::class);
    }

    public function recruitmentJobs(): HasMany
    {
        return $this->hasMany(RecruitmentJob::class);
    }
}
