<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationAiAnalysis extends Model
{
    protected $fillable = [
        'application_id',
        'cv_extraction_id',
        'analysis_type',
        'status',
        'score',
        'recommendation',
        'summary',
        'strengths',
        'gaps',
        'suggested_note',
        'result_json',
        'raw_response',
        'provider',
        'model',
        'created_by',
        'created_from',
        'error_message',
        'analyzed_at',
    ];

    protected $casts = [
        'strengths' => 'array',
        'gaps' => 'array',
        'result_json' => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function cvExtraction(): BelongsTo
    {
        return $this->belongsTo(CvExtraction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
