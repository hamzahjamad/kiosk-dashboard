<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundSetting extends Model
{
    protected $fillable = [
        'slide_interval',
        'transition_duration',
        'overlay_opacity',
    ];

    protected $casts = [
        'slide_interval' => 'integer',
        'transition_duration' => 'integer',
        'overlay_opacity' => 'integer',
    ];

    /**
     * Get the singleton settings record
     */
    public static function getSettings()
    {
        $settings = self::first();

        if (! $settings) {
            $settings = self::create([
                'slide_interval' => 10,
                'transition_duration' => 2,
                'overlay_opacity' => 50,
            ]);
        }

        return $settings;
    }
}
