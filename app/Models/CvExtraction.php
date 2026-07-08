<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CvExtraction extends Model
{
    protected $fillable = [
        'cv_hash',
        'file_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'extracted_text',
        'status',
        'error_message',
        'extracted_at',
    ];

    protected $casts = [
        'extracted_at' => 'datetime',
    ];

    public function aiAnalyses(): HasMany
    {
        return $this->hasMany(ApplicationAiAnalysis::class);
    }
}
