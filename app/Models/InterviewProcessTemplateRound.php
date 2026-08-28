<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewProcessTemplateRound extends Model
{
    protected $fillable = [
        'interview_process_template_id',
        'round_number',
        'name',
        'candidate_label',
        'objective',
        'scorecard_template_id',
        'evaluator_roles',
    ];

    protected $casts = [
        'round_number' => 'integer',
        'evaluator_roles' => 'array',
    ];

    public function processTemplate(): BelongsTo
    {
        return $this->belongsTo(
            InterviewProcessTemplate::class,
            'interview_process_template_id'
        );
    }

    public function scorecardTemplate(): BelongsTo
    {
        return $this->belongsTo(ScorecardTemplate::class);
    }
}
