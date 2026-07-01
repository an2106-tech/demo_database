<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scorecard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'interview_id',
        'template_id',
        'evaluator_id',
        'criteria',
        'average_score',
        'recommended_conclusion',
        'notes',
        'override_reason',
        'conclusion',
    ];

    protected $casts = [
        'criteria' => 'array',
        'average_score' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(Interview::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ScorecardTemplate::class, 'template_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}

