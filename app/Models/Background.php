<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Background extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get visible backgrounds ordered by sort_order
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Get backgrounds ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the full URL to the background image
     */
    public function getUrlAttribute()
    {
        return asset($this->path);
    }
}
