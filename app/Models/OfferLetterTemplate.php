<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferLetterTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'body_html',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'offer_letter_template_id');
    }
}
