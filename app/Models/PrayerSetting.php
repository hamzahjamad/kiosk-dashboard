<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerSetting extends Model
{
    protected $fillable = [
        'city',
        'country',
        'method',
        'method_name',
        'show_subuh',
        'show_syuruk',
        'show_zohor',
        'show_asar',
        'show_maghrib',
        'show_isyak',
        'cached_times',
        'cached_date',
    ];

    protected $casts = [
        'method' => 'integer',
        'show_subuh' => 'boolean',
        'show_syuruk' => 'boolean',
        'show_zohor' => 'boolean',
        'show_asar' => 'boolean',
        'show_maghrib' => 'boolean',
        'show_isyak' => 'boolean',
        'cached_times' => 'array',
        'cached_date' => 'date',
    ];

    /**
     * Available calculation methods
     */
    public static function getMethods()
    {
        return [
            1 => 'University of Islamic Sciences, Karachi',
            2 => 'Islamic Society of North America (ISNA)',
            3 => 'Muslim World League (MWL)',
            4 => 'Umm Al-Qura University, Makkah',
            5 => 'Egyptian General Authority of Survey',
            7 => 'Institute of Geophysics, University of Tehran',
            8 => 'Gulf Region',
            9 => 'Kuwait',
            10 => 'Qatar',
            11 => 'Majlis Ugama Islam Singapura',
            12 => 'Union Organization Islamic de France',
            13 => 'Diyanet İşleri Başkanlığı, Turkey',
            14 => 'Spiritual Administration of Muslims of Russia',
            15 => 'Moonsighting Committee Worldwide',
            3 => 'JAKIM (Malaysia)', // Override method 3 name for Malaysia
        ];
    }

    /**
     * Get or create the singleton settings record
     */
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'city' => 'Labuan',
                'country' => 'Malaysia',
                'method' => 3,
                'method_name' => 'JAKIM',
            ]);
        }
        
        return $settings;
    }
}
