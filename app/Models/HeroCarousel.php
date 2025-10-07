<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroCarousel extends Model
{
    protected $fillable = [
        'title',
        'description',
        'background',
        'action_text',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope for active hero carousels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering hero carousels
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
