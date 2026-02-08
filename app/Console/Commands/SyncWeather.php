<?php

namespace App\Console\Commands;

use App\Models\WeatherSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync weather data from wttr.in API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing weather data...');

        $settings = WeatherSetting::getSettings();

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

                    $settings->update([
                        'cached_weather' => $weather,
                        'cached_at' => now(),
                    ]);

                    $this->info("Weather synced: {$weather['temp_c']}°C, {$weather['description']}");
                    return Command::SUCCESS;
                }
            }

            $this->error('Failed to fetch weather data');
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
