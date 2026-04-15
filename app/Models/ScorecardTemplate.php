<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScorecardTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'scorecard_templates';

    protected $fillable = [
        'name',
        'criteria',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_default' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

