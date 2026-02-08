<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'type',
        'source',
        'source_id',
        'is_visible',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_visible' => 'boolean',
    ];

    /**
     * Scope to get only visible holidays
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope to get upcoming holidays within X days
     */
    public function scopeUpcoming($query, $days = 30)
    {
        $today = now()->startOfDay();
        $endDate = now()->addDays($days)->endOfDay();
        
        return $query->whereBetween('date', [$today, $endDate])
                     ->orderBy('date', 'asc');
    }

    /**
     * Scope to get holidays from API source
     */
    public function scopeFromApi($query)
    {
        return $query->where('source', 'calendarific');
    }

    /**
     * Scope to get manually added holidays
     */
    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }
}
