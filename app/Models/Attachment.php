<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'path',
        'type',
        'original_filename',
        'mime_type',
        'size_bytes',
        'description',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

