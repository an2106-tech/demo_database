<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'interviewer_id',
        'scorecard_template_id',
        'scorecard_template_snapshot',
        'round_number',
        'round_name',
        'scheduled_at',
        'actual_ended_at',
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
        'invite_sent_at' => 'datetime',
        'invite_confirmed_at' => 'datetime',
        'actual_ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'round_number' => 'integer',
        'scorecard_template_snapshot' => 'array',
    ];

    protected function scheduledAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?CarbonInterface => $value
                ? Carbon::parse((string) $value, $this->interviewTimezone())
                : null,
            set: fn ($value): ?string => $value
                ? $this->asInterviewDateTime($value)->format('Y-m-d H:i:s')
                : null,
        );
    }

    private function asInterviewDateTime(mixed $value): CarbonInterface
    {
        $timezone = $this->interviewTimezone();

        if ($value instanceof CarbonInterface) {
            return $value->copy()->setTimezone($timezone);
        }

        return Carbon::parse((string) $value, $timezone);
    }

    private function interviewTimezone(): string
    {
        return config('app.interview_timezone', 'Asia/Ho_Chi_Minh');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function scorecardTemplate(): BelongsTo
    {
        return $this->belongsTo(ScorecardTemplate::class, 'scorecard_template_id');
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(Scorecard::class);
    }
}
