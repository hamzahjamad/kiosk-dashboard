<?php

namespace App\Http\Controllers;

use App\Models\WeatherSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    /**
     * Get current weather data for dashboard
     */
    public function current()
    {
        $settings = WeatherSetting::getSettings();

        // Check if we have valid cached data
        if ($settings->isCacheValid()) {
            return response()->json([
                'success' => true,
                'weather' => $this->formatWeather($settings->cached_weather, $settings),
                'cached' => true,
            ]);
        }

        // Fetch fresh data
        $weather = $this->fetchFromApi($settings);

        if ($weather) {
            return response()->json([
                'success' => true,
                'weather' => $this->formatWeather($weather, $settings),
                'cached' => false,
            ]);
        }

        // Return cached data even if expired, or error
        if ($settings->cached_weather) {
            return response()->json([
                'success' => true,
                'weather' => $this->formatWeather($settings->cached_weather, $settings),
                'cached' => true,
                'stale' => true,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to fetch weather data',
        ]);
    }

    /**
     * Get weather settings
     */
    public function settings()
    {
        $settings = WeatherSetting::getSettings();

        return response()->json([
            'success' => true,
            'settings' => $settings,
            'units' => WeatherSetting::getUnits(),
        ]);
    }

    /**
     * Update weather settings
     */
    public function updateSettings(Request $request)
    {
        $settings = WeatherSetting::getSettings();

        $settings->update([
            'city' => $request->input('city', $settings->city),
            'country' => $request->input('country', $settings->country),
            'unit' => $request->input('unit', $settings->unit),
            'show_temperature' => $request->input('show_temperature', $settings->show_temperature),
            'show_description' => $request->input('show_description', $settings->show_description),
            'show_humidity' => $request->input('show_humidity', $settings->show_humidity),
            'show_wind' => $request->input('show_wind', $settings->show_wind),
        ]);

        // Clear cache when location changes
        if ($request->has('city') || $request->has('country')) {
            $settings->update([
                'cached_weather' => null,
                'cached_at' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'settings' => $settings->fresh(),
        ]);
    }

    /**
     * Force refresh weather data
     */
    public function refresh()
    {
        $settings = WeatherSetting::getSettings();

        // Clear cache
        $settings->update([
            'cached_weather' => null,
            'cached_at' => null,
        ]);

        $weather = $this->fetchFromApi($settings);

        if ($weather) {
            return response()->json([
                'success' => true,
                'message' => 'Weather refreshed successfully',
                'weather' => $this->formatWeather($weather, $settings),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to fetch weather data',
        ]);
    }

    /**
     * Fetch weather from wttr.in API
     */
    private function fetchFromApi(WeatherSetting $settings)
    {
        try {
            $location = urlencode("{$settings->city},{$settings->country}");
            $response = Http::timeout(10)->get("https://wttr.in/{$location}?format=j1");

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current_condition'][0] ?? null;

                if ($current) {
                    $weather = [
                        'temp_c' => $current['temp_C'],
                        'temp_f' => $current['temp_F'],
                        'description' => $current['weatherDesc'][0]['value'] ?? 'Unknown',
                        'humidity' => $current['humidity'],
                        'wind_kph' => $current['windspeedKmph'],
                        'wind_mph' => $current['windspeedMiles'],
                        'feels_like_c' => $current['FeelsLikeC'],
                        'feels_like_f' => $current['FeelsLikeF'],
                        'weather_code' => $current['weatherCode'],
                    ];

                    // Cache the result
                    $settings->update([
                        'cached_weather' => $weather,
                        'cached_at' => now(),
                    ]);

                    return $weather;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Weather API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Format weather data based on settings
     */
    private function formatWeather($weather, WeatherSetting $settings)
    {
        $unit = $settings->unit;

        return [
            'temperature' => $unit === 'celsius' ? $weather['temp_c'] : $weather['temp_f'],
            'unit' => $unit === 'celsius' ? '°C' : '°F',
            'description' => $weather['description'],
            'humidity' => $weather['humidity'] . '%',
            'wind' => $unit === 'celsius' 
                ? $weather['wind_kph'] . ' km/h' 
                : $weather['wind_mph'] . ' mph',
            'feels_like' => $unit === 'celsius' ? $weather['feels_like_c'] : $weather['feels_like_f'],
            'show_temperature' => $settings->show_temperature,
            'show_description' => $settings->show_description,
            'show_humidity' => $settings->show_humidity,
            'show_wind' => $settings->show_wind,
        ];
    }
}
