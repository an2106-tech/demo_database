<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'cv_file',
        'experience_years',
        'match_score',
        'match_reasons',
        'blacklist',
        'blacklist_reason',
        'blacklisted_at',
        'blacklisted_by',
        'metadata',
    ];

    protected $casts = [
        'match_reasons' => 'array',
        'metadata' => 'array',
        'blacklist' => 'boolean',
        'blacklisted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blacklistedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blacklisted_by');
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(CandidateResume::class, 'candidate_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_id');
    }
}

