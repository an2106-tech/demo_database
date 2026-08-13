<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationPreScreening extends Model
{
    protected $fillable = [
        'application_id',
        'handled_by_user_id',
        'contact_channel',
        'contact_channel_detail',
        'contacted_at',
        'outcome',
        'follow_up_at',
        'follow_up_reminded_at',
        'note',
        'rejection_reason_code',
        'rejection_reason',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'follow_up_at' => 'datetime',
        'follow_up_reminded_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
