<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherSetting extends Model
{
    protected $fillable = [
        'city',
        'country',
        'unit',
        'show_temperature',
        'show_description',
        'show_humidity',
        'show_wind',
        'cached_weather',
        'cached_at',
    ];

    protected $casts = [
        'show_temperature' => 'boolean',
        'show_description' => 'boolean',
        'show_humidity' => 'boolean',
        'show_wind' => 'boolean',
        'cached_weather' => 'array',
        'cached_at' => 'datetime',
    ];

    /**
     * Get the singleton settings record
     */
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'city' => 'Labuan',
                'country' => 'Malaysia',
                'unit' => 'celsius',
                'show_temperature' => true,
                'show_description' => true,
                'show_humidity' => false,
                'show_wind' => false,
            ]);
        }
        
        return $settings;
    }

    /**
     * Get available temperature units
     */
    public static function getUnits()
    {
        return [
            'celsius' => 'Celsius (°C)',
            'fahrenheit' => 'Fahrenheit (°F)',
        ];
    }

    /**
     * Check if cache is still valid (30 minutes)
     */
    public function isCacheValid()
    {
        if (!$this->cached_at || !$this->cached_weather) {
            return false;
        }

        return $this->cached_at->diffInMinutes(now()) < 30;
    }
}
