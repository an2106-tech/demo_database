<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cv extends Model
{
    protected $fillable = [
        'candidate_id',
        'file',
        'title',
        'is_default'
    ];

    protected static function booted(): void
    {
        static::saving(function (Cv $cv) {
            if (! $cv->is_default || empty($cv->candidate_id)) {
                return;
            }

            static::query()
                ->where('candidate_id', $cv->candidate_id)
                ->whereKeyNot($cv->id)
                ->update(['is_default' => false]);
        });
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
