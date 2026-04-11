<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workplace extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'capacity' => 'integer',
        'is_interview_room' => 'boolean',
        'is_active' => 'boolean',
    ];
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }


    public function recruitmentJob():HasMany{
        return $this->hasMany(RecruitmentJob::class, 'workplace_id', 'id');
    }

}
