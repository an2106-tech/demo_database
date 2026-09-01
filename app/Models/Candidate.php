<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
        return $this->belongsTo(User::class);
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(CandidateResume::class);
    }

    public function resume(): HasOne
    {
        return $this->hasOne(CandidateResume::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function latestVisibleApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'latest_visible_application_id');
    }

    public function blacklistedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blacklisted_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CandidateJobSubmission::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function currentCvUrl(): ?string
    {
        if (! $this->cv_file) {
            return null;
        }

        if (Route::has('public-file.preview') && Storage::disk('public')->exists($this->cv_file)) {
            return route('public-file.preview', ['path' => $this->cv_file]);
        }

        return asset('storage/'.ltrim($this->cv_file, '/'));
    }
}
