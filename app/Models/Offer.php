<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'content',
        'salary_offered',
        'start_date',
        'probation_months',
        'status',
        'declined_reason',
        'response_at',
        'pdf_path',
        'sent_at',
        'accepted_at',
    ];

    protected $casts = [
        'salary_offered' => 'decimal:2',
        'start_date' => 'date',
        'response_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'probation_months' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
