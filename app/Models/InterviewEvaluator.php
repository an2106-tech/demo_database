<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewEvaluator extends Model
{
    protected $fillable = [
        'interview_id',
        'user_id',
        'role',
        'is_required',
        'assigned_at',
        'submitted_at',
        'waived_at',
        'waived_by_user_id',
        'waiver_reason',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'assigned_at' => 'datetime',
        'submitted_at' => 'datetime',
        'waived_at' => 'datetime',
    ];

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by_user_id');
    }
}
