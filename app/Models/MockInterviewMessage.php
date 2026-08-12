<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockInterviewMessage extends Model
{
    protected $fillable = [
        'mock_interview_id',
        'question_number',
        'question_text',
        'answer_text',
        'score',
        'feedback',
        'suggested_answer',
    ];

    protected $casts = [
        'question_number' => 'integer',
        'score' => 'integer',
    ];

    public function mockInterview(): BelongsTo
    {
        return $this->belongsTo(MockInterview::class);
    }
}
