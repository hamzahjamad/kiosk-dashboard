<?php

namespace App\Http\Controllers;

use App\Models\PrayerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PrayerController extends Controller
{
    /**
     * Get today's prayer times (for dashboard)
     */
    public function times()
    {
        $settings = PrayerSetting::getSettings();
        
        // Check if we have cached times for today
        if ($settings->cached_date && $settings->cached_date->isToday() && $settings->cached_times) {
            $times = $settings->cached_times;
        } else {
            // Fetch from API and cache
            $times = $this->fetchFromApi($settings);
            
            if ($times) {
                $settings->update([
                    'cached_times' => $times,
                    'cached_date' => now()->toDateString(),
                ]);
            }
        }

        // Filter visible prayers
        $visibleTimes = [];
        $prayerMap = [
            'subuh' => 'show_subuh',
            'syuruk' => 'show_syuruk',
            'zohor' => 'show_zohor',
            'asar' => 'show_asar',
            'maghrib' => 'show_maghrib',
            'isyak' => 'show_isyak',
        ];

        foreach ($prayerMap as $prayer => $setting) {
            if ($settings->$setting && isset($times[$prayer])) {
                $visibleTimes[$prayer] = $times[$prayer];
            }
        }

        return response()->json([
            'success' => true,
            'times' => $visibleTimes,
            'location' => $settings->city . ', ' . $settings->country,
            'method' => $settings->method_name,
        ]);
    }

    /**
     * Get current settings
     */
    public function settings()
    {
        $settings = PrayerSetting::getSettings();
        
        return response()->json([
            'success' => true,
            'settings' => $settings,
            'methods' => PrayerSetting::getMethods(),
        ]);
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'method' => 'required|integer',
            'method_name' => 'required|string|max:255',
            'show_subuh' => 'boolean',
            'show_syuruk' => 'boolean',
            'show_zohor' => 'boolean',
            'show_asar' => 'boolean',
            'show_maghrib' => 'boolean',
            'show_isyak' => 'boolean',
        ]);

        $settings = PrayerSetting::getSettings();
        
        // Clear cache if location or method changed
        if ($settings->city !== $validated['city'] || 
            $settings->country !== $validated['country'] || 
            $settings->method !== $validated['method']) {
            $validated['cached_times'] = null;
            $validated['cached_date'] = null;
        }

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => $settings->fresh(),
        ]);
    }

    /**
     * Force refresh prayer times from API
     */
    public function refresh()
    {
        $settings = PrayerSetting::getSettings();
        $times = $this->fetchFromApi($settings);

        if ($times) {
            $settings->update([
                'cached_times' => $times,
                'cached_date' => now()->toDateString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prayer times refreshed successfully',
                'times' => $times,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch prayer times from API',
        ], 500);
    }

    /**
     * Fetch prayer times from Aladhan API
     */
    private function fetchFromApi(PrayerSetting $settings)
    {
        try {
            $today = now();
            $date = $today->format('d-m-Y');

            $response = Http::get("https://api.aladhan.com/v1/timingsByCity/{$date}", [
                'city' => $settings->city,
                'country' => $settings->country,
                'method' => $settings->method,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']['timings'])) {
                    $timings = $data['data']['timings'];
                    
                    return [
                        'subuh' => $this->formatTime($timings['Fajr']),
                        'syuruk' => $this->formatTime($timings['Sunrise']),
                        'zohor' => $this->formatTime($timings['Dhuhr']),
                        'asar' => $this->formatTime($timings['Asr']),
                        'maghrib' => $this->formatTime($timings['Maghrib']),
                        'isyak' => $this->formatTime($timings['Isha']),
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Aladhan API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Format time string (remove timezone info)
     */
    private function formatTime($time)
    {
        return substr(explode(' ', $time)[0], 0, 5);
    }
}
