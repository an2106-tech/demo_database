<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'interviewer_id',
        'round_number',
        'round_name',
        'scheduled_at',
        'duration_minutes',
        'type',
        'meeting_link',
        'workplace_id',
        'invite_sent_at',
        'invite_confirmed_at',
        'notes',
        'result',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'invite_sent_at' => 'datetime',
        'invite_confirmed_at' => 'datetime',
        'duration_minutes' => 'integer',
        'round_number' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }
}
